-- 🚀 Purrr.love Database Migration v2.2.0 - Comprehensive Enhancement
-- Revolutionary Cat Personality, Needs, AI Analysis, and Multi-Cat Management System
-- Migration Date: 2025-09-04
-- Version: 2.2.0

-- ============================================================================
-- MIGRATION METADATA
-- ============================================================================
CREATE TABLE IF NOT EXISTS schema_migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration_version VARCHAR(20) NOT NULL,
    migration_name VARCHAR(255) NOT NULL,
    migration_description TEXT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rollback_script TEXT,
    UNIQUE KEY unique_version (migration_version)
) ENGINE=InnoDB;

-- Record this migration
INSERT IGNORE INTO schema_migrations (migration_version, migration_name, migration_description) 
VALUES ('2.2.0', 'Comprehensive Cat Care Enhancement', 'Revolutionary personality system, advanced needs tracking, AI analysis, and multi-cat management');

-- ============================================================================
-- ENHANCED CATS TABLE MODIFICATIONS
-- ============================================================================
-- Add new personality and wellness columns
ALTER TABLE cats 
ADD COLUMN IF NOT EXISTS personality_type VARCHAR(50) DEFAULT 'the_gentle_giant',
ADD COLUMN IF NOT EXISTS needs_satisfaction_score DECIMAL(3,2) DEFAULT 0.50,
ADD COLUMN IF NOT EXISTS last_needs_assessment TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS personality_confidence DECIMAL(3,2) DEFAULT 0.50,
ADD COLUMN IF NOT EXISTS wellness_score DECIMAL(3,2) DEFAULT 0.75,
ADD COLUMN IF NOT EXISTS activity_level ENUM('very_low', 'low', 'medium', 'high', 'very_high') DEFAULT 'medium',
ADD COLUMN IF NOT EXISTS stress_level ENUM('minimal', 'low', 'moderate', 'high', 'severe') DEFAULT 'low',
ADD COLUMN IF NOT EXISTS socialization_score DECIMAL(3,2) DEFAULT 0.50,
ADD COLUMN IF NOT EXISTS last_activity_logged TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS photo_analysis_enabled BOOLEAN DEFAULT true,
ADD COLUMN IF NOT EXISTS reminder_preferences JSON;

-- Add indexes for performance
CREATE INDEX IF NOT EXISTS idx_cats_personality_type ON cats(personality_type);
CREATE INDEX IF NOT EXISTS idx_cats_needs_score ON cats(needs_satisfaction_score);
CREATE INDEX IF NOT EXISTS idx_cats_wellness_score ON cats(wellness_score);
CREATE INDEX IF NOT EXISTS idx_cats_activity_level ON cats(activity_level);

-- ============================================================================
-- ENHANCED NEEDS TRACKING SYSTEM
-- ============================================================================

-- Cat Needs Tracking Table (Enhanced)
CREATE TABLE IF NOT EXISTS cat_needs_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    need_category ENUM('physical_needs', 'mental_needs', 'social_needs', 'emotional_needs') NOT NULL,
    need_type VARCHAR(100) NOT NULL,
    fulfillment_level DECIMAL(3,2) NOT NULL, -- 0.00 to 1.00
    urgency_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    notes TEXT,
    location VARCHAR(100), -- Where the need was fulfilled
    weather_conditions VARCHAR(50), -- Environmental context
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    recorded_by ENUM('user', 'system', 'ai_analysis', 'sensor', 'photo_analysis') DEFAULT 'user',
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_needs_tracking_cat (cat_id),
    INDEX idx_needs_tracking_category (need_category),
    INDEX idx_needs_tracking_urgency (urgency_level),
    INDEX idx_needs_tracking_date (recorded_at)
) ENGINE=InnoDB;

-- Cat Personality Assessments (Enhanced)
CREATE TABLE IF NOT EXISTS cat_personality_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    assessment_type ENUM('initial', 'periodic', 'behavioral_update', 'manual', 'photo_analysis', 'ai_deep_scan') DEFAULT 'initial',
    personality_type VARCHAR(50) NOT NULL,
    confidence_score DECIMAL(3,2) NOT NULL,
    trait_scores JSON,
    assessment_factors JSON,
    environmental_context JSON,
    assessment_duration_minutes INT DEFAULT 5,
    photo_analysis_data JSON, -- For AI photo analysis results
    notes TEXT,
    assessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assessed_by ENUM('ai_system', 'user', 'veterinarian', 'photo_ai', 'behavioral_ai') DEFAULT 'ai_system',
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_personality_assessments_cat (cat_id),
    INDEX idx_personality_assessments_type (personality_type),
    INDEX idx_personality_assessments_method (assessed_by),
    INDEX idx_personality_assessments_date (assessed_at)
) ENGINE=InnoDB;

