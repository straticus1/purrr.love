-- 🧬 Phase 2.1: Core Systems Database Schema
-- Core systems for evolution, genetics, and AI infrastructure

-- Genetic Profiles table
CREATE TABLE cat_genetic_profiles (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    genetic_markers JSONB NOT NULL DEFAULT '{}',
    trait_data JSONB NOT NULL DEFAULT '{}',
    mutation_history JSONB NOT NULL DEFAULT '[]',
    generation INTEGER DEFAULT 1,
    lineage_path TEXT[],
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Evolution Data table
CREATE TABLE cat_evolution_data (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    evolution_stage INTEGER DEFAULT 1,
    experience_points INTEGER DEFAULT 0,
    evolution_path JSONB NOT NULL DEFAULT '{}',
    adaptations JSONB NOT NULL DEFAULT '[]',
    mutation_rate FLOAT DEFAULT 0.01,
    fitness_score FLOAT DEFAULT 1.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Neural Network States table
CREATE TABLE cat_neural_networks (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    network_type VARCHAR(50) NOT NULL,
    network_state JSONB NOT NULL,
    weights JSONB NOT NULL,
    training_progress FLOAT DEFAULT 0,
    accuracy_metrics JSONB DEFAULT '{}',
    last_training TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Enhanced Learning Memory table
CREATE TABLE cat_learning_memory (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    memory_type VARCHAR(50) NOT NULL,
    memory_data JSONB NOT NULL,
    importance_score FLOAT DEFAULT 1.0,
    retention_strength FLOAT DEFAULT 1.0,
    last_accessed TIMESTAMP,
    decay_rate FLOAT DEFAULT 0.1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Evolution Events table
CREATE TABLE evolution_events (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    event_type VARCHAR(50) NOT NULL,
    event_data JSONB NOT NULL,
    impact_score FLOAT DEFAULT 1.0,
    triggered_mutations JSONB DEFAULT '[]',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Trait Inheritance Patterns table
CREATE TABLE trait_inheritance_patterns (
    id SERIAL PRIMARY KEY,
    trait_name VARCHAR(100) NOT NULL,
    inheritance_type VARCHAR(50) NOT NULL,
    gene_markers TEXT[] NOT NULL,
    dominance_factors JSONB NOT NULL,
    mutation_rates JSONB NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (trait_name)
);

-- Insert default trait inheritance patterns
INSERT INTO trait_inheritance_patterns 
(trait_name, inheritance_type, gene_markers, dominance_factors, mutation_rates) VALUES
('coat_color', 'complex', 
    ARRAY['color1', 'color2', 'color3'], 
    '{"black": 0.8, "white": 0.6, "orange": 0.7}',
    '{"base": 0.01, "modifier": 0.005}'
),
('eye_color', 'simple',
    ARRAY['eye1', 'eye2'],
    '{"blue": 0.5, "green": 0.6, "gold": 0.7}',
    '{"base": 0.008, "modifier": 0.003}'
),
('body_size', 'polygenic',
    ARRAY['size1', 'size2', 'size3', 'size4'],
    '{"large": 0.6, "medium": 0.7, "small": 0.5}',
    '{"base": 0.015, "modifier": 0.007}'
);

-- Create indexes for performance
CREATE INDEX idx_genetic_profiles_cat ON cat_genetic_profiles(cat_id);
CREATE INDEX idx_evolution_data_cat ON cat_evolution_data(cat_id);
CREATE INDEX idx_neural_networks_cat_type ON cat_neural_networks(cat_id, network_type);
CREATE INDEX idx_learning_memory_cat_type ON cat_learning_memory(cat_id, memory_type);
CREATE INDEX idx_evolution_events_cat ON evolution_events(cat_id);
CREATE INDEX idx_trait_inheritance_name ON trait_inheritance_patterns(trait_name);

-- Add constraints
ALTER TABLE cat_genetic_profiles 
ADD CONSTRAINT valid_mutation_rate 
CHECK (mutation_history::text != '[]' AND mutation_history::text != 'null');

ALTER TABLE cat_evolution_data
ADD CONSTRAINT valid_fitness
CHECK (fitness_score >= 0 AND fitness_score <= 10);

-- Create triggers for updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_genetic_profiles_timestamp
    BEFORE UPDATE ON cat_genetic_profiles
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_evolution_data_timestamp
    BEFORE UPDATE ON cat_evolution_data
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_neural_networks_timestamp
    BEFORE UPDATE ON cat_neural_networks
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_learning_memory_timestamp
    BEFORE UPDATE ON cat_learning_memory
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_trait_inheritance_timestamp
    BEFORE UPDATE ON trait_inheritance_patterns
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Functions for genetic processing
CREATE OR REPLACE FUNCTION process_genetic_inheritance(
    parent1_id INTEGER,
    parent2_id INTEGER
) RETURNS JSONB AS $$
DECLARE
    parent1_genes JSONB;
    parent2_genes JSONB;
    child_genes JSONB;
BEGIN
    -- Get parent genes
    SELECT genetic_markers INTO parent1_genes
    FROM cat_genetic_profiles
    WHERE cat_id = parent1_id;
    
    SELECT genetic_markers INTO parent2_genes
    FROM cat_genetic_profiles
    WHERE cat_id = parent2_id;
    
    -- Process inheritance (simplified example)
    child_genes = jsonb_build_object(
        'inherited_markers',
        jsonb_build_array(
            parent1_genes->'primary_markers',
            parent2_genes->'primary_markers'
        )
    );
    
    RETURN child_genes;
END;
$$ LANGUAGE plpgsql;

-- Function to calculate fitness score
CREATE OR REPLACE FUNCTION calculate_fitness_score(
    genetic_data JSONB,
    evolution_data JSONB
) RETURNS FLOAT AS $$
DECLARE
    base_score FLOAT := 1.0;
    genetic_factor FLOAT := 1.0;
    evolution_factor FLOAT := 1.0;
BEGIN
    -- Calculate genetic contribution
    IF genetic_data ? 'fitness_markers' THEN
        genetic_factor := genetic_factor * (genetic_data->'fitness_markers'->>'value')::float;
    END IF;
    
    -- Calculate evolution contribution
    IF evolution_data ? 'adaptations' THEN
        evolution_factor := evolution_factor * (evolution_data->'adaptations'->>'efficiency')::float;
    END IF;
    
    RETURN base_score * genetic_factor * evolution_factor;
END;
$$ LANGUAGE plpgsql;

-- Function to handle evolution events
CREATE OR REPLACE FUNCTION process_evolution_event(
    p_cat_id INTEGER,
    p_event_type VARCHAR,
    p_event_data JSONB
) RETURNS JSONB AS $$
DECLARE
    current_evolution_data JSONB;
    event_impact FLOAT;
    mutation_triggered BOOLEAN := false;
BEGIN
    -- Get current evolution data
    SELECT evolution_path INTO current_evolution_data
    FROM cat_evolution_data
    WHERE cat_id = p_cat_id;
    
    -- Calculate event impact
    event_impact := (p_event_data->>'intensity')::float * 
                    (p_event_data->>'duration')::float;
    
    -- Check for mutation trigger
    IF event_impact > 0.8 THEN
        mutation_triggered := true;
    END IF;
    
    -- Record event
    INSERT INTO evolution_events (
        cat_id,
        event_type,
        event_data,
        impact_score,
        triggered_mutations
    ) VALUES (
        p_cat_id,
        p_event_type,
        p_event_data,
        event_impact,
        CASE WHEN mutation_triggered 
            THEN jsonb_build_array(jsonb_build_object('type', 'event_triggered', 'impact', event_impact))
            ELSE '[]'::jsonb
        END
    );
    
    RETURN jsonb_build_object(
        'event_processed', true,
        'impact_score', event_impact,
        'mutation_triggered', mutation_triggered
    );
END;
$$ LANGUAGE plpgsql;

-- Grant permissions
GRANT SELECT, INSERT, UPDATE ON ALL TABLES IN SCHEMA public TO purrr_user;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO purrr_user;
