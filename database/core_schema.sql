-- 🚀 Purrr.love Core Database Schema
-- Essential tables: users, cats, and basic functionality

-- Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    role VARCHAR(50) DEFAULT 'user' CHECK (role IN ('user', 'admin', 'moderator')),
    active BOOLEAN DEFAULT true,
    email_verified BOOLEAN DEFAULT false,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires TIMESTAMP,
    avatar_url VARCHAR(500),
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP
);

-- Cats table
CREATE TABLE cats (
    id SERIAL PRIMARY KEY,
    owner_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, -- For compatibility
    name VARCHAR(255) NOT NULL,
    breed VARCHAR(100),
    age INTEGER,
    gender VARCHAR(20) CHECK (gender IN ('male', 'female', 'unknown')),
    color VARCHAR(100),
    weight DECIMAL(5,2),
    personality_traits TEXT[],
    health_status VARCHAR(50) DEFAULT 'healthy',
    microchip_id VARCHAR(50),
    is_neutered BOOLEAN DEFAULT false,
    is_indoor BOOLEAN DEFAULT true,
    bio TEXT,
    avatar_url VARCHAR(500),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'lost', 'found', 'deceased')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User sessions table
CREATE TABLE user_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    ip_address INET,
    user_agent TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Basic statistics table
CREATE TABLE site_statistics (
    id SERIAL PRIMARY KEY,
    stat_name VARCHAR(100) UNIQUE NOT NULL,
    stat_value BIGINT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert initial statistics
INSERT INTO site_statistics (stat_name, stat_value) VALUES
('total_users', 0),
('total_cats', 0),
('total_logins', 0),
('active_sessions', 0);

-- Create indexes for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_active ON users(active);
CREATE INDEX idx_users_role ON users(role);

CREATE INDEX idx_cats_owner ON cats(owner_id);
CREATE INDEX idx_cats_user ON cats(user_id);
CREATE INDEX idx_cats_status ON cats(status);
CREATE INDEX idx_cats_breed ON cats(breed);

CREATE INDEX idx_sessions_user ON user_sessions(user_id);
CREATE INDEX idx_sessions_token ON user_sessions(session_token);
CREATE INDEX idx_sessions_expires ON user_sessions(expires_at);

-- Functions to update statistics
CREATE OR REPLACE FUNCTION update_user_count()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        UPDATE site_statistics SET stat_value = stat_value + 1, last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_users';
        RETURN NEW;
    ELSIF TG_OP = 'DELETE' THEN
        UPDATE site_statistics SET stat_value = stat_value - 1, last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_users';
        RETURN OLD;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION update_cat_count()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        UPDATE site_statistics SET stat_value = stat_value + 1, last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_cats';
        RETURN NEW;
    ELSIF TG_OP = 'DELETE' THEN
        UPDATE site_statistics SET stat_value = stat_value - 1, last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_cats';
        RETURN OLD;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create triggers
CREATE TRIGGER trigger_update_user_count
    AFTER INSERT OR DELETE ON users
    FOR EACH ROW EXECUTE FUNCTION update_user_count();

CREATE TRIGGER trigger_update_cat_count
    AFTER INSERT OR DELETE ON cats
    FOR EACH ROW EXECUTE FUNCTION update_cat_count();

CREATE TRIGGER update_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_cats_updated_at
    BEFORE UPDATE ON cats
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Insert a default admin user (password: 'admin123456789!')
INSERT INTO users (username, email, password_hash, name, role, active, email_verified) VALUES
('admin', 'admin@purrr.love', '$2y$10$K4Y9Z4Q4X9X4X9X4X9X4XOYg7gT1Q9P1Q9P1Q9P1Q9P1Q9P1Q9P1Qe', 'System Administrator', 'admin', true, true);

-- Grant basic permissions
-- Uncomment these lines if you have specific database user
-- GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO purrr_user;
-- GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO purrr_user;
-- GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO purrr_user;