-- ============================================================================
-- ADVANCED AI & BEHAVIORAL ANALYSIS
-- ============================================================================

-- Enhanced Behavioral Observations
CREATE TABLE IF NOT EXISTS cat_behavior_observations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    behavior_type ENUM('play', 'rest', 'explore', 'socialize', 'groom', 'hunt', 'eat', 'sleep', 'vocalize', 'aggressive', 'submissive', 'curious', 'anxious', 'content', 'hiding', 'territorial', 'affectionate') NOT NULL,
    behavior_intensity ENUM('very_low', 'low', 'medium', 'high', 'very_high') DEFAULT 'medium',
    duration_minutes INT DEFAULT 1,
    environmental_context JSON,
    social_context JSON,
    triggers JSON, -- What triggered this behavior
    outcomes JSON, -- Results of the behavior
    photo_evidence VARCHAR(500), -- Path to photo/video evidence
    observed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observer_type ENUM('human', 'ai_system', 'sensor', 'camera_ai', 'smart_collar') DEFAULT 'human',
    confidence_score DECIMAL(3,2) DEFAULT 1.00,
    validated_by_user BOOLEAN DEFAULT false,
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_behavior_observations_cat (cat_id),
    INDEX idx_behavior_observations_type (behavior_type),
    INDEX idx_behavior_observations_intensity (behavior_intensity),
    INDEX idx_behavior_observations_date (observed_at)
) ENGINE=InnoDB;

-- Real-time Cat Status Table
CREATE TABLE IF NOT EXISTS cat_realtime_status (
    cat_id INT PRIMARY KEY,
    current_activity VARCHAR(100),
    current_location VARCHAR(100),
    current_mood ENUM('very_happy', 'happy', 'content', 'neutral', 'stressed', 'anxious', 'playful', 'sleepy', 'hungry', 'alert') DEFAULT 'content',
    energy_level_current DECIMAL(3,2) DEFAULT 0.50,
    last_feeding TIMESTAMP NULL,
    last_play_session TIMESTAMP NULL,
    last_social_interaction TIMESTAMP NULL,
    current_stress_level DECIMAL(3,2) DEFAULT 0.20,
    is_sleeping BOOLEAN DEFAULT false,
    is_eating BOOLEAN DEFAULT false,
    is_playing BOOLEAN DEFAULT false,
    temperature_celsius DECIMAL(4,2) NULL, -- From smart collar/sensor
    heart_rate_bpm INT NULL,
    activity_count_today INT DEFAULT 0,
    calories_consumed_today DECIMAL(6,2) DEFAULT 0,
    water_consumed_ml_today DECIMAL(6,2) DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_realtime_status_mood (current_mood),
    INDEX idx_realtime_status_activity (current_activity),
    INDEX idx_realtime_status_updated (last_updated)
) ENGINE=InnoDB;

-- ============================================================================
-- MULTI-CAT HOUSEHOLD MANAGEMENT
-- ============================================================================

-- Cat Households Table
CREATE TABLE IF NOT EXISTS cat_households (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    household_name VARCHAR(255) NOT NULL,
    total_cats INT DEFAULT 0,
    household_type ENUM('single_cat', 'multi_cat', 'foster_home', 'shelter', 'breeding') DEFAULT 'single_cat',
    dominant_cat_id INT NULL, -- The alpha cat
    social_harmony_score DECIMAL(3,2) DEFAULT 0.50,
    household_stress_level ENUM('very_low', 'low', 'moderate', 'high', 'very_high') DEFAULT 'low',
    establishment_date DATE NOT NULL,
    last_harmony_assessment TIMESTAMP NULL,
    notes TEXT,
    
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dominant_cat_id) REFERENCES cats(id) ON DELETE SET NULL,
    INDEX idx_households_owner (owner_id),
    INDEX idx_households_harmony (social_harmony_score)
) ENGINE=InnoDB;

