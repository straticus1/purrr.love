-- 🔐 Purrr.love Security Database Schema (MySQL)
-- Security logging, audit trails, and monitoring tables

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

-- API Security Events Table
CREATE TABLE IF NOT EXISTS api_security_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(100) NOT NULL,
    endpoint VARCHAR(255),
    method VARCHAR(10),
    ip_address VARCHAR(45), -- Supports IPv6
    user_id INT,
    api_key_id INT,
    request_data JSON,
    response_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_api_security_event_type (event_type),
    INDEX idx_api_security_endpoint (endpoint),
    INDEX idx_api_security_ip (ip_address),
    INDEX idx_api_security_user (user_id),
    INDEX idx_api_security_created (created_at)
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

-- OAuth2 Refresh Tokens Table
CREATE TABLE IF NOT EXISTS oauth2_refresh_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    access_token_id INT NOT NULL,
    user_id INT NOT NULL,
    client_id VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    revoked BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (access_token_id) REFERENCES oauth2_access_tokens(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_oauth2_refresh_tokens_access (access_token_id),
    INDEX idx_oauth2_refresh_tokens_user (user_id),
    INDEX idx_oauth2_refresh_tokens_token (token),
    INDEX idx_oauth2_refresh_tokens_client (client_id),
    INDEX idx_oauth2_refresh_tokens_expires (expires_at),
    INDEX idx_oauth2_refresh_tokens_revoked (revoked)
) ENGINE=InnoDB;

-- Audit Trail Table
CREATE TABLE IF NOT EXISTS audit_trail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_values JSON,
    new_values JSON,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_trail_table (table_name),
    INDEX idx_audit_trail_record (table_name, record_id),
    INDEX idx_audit_trail_action (action),
    INDEX idx_audit_trail_user (user_id),
    INDEX idx_audit_trail_created (created_at)
) ENGINE=InnoDB;

-- Create stored procedure for recording failed login attempts (MySQL equivalent)
DELIMITER $$

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

DELIMITER ;

-- Create function to check if IP is blocked
DELIMITER $$

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

-- Create function to get client IP address
DELIMITER $$

CREATE FUNCTION IF NOT EXISTS get_client_ip()
RETURNS VARCHAR(45)
READS SQL DATA
DETERMINISTIC
BEGIN
    -- This is a placeholder function - actual IP detection should be done in PHP
    RETURN '127.0.0.1';
END$$

DELIMITER ;

-- Insert default security settings
INSERT IGNORE INTO site_statistics (stat_name, stat_value) VALUES
('failed_login_attempts', 0),
('api_requests_blocked', 0),
('security_events_logged', 0);

-- Create triggers for security logging
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS log_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_trail (table_name, record_id, action, new_values, created_at)
    VALUES ('users', NEW.id, 'INSERT', JSON_OBJECT('username', NEW.username, 'email', NEW.email, 'role', NEW.role), NOW());
END$$

CREATE TRIGGER IF NOT EXISTS log_user_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_trail (table_name, record_id, action, old_values, new_values, created_at)
    VALUES ('users', NEW.id, 'UPDATE', 
        JSON_OBJECT('username', OLD.username, 'email', OLD.email, 'role', OLD.role),
        JSON_OBJECT('username', NEW.username, 'email', NEW.email, 'role', NEW.role),
        NOW());
END$$

CREATE TRIGGER IF NOT EXISTS log_user_delete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_trail (table_name, record_id, action, old_values, created_at)
    VALUES ('users', OLD.id, 'DELETE', 
        JSON_OBJECT('username', OLD.username, 'email', OLD.email, 'role', OLD.role),
        NOW());
END$$

DELIMITER ;
