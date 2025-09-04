CREATE DATABASE IF NOT EXISTS purrr_love; 
USE purrr_love; 
CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(255) UNIQUE NOT NULL, email VARCHAR(255) UNIQUE NOT NULL, password_hash VARCHAR(255) NOT NULL, name VARCHAR(255), role ENUM('user','admin','moderator') DEFAULT 'user', active BOOLEAN DEFAULT true, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP); 
CREATE TABLE IF NOT EXISTS cats (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, owner_id INT, name VARCHAR(255) NOT NULL, breed VARCHAR(100), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (owner_id) REFERENCES users(id)); 
CREATE TABLE IF NOT EXISTS site_statistics (id INT AUTO_INCREMENT PRIMARY KEY, stat_name VARCHAR(100) UNIQUE NOT NULL, stat_value BIGINT DEFAULT 0, last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP); 
INSERT IGNORE INTO users (username, email, password_hash, name, role, active) VALUES ('admin', 'admin@purrr.love', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', true); 
INSERT IGNORE INTO site_statistics (stat_name, stat_value) VALUES ('total_users', 1), ('total_cats', 0), ('total_logins', 0), ('active_sessions', 0);
