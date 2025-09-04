-- 🐱 Purrr.love Enhanced Cat Needs & Personality Schema (MySQL)
-- Comprehensive needs tracking and personality management system

-- Enhanced Cats Table with Personality and Needs Columns
ALTER TABLE cats ADD COLUMN IF NOT EXISTS personality_type VARCHAR(50) DEFAULT 'the_gentle_giant';
ALTER TABLE cats ADD COLUMN IF NOT EXISTS needs_satisfaction_score DECIMAL(3,2) DEFAULT 0.50;
ALTER TABLE cats ADD COLUMN IF NOT EXISTS last_needs_assessment TIMESTAMP NULL;
ALTER TABLE cats ADD COLUMN IF NOT EXISTS personality_confidence DECIMAL(3,2) DEFAULT 0.50;

-- Cat Needs Tracking Table
CREATE TABLE IF NOT EXISTS cat_needs_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    need_category ENUM('physical_needs', 'mental_needs', 'social_needs', 'emotional_needs') NOT NULL,
    need_type VARCHAR(100) NOT NULL,
    fulfillment_level DECIMAL(3,2) NOT NULL, -- 0.00 to 1.00
    notes TEXT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    recorded_by ENUM('user', 'system', 'ai_analysis') DEFAULT 'user',
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_needs_tracking_cat (cat_id),
    INDEX idx_needs_tracking_category (need_category),
    INDEX idx_needs_tracking_type (need_type),
    INDEX idx_needs_tracking_date (recorded_at)
) ENGINE=InnoDB;

-- Cat Personality Assessment Table
CREATE TABLE IF NOT EXISTS cat_personality_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    assessment_type ENUM('initial', 'periodic', 'behavioral_update', 'manual') DEFAULT 'initial',
    personality_type VARCHAR(50) NOT NULL,
    confidence_score DECIMAL(3,2) NOT NULL,
    trait_scores JSON,
    assessment_factors JSON,
    notes TEXT,
    assessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assessed_by ENUM('ai_system', 'user', 'veterinarian') DEFAULT 'ai_system',
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_personality_assessments_cat (cat_id),
    INDEX idx_personality_assessments_type (personality_type),
    INDEX idx_personality_assessments_date (assessed_at)
) ENGINE=InnoDB;

-- Cat Care Activities Table
CREATE TABLE IF NOT EXISTS cat_care_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    activity_type ENUM('feeding', 'play', 'grooming', 'exercise', 'social_interaction', 'mental_stimulation', 'health_check', 'training') NOT NULL,
    activity_name VARCHAR(255) NOT NULL,
    duration_minutes INT DEFAULT 0,
    intensity ENUM('low', 'medium', 'high') DEFAULT 'medium',
    satisfaction_rating DECIMAL(3,2) DEFAULT 0.50, -- Cat's apparent satisfaction
    notes TEXT,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_by ENUM('user', 'system', 'automated') DEFAULT 'user',
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_care_activities_cat (cat_id),
    INDEX idx_care_activities_type (activity_type),
    INDEX idx_care_activities_date (completed_at)
) ENGINE=InnoDB;

-- Cat Environment Setup Table
CREATE TABLE IF NOT EXISTS cat_environment_setup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    environment_type ENUM('indoor', 'outdoor', 'mixed', 'enclosed_outdoor') NOT NULL,
    space_size ENUM('small', 'medium', 'large', 'very_large') NOT NULL,
    has_climbing_structures BOOLEAN DEFAULT false,
    has_hiding_spots BOOLEAN DEFAULT false,
    has_elevated_perches BOOLEAN DEFAULT false,
    has_scratching_posts BOOLEAN DEFAULT false,
    has_interactive_toys BOOLEAN DEFAULT false,
    has_puzzle_feeders BOOLEAN DEFAULT false,
    has_quiet_zones BOOLEAN DEFAULT false,
    has_social_spaces BOOLEAN DEFAULT false,
    noise_level ENUM('very_quiet', 'quiet', 'moderate', 'loud', 'very_loud') DEFAULT 'moderate',
    lighting_quality ENUM('poor', 'fair', 'good', 'excellent') DEFAULT 'good',
    temperature_control BOOLEAN DEFAULT false,
    air_quality ENUM('poor', 'fair', 'good', 'excellent') DEFAULT 'good',
    setup_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_environment_setup_cat (cat_id),
    INDEX idx_environment_setup_type (environment_type)
) ENGINE=InnoDB;