-- Cat Household Memberships
CREATE TABLE IF NOT EXISTS cat_household_memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    household_id INT NOT NULL,
    cat_id INT NOT NULL,
    role_in_household ENUM('alpha', 'beta', 'omega', 'neutral', 'newcomer', 'senior', 'playmate') DEFAULT 'neutral',
    integration_status ENUM('well_integrated', 'integrating', 'struggling', 'isolated', 'aggressive') DEFAULT 'integrating',
    hierarchy_position INT DEFAULT 1,
    joined_household DATE NOT NULL,
    adaptation_score DECIMAL(3,2) DEFAULT 0.50,
    
    FOREIGN KEY (household_id) REFERENCES cat_households(id) ON DELETE CASCADE,
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cat_household (cat_id, household_id),
    INDEX idx_household_memberships_household (household_id),
    INDEX idx_household_memberships_cat (cat_id),
    INDEX idx_household_memberships_role (role_in_household)
) ENGINE=InnoDB;

-- Inter-Cat Relationships
CREATE TABLE IF NOT EXISTS cat_relationships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat1_id INT NOT NULL,
    cat2_id INT NOT NULL,
    relationship_type ENUM('best_friends', 'friends', 'neutral', 'competitors', 'avoid_each_other', 'aggressive', 'territorial', 'mother_child', 'siblings') DEFAULT 'neutral',
    compatibility_score DECIMAL(3,2) DEFAULT 0.50,
    interaction_frequency ENUM('constantly', 'very_often', 'often', 'sometimes', 'rarely', 'never') DEFAULT 'sometimes',
    play_compatibility DECIMAL(3,2) DEFAULT 0.50,
    food_sharing_comfort DECIMAL(3,2) DEFAULT 0.50,
    territory_sharing_comfort DECIMAL(3,2) DEFAULT 0.50,
    established_date DATE NOT NULL,
    last_interaction TIMESTAMP NULL,
    relationship_stability ENUM('very_stable', 'stable', 'fluctuating', 'unstable', 'volatile') DEFAULT 'stable',
    notes TEXT,
    
    FOREIGN KEY (cat1_id) REFERENCES cats(id) ON DELETE CASCADE,
    FOREIGN KEY (cat2_id) REFERENCES cats(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cat_pair (LEAST(cat1_id, cat2_id), GREATEST(cat1_id, cat2_id)),
    INDEX idx_relationships_compatibility (compatibility_score),
    INDEX idx_relationships_type (relationship_type)
) ENGINE=InnoDB;

-- ============================================================================
-- AI-POWERED PHOTO ANALYSIS
-- ============================================================================

-- Cat Photo Analysis Table
CREATE TABLE IF NOT EXISTS cat_photo_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    photo_path VARCHAR(500) NOT NULL,
    analysis_type ENUM('mood_detection', 'health_assessment', 'behavior_analysis', 'body_condition', 'facial_expression', 'posture_analysis') NOT NULL,
    ai_model_version VARCHAR(50) DEFAULT 'vision_v2.0',
    detected_mood ENUM('very_happy', 'happy', 'content', 'neutral', 'stressed', 'anxious', 'sick', 'playful', 'sleepy', 'alert', 'curious', 'frustrated') NULL,
    mood_confidence DECIMAL(3,2) DEFAULT 0.00,
    health_indicators JSON, -- Eyes, ears, coat, posture, etc.
    behavioral_indicators JSON, -- Body language, positioning, etc.
    facial_features_analysis JSON,
    body_condition_score DECIMAL(3,2) NULL, -- 1-9 scale converted to 0-1
    stress_indicators_detected JSON,
    positive_indicators_detected JSON,
    analysis_metadata JSON, -- Processing time, image quality, etc.
    analyzed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    human_validation JSON, -- User can validate/correct AI results
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_photo_analysis_cat (cat_id),
    INDEX idx_photo_analysis_mood (detected_mood),
    INDEX idx_photo_analysis_date (analyzed_at),
    INDEX idx_photo_analysis_type (analysis_type)
) ENGINE=InnoDB;

-- ============================================================================
-- SMART CARE REMINDER SYSTEM
-- ============================================================================

-- Care Reminder Templates
CREATE TABLE IF NOT EXISTS care_reminder_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(255) NOT NULL,
    reminder_type ENUM('feeding', 'play', 'grooming', 'health_check', 'social_time', 'exercise', 'mental_stimulation', 'medication', 'vet_appointment', 'custom') NOT NULL,
    personality_types JSON, -- Which personality types this applies to
    default_frequency_hours INT NOT NULL,
    importance_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    message_template TEXT NOT NULL,
    conditions JSON, -- When this reminder should trigger
    customization_options JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_reminder_templates_type (reminder_type),
    INDEX idx_reminder_templates_importance (importance_level)
) ENGINE=InnoDB;

