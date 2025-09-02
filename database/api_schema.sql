-- 🚀 Purrr.love API Database Schema
-- Complete database structure for OAuth2, API keys, and API functionality

-- OAuth2 Clients Table
CREATE TABLE oauth2_clients (
    id SERIAL PRIMARY KEY,
    client_id VARCHAR(255) UNIQUE NOT NULL,
    client_secret VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    redirect_uris TEXT[], -- Array of allowed redirect URIs
    scopes TEXT[], -- Array of allowed scopes
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- OAuth2 Client Redirect URIs (for validation)
CREATE TABLE oauth2_client_redirect_uris (
    id SERIAL PRIMARY KEY,
    client_id INTEGER REFERENCES oauth2_clients(id) ON DELETE CASCADE,
    redirect_uri VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(client_id, redirect_uri)
);

-- OAuth2 Authorization Codes
CREATE TABLE oauth2_authorization_codes (
    id SERIAL PRIMARY KEY,
    code VARCHAR(255) UNIQUE NOT NULL,
    client_id INTEGER REFERENCES oauth2_clients(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    redirect_uri VARCHAR(500) NOT NULL,
    scope VARCHAR(255) NOT NULL,
    state VARCHAR(255),
    code_challenge VARCHAR(255),
    code_challenge_method VARCHAR(10),
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- OAuth2 Access Tokens
CREATE TABLE oauth2_access_tokens (
    id SERIAL PRIMARY KEY,
    token VARCHAR(255) UNIQUE NOT NULL,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    client_id INTEGER REFERENCES oauth2_clients(id) ON DELETE CASCADE,
    scope VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    revoked BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- OAuth2 Refresh Tokens
CREATE TABLE oauth2_refresh_tokens (
    id SERIAL PRIMARY KEY,
    token VARCHAR(255) UNIQUE NOT NULL,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    client_id INTEGER REFERENCES oauth2_clients(id) ON DELETE CASCADE,
    scope VARCHAR(255) NOT NULL,
    access_token_id INTEGER REFERENCES oauth2_access_tokens(id) ON DELETE CASCADE,
    expires_at TIMESTAMP NOT NULL,
    revoked BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- OAuth2 Events Log
CREATE TABLE oauth2_events (
    id SERIAL PRIMARY KEY,
    event VARCHAR(100) NOT NULL,
    data JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- API Keys Table
CREATE TABLE api_keys (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    key_hash VARCHAR(255) UNIQUE NOT NULL,
    scopes TEXT[] NOT NULL,
    expires_at TIMESTAMP,
    ip_whitelist TEXT[], -- Array of allowed IP addresses
    last_used_at TIMESTAMP,
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- API Key Usage Tracking
CREATE TABLE api_key_usage (
    id SERIAL PRIMARY KEY,
    api_key_id INTEGER REFERENCES api_keys(id) ON DELETE CASCADE,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    status_code INTEGER NOT NULL,
    ip_address INET,
    response_time INTEGER, -- Response time in milliseconds
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- API Key Events Log
CREATE TABLE api_key_events (
    id SERIAL PRIMARY KEY,
    event VARCHAR(100) NOT NULL,
    data JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rate Limiting Table
CREATE TABLE rate_limits (
    id SERIAL PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL, -- User ID, IP, or API key
    endpoint VARCHAR(255) NOT NULL,
    requests_count INTEGER DEFAULT 1,
    window_start TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(identifier, endpoint, window_start)
);

-- API Request Logs
CREATE TABLE api_requests (
    id SERIAL PRIMARY KEY,
    request_id VARCHAR(255) UNIQUE NOT NULL,
    user_id INTEGER REFERENCES users(id),
    api_key_id INTEGER REFERENCES api_keys(id),
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    ip_address INET,
    user_agent TEXT,
    request_headers JSONB,
    request_body JSONB,
    response_status INTEGER,
    response_time INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User API Applications
CREATE TABLE user_api_applications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    website_url VARCHAR(500),
    callback_url VARCHAR(500),
    scopes TEXT[] DEFAULT ARRAY['read'],
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User API Application OAuth2 Clients
CREATE TABLE user_oauth2_clients (
    id SERIAL PRIMARY KEY,
    application_id INTEGER REFERENCES user_api_applications(id) ON DELETE CASCADE,
    client_id VARCHAR(255) UNIQUE NOT NULL,
    client_secret VARCHAR(255) NOT NULL,
    scopes TEXT[] DEFAULT ARRAY['read'],
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- API Endpoints Configuration
CREATE TABLE api_endpoints (
    id SERIAL PRIMARY KEY,
    path VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    required_scopes TEXT[],
    rate_limit_per_hour INTEGER DEFAULT 1000,
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(path, method)
);

-- API Endpoint Permissions
CREATE TABLE api_endpoint_permissions (
    id SERIAL PRIMARY KEY,
    endpoint_id INTEGER REFERENCES api_endpoints(id) ON DELETE CASCADE,
    role VARCHAR(100) NOT NULL,
    allowed_scopes TEXT[],
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(endpoint_id, role)
);

-- User API Statistics
CREATE TABLE user_api_stats (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    total_requests INTEGER DEFAULT 0,
    successful_requests INTEGER DEFAULT 0,
    failed_requests INTEGER DEFAULT 0,
    total_response_time INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, date)
);

-- API Error Logs
CREATE TABLE api_error_logs (
    id SERIAL PRIMARY KEY,
    request_id VARCHAR(255) REFERENCES api_requests(request_id),
    error_code VARCHAR(100) NOT NULL,
    error_message TEXT NOT NULL,
    error_details JSONB,
    stack_trace TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Webhook Subscriptions
CREATE TABLE webhook_subscriptions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    events TEXT[] NOT NULL, -- Array of event types to subscribe to
    secret VARCHAR(255), -- Webhook secret for signature verification
    active BOOLEAN DEFAULT true,
    last_delivery_at TIMESTAMP,
    last_delivery_status INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Webhook Deliveries
CREATE TABLE webhook_deliveries (
    id SERIAL PRIMARY KEY,
    subscription_id INTEGER REFERENCES webhook_subscriptions(id) ON DELETE CASCADE,
    event_type VARCHAR(100) NOT NULL,
    payload JSONB NOT NULL,
    delivery_url VARCHAR(500) NOT NULL,
    status_code INTEGER,
    response_body TEXT,
    attempts INTEGER DEFAULT 0,
    max_attempts INTEGER DEFAULT 3,
    next_retry_at TIMESTAMP,
    delivered_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for performance
CREATE INDEX idx_oauth2_auth_codes_code ON oauth2_authorization_codes(code);
CREATE INDEX idx_oauth2_auth_codes_expires ON oauth2_authorization_codes(expires_at);
CREATE INDEX idx_oauth2_access_tokens_token ON oauth2_access_tokens(token);
CREATE INDEX idx_oauth2_access_tokens_user ON oauth2_access_tokens(user_id);
CREATE INDEX idx_oauth2_refresh_tokens_token ON oauth2_refresh_tokens(token);
CREATE INDEX idx_oauth2_refresh_tokens_user ON oauth2_refresh_tokens(user_id);

CREATE INDEX idx_api_keys_hash ON api_keys(key_hash);
CREATE INDEX idx_api_keys_user ON api_keys(user_id);
CREATE INDEX idx_api_keys_active ON api_keys(active);
CREATE INDEX idx_api_key_usage_key ON api_key_usage(api_key_id);
CREATE INDEX idx_api_key_usage_created ON api_key_usage(created_at);

CREATE INDEX idx_rate_limits_identifier ON rate_limits(identifier);
CREATE INDEX idx_rate_limits_window ON rate_limits(window_start);
CREATE INDEX idx_api_requests_user ON api_requests(user_id);
CREATE INDEX idx_api_requests_created ON api_requests(created_at);
CREATE INDEX idx_api_requests_endpoint ON api_requests(endpoint);

CREATE INDEX idx_user_api_stats_user_date ON user_api_stats(user_id, date);
CREATE INDEX idx_webhook_subscriptions_user ON webhook_subscriptions(user_id);
CREATE INDEX idx_webhook_deliveries_subscription ON webhook_deliveries(subscription_id);

-- Insert default OAuth2 client for CLI
INSERT INTO oauth2_clients (client_id, client_secret, name, description, scopes, active) 
VALUES ('cli', 'cli_secret', 'Purrr.love CLI', 'Command-line interface client', ARRAY['read', 'write'], true);

-- Insert default API endpoints
INSERT INTO api_endpoints (path, method, name, description, required_scopes, rate_limit_per_hour) VALUES
('/api/v1/cats', 'GET', 'List Cats', 'Get user\'s cats', ARRAY['read'], 1000),
('/api/v1/cats', 'POST', 'Create Cat', 'Create a new cat', ARRAY['write'], 100),
('/api/v1/cats/{id}', 'GET', 'Get Cat', 'Get cat details', ARRAY['read'], 1000),
('/api/v1/cats/{id}', 'PUT', 'Update Cat', 'Update cat information', ARRAY['write'], 100),
('/api/v1/cats/{id}', 'DELETE', 'Delete Cat', 'Delete a cat', ARRAY['write'], 50),
('/api/v1/games', 'GET', 'List Games', 'Get available games', ARRAY['read'], 1000),
('/api/v1/games/{type}/play', 'POST', 'Play Game', 'Play a specific game', ARRAY['write'], 100),
('/api/v1/breeding/pairs', 'GET', 'Breeding Pairs', 'Get breeding pairs', ARRAY['read'], 500),
('/api/v1/breeding/breed', 'POST', 'Start Breeding', 'Start breeding process', ARRAY['write'], 50),
('/api/v1/quests', 'GET', 'List Quests', 'Get available quests', ARRAY['read'], 1000),
('/api/v1/quests/{id}/start', 'POST', 'Start Quest', 'Start a quest', ARRAY['write'], 100),
('/api/v1/store/items', 'GET', 'Store Items', 'Get store items', ARRAY['read'], 1000),
('/api/v1/store/purchase', 'POST', 'Purchase Item', 'Purchase store item', ARRAY['write'], 100),
('/api/v1/economy/balance', 'GET', 'Get Balance', 'Get crypto balance', ARRAY['read'], 1000),
('/api/v1/economy/deposit', 'POST', 'Create Deposit', 'Create crypto deposit', ARRAY['write'], 50),
('/api/v1/economy/withdraw', 'POST', 'Create Withdrawal', 'Create crypto withdrawal', ARRAY['write'], 50),
('/api/v1/social/friends', 'GET', 'List Friends', 'Get user friends', ARRAY['read'], 1000),
('/api/v1/social/friends/add', 'POST', 'Add Friend', 'Add a friend', ARRAY['write'], 100),
('/api/v1/keys', 'GET', 'List API Keys', 'Get user API keys', ARRAY['read'], 100),
('/api/v1/keys', 'POST', 'Create API Key', 'Generate new API key', ARRAY['write'], 50),
('/api/v1/keys/{id}', 'PUT', 'Update API Key', 'Update API key', ARRAY['write'], 100),
('/api/v1/keys/{id}', 'DELETE', 'Revoke API Key', 'Revoke API key', ARRAY['write'], 50);

-- Create function to update timestamps
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create triggers for updated_at columns
CREATE TRIGGER update_oauth2_clients_updated_at BEFORE UPDATE ON oauth2_clients FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_api_keys_updated_at BEFORE UPDATE ON api_keys FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_user_api_applications_updated_at BEFORE UPDATE ON user_api_applications FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Create function to clean up expired tokens
CREATE OR REPLACE FUNCTION cleanup_expired_tokens()
RETURNS void AS $$
BEGIN
    -- Clean up expired access tokens
    UPDATE oauth2_access_tokens SET revoked = true WHERE expires_at < CURRENT_TIMESTAMP AND revoked = false;
    
    -- Clean up expired refresh tokens
    UPDATE oauth2_refresh_tokens SET revoked = true WHERE expires_at < CURRENT_TIMESTAMP AND revoked = false;
    
    -- Clean up expired authorization codes
    DELETE FROM oauth2_authorization_codes WHERE expires_at < CURRENT_TIMESTAMP;
    
    -- Clean up expired API keys
    UPDATE api_keys SET active = false WHERE expires_at < CURRENT_TIMESTAMP AND active = true;
    
    -- Clean up old rate limit records (older than 24 hours)
    DELETE FROM rate_limits WHERE window_start < CURRENT_TIMESTAMP - INTERVAL '24 hours';
    
    -- Clean up old API request logs (older than 30 days)
    DELETE FROM api_requests WHERE created_at < CURRENT_TIMESTAMP - INTERVAL '30 days';
    
    -- Clean up old webhook deliveries (older than 7 days)
    DELETE FROM webhook_deliveries WHERE created_at < CURRENT_TIMESTAMP - INTERVAL '7 days';
END;
$$ LANGUAGE plpgsql;

-- Create function to get user API statistics
CREATE OR REPLACE FUNCTION get_user_api_stats(user_id_param INTEGER, days_back INTEGER DEFAULT 30)
RETURNS TABLE(
    date DATE,
    total_requests BIGINT,
    successful_requests BIGINT,
    failed_requests BIGINT,
    avg_response_time NUMERIC
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        ar.created_at::DATE as date,
        COUNT(*) as total_requests,
        COUNT(CASE WHEN ar.response_status < 400 THEN 1 END) as successful_requests,
        COUNT(CASE WHEN ar.response_status >= 400 THEN 1 END) as failed_requests,
        AVG(ar.response_time) as avg_response_time
    FROM api_requests ar
    WHERE ar.user_id = user_id_param
    AND ar.created_at >= CURRENT_DATE - INTERVAL '1 day' * days_back
    GROUP BY ar.created_at::DATE
    ORDER BY date DESC;
END;
$$ LANGUAGE plpgsql;

-- Grant permissions (adjust as needed for your setup)
GRANT USAGE ON SCHEMA public TO purrr_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO purrr_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO purrr_user;
GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO purrr_user;
