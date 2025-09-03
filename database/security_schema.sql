-- 🔐 Purrr.love Security Database Schema
-- Security logging, audit trails, and monitoring tables

-- Security Logs Table
CREATE TABLE security_logs (
    id SERIAL PRIMARY KEY,
    event VARCHAR(100) NOT NULL,
    level VARCHAR(20) NOT NULL DEFAULT 'INFO',
    ip_address INET,
    user_agent TEXT,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    data JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for performance
CREATE INDEX idx_security_logs_event ON security_logs(event);
CREATE INDEX idx_security_logs_level ON security_logs(level);
CREATE INDEX idx_security_logs_ip ON security_logs(ip_address);
CREATE INDEX idx_security_logs_user ON security_logs(user_id);
CREATE INDEX idx_security_logs_created ON security_logs(created_at);

-- Failed Login Attempts Table
CREATE TABLE failed_login_attempts (
    id SERIAL PRIMARY KEY,
    ip_address INET NOT NULL,
    username VARCHAR(255),
    user_agent TEXT,
    attempt_count INTEGER DEFAULT 1,
    first_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    blocked_until TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for performance
CREATE INDEX idx_failed_login_ip ON failed_login_attempts(ip_address);
CREATE INDEX idx_failed_login_username ON failed_login_attempts(username);
CREATE INDEX idx_failed_login_blocked ON failed_login_attempts(blocked_until);

-- API Security Events Table
CREATE TABLE api_security_events (
    id SERIAL PRIMARY KEY,
    event_type VARCHAR(100) NOT NULL,
    endpoint VARCHAR(255),
    method VARCHAR(10),
    ip_address INET,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    api_key_id INTEGER REFERENCES api_keys(id) ON DELETE SET NULL,
    request_data JSONB,
    response_code INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for performance
CREATE INDEX idx_api_security_event_type ON api_security_events(event_type);
CREATE INDEX idx_api_security_endpoint ON api_security_events(endpoint);
CREATE INDEX idx_api_security_ip ON api_security_events(ip_address);
CREATE INDEX idx_api_security_user ON api_security_events(user_id);
CREATE INDEX idx_api_security_created ON api_security_events(created_at);

-- Rate Limit Violations Table
CREATE TABLE rate_limit_violations (
    id SERIAL PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    ip_address INET,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    violation_count INTEGER DEFAULT 1,
    first_violation_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_violation_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for performance
CREATE INDEX idx_rate_limit_violations_identifier ON rate_limit_violations(identifier);
CREATE INDEX idx_rate_limit_violations_endpoint ON rate_limit_violations(endpoint);
CREATE INDEX idx_rate_limit_violations_ip ON rate_limit_violations(ip_address);
CREATE INDEX idx_rate_limit_violations_user ON rate_limit_violations(user_id);

-- Security Alerts Table
CREATE TABLE security_alerts (
    id SERIAL PRIMARY KEY,
    alert_type VARCHAR(100) NOT NULL,
    severity VARCHAR(20) NOT NULL DEFAULT 'MEDIUM',
    title VARCHAR(255) NOT NULL,
    description TEXT,
    ip_address INET,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    data JSONB,
    acknowledged BOOLEAN DEFAULT FALSE,
    acknowledged_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    acknowledged_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for performance
CREATE INDEX idx_security_alerts_type ON security_alerts(alert_type);
CREATE INDEX idx_security_alerts_severity ON security_alerts(severity);
CREATE INDEX idx_security_alerts_acknowledged ON security_alerts(acknowledged);
CREATE INDEX idx_security_alerts_created ON security_alerts(created_at);

-- User Security Settings Table
CREATE TABLE user_security_settings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE UNIQUE,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_method VARCHAR(50) DEFAULT 'totp',
    login_notifications BOOLEAN DEFAULT TRUE,
    suspicious_activity_alerts BOOLEAN DEFAULT TRUE,
    session_timeout_minutes INTEGER DEFAULT 60,
    max_concurrent_sessions INTEGER DEFAULT 3,
    password_last_changed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for performance
CREATE INDEX idx_user_security_user ON user_security_settings(user_id);

-- Security Policy Rules Table
CREATE TABLE security_policy_rules (
    id SERIAL PRIMARY KEY,
    rule_name VARCHAR(255) NOT NULL UNIQUE,
    rule_type VARCHAR(100) NOT NULL,
    rule_config JSONB NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    priority INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for performance
CREATE INDEX idx_security_policy_type ON security_policy_rules(rule_type);
CREATE INDEX idx_security_policy_active ON security_policy_rules(active);
CREATE INDEX idx_security_policy_priority ON security_policy_rules(priority);

-- Insert default security policies
INSERT INTO security_policy_rules (rule_name, rule_type, rule_config, priority) VALUES
('max_failed_logins', 'login_security', '{"max_attempts": 5, "block_duration_minutes": 15}', 1),
('password_complexity', 'password_policy', '{"min_length": 12, "require_uppercase": true, "require_lowercase": true, "require_numbers": true, "require_special": true}', 2),
('session_security', 'session_policy', '{"max_lifetime_minutes": 60, "regenerate_id_interval": 5}', 3),
('api_rate_limiting', 'rate_limit_policy', '{"default_limit": 1000, "burst_limit": 2000, "window_seconds": 3600}', 4);

-- Create function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create triggers for updated_at columns
CREATE TRIGGER update_failed_login_attempts_updated_at 
    BEFORE UPDATE ON failed_login_attempts 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_rate_limit_violations_updated_at 
    BEFORE UPDATE ON rate_limit_violations 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_security_alerts_updated_at 
    BEFORE UPDATE ON security_alerts 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_user_security_settings_updated_at 
    BEFORE UPDATE ON user_security_settings 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_security_policy_rules_updated_at 
    BEFORE UPDATE ON security_policy_rules 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Create function to clean up old security logs
CREATE OR REPLACE FUNCTION cleanup_old_security_logs()
RETURNS void AS $$
BEGIN
    -- Clean up security logs older than 90 days
    DELETE FROM security_logs WHERE created_at < CURRENT_TIMESTAMP - INTERVAL '90 days';
    
    -- Clean up failed login attempts older than 30 days
    DELETE FROM failed_login_attempts WHERE created_at < CURRENT_TIMESTAMP - INTERVAL '30 days';
    
    -- Clean up API security events older than 60 days
    DELETE FROM api_security_events WHERE created_at < CURRENT_TIMESTAMP - INTERVAL '60 days';
    
    -- Clean up rate limit violations older than 7 days
    DELETE FROM rate_limit_violations WHERE created_at < CURRENT_TIMESTAMP - INTERVAL '7 days';
    
    -- Clean up old security alerts (acknowledged and older than 30 days)
    DELETE FROM security_alerts WHERE acknowledged = TRUE AND created_at < CURRENT_TIMESTAMP - INTERVAL '30 days';
END;
$$ LANGUAGE plpgsql;

-- Create function to check if IP is blocked
CREATE OR REPLACE FUNCTION is_ip_blocked(ip_address INET)
RETURNS BOOLEAN AS $$
DECLARE
    blocked_until TIMESTAMP;
BEGIN
    SELECT blocked_until INTO blocked_until 
    FROM failed_login_attempts 
    WHERE ip_address = $1 AND blocked_until > CURRENT_TIMESTAMP 
    ORDER BY last_attempt_at DESC 
    LIMIT 1;
    
    RETURN blocked_until IS NOT NULL;
END;
$$ LANGUAGE plpgsql;

-- Create function to record failed login attempt
CREATE OR REPLACE FUNCTION record_failed_login(ip_address INET, username VARCHAR, user_agent TEXT)
RETURNS void AS $$
DECLARE
    existing_record RECORD;
    max_attempts INTEGER;
    block_duration_minutes INTEGER;
BEGIN
    -- Get security policy
    SELECT rule_config->>'max_attempts' INTO max_attempts,
           rule_config->>'block_duration_minutes' INTO block_duration_minutes
    FROM security_policy_rules 
    WHERE rule_name = 'max_failed_logins';
    
    -- Set defaults if policy not found
    max_attempts := COALESCE(max_attempts::INTEGER, 5);
    block_duration_minutes := COALESCE(block_duration_minutes::INTEGER, 15);
    
    -- Check if IP already has failed attempts
    SELECT * INTO existing_record 
    FROM failed_login_attempts 
    WHERE ip_address = $1 
    ORDER BY last_attempt_at DESC 
    LIMIT 1;
    
    IF existing_record IS NOT NULL THEN
        -- Update existing record
        UPDATE failed_login_attempts 
        SET attempt_count = attempt_count + 1,
            last_attempt_at = CURRENT_TIMESTAMP,
            blocked_until = CASE 
                WHEN attempt_count + 1 >= max_attempts 
                THEN CURRENT_TIMESTAMP + (block_duration_minutes || ' minutes')::INTERVAL
                ELSE blocked_until
            END
        WHERE id = existing_record.id;
    ELSE
        -- Create new record
        INSERT INTO failed_login_attempts (ip_address, username, user_agent)
        VALUES ($1, $2, $3);
    END IF;
END;
$$ LANGUAGE plpgsql;

-- Create view for security dashboard
CREATE VIEW security_dashboard AS
SELECT 
    'security_logs' as table_name,
    COUNT(*) as record_count,
    MAX(created_at) as latest_record
FROM security_logs
UNION ALL
SELECT 
    'failed_login_attempts' as table_name,
    COUNT(*) as record_count,
    MAX(created_at) as latest_record
FROM failed_login_attempts
UNION ALL
SELECT 
    'api_security_events' as table_name,
    COUNT(*) as record_count,
    MAX(created_at) as latest_record
FROM api_security_events
UNION ALL
SELECT 
    'rate_limit_violations' as table_name,
    COUNT(*) as record_count,
    MAX(created_at) as latest_record
FROM rate_limit_violations
UNION ALL
SELECT 
    'security_alerts' as table_name,
    COUNT(*) as record_count,
    MAX(created_at) as latest_record
FROM security_alerts;

-- Grant permissions (adjust as needed for your setup)
-- GRANT SELECT, INSERT, UPDATE ON ALL TABLES IN SCHEMA public TO purrr_user;
-- GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO purrr_user;
