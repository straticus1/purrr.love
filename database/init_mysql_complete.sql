-- 🚀 Purrr.love Complete MySQL Database Initialization
-- This script sets up the entire database with all features

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS purrr_love CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE purrr_love;

-- ==============================================
-- CORE SCHEMA (Users, Cats, Basic Features)
-- ==============================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    role ENUM('user', 'admin', 'moderator') DEFAULT 'user',
    active BOOLEAN DEFAULT true,
    email_verified BOOLEAN DEFAULT false,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires TIMESTAMP NULL,
    avatar_url VARCHAR(500),
    bio TEXT,
    level INT DEFAULT 1,
    experience INT DEFAULT 0,
    coins INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    
    INDEX idx_users_email (email),
    INDEX idx_users_username (username),
    INDEX idx_users_active (active),
    INDEX idx_users_role (role)
) ENGINE=InnoDB;

-- Cats table
CREATE TABLE IF NOT EXISTS cats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT,
    user_id INT, -- For compatibility
    name VARCHAR(255) NOT NULL,
    breed VARCHAR(100),
    age INT,
    gender ENUM('male', 'female', 'unknown') DEFAULT 'unknown',
    color VARCHAR(100),
    weight DECIMAL(5,2),
    personality_traits JSON,
    health_status VARCHAR(50) DEFAULT 'healthy',
    health INT DEFAULT 100,
    happiness INT DEFAULT 100,
    energy INT DEFAULT 100,
    hunger INT DEFAULT 0,
    cleanliness INT DEFAULT 100,
    microchip_id VARCHAR(50),
    is_neutered BOOLEAN DEFAULT false,
    is_indoor BOOLEAN DEFAULT true,
    bio TEXT,
    avatar_url VARCHAR(500),
    status ENUM('active', 'lost', 'found', 'deceased') DEFAULT 'active',
    temperature DECIMAL(4,2),
    heart_rate INT,
    last_health_check TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_cats_owner (owner_id),
    INDEX idx_cats_user (user_id),
    INDEX idx_cats_status (status),
    INDEX idx_cats_breed (breed)
) ENGINE=InnoDB;

-- Health logs table
CREATE TABLE IF NOT EXISTS health_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat_id INT,
    health INT,
    happiness INT,
    energy INT,
    hunger INT,
    cleanliness INT,
    weight DECIMAL(5,2),
    temperature DECIMAL(4,2),
    heart_rate INT,
    notes TEXT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
    INDEX idx_health_logs_cat (cat_id),
    INDEX idx_health_logs_recorded (recorded_at)
) ENGINE=InnoDB;

-- User sessions table
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    ip_address VARCHAR(45), -- Supports IPv6
    user_agent TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_token (session_token),
    INDEX idx_sessions_expires (expires_at)
) ENGINE=InnoDB;

-- Support tickets table
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(500) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_support_user_id (user_id),
    INDEX idx_support_status (status),
    INDEX idx_support_priority (priority)
) ENGINE=InnoDB;

-- Basic statistics table
CREATE TABLE IF NOT EXISTS site_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_name VARCHAR(100) UNIQUE NOT NULL,
    stat_value BIGINT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_stats_name (stat_name)
) ENGINE=InnoDB;

-- ==============================================
-- SECURITY SCHEMA
-- ==============================================

-- Security Logs Table
CREATE TABLE IF NOT EXISTS security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event VARCHAR(100) NOT NULL,
    level VARCHAR(20) NOT NULL DEFAULT 'INFO',
    ip_address VARCHAR(45), -- Supports IPv6
    user_agent TEXT,
    user_id INT,
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_security_logs_event (event),
    INDEX idx_security_logs_level (level),
    INDEX idx_security_logs_ip (ip_address),
    INDEX idx_security_logs_user (user_id),
    INDEX idx_security_logs_created (created_at)
) ENGINE=InnoDB;

-- Failed Login Attempts Table
CREATE TABLE IF NOT EXISTS failed_login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL, -- Supports IPv6
    username VARCHAR(255),
    user_agent TEXT,
    attempt_count INT DEFAULT 1,
    first_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    blocked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_failed_login_ip (ip_address),
    INDEX idx_failed_login_username (username),
    INDEX idx_failed_login_blocked (blocked_until)
) ENGINE=InnoDB;

-- API Keys Table
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    key_name VARCHAR(255) NOT NULL,
    key_hash VARCHAR(255) NOT NULL UNIQUE,
    scopes JSON,
    ip_whitelist JSON,
    rate_limit INT DEFAULT 1000,
    expires_at TIMESTAMP NULL,
    last_used_at TIMESTAMP NULL,
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_api_keys_user (user_id),
    INDEX idx_api_keys_hash (key_hash),
    INDEX idx_api_keys_active (active),
    INDEX idx_api_keys_expires (expires_at)
) ENGINE=InnoDB;

-- OAuth2 Access Tokens Table
CREATE TABLE IF NOT EXISTS oauth2_access_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    client_id VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    scope TEXT,
    expires_at TIMESTAMP NOT NULL,
    revoked BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_oauth2_tokens_user (user_id),
    INDEX idx_oauth2_tokens_token (token),
    INDEX idx_oauth2_tokens_client (client_id),
    INDEX idx_oauth2_tokens_expires (expires_at),
    INDEX idx_oauth2_tokens_revoked (revoked)
) ENGINE=InnoDB;