-- Active Care Reminders
CREATE TABLE IF NOT EXISTS cat_care_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    template_id INT NOT NULL,
    reminder_title VARCHAR(255) NOT NULL,
    reminder_message TEXT NOT NULL,
    reminder_type ENUM('feeding', 'play', 'grooming', 'health_check', 'social_time', 'exercise', 'mental_stimulation', 'medication', 'vet_appointment', 'custom') NOT NULL,
    schedule_type ENUM('once', 'daily', 'weekly', 'monthly', 'custom_interval') NOT NULL,
    frequency_hours INT NOT NULL,
    next_due TIMESTAMP NOT NULL,
    priority_level ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    is_active BOOLEAN DEFAULT true,
    is_completed BOOLEAN DEFAULT false,
    completed_at TIMESTAMP NULL,
    snooze_until TIMESTAMP NULL,
    auto_generated BOOLEAN DEFAULT true, -- Generated by AI vs manual
    personalization_factors JSON, -- Cat-specific customizations
    effectiveness_rating DECIMAL(3,2) NULL, -- User feedback
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES care_reminder_templates(id) ON DELETE CASCADE,
    INDEX idx_care_reminders_cat (cat_id),
    INDEX idx_care_reminders_due (next_due),
    INDEX idx_care_reminders_priority (priority_level),
    INDEX idx_care_reminders_active (is_active)
) ENGINE=InnoDB;

-- ============================================================================
-- REAL-TIME MONITORING & WEBSOCKET SUPPORT
-- ============================================================================

-- Real-time Activity Stream
CREATE TABLE IF NOT EXISTS cat_activity_stream (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    activity_type ENUM('feeding', 'playing', 'sleeping', 'grooming', 'exploring', 'socializing', 'using_litter', 'drinking', 'vocalizing', 'climbing', 'hiding', 'sunbathing') NOT NULL,
    activity_subtype VARCHAR(100), -- Specific activity details
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NULL,
    duration_minutes INT NULL,
    intensity ENUM('very_low', 'low', 'medium', 'high', 'very_high') DEFAULT 'medium',
    location VARCHAR(100),
    companions JSON, -- Other cats/humans present
    environmental_conditions JSON,
    activity_quality ENUM('poor', 'fair', 'good', 'excellent') DEFAULT 'good',
    detection_method ENUM('manual', 'ai_camera', 'smart_collar', 'sensor', 'photo_analysis', 'pattern_recognition') DEFAULT 'manual',
    confidence_score DECIMAL(3,2) DEFAULT 1.00,
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_activity_stream_cat (cat_id),
    INDEX idx_activity_stream_type (activity_type),
    INDEX idx_activity_stream_start (start_time),
    INDEX idx_activity_stream_method (detection_method)
) ENGINE=InnoDB;

-- WebSocket Connection Registry
CREATE TABLE IF NOT EXISTS websocket_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    connection_id VARCHAR(100) NOT NULL UNIQUE,
    cat_ids JSON, -- Which cats this connection is monitoring
    connection_type ENUM('dashboard', 'mobile', 'realtime_monitor', 'alerts_only') DEFAULT 'dashboard',
    established_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_ping TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT true,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_websocket_user (user_id),
    INDEX idx_websocket_active (is_active),
    INDEX idx_websocket_ping (last_ping)
) ENGINE=InnoDB;

-- ============================================================================
-- PROGRESSIVE WEB APP (PWA) SUPPORT
-- ============================================================================

-- PWA User Preferences
CREATE TABLE IF NOT EXISTS user_pwa_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    notification_enabled BOOLEAN DEFAULT true,
    offline_mode_enabled BOOLEAN DEFAULT true,
    sync_frequency_minutes INT DEFAULT 15,
    dashboard_layout JSON,
    widget_preferences JSON,
    theme_preference ENUM('light', 'dark', 'auto', 'cat_themed') DEFAULT 'auto',
    language_preference VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'UTC',
    data_usage_preference ENUM('minimal', 'standard', 'full') DEFAULT 'standard',
    last_sync TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_pwa_preferences_user (user_id)
) ENGINE=InnoDB;

-- Offline Data Queue
CREATE TABLE IF NOT EXISTS offline_data_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    data_type ENUM('behavior_observation', 'needs_tracking', 'care_activity', 'photo_upload', 'reminder_completion') NOT NULL,
    data_payload JSON NOT NULL,
    created_offline_at TIMESTAMP NOT NULL,
    synced_at TIMESTAMP NULL,
    sync_status ENUM('pending', 'synced', 'failed', 'duplicate') DEFAULT 'pending',
    sync_attempts INT DEFAULT 0,
    error_message TEXT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_offline_queue_user (user_id),
    INDEX idx_offline_queue_status (sync_status),
    INDEX idx_offline_queue_type (data_type)
) ENGINE=InnoDB;

