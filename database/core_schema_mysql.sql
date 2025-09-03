-- 🚀 Purrr.love Core Database Schema (MySQL)
-- Essential tables: users, cats, and basic functionality

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS purrr_love CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE purrr_love;

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
    microchip_id VARCHAR(50),
    is_neutered BOOLEAN DEFAULT false,
    is_indoor BOOLEAN DEFAULT true,
    bio TEXT,
    avatar_url VARCHAR(500),
    status ENUM('active', 'lost', 'found', 'deceased') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_cats_owner (owner_id),
    INDEX idx_cats_user (user_id),
    INDEX idx_cats_status (status),
    INDEX idx_cats_breed (breed)
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

-- Basic statistics table
CREATE TABLE IF NOT EXISTS site_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_name VARCHAR(100) UNIQUE NOT NULL,
    stat_value BIGINT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_stats_name (stat_name)
) ENGINE=InnoDB;

-- Insert initial statistics
INSERT IGNORE INTO site_statistics (stat_name, stat_value) VALUES
('total_users', 0),
('total_cats', 0),
('total_logins', 0),
('active_sessions', 0);

-- Create triggers for statistics updates
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

-- Insert a default admin user (password: 'admin123456789!')
-- Password hash for 'admin123456789!' using password_hash() function
INSERT IGNORE INTO users (username, email, password_hash, name, role, active, email_verified) VALUES
('admin', 'admin@purrr.love', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', true, true);

-- Update statistics to current counts
UPDATE site_statistics SET stat_value = (SELECT COUNT(*) FROM users), last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_users';
UPDATE site_statistics SET stat_value = (SELECT COUNT(*) FROM cats), last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_cats';