-- Rate Limiting Table
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL, -- IP, user_id, or API key
    action VARCHAR(100) NOT NULL,
    request_count INT DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    window_end TIMESTAMP NOT NULL,
    blocked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_identifier_action (identifier, action, window_start),
    INDEX idx_rate_limits_identifier (identifier),
    INDEX idx_rate_limits_action (action),
    INDEX idx_rate_limits_window (window_start, window_end),
    INDEX idx_rate_limits_blocked (blocked_until)
) ENGINE=InnoDB;

-- ==============================================
-- STORED PROCEDURES AND FUNCTIONS
-- ==============================================

DELIMITER $$

-- Procedure to record failed login attempts
CREATE PROCEDURE IF NOT EXISTS record_failed_login(
    IN p_ip_address VARCHAR(45),
    IN p_username VARCHAR(255),
    IN p_user_agent TEXT
)
BEGIN
    DECLARE existing_count INT DEFAULT 0;
    DECLARE first_attempt TIMESTAMP;
    DECLARE blocked_until TIMESTAMP NULL;
    
    -- Check if there's an existing record for this IP
    SELECT attempt_count, first_attempt_at, blocked_until 
    INTO existing_count, first_attempt, blocked_until
    FROM failed_login_attempts 
    WHERE ip_address = p_ip_address 
    AND (blocked_until IS NULL OR blocked_until < NOW())
    ORDER BY created_at DESC 
    LIMIT 1;
    
    IF existing_count > 0 THEN
        -- Update existing record
        UPDATE failed_login_attempts 
        SET attempt_count = attempt_count + 1,
            last_attempt_at = NOW(),
            updated_at = NOW(),
            blocked_until = CASE 
                WHEN attempt_count + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 1 HOUR)
                ELSE NULL 
            END
        WHERE ip_address = p_ip_address 
        AND (blocked_until IS NULL OR blocked_until < NOW())
        ORDER BY created_at DESC 
        LIMIT 1;
    ELSE
        -- Insert new record
        INSERT INTO failed_login_attempts (
            ip_address, username, user_agent, attempt_count, 
            first_attempt_at, last_attempt_at, created_at, updated_at
        ) VALUES (
            p_ip_address, p_username, p_user_agent, 1,
            NOW(), NOW(), NOW(), NOW()
        );
    END IF;
END$$

-- Function to check if IP is blocked
CREATE FUNCTION IF NOT EXISTS is_ip_blocked(p_ip_address VARCHAR(45))
RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE blocked_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO blocked_count
    FROM failed_login_attempts 
    WHERE ip_address = p_ip_address 
    AND blocked_until IS NOT NULL 
    AND blocked_until > NOW();
    
    RETURN blocked_count > 0;
END$$

DELIMITER ;

-- ==============================================
-- TRIGGERS FOR STATISTICS
-- ==============================================

DELIMITER $$

CREATE TRIGGER IF NOT EXISTS update_user_count_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    UPDATE site_statistics SET stat_value = stat_value + 1, last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_users';
END$$

CREATE TRIGGER IF NOT EXISTS update_user_count_delete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    UPDATE site_statistics SET stat_value = stat_value - 1, last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_users';
END$$

CREATE TRIGGER IF NOT EXISTS update_cat_count_insert
AFTER INSERT ON cats
FOR EACH ROW
BEGIN
    UPDATE site_statistics SET stat_value = stat_value + 1, last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_cats';
END$$

CREATE TRIGGER IF NOT EXISTS update_cat_count_delete
AFTER DELETE ON cats
FOR EACH ROW
BEGIN
    UPDATE site_statistics SET stat_value = stat_value - 1, last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_cats';
END$$

DELIMITER ;

-- ==============================================
-- INITIAL DATA
-- ==============================================

-- Insert initial statistics
INSERT IGNORE INTO site_statistics (stat_name, stat_value) VALUES
('total_users', 0),
('total_cats', 0),
('total_logins', 0),
('active_sessions', 0),
('failed_login_attempts', 0),
('api_requests_blocked', 0),
('security_events_logged', 0);

-- Insert a default admin user (password: 'admin123456789!')
-- Password hash for 'admin123456789!' using password_hash() function
INSERT IGNORE INTO users (username, email, password_hash, name, role, active, email_verified, level, experience, coins) VALUES
('admin', 'admin@purrr.love', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', true, true, 1, 0, 1000);

-- Update statistics to current counts
UPDATE site_statistics SET stat_value = (SELECT COUNT(*) FROM users), last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_users';
UPDATE site_statistics SET stat_value = (SELECT COUNT(*) FROM cats), last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_cats';

-- ==============================================
-- COMPLETION MESSAGE
-- ==============================================

SELECT 'Purrr.love MySQL database initialization completed successfully!' as message;
SELECT 'Default admin user created: admin@purrr.love (password: admin123456789!)' as admin_info;
SELECT 'All tables, triggers, and stored procedures have been created.' as status;
