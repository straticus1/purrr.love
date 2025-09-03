-- Lost Pet Finder Database Schema
-- This schema supports the comprehensive lost pet finder system
-- with Facebook app integration and advanced search capabilities

-- Enable PostGIS extension for location-based queries
CREATE EXTENSION IF NOT EXISTS postgis;

-- Lost Pet Reports Table
CREATE TABLE lost_pet_reports (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    pet_name VARCHAR(100) NOT NULL,
    pet_type VARCHAR(50) DEFAULT 'cat',
    breed VARCHAR(100),
    color VARCHAR(100),
    age INTEGER,
    microchip_id VARCHAR(100),
    collar_id VARCHAR(100),
    last_seen_location TEXT NOT NULL,
    last_seen_date DATE NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    contact_info JSONB,
    reward_amount DECIMAL(10, 2) DEFAULT 0,
    description TEXT,
    photos JSONB DEFAULT '[]',
    facebook_share_enabled BOOLEAN DEFAULT false,
    facebook_post_id VARCHAR(100),
    privacy_level VARCHAR(20) DEFAULT 'public' CHECK (privacy_level IN ('public', 'community', 'private')),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'found', 'expired', 'cancelled')),
    found_date TIMESTAMP,
    found_location TEXT,
    found_details JSONB,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    
    -- Indexes for performance
    CONSTRAINT unique_active_microchip UNIQUE (microchip_id, status) WHERE status = 'active',
    CONSTRAINT unique_active_pet_user UNIQUE (pet_name, user_id, status) WHERE status = 'active'
);

-- Create spatial index for location-based queries
CREATE INDEX idx_lost_pet_reports_location ON lost_pet_reports USING GIST (
    ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)
);

-- Create indexes for common search fields
CREATE INDEX idx_lost_pet_reports_status ON lost_pet_reports(status);
CREATE INDEX idx_lost_pet_reports_breed ON lost_pet_reports(breed);
CREATE INDEX idx_lost_pet_reports_color ON lost_pet_reports(color);
CREATE INDEX idx_lost_pet_reports_created_at ON lost_pet_reports(created_at);
CREATE INDEX idx_lost_pet_reports_user_id ON lost_pet_reports(user_id);
CREATE INDEX idx_lost_pet_reports_facebook_post_id ON lost_pet_reports(facebook_post_id);

-- Pet Sightings Table
CREATE TABLE pet_sightings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    lost_pet_report_id INTEGER NOT NULL REFERENCES lost_pet_reports(id) ON DELETE CASCADE,
    location TEXT NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    sighting_date DATE NOT NULL,
    description TEXT,
    photos JSONB DEFAULT '[]',
    confidence_level VARCHAR(20) DEFAULT 'medium' CHECK (confidence_level IN ('low', 'medium', 'high')),
    contact_info JSONB,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'verified', 'rejected', 'contacted')),
    verified_by INTEGER REFERENCES users(id),
    verified_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    
    -- Indexes for performance
    CONSTRAINT unique_sighting_per_user UNIQUE (user_id, lost_pet_report_id, sighting_date)
);

-- Create spatial index for sightings
CREATE INDEX idx_pet_sightings_location ON pet_sightings USING GIST (
    ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)
);

-- Create indexes for common query fields
CREATE INDEX idx_pet_sightings_lost_pet_report_id ON pet_sightings(lost_pet_report_id);
CREATE INDEX idx_pet_sightings_user_id ON pet_sightings(user_id);
CREATE INDEX idx_pet_sightings_status ON pet_sightings(status);
CREATE INDEX idx_pet_sightings_sighting_date ON pet_sightings(sighting_date);

-- Lost Pet Alerts Table
CREATE TABLE lost_pet_alerts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    alert_type VARCHAR(50) NOT NULL CHECK (alert_type IN ('location', 'breed', 'color', 'custom')),
    alert_settings JSONB NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    
    -- Indexes
    CONSTRAINT unique_user_alert_type UNIQUE (user_id, alert_type)
);

CREATE INDEX idx_lost_pet_alerts_user_id ON lost_pet_alerts(user_id);
CREATE INDEX idx_lost_pet_alerts_is_active ON lost_pet_alerts(is_active);

-- Lost Pet Search History Table
CREATE TABLE lost_pet_search_history (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    search_criteria JSONB NOT NULL,
    results_count INTEGER DEFAULT 0,
    search_date TIMESTAMP DEFAULT NOW(),
    ip_address INET,
    user_agent TEXT
);

CREATE INDEX idx_lost_pet_search_history_user_id ON lost_pet_search_history(user_id);
CREATE INDEX idx_lost_pet_search_history_date ON lost_pet_search_history(search_date);

