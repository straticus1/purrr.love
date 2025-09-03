-- 🚀 Purrr.love Database Quick Setup (MySQL CLI)
-- Run this with: mysql -u root -p < create-database.sql

-- Create database
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
    last_login TIMESTAMP NULL
) ENGINE=InnoDB;

-- Cats table
CREATE TABLE IF NOT EXISTS cats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT,
    user_id INT,
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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Site statistics
CREATE TABLE IF NOT EXISTS site_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_name VARCHAR(100) UNIQUE NOT NULL,
    stat_value BIGINT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert initial data
INSERT IGNORE INTO site_statistics (stat_name, stat_value) VALUES
('total_users', 0),
('total_cats', 0),
('total_logins', 0),
('active_sessions', 0);

-- Insert default admin user (password: 'password')  
INSERT IGNORE INTO users (username, email, password_hash, name, role, active, email_verified) VALUES
('admin', 'admin@purrr.love', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', true, true);

-- Update statistics
UPDATE site_statistics SET stat_value = (SELECT COUNT(*) FROM users), last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_users';
UPDATE site_statistics SET stat_value = (SELECT COUNT(*) FROM cats), last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_cats';