-- Cat Health & Wellness Tracking Table
CREATE TABLE IF NOT EXISTS cat_health_wellness (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    health_metric ENUM('weight', 'body_condition', 'coat_condition', 'dental_health', 'energy_level', 'appetite', 'hydration', 'mobility', 'mental_alertness', 'social_behavior') NOT NULL,
    metric_value DECIMAL(5,2) NOT NULL,
    metric_scale ENUM('1-5', '1-10', 'percentage', 'weight_kg', 'temperature_c') NOT NULL,
    notes TEXT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    recorded_by ENUM('user', 'veterinarian', 'ai_system') DEFAULT 'user',
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_health_wellness_cat (cat_id),
    INDEX idx_health_wellness_metric (health_metric),
    INDEX idx_health_wellness_date (recorded_at)
) ENGINE=InnoDB;

-- Cat Behavioral Patterns Table
CREATE TABLE IF NOT EXISTS cat_behavioral_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    pattern_type ENUM('daily_routine', 'sleep_pattern', 'eating_pattern', 'play_pattern', 'social_pattern', 'stress_response', 'communication_style') NOT NULL,
    pattern_data JSON NOT NULL,
    confidence_score DECIMAL(3,2) DEFAULT 0.50,
    observation_period_days INT DEFAULT 7,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_behavioral_patterns_cat (cat_id),
    INDEX idx_behavioral_patterns_type (pattern_type),
    INDEX idx_behavioral_patterns_date (recorded_at)
) ENGINE=InnoDB;

-- Cat Care Recommendations Table
CREATE TABLE IF NOT EXISTS cat_care_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    recommendation_type ENUM('immediate_priority', 'daily_care', 'weekly_care', 'environmental_setup', 'behavioral_tip', 'warning_sign', 'health_concern') NOT NULL,
    recommendation_text TEXT NOT NULL,
    priority_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    category ENUM('physical', 'mental', 'social', 'emotional', 'environmental', 'health') NOT NULL,
    is_implemented BOOLEAN DEFAULT false,
    implementation_date TIMESTAMP NULL,
    effectiveness_rating DECIMAL(3,2) NULL, -- User rating of effectiveness
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    generated_by ENUM('ai_system', 'veterinarian', 'user') DEFAULT 'ai_system',
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_care_recommendations_cat (cat_id),
    INDEX idx_care_recommendations_type (recommendation_type),
    INDEX idx_care_recommendations_priority (priority_level),
    INDEX idx_care_recommendations_implemented (is_implemented)
) ENGINE=InnoDB;

-- Cat Social Dynamics Table
CREATE TABLE IF NOT EXISTS cat_social_dynamics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    interaction_type ENUM('human_family', 'other_cats', 'dogs', 'children', 'visitors', 'veterinarian', 'groomer') NOT NULL,
    interaction_quality ENUM('excellent', 'good', 'neutral', 'poor', 'stressful') NOT NULL,
    interaction_frequency ENUM('daily', 'weekly', 'monthly', 'rarely', 'never') NOT NULL,
    comfort_level DECIMAL(3,2) DEFAULT 0.50, -- 0.00 to 1.00
    stress_indicators JSON,
    positive_behaviors JSON,
    notes TEXT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_social_dynamics_cat (cat_id),
    INDEX idx_social_dynamics_type (interaction_type),
    INDEX idx_social_dynamics_quality (interaction_quality)
) ENGINE=InnoDB;

-- Cat Enrichment Activities Table
CREATE TABLE IF NOT EXISTS cat_enrichment_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    activity_name VARCHAR(255) NOT NULL,
    activity_category ENUM('physical', 'mental', 'social', 'sensory', 'hunting_simulation', 'exploration', 'rest') NOT NULL,
    duration_minutes INT DEFAULT 0,
    engagement_level ENUM('low', 'medium', 'high', 'very_high') DEFAULT 'medium',
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'intermediate',
    equipment_needed JSON,
    setup_time_minutes INT DEFAULT 0,
    safety_notes TEXT,
    effectiveness_rating DECIMAL(3,2) DEFAULT 0.50,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    performed_by ENUM('user', 'automated', 'system') DEFAULT 'user',
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_enrichment_activities_cat (cat_id),
    INDEX idx_enrichment_activities_category (activity_category),
    INDEX idx_enrichment_activities_date (performed_at)
) ENGINE=InnoDB;