-- Facebook Integration Table
CREATE TABLE facebook_integration (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    facebook_user_id VARCHAR(100) UNIQUE NOT NULL,
    access_token TEXT NOT NULL,
    token_expires_at TIMESTAMP,
    permissions JSONB DEFAULT '[]',
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_facebook_integration_user_id ON facebook_integration(user_id);
CREATE INDEX idx_facebook_integration_facebook_user_id ON facebook_integration(facebook_user_id);

-- Facebook Post Analytics Table
CREATE TABLE facebook_post_analytics (
    id SERIAL PRIMARY KEY,
    lost_pet_report_id INTEGER NOT NULL REFERENCES lost_pet_reports(id) ON DELETE CASCADE,
    facebook_post_id VARCHAR(100) NOT NULL,
    reach_count INTEGER DEFAULT 0,
    engagement_count INTEGER DEFAULT 0,
    shares_count INTEGER DEFAULT 0,
    comments_count INTEGER DEFAULT 0,
    clicks_count INTEGER DEFAULT 0,
    analytics_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    
    -- Indexes
    CONSTRAINT unique_post_date UNIQUE (facebook_post_id, analytics_date)
);

CREATE INDEX idx_facebook_post_analytics_lost_pet_report_id ON facebook_post_analytics(lost_pet_report_id);
CREATE INDEX idx_facebook_post_analytics_facebook_post_id ON facebook_post_analytics(facebook_post_id);
CREATE INDEX idx_facebook_post_analytics_date ON facebook_post_analytics(analytics_date);

-- Community Support Table
CREATE TABLE community_support (
    id SERIAL PRIMARY KEY,
    lost_pet_report_id INTEGER NOT NULL REFERENCES lost_pet_reports(id) ON DELETE CASCADE,
    supporter_user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    support_type VARCHAR(50) NOT NULL CHECK (support_type IN ('share', 'volunteer', 'donation', 'information')),
    support_details JSONB,
    is_anonymous BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_community_support_lost_pet_report_id ON community_support(lost_pet_report_id);
CREATE INDEX idx_community_support_supporter_user_id ON community_support(supporter_user_id);
CREATE INDEX idx_community_support_type ON community_support(support_type);

-- Lost Pet Statistics Cache Table
CREATE TABLE lost_pet_statistics_cache (
    id SERIAL PRIMARY KEY,
    cache_key VARCHAR(255) UNIQUE NOT NULL,
    cache_data JSONB NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_lost_pet_statistics_cache_key ON lost_pet_statistics_cache(cache_key);
CREATE INDEX idx_lost_pet_statistics_expires ON lost_pet_statistics_cache(expires_at);

-- Triggers for automatic timestamp updates
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_lost_pet_reports_updated_at 
    BEFORE UPDATE ON lost_pet_reports 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_lost_pet_alerts_updated_at 
    BEFORE UPDATE ON lost_pet_alerts 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_facebook_integration_updated_at 
    BEFORE UPDATE ON facebook_integration 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Function to calculate distance between two points
CREATE OR REPLACE FUNCTION calculate_distance(
    lat1 DECIMAL, lng1 DECIMAL, 
    lat2 DECIMAL, lng2 DECIMAL
) RETURNS DECIMAL AS $$
BEGIN
    RETURN ST_Distance_Sphere(
        ST_SetSRID(ST_MakePoint(lng1, lat1), 4326),
        ST_SetSRID(ST_MakePoint(lng2, lat2), 4326)
    );
END;
$$ LANGUAGE plpgsql;

-- Function to find nearby lost pets
CREATE OR REPLACE FUNCTION find_nearby_lost_pets(
    search_lat DECIMAL, 
    search_lng DECIMAL, 
    radius_meters INTEGER DEFAULT 5000
) RETURNS TABLE(
    report_id INTEGER,
    pet_name VARCHAR(100),
    breed VARCHAR(100),
    color VARCHAR(100),
    distance_meters DECIMAL,
    last_seen_date DATE
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        lpr.id,
        lpr.pet_name,
        lpr.breed,
        lpr.color,
        calculate_distance(search_lat, search_lng, lpr.latitude, lpr.longitude) as distance_meters,
        lpr.last_seen_date
    FROM lost_pet_reports lpr
    WHERE lpr.status = 'active'
    AND lpr.privacy_level IN ('public', 'community')
    AND lpr.latitude IS NOT NULL 
    AND lpr.longitude IS NOT NULL
    AND calculate_distance(search_lat, search_lng, lpr.latitude, lpr.longitude) <= radius_meters
    ORDER BY distance_meters ASC;
END;
$$ LANGUAGE plpgsql;

-- Function to get lost pet statistics
CREATE OR REPLACE FUNCTION get_lost_pet_statistics()
RETURNS JSON AS $$
DECLARE
    result JSON;
BEGIN
    SELECT json_build_object(
        'total_reports', COUNT(*),
        'active_reports', COUNT(CASE WHEN status = 'active' THEN 1 END),
        'found_pets', COUNT(CASE WHEN status = 'found' THEN 1 END),
        'recent_reports', COUNT(CASE WHEN created_at >= NOW() - INTERVAL '7 days' THEN 1 END),
        'success_rate', ROUND(
            COUNT(CASE WHEN status = 'found' THEN 1 END)::decimal / 
            NULLIF(COUNT(*), 0) * 100, 2
        ),
        'avg_days_to_find', AVG(
            EXTRACT(EPOCH FROM (found_date - created_at))/86400
        )
    ) INTO result
    FROM lost_pet_reports;
    
    RETURN result;
END;
$$ LANGUAGE plpgsql;

-- Function to clean up expired reports
CREATE OR REPLACE FUNCTION cleanup_expired_lost_pet_reports()
RETURNS INTEGER AS $$
DECLARE
    expired_count INTEGER;
BEGIN
    UPDATE lost_pet_reports 
    SET status = 'expired'
    WHERE status = 'active' 
    AND created_at < NOW() - INTERVAL '90 days';
    
    GET DIAGNOSTICS expired_count = ROW_COUNT;
    
    RETURN expired_count;
END;
$$ LANGUAGE plpgsql;

-- Create a scheduled job to clean up expired reports (runs daily)
-- Note: This requires pg_cron extension or external cron job
-- SELECT cron.schedule('cleanup-expired-reports', '0 2 * * *', 'SELECT cleanup_expired_lost_pet_reports();');

-- Insert sample data for testing
INSERT INTO lost_pet_reports (
    user_id, pet_name, pet_type, breed, color, age, 
    last_seen_location, last_seen_date, latitude, longitude,
    description, privacy_level
) VALUES 
(1, 'Whiskers', 'cat', 'Persian', 'White', 3, 'Central Park, New York', '2024-12-01', 40.7829, -73.9654, 'Friendly white Persian cat with blue eyes', 'public'),
(1, 'Shadow', 'cat', 'Maine Coon', 'Black', 5, 'Brooklyn Bridge Park', '2024-12-02', 40.7021, -73.9969, 'Large black Maine Coon with green eyes', 'public'),
(2, 'Luna', 'cat', 'Siamese', 'Cream', 2, 'Times Square', '2024-12-03', 40.7580, -73.9855, 'Cream Siamese cat with blue eyes', 'community');

-- Create views for common queries
CREATE VIEW active_lost_pets AS
SELECT 
    lpr.*,
    u.username as owner_username,
    u.email as owner_email,
    COUNT(ps.id) as sighting_count
FROM lost_pet_reports lpr
JOIN users u ON lpr.user_id = u.id
LEFT JOIN pet_sightings ps ON lpr.id = ps.lost_pet_report_id
WHERE lpr.status = 'active'
GROUP BY lpr.id, u.username, u.email;

CREATE VIEW lost_pet_success_rates AS
SELECT 
    breed,
    COUNT(*) as total_reports,
    COUNT(CASE WHEN status = 'found' THEN 1 END) as found_count,
    ROUND(
        COUNT(CASE WHEN status = 'found' THEN 1 END)::decimal / COUNT(*) * 100, 2
    ) as success_rate
FROM lost_pet_reports
WHERE breed IS NOT NULL
GROUP BY breed
HAVING COUNT(*) >= 3
ORDER BY success_rate DESC;

-- Grant permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO purrr_user;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO purrr_user;
GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO purrr_user;

-- Create indexes for performance optimization
CREATE INDEX CONCURRENTLY idx_lost_pet_reports_composite_search 
ON lost_pet_reports(breed, color, status, created_at) 
WHERE status = 'active';

CREATE INDEX CONCURRENTLY idx_pet_sightings_composite 
ON pet_sightings(lost_pet_report_id, status, sighting_date);

-- Add comments for documentation
COMMENT ON TABLE lost_pet_reports IS 'Stores lost pet reports with location and contact information';
COMMENT ON TABLE pet_sightings IS 'Records of pet sightings reported by community members';
COMMENT ON TABLE lost_pet_alerts IS 'User alert preferences for lost pet notifications';
COMMENT ON TABLE facebook_integration IS 'Facebook app integration data for users';
COMMENT ON TABLE facebook_post_analytics IS 'Analytics data for Facebook posts about lost pets';
COMMENT ON TABLE community_support IS 'Community support and volunteer activities for lost pets';
COMMENT ON TABLE lost_pet_statistics_cache IS 'Cached statistics for performance optimization';

COMMENT ON COLUMN lost_pet_reports.privacy_level IS 'Privacy level: public (visible to all), community (visible to registered users), private (visible only to owner)';
COMMENT ON COLUMN lost_pet_reports.status IS 'Report status: active (searching), found (pet located), expired (90+ days old), cancelled (owner cancelled)';
COMMENT ON COLUMN pet_sightings.confidence_level IS 'Confidence in sighting: low, medium, high';
COMMENT ON COLUMN pet_sightings.status IS 'Sighting status: pending (awaiting verification), verified (confirmed), rejected (false alarm), contacted (owner notified)';
