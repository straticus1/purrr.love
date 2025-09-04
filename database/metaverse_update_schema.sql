-- =====================================================
-- 🌐 Purrr.love Metaverse Update Schema
-- Additional tables for new metaverse activity systems
-- =====================================================

-- AI NPC Management
CREATE TABLE IF NOT EXISTS metaverse_ai_npcs (
    id SERIAL PRIMARY KEY,
    world_id VARCHAR(255) NOT NULL,
    cat_name VARCHAR(100) NOT NULL,
    cat_breed VARCHAR(50),
    personality_type VARCHAR(50) NOT NULL,
    behavior_patterns JSONB NOT NULL,
    activity_level INTEGER DEFAULT 75,
    social_preferences JSONB,
    spawn_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'despawned')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_metaverse_ai_npcs_world_id ON metaverse_ai_npcs(world_id);
CREATE INDEX idx_metaverse_ai_npcs_status ON metaverse_ai_npcs(status);

-- World Events Tracking
CREATE TABLE IF NOT EXISTS metaverse_world_events (
    id SERIAL PRIMARY KEY,
    world_id VARCHAR(255) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_data JSONB NOT NULL,
    triggered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    duration_minutes INTEGER,
    participants_affected INTEGER DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'completed', 'cancelled')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_metaverse_world_events_world_id ON metaverse_world_events(world_id);
CREATE INDEX idx_metaverse_world_events_triggered_at ON metaverse_world_events(triggered_at);

-- User Daily Quests
CREATE TABLE IF NOT EXISTS user_daily_quests (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    quest_title VARCHAR(200) NOT NULL,
    quest_description TEXT,
    target_value INTEGER NOT NULL,
    current_progress INTEGER DEFAULT 0,
    progress_type VARCHAR(50) NOT NULL,
    rewards JSONB,
    quest_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'completed', 'expired')),
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, quest_title, quest_date)
);

CREATE INDEX idx_user_daily_quests_user_id ON user_daily_quests(user_id);
CREATE INDEX idx_user_daily_quests_date ON user_daily_quests(quest_date);
CREATE INDEX idx_user_daily_quests_status ON user_daily_quests(status);

-- World Weather States
CREATE TABLE IF NOT EXISTS world_weather_states (
    id SERIAL PRIMARY KEY,
    world_id VARCHAR(255) NOT NULL,
    weather_type VARCHAR(50) NOT NULL,
    weather_effects JSONB,
    visibility INTEGER DEFAULT 100,
    temperature INTEGER DEFAULT 20,
    special_effects JSONB,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    duration_minutes INTEGER DEFAULT 60,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(world_id)
);

CREATE INDEX idx_world_weather_states_world_id ON world_weather_states(world_id);

-- Metaverse Engagement Analytics
CREATE TABLE IF NOT EXISTS metaverse_engagement_logs (
    id SERIAL PRIMARY KEY,
    overall_score DECIMAL(5,3) NOT NULL,
    active_users INTEGER NOT NULL,
    avg_session_duration DECIMAL(8,2),
    interaction_count INTEGER DEFAULT 0,
    social_engagement_ratio DECIMAL(5,3),
    world_utilization DECIMAL(5,3),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_metaverse_engagement_logs_recorded_at ON metaverse_engagement_logs(recorded_at);

-- User Metaverse Progress Tracking
CREATE TABLE IF NOT EXISTS user_metaverse_progress (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    progress_type VARCHAR(100) NOT NULL,
    progress_value INTEGER DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, progress_type)
);

CREATE INDEX idx_user_metaverse_progress_user_id ON user_metaverse_progress(user_id);