-- Cat Stress Indicators Table
CREATE TABLE IF NOT EXISTS cat_stress_indicators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    stress_type ENUM('environmental', 'social', 'health', 'behavioral', 'routine_change', 'separation') NOT NULL,
    indicator_type ENUM('physical', 'behavioral', 'emotional', 'physiological') NOT NULL,
    severity_level ENUM('mild', 'moderate', 'severe', 'critical') NOT NULL,
    description TEXT NOT NULL,
    duration_hours INT DEFAULT 0,
    triggers JSON,
    coping_strategies JSON,
    resolution_status ENUM('ongoing', 'resolved', 'improving', 'worsening') DEFAULT 'ongoing',
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_stress_indicators_cat (cat_id),
    INDEX idx_stress_indicators_type (stress_type),
    INDEX idx_stress_indicators_severity (severity_level),
    INDEX idx_stress_indicators_status (resolution_status)
) ENGINE=InnoDB;

-- Insert default personality types and their characteristics
INSERT IGNORE INTO cat_personality_assessments (cat_id, personality_type, confidence_score, trait_scores, assessment_factors, assessed_by) 
SELECT 
    id,
    'the_gentle_giant',
    0.75,
    JSON_OBJECT(
        'energy_level', 'low_to_medium',
        'social_preference', 'moderate',
        'independence_level', 'medium',
        'activity_preference', 'calm',
        'noise_tolerance', 'low'
    ),
    JSON_OBJECT(
        'breed', breed,
        'age', age,
        'health_status', health_status,
        'assessment_method', 'default_assignment'
    ),
    'ai_system'
FROM cats 
WHERE personality_type IS NULL OR personality_type = '';

-- Create triggers for automatic needs tracking
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS update_needs_on_behavior
AFTER INSERT ON cat_behavior_observations
FOR EACH ROW
BEGIN
    -- Update mental stimulation needs based on behavior
    IF NEW.behavior_type IN ('play', 'explore', 'hunt') THEN
        INSERT INTO cat_needs_tracking (cat_id, need_category, need_type, fulfillment_level, recorded_by)
        VALUES (NEW.cat_id, 'mental_needs', 'stimulation', 
                CASE NEW.behavior_intensity 
                    WHEN 'low' THEN 0.3
                    WHEN 'medium' THEN 0.6
                    WHEN 'high' THEN 0.9
                    ELSE 0.5
                END, 'system');
    END IF;
    
    -- Update social needs based on behavior
    IF NEW.behavior_type = 'socialize' THEN
        INSERT INTO cat_needs_tracking (cat_id, need_category, need_type, fulfillment_level, recorded_by)
        VALUES (NEW.cat_id, 'social_needs', 'interaction', 
                CASE NEW.behavior_intensity 
                    WHEN 'low' THEN 0.4
                    WHEN 'medium' THEN 0.7
                    WHEN 'high' THEN 1.0
                    ELSE 0.5
                END, 'system');
    END IF;
    
    -- Update physical needs based on behavior
    IF NEW.behavior_type IN ('play', 'hunt', 'explore') THEN
        INSERT INTO cat_needs_tracking (cat_id, need_category, need_type, fulfillment_level, recorded_by)
        VALUES (NEW.cat_id, 'physical_needs', 'exercise', 
                CASE NEW.behavior_intensity 
                    WHEN 'low' THEN 0.3
                    WHEN 'medium' THEN 0.6
                    WHEN 'high' THEN 0.9
                    ELSE 0.5
                END, 'system');
    END IF;
END$$

