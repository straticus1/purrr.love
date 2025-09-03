-- 🌙 Purrr.love Night Watch: Save the Strays Database Schema
-- Tables for the nighttime protection system where cats patrol neighborhoods

-- Night Watch Systems Table
CREATE TABLE night_watch_systems (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    protection_level INTEGER DEFAULT 1,
    active_zones INTEGER DEFAULT 0,
    total_cats_saved INTEGER DEFAULT 0,
    total_bobcat_encounters INTEGER DEFAULT 0,
    community_reputation INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Night Patrols Table
CREATE TABLE night_patrols (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    patrol_area VARCHAR(255) NOT NULL,
    deployed_cats JSONB NOT NULL, -- Array of deployed cat data
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP,
    status VARCHAR(50) DEFAULT 'active', -- active, completed, cancelled
    results JSONB, -- Patrol results and outcomes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Protection Zones Table
CREATE TABLE protection_zones (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    zone_type VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    radius INTEGER DEFAULT 50,
    protection_level DECIMAL(3,2) DEFAULT 0.5,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Night Watch Events Table
CREATE TABLE night_watch_events (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    event_type VARCHAR(100) NOT NULL,
    event_data JSONB, -- Event-specific data
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Stray Cat Encounters Table
CREATE TABLE stray_cat_encounters (
    id SERIAL PRIMARY KEY,
    patrol_id INTEGER REFERENCES night_patrols(id) ON DELETE CASCADE,
    stray_name VARCHAR(255),
    stray_condition INTEGER DEFAULT 100,
    location VARCHAR(255),
    needs_rescue BOOLEAN DEFAULT false,
    was_rescued BOOLEAN DEFAULT false,
    rescue_time TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bobcat Encounters Table
CREATE TABLE bobcat_encounters (
    id SERIAL PRIMARY KEY,
    patrol_id INTEGER REFERENCES night_patrols(id) ON DELETE CASCADE,
    threat_level VARCHAR(50) NOT NULL, -- low, medium, high, critical
    activity_level DECIMAL(3,2) NOT NULL,
    location VARCHAR(255),
    was_deterred BOOLEAN DEFAULT false,
    cats_protected INTEGER DEFAULT 0,
    injuries_prevented INTEGER DEFAULT 0,
    encounter_time TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Guardian Cat Specializations Table
CREATE TABLE guardian_cat_specializations (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    primary_role VARCHAR(100) NOT NULL,
    secondary_role VARCHAR(100),
    experience_points INTEGER DEFAULT 0,
    cats_saved INTEGER DEFAULT 0,
    bobcats_deterred INTEGER DEFAULT 0,
    special_abilities JSONB, -- Array of special abilities
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Community Alerts Table
CREATE TABLE community_alerts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    alert_type VARCHAR(100) NOT NULL, -- bobcat_sighting, stray_in_danger, emergency
    alert_level VARCHAR(50) NOT NULL, -- low, medium, high, critical
    location VARCHAR(255) NOT NULL,
    description TEXT,
    coordinates JSONB, -- Latitude/longitude for mapping
    is_active BOOLEAN DEFAULT true,
    responded_by JSONB, -- Array of responding users
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Night Watch Achievements Table
CREATE TABLE night_watch_achievements (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    achievement_type VARCHAR(100) NOT NULL,
    achievement_name VARCHAR(255) NOT NULL,
    description TEXT,
    criteria_met JSONB, -- Achievement criteria and progress
    unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for performance
CREATE INDEX idx_night_watch_systems_user_id ON night_watch_systems(user_id);
CREATE INDEX idx_night_patrols_user_id ON night_patrols(user_id);
CREATE INDEX idx_night_patrols_status ON night_patrols(status);
CREATE INDEX idx_night_patrols_start_time ON night_patrols(start_time);
CREATE INDEX idx_protection_zones_user_id ON protection_zones(user_id);
CREATE INDEX idx_protection_zones_type ON protection_zones(zone_type);
CREATE INDEX idx_night_watch_events_user_id ON night_watch_events(user_id);
CREATE INDEX idx_night_watch_events_type ON night_watch_events(event_type);
CREATE INDEX idx_stray_cat_encounters_patrol_id ON stray_cat_encounters(patrol_id);
CREATE INDEX idx_bobcat_encounters_patrol_id ON bobcat_encounters(patrol_id);
CREATE INDEX idx_bobcat_encounters_threat_level ON bobcat_encounters(threat_level);
CREATE INDEX idx_guardian_cat_specializations_cat_id ON guardian_cat_specializations(cat_id);
CREATE INDEX idx_community_alerts_user_id ON community_alerts(user_id);
CREATE INDEX idx_community_alerts_level ON community_alerts(alert_level);
CREATE INDEX idx_night_watch_achievements_user_id ON night_watch_achievements(user_id);

-- Create function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_night_watch_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create triggers for updated_at
CREATE TRIGGER update_night_watch_systems_updated_at
    BEFORE UPDATE ON night_watch_systems
    FOR EACH ROW EXECUTE FUNCTION update_night_watch_updated_at();

CREATE TRIGGER update_night_patrols_updated_at
    BEFORE UPDATE ON night_patrols
    FOR EACH ROW EXECUTE FUNCTION update_night_watch_updated_at();

CREATE TRIGGER update_protection_zones_updated_at
    BEFORE UPDATE ON protection_zones
    FOR EACH ROW EXECUTE FUNCTION update_night_watch_updated_at();

CREATE TRIGGER update_guardian_cat_specializations_updated_at
    BEFORE UPDATE ON guardian_cat_specializations
    FOR EACH ROW EXECUTE FUNCTION update_night_watch_updated_at();

-- Insert default night watch achievements
INSERT INTO night_watch_achievements (achievement_type, achievement_name, description) VALUES
('first_patrol', 'First Night Watch', 'Deploy your first night patrol'),
('cats_saved', 'Stray Savior', 'Save 10 stray cats from danger'),
('bobcat_deterred', 'Bobcat Buster', 'Successfully deter 5 bobcat encounters'),
('guardian_master', 'Guardian Master', 'Have a cat reach level 20 in guardian specialization'),
('community_hero', 'Community Hero', 'Respond to 25 community alerts'),
('night_protector', 'Night Protector', 'Complete 100 night patrols'),
('zone_master', 'Zone Master', 'Create and maintain 10 protection zones'),
('emergency_responder', 'Emergency Responder', 'Respond to 10 critical alerts within 5 minutes'),
('stray_rehabilitator', 'Stray Rehabilitator', 'Successfully rehabilitate 50 injured stray cats'),
('bobcat_expert', 'Bobcat Expert', 'Learn all bobcat behavior patterns and countermeasures');

-- Create view for night watch dashboard
CREATE VIEW night_watch_dashboard AS
SELECT 
    nws.user_id,
    nws.protection_level,
    nws.total_cats_saved,
    nws.total_bobcat_encounters,
    nws.community_reputation,
    COUNT(np.id) as total_patrols,
    COUNT(CASE WHEN np.status = 'active' THEN 1 END) as active_patrols,
    COUNT(pz.id) as protection_zones,
    COUNT(nwa.id) as achievements_unlocked
FROM night_watch_systems nws
LEFT JOIN night_patrols np ON nws.user_id = np.user_id
LEFT JOIN protection_zones pz ON nws.user_id = pz.user_id AND pz.is_active = true
LEFT JOIN night_watch_achievements nwa ON nws.user_id = nwa.user_id
GROUP BY nws.id, nws.user_id, nws.protection_level, nws.total_cats_saved, 
         nws.total_bobcat_encounters, nws.community_reputation;

-- Create function to calculate night watch score
CREATE OR REPLACE FUNCTION calculate_night_watch_score(user_id_param INTEGER)
RETURNS INTEGER AS $$
DECLARE
    total_score INTEGER;
BEGIN
    SELECT 
        COALESCE(nws.total_cats_saved * 100, 0) +
        COALESCE(nws.total_bobcat_encounters * 50, 0) +
        COALESCE(nws.community_reputation * 10, 0) +
        COALESCE(COUNT(np.id) * 25, 0) +
        COALESCE(COUNT(nwa.id) * 500, 0)
    INTO total_score
    FROM night_watch_systems nws
    LEFT JOIN night_patrols np ON nws.user_id = np.user_id
    LEFT JOIN night_watch_achievements nwa ON nws.user_id = nwa.user_id
    WHERE nws.user_id = user_id_param
    GROUP BY nws.total_cats_saved, nws.total_bobcat_encounters, nws.community_reputation;
    
    RETURN COALESCE(total_score, 0);
END;
$$ LANGUAGE plpgsql;
