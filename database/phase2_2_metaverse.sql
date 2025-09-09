-- 🌌 Phase 2.2: Metaverse and VR Database Schema
-- Virtual environments, physics, and interactions

-- Virtual Environments table
CREATE TABLE virtual_environments (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type VARCHAR(50) NOT NULL,
    difficulty INTEGER DEFAULT 1,
    physics_config JSONB NOT NULL DEFAULT '{}',
    weather_enabled BOOLEAN DEFAULT true,
    time_cycle_enabled BOOLEAN DEFAULT true,
    capacity INTEGER DEFAULT 10,
    creator_id INTEGER REFERENCES users(id),
    is_template BOOLEAN DEFAULT false,
    parent_template_id INTEGER REFERENCES virtual_environments(id),
    status VARCHAR(20) DEFAULT 'active',
    metadata JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Environment Objects table
CREATE TABLE environment_objects (
    id SERIAL PRIMARY KEY,
    environment_id INTEGER REFERENCES virtual_environments(id) ON DELETE CASCADE,
    object_type VARCHAR(50) NOT NULL,
    name VARCHAR(100),
    position JSONB NOT NULL, -- {x: 0, y: 0, z: 0}
    rotation JSONB NOT NULL, -- {x: 0, y: 0, z: 0}
    scale JSONB NOT NULL, -- {x: 1, y: 1, z: 1}
    physics_properties JSONB,
    interaction_type VARCHAR(50),
    interaction_data JSONB,
    collision_mesh TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Physics Configurations table
CREATE TABLE physics_configurations (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    gravity JSONB NOT NULL DEFAULT '{"x": 0, "y": -9.81, "z": 0}',
    air_resistance FLOAT DEFAULT 0.1,
    friction FLOAT DEFAULT 0.5,
    restitution FLOAT DEFAULT 0.3,
    collision_config JSONB,
    simulation_rate INTEGER DEFAULT 60,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- VR Sessions table
CREATE TABLE vr_sessions (
    id SERIAL PRIMARY KEY,
    environment_id INTEGER REFERENCES virtual_environments(id),
    cat_id INTEGER REFERENCES cats(id),
    user_id INTEGER REFERENCES users(id),
    session_type VARCHAR(50) NOT NULL,
    start_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP WITH TIME ZONE,
    duration INTEGER, -- in seconds
    interaction_count INTEGER DEFAULT 0,
    physics_events_count INTEGER DEFAULT 0,
    performance_metrics JSONB,
    session_data JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- VR Interactions table
CREATE TABLE vr_interactions (
    id SERIAL PRIMARY KEY,
    session_id INTEGER REFERENCES vr_sessions(id),
    cat_id INTEGER REFERENCES cats(id),
    interaction_type VARCHAR(50) NOT NULL,
    target_type VARCHAR(50),
    target_id INTEGER,
    position JSONB,
    rotation JSONB,
    force FLOAT,
    duration FLOAT,
    result_data JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Environment Templates table
CREATE TABLE environment_templates (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    difficulty INTEGER DEFAULT 1,
    description TEXT,
    base_objects JSONB NOT NULL,
    weather_presets JSONB,
    time_cycle_config JSONB,
    physics_preset INTEGER REFERENCES physics_configurations(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Weather States table
CREATE TABLE weather_states (
    id SERIAL PRIMARY KEY,
    environment_id INTEGER REFERENCES virtual_environments(id),
    weather_type VARCHAR(50) NOT NULL,
    intensity FLOAT DEFAULT 1.0,
    temperature FLOAT,
    humidity FLOAT,
    wind_speed FLOAT,
    wind_direction JSONB,
    particles_enabled BOOLEAN DEFAULT true,
    affects_physics BOOLEAN DEFAULT true,
    start_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Time Cycles table
CREATE TABLE time_cycles (
    id SERIAL PRIMARY KEY,
    environment_id INTEGER REFERENCES virtual_environments(id),
    cycle_speed FLOAT DEFAULT 1.0, -- 1.0 = real-time
    day_length INTEGER DEFAULT 1440, -- in minutes
    current_time TIME DEFAULT '12:00:00',
    affects_lighting BOOLEAN DEFAULT true,
    affects_behavior BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- VR Training Scenarios table
CREATE TABLE vr_training_scenarios (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    difficulty INTEGER DEFAULT 1,
    environment_template_id INTEGER REFERENCES environment_templates(id),
    objectives JSONB NOT NULL,
    success_criteria JSONB NOT NULL,
    reward_config JSONB,
    max_duration INTEGER, -- in seconds
    required_level INTEGER DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Multi-cat Interactions table
CREATE TABLE vr_social_interactions (
    id SERIAL PRIMARY KEY,
    session_id INTEGER REFERENCES vr_sessions(id),
    initiator_cat_id INTEGER REFERENCES cats(id),
    target_cat_id INTEGER REFERENCES cats(id),
    interaction_type VARCHAR(50) NOT NULL,
    start_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP WITH TIME ZONE,
    duration INTEGER,
    success_rate FLOAT,
    interaction_data JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Environment Statistics table
CREATE TABLE environment_statistics (
    id SERIAL PRIMARY KEY,
    environment_id INTEGER REFERENCES virtual_environments(id),
    total_visits INTEGER DEFAULT 0,
    total_time INTEGER DEFAULT 0, -- in seconds
    average_duration INTEGER DEFAULT 0,
    peak_concurrent_users INTEGER DEFAULT 0,
    popular_objects JSONB,
    interaction_heatmap JSONB,
    performance_metrics JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for performance
CREATE INDEX idx_venv_type ON virtual_environments(type);
CREATE INDEX idx_venv_status ON virtual_environments(status);
CREATE INDEX idx_venv_template ON virtual_environments(is_template);
CREATE INDEX idx_env_objects_type ON environment_objects(object_type);
CREATE INDEX idx_env_objects_env ON environment_objects(environment_id);
CREATE INDEX idx_vr_sessions_cat ON vr_sessions(cat_id);
CREATE INDEX idx_vr_sessions_user ON vr_sessions(user_id);
CREATE INDEX idx_vr_sessions_env ON vr_sessions(environment_id);
CREATE INDEX idx_vr_interactions_session ON vr_interactions(session_id);
CREATE INDEX idx_vr_interactions_cat ON vr_interactions(cat_id);
CREATE INDEX idx_weather_env ON weather_states(environment_id);
CREATE INDEX idx_time_cycles_env ON time_cycles(environment_id);
CREATE INDEX idx_vr_social_session ON vr_social_interactions(session_id);
CREATE INDEX idx_env_stats_env ON environment_statistics(environment_id);

-- Create triggers for updated_at timestamps
CREATE TRIGGER update_virtual_environments_timestamp
    BEFORE UPDATE ON virtual_environments
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_environment_objects_timestamp
    BEFORE UPDATE ON environment_objects
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_physics_config_timestamp
    BEFORE UPDATE ON physics_configurations
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_env_templates_timestamp
    BEFORE UPDATE ON environment_templates
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_time_cycles_timestamp
    BEFORE UPDATE ON time_cycles
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_training_scenarios_timestamp
    BEFORE UPDATE ON vr_training_scenarios
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_env_stats_timestamp
    BEFORE UPDATE ON environment_statistics
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Insert default physics configurations
INSERT INTO physics_configurations 
(name, gravity, air_resistance, friction, restitution, collision_config, simulation_rate) 
VALUES
('default', '{"x": 0, "y": -9.81, "z": 0}', 0.1, 0.5, 0.3, 
 '{"continuous_detection": true, "sub_steps": 3}', 60),
('low_gravity', '{"x": 0, "y": -4.905, "z": 0}', 0.05, 0.3, 0.5,
 '{"continuous_detection": true, "sub_steps": 2}', 60),
('zero_gravity', '{"x": 0, "y": 0, "z": 0}', 0.01, 0.1, 0.8,
 '{"continuous_detection": true, "sub_steps": 1}', 30),
('water', '{"x": 0, "y": -2.0, "z": 0}', 0.8, 0.2, 0.1,
 '{"continuous_detection": true, "sub_steps": 4, "buoyancy": true}', 90);

-- Insert default environment templates
INSERT INTO environment_templates 
(name, category, difficulty, description, base_objects, weather_presets, time_cycle_config)
VALUES
('Cozy Home', 'indoor', 1, 'A comfortable home environment for cats',
 '{"furniture": ["couch", "bed", "cat_tree"], "toys": ["ball", "mouse", "laser"]}',
 '{"enabled": false}',
 '{"day_length": 1440, "lighting": true}'),
('Garden Adventure', 'outdoor', 2, 'An exciting garden with various activities',
 '{"nature": ["trees", "grass", "flowers"], "obstacles": ["fence", "rocks", "bushes"]}',
 '{"enabled": true, "types": ["sunny", "rainy", "cloudy"]}',
 '{"day_length": 1440, "lighting": true}'),
('Cat Cafe', 'social', 2, 'A social space for cats to interact',
 '{"furniture": ["tables", "chairs", "cat_beds"], "activities": ["feeding_area", "play_zone"]}',
 '{"enabled": false}',
 '{"day_length": 720, "lighting": true}'),
('Training Ground', 'training', 3, 'Special environment for skill development',
 '{"equipment": ["platforms", "tunnels", "targets"], "challenges": ["moving_targets", "puzzles"]}',
 '{"enabled": true, "types": ["clear"]}',
 '{"day_length": 360, "lighting": true}');

-- Create function to calculate environment statistics
CREATE OR REPLACE FUNCTION calculate_environment_statistics(p_environment_id INTEGER)
RETURNS VOID AS $$
DECLARE
    v_total_visits INTEGER;
    v_total_time INTEGER;
    v_avg_duration INTEGER;
    v_peak_users INTEGER;
BEGIN
    -- Calculate basic statistics
    SELECT 
        COUNT(*),
        COALESCE(SUM(duration), 0),
        COALESCE(AVG(duration), 0)::INTEGER
    INTO
        v_total_visits,
        v_total_time,
        v_avg_duration
    FROM vr_sessions
    WHERE environment_id = p_environment_id;

    -- Calculate peak concurrent users
    SELECT COALESCE(MAX(concurrent_users), 0)
    INTO v_peak_users
    FROM (
        SELECT COUNT(*) as concurrent_users
        FROM vr_sessions
        WHERE environment_id = p_environment_id
        AND end_time IS NULL
        GROUP BY DATE_TRUNC('hour', start_time)
    ) as hourly_stats;

    -- Update statistics
    INSERT INTO environment_statistics (
        environment_id,
        total_visits,
        total_time,
        average_duration,
        peak_concurrent_users,
        updated_at
    )
    VALUES (
        p_environment_id,
        v_total_visits,
        v_total_time,
        v_avg_duration,
        v_peak_users,
        CURRENT_TIMESTAMP
    )
    ON CONFLICT (environment_id) DO UPDATE
    SET
        total_visits = EXCLUDED.total_visits,
        total_time = EXCLUDED.total_time,
        average_duration = EXCLUDED.average_duration,
        peak_concurrent_users = EXCLUDED.peak_concurrent_users,
        updated_at = CURRENT_TIMESTAMP;
END;
$$ LANGUAGE plpgsql;

-- Create trigger to update environment statistics
CREATE OR REPLACE FUNCTION update_environment_statistics()
RETURNS TRIGGER AS $$
BEGIN
    PERFORM calculate_environment_statistics(NEW.environment_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_update_env_stats
    AFTER INSERT OR UPDATE ON vr_sessions
    FOR EACH ROW
    EXECUTE FUNCTION update_environment_statistics();

-- Grant permissions
GRANT SELECT, INSERT, UPDATE ON ALL TABLES IN SCHEMA public TO purrr_user;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO purrr_user;