CREATE TRIGGER IF NOT EXISTS update_needs_on_care_activity
AFTER INSERT ON cat_care_activities
FOR EACH ROW
BEGIN
    -- Map care activities to needs categories
    CASE NEW.activity_type
        WHEN 'feeding' THEN
            INSERT INTO cat_needs_tracking (cat_id, need_category, need_type, fulfillment_level, recorded_by)
            VALUES (NEW.cat_id, 'physical_needs', 'nutrition', NEW.satisfaction_rating, 'user');
            
        WHEN 'play' THEN
            INSERT INTO cat_needs_tracking (cat_id, need_category, need_type, fulfillment_level, recorded_by)
            VALUES (NEW.cat_id, 'mental_needs', 'stimulation', NEW.satisfaction_rating, 'user');
            
        WHEN 'exercise' THEN
            INSERT INTO cat_needs_tracking (cat_id, need_category, need_type, fulfillment_level, recorded_by)
            VALUES (NEW.cat_id, 'physical_needs', 'exercise', NEW.satisfaction_rating, 'user');
            
        WHEN 'social_interaction' THEN
            INSERT INTO cat_needs_tracking (cat_id, need_category, need_type, fulfillment_level, recorded_by)
            VALUES (NEW.cat_id, 'social_needs', 'interaction', NEW.satisfaction_rating, 'user');
            
        WHEN 'grooming' THEN
            INSERT INTO cat_needs_tracking (cat_id, need_category, need_type, fulfillment_level, recorded_by)
            VALUES (NEW.cat_id, 'physical_needs', 'grooming', NEW.satisfaction_rating, 'user');
    END CASE;
END$$

DELIMITER ;

-- Create views for easy data access
CREATE OR REPLACE VIEW cat_personality_summary AS
SELECT 
    c.id,
    c.name,
    c.breed,
    c.age,
    c.personality_type,
    c.needs_satisfaction_score,
    c.last_needs_assessment,
    c.personality_confidence,
    cpa.confidence_score as assessment_confidence,
    cpa.assessed_at as last_assessment_date
FROM cats c
LEFT JOIN cat_personality_assessments cpa ON c.id = cpa.cat_id
WHERE cpa.assessed_at = (
    SELECT MAX(assessed_at) 
    FROM cat_personality_assessments cpa2 
    WHERE cpa2.cat_id = c.id
);

CREATE OR REPLACE VIEW recent_needs_tracking AS
SELECT 
    cat_id,
    need_category,
    need_type,
    AVG(fulfillment_level) as avg_fulfillment,
    COUNT(*) as tracking_count,
    MAX(recorded_at) as last_recorded
FROM cat_needs_tracking
WHERE recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY cat_id, need_category, need_type
ORDER BY cat_id, need_category, avg_fulfillment DESC;

CREATE OR REPLACE VIEW cat_care_summary AS
SELECT 
    c.id,
    c.name,
    c.personality_type,
    c.needs_satisfaction_score,
    COUNT(cca.id) as total_care_activities,
    AVG(cca.satisfaction_rating) as avg_satisfaction,
    MAX(cca.completed_at) as last_care_activity
FROM cats c
LEFT JOIN cat_care_activities cca ON c.id = cca.cat_id
WHERE cca.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY c.id, c.name, c.personality_type, c.needs_satisfaction_score;

-- Insert sample enrichment activities
INSERT IGNORE INTO cat_enrichment_activities (cat_id, activity_name, activity_category, duration_minutes, engagement_level, difficulty_level, equipment_needed, setup_time_minutes, safety_notes, effectiveness_rating, performed_by) 
SELECT 
    id,
    'Interactive Puzzle Feeder',
    'mental',
    30,
    'high',
    'intermediate',
    JSON_OBJECT('equipment', 'puzzle_feeder', 'food', 'dry_kibble'),
    5,
    'Monitor for frustration, ensure food is accessible',
    0.85,
    'user'
FROM cats 
WHERE id IN (SELECT id FROM cats LIMIT 5);

-- Insert sample care recommendations
INSERT IGNORE INTO cat_care_recommendations (cat_id, recommendation_type, recommendation_text, priority_level, category, generated_by) 
SELECT 
    id,
    'daily_care',
    'Provide 15-20 minutes of interactive play daily to meet exercise and mental stimulation needs',
    'high',
    'physical',
    'ai_system'
FROM cats 
WHERE personality_type = 'the_energetic_explorer';

INSERT IGNORE INTO cat_care_recommendations (cat_id, recommendation_type, recommendation_text, priority_level, category, generated_by) 
SELECT 
    id,
    'environmental_setup',
    'Create quiet, comfortable resting areas away from high-traffic zones',
    'high',
    'environmental',
    'ai_system'
FROM cats 
WHERE personality_type = 'the_anxious_angel';