-- VR Interaction Sessions
CREATE TABLE IF NOT EXISTS vr_interaction_sessions (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    vr_device VARCHAR(50) DEFAULT 'webvr',
    session_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_end TIMESTAMP,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_vr_interaction_sessions_user_id ON vr_interaction_sessions(user_id);
CREATE INDEX idx_vr_interaction_sessions_active ON vr_interaction_sessions(active);

-- Cat VR Behavior Profiles
CREATE TABLE IF NOT EXISTS cat_vr_behavior (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER NOT NULL UNIQUE,
    petting_preferences JSONB,
    play_preferences JSONB,
    grooming_preferences JSONB,
    training_preferences JSONB,
    sensitivity VARCHAR(20) DEFAULT 'normal',
    play_style VARCHAR(20) DEFAULT 'curious',
    grooming_tolerance VARCHAR(20) DEFAULT 'normal',
    training_level INTEGER DEFAULT 1,
    learned_commands JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_cat_vr_behavior_cat_id ON cat_vr_behavior(cat_id);

-- VR Interaction Logs
CREATE TABLE IF NOT EXISTS vr_interaction_logs (
    id SERIAL PRIMARY KEY,
    session_id INTEGER NOT NULL,
    interaction_type VARCHAR(50) NOT NULL,
    interaction_data JSONB,
    result_data JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_vr_interaction_logs_session_id ON vr_interaction_logs(session_id);

-- Metaverse Social Events
CREATE TABLE IF NOT EXISTS metaverse_social_events (
    id SERIAL PRIMARY KEY,
    world_id VARCHAR(255) NOT NULL,
    event_name VARCHAR(200) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    participants_count INTEGER DEFAULT 0,
    max_participants INTEGER DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_metaverse_social_events_world_id ON metaverse_social_events(world_id);

-- Metaverse Notifications
CREATE TABLE IF NOT EXISTS metaverse_notifications (
    id SERIAL PRIMARY KEY,
    world_id VARCHAR(255),
    user_id INTEGER,
    notification_type VARCHAR(50) NOT NULL,
    notification_data JSONB,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_metaverse_notifications_user_id ON metaverse_notifications(user_id);
CREATE INDEX idx_metaverse_notifications_world_id ON metaverse_notifications(world_id);

-- User Achievement Tracking
CREATE TABLE IF NOT EXISTS user_achievements (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    achievement_id VARCHAR(100) NOT NULL,
    achievement_name VARCHAR(200),
    unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progress_data JSONB,
    UNIQUE(user_id, achievement_id)
);

CREATE INDEX idx_user_achievements_user_id ON user_achievements(user_id);

-- Metaverse Special Areas
CREATE TABLE IF NOT EXISTS metaverse_special_areas (
    id SERIAL PRIMARY KEY,
    area_name VARCHAR(200) NOT NULL,
    area_type VARCHAR(50) NOT NULL,
    world_id VARCHAR(255) NOT NULL,
    rarity VARCHAR(20) NOT NULL,
    max_visitors INTEGER DEFAULT 20,
    current_visitors INTEGER DEFAULT 0,
    spawned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    special_effects JSONB,
    rewards JSONB,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'expired', 'full'))
);

CREATE INDEX idx_metaverse_special_areas_world_id ON metaverse_special_areas(world_id);
CREATE INDEX idx_metaverse_special_areas_expires_at ON metaverse_special_areas(expires_at);

-- Competition Participants
CREATE TABLE IF NOT EXISTS competition_participants (
    id SERIAL PRIMARY KEY,
    competition_id VARCHAR(100) NOT NULL,
    user_id INTEGER NOT NULL,
    cat_id INTEGER NOT NULL,
    entry_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    final_score DECIMAL(10,2),
    placement INTEGER,
    rewards JSONB,
    UNIQUE(competition_id, user_id)
);

CREATE INDEX idx_competition_participants_competition_id ON competition_participants(competition_id);
CREATE INDEX idx_competition_participants_user_id ON competition_participants(user_id);

-- =====================================================
-- Data Migration and Initial Setup
-- =====================================================

-- Insert initial achievement definitions
INSERT INTO user_achievements (user_id, achievement_id, achievement_name, unlocked_at, progress_data) 
SELECT -1, 'system_init', 'System Initialization', CURRENT_TIMESTAMP, '{}' 
WHERE NOT EXISTS (SELECT 1 FROM user_achievements WHERE achievement_id = 'system_init');

-- Create default world weather states
INSERT INTO world_weather_states (world_id, weather_type, weather_effects, visibility, temperature)
VALUES 
    ('cat_paradise', 'sunny', '{"effects": ["bright_lighting", "warm_temperature"]}', 100, 25),
    ('mystic_forest', 'misty', '{"effects": ["mystical_atmosphere", "reduced_visibility"]}', 60, 15),
    ('cosmic_city', 'clear_dome', '{"effects": ["clear_space_view", "artificial_atmosphere"]}', 100, 22),
    ('winter_wonderland', 'light_snow', '{"effects": ["gentle_snowfall", "crisp_air"]}', 80, -2),
    ('desert_oasis', 'hot_sunny', '{"effects": ["intense_heat", "clear_skies"]}', 100, 35)
ON CONFLICT (world_id) DO NOTHING;

-- =====================================================
-- Views for Analytics
-- =====================================================

-- Daily engagement summary view
CREATE OR REPLACE VIEW daily_engagement_summary AS
SELECT 
    DATE(recorded_at) as engagement_date,
    AVG(overall_score) as avg_engagement_score,
    AVG(active_users) as avg_active_users,
    AVG(avg_session_duration) as avg_session_duration,
    SUM(interaction_count) as total_interactions
FROM metaverse_engagement_logs
GROUP BY DATE(recorded_at)
ORDER BY engagement_date DESC;

-- Active worlds summary view
CREATE OR REPLACE VIEW active_worlds_summary AS
SELECT 
    w.world_id,
    w.world_type,
    w.name,
    COUNT(DISTINCT s.user_id) as unique_visitors_today,
    AVG(ws.visibility) as avg_visibility,
    ws.weather_type as current_weather,
    COUNT(npc.id) as active_npcs
FROM metaverse_worlds w
LEFT JOIN metaverse_sessions s ON w.world_id = s.world_id 
    AND s.joined_at >= CURRENT_DATE
LEFT JOIN world_weather_states ws ON w.world_id = ws.world_id
LEFT JOIN metaverse_ai_npcs npc ON w.world_id = npc.world_id 
    AND npc.status = 'active'
WHERE w.status = 'active'
GROUP BY w.world_id, w.world_type, w.name, ws.weather_type, ws.visibility;

-- User progress leaderboard view
CREATE OR REPLACE VIEW user_progress_leaderboard AS
SELECT 
    u.id as user_id,
    u.username,
    COUNT(ua.id) as total_achievements,
    SUM(CASE WHEN ump.progress_type = 'total_metaverse_time' THEN ump.progress_value ELSE 0 END) as total_time_minutes,
    SUM(CASE WHEN ump.progress_type = 'competition_wins' THEN ump.progress_value ELSE 0 END) as competition_wins,
    SUM(CASE WHEN ump.progress_type = 'social_interactions' THEN ump.progress_value ELSE 0 END) as social_interactions
FROM users u
LEFT JOIN user_achievements ua ON u.id = ua.user_id
LEFT JOIN user_metaverse_progress ump ON u.id = ump.user_id
GROUP BY u.id, u.username
ORDER BY total_achievements DESC, competition_wins DESC, total_time_minutes DESC;

-- =====================================================
-- Triggers for Automation
-- =====================================================

-- Auto-update last_activity for AI NPCs
CREATE OR REPLACE FUNCTION update_npc_last_activity()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE metaverse_ai_npcs 
    SET last_activity = CURRENT_TIMESTAMP 
    WHERE world_id = NEW.world_id AND status = 'active';
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_update_npc_activity
    AFTER INSERT ON metaverse_world_events
    FOR EACH ROW
    EXECUTE FUNCTION update_npc_last_activity();

-- Auto-expire old special areas
CREATE OR REPLACE FUNCTION cleanup_expired_special_areas()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE metaverse_special_areas 
    SET status = 'expired' 
    WHERE expires_at < CURRENT_TIMESTAMP AND status = 'active';
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- =====================================================
-- Indexes for Performance
-- =====================================================

-- Composite indexes for common queries
CREATE INDEX IF NOT EXISTS idx_metaverse_sessions_user_world_date 
    ON metaverse_sessions(user_id, world_id, joined_at);

CREATE INDEX IF NOT EXISTS idx_user_daily_quests_user_date_status 
    ON user_daily_quests(user_id, quest_date, status);

CREATE INDEX IF NOT EXISTS idx_vr_interaction_logs_session_type 
    ON vr_interaction_logs(session_id, interaction_type);

-- Partial indexes for active records only
CREATE INDEX IF NOT EXISTS idx_active_ai_npcs 
    ON metaverse_ai_npcs(world_id, last_activity) 
    WHERE status = 'active';

CREATE INDEX IF NOT EXISTS idx_active_special_areas 
    ON metaverse_special_areas(world_id, expires_at) 
    WHERE status = 'active';

-- =====================================================
-- Comments for Documentation
-- =====================================================

COMMENT ON TABLE metaverse_ai_npcs IS 'AI-controlled cat NPCs that spawn in worlds to maintain activity';
COMMENT ON TABLE metaverse_world_events IS 'Dynamic events that occur in metaverse worlds';
COMMENT ON TABLE user_daily_quests IS 'Daily quests generated for users to encourage engagement';
COMMENT ON TABLE world_weather_states IS 'Current weather conditions in each metaverse world';
COMMENT ON TABLE metaverse_engagement_logs IS 'Historical engagement metrics for analytics';
COMMENT ON TABLE user_metaverse_progress IS 'User progress tracking for achievements and gamification';
COMMENT ON TABLE vr_interaction_sessions IS 'VR sessions for immersive cat interactions';
COMMENT ON TABLE cat_vr_behavior IS 'Individual cat behavior profiles for VR interactions';
COMMENT ON TABLE metaverse_special_areas IS 'Limited-time special areas that appear in worlds';

-- =====================================================
-- Final Setup
-- =====================================================

-- Grant permissions (adjust as needed for your user)
-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO your_app_user;
-- GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO your_app_user;

COMMIT;