-- ============================================================================
-- ENHANCED ANALYTICS & INSIGHTS
-- ============================================================================

-- Cat Wellness Analytics
CREATE TABLE IF NOT EXISTS cat_wellness_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    analysis_period_start DATE NOT NULL,
    analysis_period_end DATE NOT NULL,
    overall_wellness_score DECIMAL(3,2) NOT NULL,
    physical_wellness_score DECIMAL(3,2) NOT NULL,
    mental_wellness_score DECIMAL(3,2) NOT NULL,
    social_wellness_score DECIMAL(3,2) NOT NULL,
    emotional_wellness_score DECIMAL(3,2) NOT NULL,
    activity_consistency_score DECIMAL(3,2) NOT NULL,
    needs_fulfillment_score DECIMAL(3,2) NOT NULL,
    stress_management_score DECIMAL(3,2) NOT NULL,
    improvement_areas JSON,
    strength_areas JSON,
    trend_analysis JSON,
    recommendations JSON,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cat_period (cat_id, analysis_period_start, analysis_period_end),
    INDEX idx_wellness_analytics_cat (cat_id),
    INDEX idx_wellness_analytics_period (analysis_period_start, analysis_period_end),
    INDEX idx_wellness_analytics_score (overall_wellness_score)
) ENGINE=InnoDB;

-- Predictive Health Alerts
CREATE TABLE IF NOT EXISTS cat_health_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT NOT NULL,
    prediction_type ENUM('health_decline', 'stress_increase', 'behavioral_change', 'social_conflict', 'routine_disruption', 'medical_attention_needed') NOT NULL,
    risk_level ENUM('very_low', 'low', 'moderate', 'high', 'very_high') NOT NULL,
    probability_score DECIMAL(3,2) NOT NULL,
    time_horizon_days INT NOT NULL, -- How far into future this predicts
    contributing_factors JSON,
    recommended_actions JSON,
    predicted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actual_outcome ENUM('prevented', 'occurred_as_predicted', 'occurred_differently', 'pending') DEFAULT 'pending',
    outcome_recorded_at TIMESTAMP NULL,
    accuracy_score DECIMAL(3,2) NULL, -- Calculated after validation
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_health_predictions_cat (cat_id),
    INDEX idx_health_predictions_risk (risk_level),
    INDEX idx_health_predictions_type (prediction_type),
    INDEX idx_health_predictions_date (predicted_at)
) ENGINE=InnoDB;

-- ============================================================================
-- ENHANCED EXISTING TABLES
-- ============================================================================

-- Add columns to existing tables for better integration
ALTER TABLE cat_care_activities 
ADD COLUMN IF NOT EXISTS environmental_conditions JSON,
ADD COLUMN IF NOT EXISTS photo_documentation VARCHAR(500),
ADD COLUMN IF NOT EXISTS ai_effectiveness_prediction DECIMAL(3,2),
ADD COLUMN IF NOT EXISTS actual_effectiveness_rating DECIMAL(3,2),
ADD COLUMN IF NOT EXISTS stress_level_before DECIMAL(3,2),
ADD COLUMN IF NOT EXISTS stress_level_after DECIMAL(3,2),
ADD COLUMN IF NOT EXISTS energy_level_before DECIMAL(3,2),
ADD COLUMN IF NOT EXISTS energy_level_after DECIMAL(3,2);

-- Add indexes for new columns
CREATE INDEX IF NOT EXISTS idx_care_activities_effectiveness ON cat_care_activities(actual_effectiveness_rating);
CREATE INDEX IF NOT EXISTS idx_care_activities_stress_impact ON cat_care_activities(stress_level_before, stress_level_after);

-- ============================================================================
-- SAMPLE DATA INSERTION
-- ============================================================================

-- Insert default care reminder templates
INSERT IGNORE INTO care_reminder_templates (template_name, reminder_type, personality_types, default_frequency_hours, importance_level, message_template, conditions) VALUES
('Energetic Explorer Play Time', 'play', '["the_energetic_explorer", "the_playful_prankster"]', 4, 'high', 'Time for an exciting play session with {cat_name}! Try interactive toys or laser pointer games.', '{"min_energy_level": 0.6}'),
('Gentle Giant Grooming', 'grooming', '["the_gentle_giant"]', 24, 'medium', '{cat_name} would enjoy some gentle brushing and grooming time.', '{"breed_needs_grooming": true}'),
('Social Butterfly Interaction', 'social_time', '["the_social_butterfly"]', 3, 'high', '{cat_name} is craving some quality social interaction and attention!', '{"alone_time_hours": 2}'),
('Anxious Angel Check-in', 'health_check', '["the_anxious_angel"]', 12, 'high', 'Time to check on {cat_name} and ensure they feel safe and secure.', '{"stress_level": "> 0.5"}'),
('Independent Thinker Mental Stimulation', 'mental_stimulation', '["the_independent_thinker", "the_wise_observer"]', 8, 'medium', '{cat_name} could use some puzzle feeders or problem-solving activities.', '{"boredom_indicators": true}');

-- Insert sample real-time status for existing cats
INSERT IGNORE INTO cat_realtime_status (cat_id, current_activity, current_mood, energy_level_current)
SELECT id, 'resting', 'content', 0.60 FROM cats LIMIT 10;

-- ============================================================================
-- ADVANCED TRIGGERS FOR AUTOMATION
-- ============================================================================

DELIMITER $$

-- Auto-update wellness scores based on activities
CREATE TRIGGER IF NOT EXISTS update_wellness_on_activity
AFTER INSERT ON cat_activity_stream
FOR EACH ROW
BEGIN
    DECLARE cat_personality VARCHAR(50);
    DECLARE base_wellness DECIMAL(3,2);
    
    SELECT personality_type, wellness_score INTO cat_personality, base_wellness 
    FROM cats WHERE id = NEW.cat_id;
    
    -- Calculate wellness impact based on activity and personality
    SET @wellness_change = CASE 
        WHEN cat_personality = 'the_energetic_explorer' AND NEW.activity_type IN ('playing', 'exploring') THEN 0.05
        WHEN cat_personality = 'the_social_butterfly' AND NEW.activity_type = 'socializing' THEN 0.04
        WHEN cat_personality = 'the_gentle_giant' AND NEW.activity_type IN ('grooming', 'sleeping') THEN 0.03
        WHEN cat_personality = 'the_anxious_angel' AND NEW.activity_type = 'hiding' THEN -0.02
        ELSE 0.01
    END;
    
    -- Update cat wellness score
    UPDATE cats 
    SET wellness_score = GREATEST(0, LEAST(1, wellness_score + @wellness_change)),
        last_activity_logged = NOW()
    WHERE id = NEW.cat_id;
    
    -- Update real-time status
    UPDATE cat_realtime_status 
    SET current_activity = NEW.activity_type,
        last_updated = NOW(),
        activity_count_today = activity_count_today + 1
    WHERE cat_id = NEW.cat_id;
END$$

-- Auto-generate health predictions
CREATE TRIGGER IF NOT EXISTS generate_health_predictions
AFTER UPDATE ON cats
FOR EACH ROW
BEGIN
    -- Generate health prediction if wellness score drops significantly
    IF NEW.wellness_score < OLD.wellness_score - 0.10 THEN
        INSERT INTO cat_health_predictions (cat_id, prediction_type, risk_level, probability_score, time_horizon_days, contributing_factors, recommended_actions)
        VALUES (
            NEW.id,
            'health_decline',
            CASE 
                WHEN NEW.wellness_score < 0.30 THEN 'very_high'
                WHEN NEW.wellness_score < 0.50 THEN 'high'
                WHEN NEW.wellness_score < 0.70 THEN 'moderate'
                ELSE 'low'
            END,
            1.00 - NEW.wellness_score,
            7,
            JSON_OBJECT('wellness_drop', OLD.wellness_score - NEW.wellness_score, 'current_score', NEW.wellness_score),
            JSON_OBJECT('recommendations', ['schedule_vet_checkup', 'monitor_behavior', 'increase_care_attention'])
        );
    END IF;
END$$

-- Auto-update household harmony
CREATE TRIGGER IF NOT EXISTS update_household_harmony
AFTER INSERT ON cat_relationships
FOR EACH ROW
BEGIN
    DECLARE household_id_val INT;
    
    -- Find household for cats
    SELECT h.id INTO household_id_val 
    FROM cat_households h 
    JOIN cat_household_memberships m ON h.id = m.household_id 
    WHERE m.cat_id = NEW.cat1_id 
    LIMIT 1;
    
    IF household_id_val IS NOT NULL THEN
        -- Recalculate household harmony score
        UPDATE cat_households 
        SET social_harmony_score = (
            SELECT AVG(compatibility_score) 
            FROM cat_relationships r
            JOIN cat_household_memberships m1 ON r.cat1_id = m1.cat_id
            JOIN cat_household_memberships m2 ON r.cat2_id = m2.cat_id
            WHERE m1.household_id = household_id_val 
            AND m2.household_id = household_id_val
        ),
        last_harmony_assessment = NOW()
        WHERE id = household_id_val;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- ENHANCED VIEWS FOR COMPREHENSIVE DATA ACCESS
-- ============================================================================

-- Comprehensive Cat Dashboard View
CREATE OR REPLACE VIEW cat_dashboard_summary AS
SELECT 
    c.id,
    c.name,
    c.breed,
    c.age,
    c.personality_type,
    c.needs_satisfaction_score,
    c.wellness_score,
    c.activity_level,
    c.stress_level,
    c.socialization_score,
    rts.current_mood,
    rts.current_activity,
    rts.energy_level_current,
    rts.last_feeding,
    rts.last_play_session,
    rts.activity_count_today,
    COUNT(DISTINCT cr.id) as pending_reminders,
    COUNT(DISTINCT cpa.id) as total_assessments,
    MAX(cpa.assessed_at) as last_assessment_date,
    AVG(cca.satisfaction_rating) as avg_care_satisfaction
FROM cats c
LEFT JOIN cat_realtime_status rts ON c.id = rts.cat_id
LEFT JOIN cat_care_reminders cr ON c.id = cr.cat_id AND cr.is_active = true AND cr.next_due <= NOW()
LEFT JOIN cat_personality_assessments cpa ON c.id = cpa.cat_id
LEFT JOIN cat_care_activities cca ON c.id = cca.cat_id AND cca.completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY c.id, c.name, c.breed, c.age, c.personality_type, c.needs_satisfaction_score, c.wellness_score, 
         c.activity_level, c.stress_level, c.socialization_score, rts.current_mood, rts.current_activity, 
         rts.energy_level_current, rts.last_feeding, rts.last_play_session, rts.activity_count_today;

-- Multi-Cat Household Overview
CREATE OR REPLACE VIEW household_management_overview AS
SELECT 
    h.id as household_id,
    h.household_name,
    h.total_cats,
    h.social_harmony_score,
    h.household_stress_level,
    COUNT(DISTINCT m.cat_id) as active_cats,
    AVG(c.wellness_score) as avg_wellness_score,
    AVG(c.needs_satisfaction_score) as avg_needs_satisfaction,
    COUNT(DISTINCT r.id) as total_relationships,
    AVG(r.compatibility_score) as avg_compatibility,
    COUNT(DISTINCT hp.id) as pending_health_predictions
FROM cat_households h
LEFT JOIN cat_household_memberships m ON h.id = m.household_id
LEFT JOIN cats c ON m.cat_id = c.id
LEFT JOIN cat_relationships r ON (r.cat1_id = c.id OR r.cat2_id = c.id)
LEFT JOIN cat_health_predictions hp ON c.id = hp.cat_id AND hp.actual_outcome = 'pending'
GROUP BY h.id, h.household_name, h.total_cats, h.social_harmony_score, h.household_stress_level;

-- Recent Activity Timeline
CREATE OR REPLACE VIEW recent_activity_timeline AS
SELECT 
    'activity' as event_type,
    cas.cat_id,
    c.name as cat_name,
    cas.activity_type as event_title,
    cas.activity_subtype as event_details,
    cas.start_time as event_time,
    cas.duration_minutes,
    cas.activity_quality as quality_rating,
    NULL as urgency_level
FROM cat_activity_stream cas
JOIN cats c ON cas.cat_id = c.id
WHERE cas.start_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)

UNION ALL

SELECT 
    'reminder' as event_type,
    cr.cat_id,
    c.name as cat_name,
    cr.reminder_title as event_title,
    cr.reminder_message as event_details,
    cr.next_due as event_time,
    NULL as duration_minutes,
    NULL as quality_rating,
    cr.priority_level as urgency_level
FROM cat_care_reminders cr
JOIN cats c ON cr.cat_id = c.id
WHERE cr.is_active = true AND cr.next_due BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)

UNION ALL

SELECT 
    'health_prediction' as event_type,
    hp.cat_id,
    c.name as cat_name,
    hp.prediction_type as event_title,
    CONCAT('Risk Level: ', hp.risk_level) as event_details,
    hp.predicted_at as event_time,
    NULL as duration_minutes,
    NULL as quality_rating,
    hp.risk_level as urgency_level
FROM cat_health_predictions hp
JOIN cats c ON hp.cat_id = c.id
WHERE hp.actual_outcome = 'pending' AND hp.predicted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)

ORDER BY event_time DESC;

-- ============================================================================
-- PERFORMANCE OPTIMIZATIONS
-- ============================================================================

-- Composite indexes for common queries
CREATE INDEX IF NOT EXISTS idx_needs_tracking_composite ON cat_needs_tracking(cat_id, need_category, recorded_at DESC);
CREATE INDEX IF NOT EXISTS idx_behavior_observations_composite ON cat_behavior_observations(cat_id, behavior_type, observed_at DESC);
CREATE INDEX IF NOT EXISTS idx_activity_stream_composite ON cat_activity_stream(cat_id, activity_type, start_time DESC);
CREATE INDEX IF NOT EXISTS idx_care_reminders_composite ON cat_care_reminders(cat_id, is_active, next_due);

-- ============================================================================
-- DATABASE MAINTENANCE PROCEDURES
-- ============================================================================

DELIMITER $$

-- Procedure to clean old data
CREATE PROCEDURE IF NOT EXISTS CleanOldAnalyticsData()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Delete old behavioral observations (keep 1 year)
    DELETE FROM cat_behavior_observations 
    WHERE observed_at < DATE_SUB(NOW(), INTERVAL 365 DAY);
    
    -- Delete old activity stream data (keep 6 months)
    DELETE FROM cat_activity_stream 
    WHERE start_time < DATE_SUB(NOW(), INTERVAL 180 DAY);
    
    -- Delete old needs tracking (keep 1 year)
    DELETE FROM cat_needs_tracking 
    WHERE recorded_at < DATE_SUB(NOW(), INTERVAL 365 DAY);
    
    -- Delete synced offline queue data (keep 30 days)
    DELETE FROM offline_data_queue 
    WHERE sync_status = 'synced' AND synced_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    COMMIT;
END$$

-- Procedure to update all wellness scores
CREATE PROCEDURE IF NOT EXISTS RecalculateAllWellnessScores()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE cat_id_val INT;
    DECLARE cat_cursor CURSOR FOR SELECT id FROM cats WHERE active = 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cat_cursor;
    
    cat_loop: LOOP
        FETCH cat_cursor INTO cat_id_val;
        IF done THEN
            LEAVE cat_loop;
        END IF;
        
        -- Recalculate needs satisfaction
        UPDATE cats SET needs_satisfaction_score = (
            SELECT AVG(fulfillment_level) 
            FROM cat_needs_tracking 
            WHERE cat_id = cat_id_val 
            AND recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ) WHERE id = cat_id_val;
        
        -- Update last assessment timestamp
        UPDATE cats SET last_needs_assessment = NOW() WHERE id = cat_id_val;
        
    END LOOP;
    
    CLOSE cat_cursor;
END$$

DELIMITER ;

-- ============================================================================
-- INITIAL DATA SETUP
-- ============================================================================

-- Create default households for existing users with cats
INSERT IGNORE INTO cat_households (owner_id, household_name, total_cats, household_type, establishment_date)
SELECT 
    u.id,
    CONCAT(u.username, '''s Cat Family'),
    COUNT(c.id),
    CASE 
        WHEN COUNT(c.id) = 1 THEN 'single_cat'
        WHEN COUNT(c.id) <= 3 THEN 'multi_cat'
        ELSE 'multi_cat'
    END,
    MIN(c.created_at)
FROM users u
LEFT JOIN cats c ON u.id = c.owner_id AND c.active = 1
WHERE c.id IS NOT NULL
GROUP BY u.id, u.username;

-- Add cats to their households
INSERT IGNORE INTO cat_household_memberships (household_id, cat_id, joined_household, role_in_household)
SELECT 
    h.id,
    c.id,
    c.created_at,
    CASE 
        WHEN c.created_at = (SELECT MIN(created_at) FROM cats c2 WHERE c2.owner_id = c.owner_id) THEN 'alpha'
        ELSE 'neutral'
    END
FROM cat_households h
JOIN cats c ON h.owner_id = c.owner_id
WHERE c.active = 1;

-- Initialize PWA preferences for all users
INSERT IGNORE INTO user_pwa_preferences (user_id)
SELECT id FROM users WHERE active = 1;

-- ============================================================================
-- COMPLETION NOTIFICATION
-- ============================================================================

-- Insert migration completion record
UPDATE schema_migrations 
SET migration_description = CONCAT(migration_description, ' - COMPLETED SUCCESSFULLY at ', NOW())
WHERE migration_version = '2.2.0';

SELECT 'Migration v2.2.0 completed successfully! 🎉' as status;
