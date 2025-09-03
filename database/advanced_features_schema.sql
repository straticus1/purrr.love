-- =====================================================
-- 🚀 Purrr.love Advanced Features Database Schema
-- Blockchain Ownership, ML Personality, Metaverse, Webhooks
-- =====================================================

-- =====================================================
-- 🧠 Machine Learning & Personality System
-- =====================================================

-- Cat personality predictions and ML results
CREATE TABLE cat_personality_predictions (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    personality_scores JSONB NOT NULL, -- 5-factor personality scores
    confidence_scores JSONB NOT NULL, -- Confidence for each dimension
    prediction_method VARCHAR(50) NOT NULL DEFAULT 'ml_enhanced',
    ml_model_version VARCHAR(20),
    training_data_size INTEGER,
    prediction_accuracy DECIMAL(5,4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Behavioral observations for ML training
CREATE TABLE cat_behavior_observations (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    behavior_type VARCHAR(100) NOT NULL,
    behavior_subtype VARCHAR(100),
    intensity_level INTEGER CHECK (intensity_level >= 1 AND intensity_level <= 10),
    duration_seconds INTEGER,
    environmental_context JSONB, -- Weather, time, location, etc.
    human_presence BOOLEAN DEFAULT false,
    other_cats_present INTEGER DEFAULT 0,
    observed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observer_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    confidence_score DECIMAL(3,2) DEFAULT 1.0
);

-- Genetic data for personality correlation
CREATE TABLE cat_genetic_data (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    genetic_markers JSONB NOT NULL, -- Specific genetic markers
    heritage_score INTEGER CHECK (heritage_score >= 0 AND heritage_score <= 100),
    breed_purity_percentage DECIMAL(5,2),
    coat_pattern VARCHAR(50),
    eye_color VARCHAR(30),
    genetic_health_risks JSONB,
    dna_test_date DATE,
    dna_test_provider VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Behavioral data aggregation
CREATE TABLE cat_behavioral_data (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    behavioral_data JSONB NOT NULL, -- Aggregated behavioral patterns
    observation_period INTEGER NOT NULL, -- Days of observation
    data_quality_score DECIMAL(3,2) DEFAULT 1.0,
    last_observation_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ML model training history
CREATE TABLE ml_model_training_history (
    id SERIAL PRIMARY KEY,
    model_name VARCHAR(100) NOT NULL,
    model_version VARCHAR(20) NOT NULL,
    training_data_size INTEGER NOT NULL,
    training_accuracy DECIMAL(5,4),
    validation_accuracy DECIMAL(5,4),
    training_duration_seconds INTEGER,
    hyperparameters JSONB,
    feature_importance JSONB,
    model_performance_metrics JSONB,
    trained_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deployed_at TIMESTAMP,
    status VARCHAR(20) DEFAULT 'training' CHECK (status IN ('training', 'completed', 'failed', 'deployed'))
);

-- =====================================================
-- ⛓️ Blockchain & NFT System
-- =====================================================

-- NFT records
CREATE TABLE nfts (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    network VARCHAR(50) NOT NULL CHECK (network IN ('ethereum', 'polygon', 'binance_smart_chain', 'solana')),
    token_id VARCHAR(255) NOT NULL,
    contract_address VARCHAR(255),
    metadata JSONB NOT NULL,
    transaction_hash VARCHAR(255) NOT NULL,
    block_number BIGINT,
    gas_used INTEGER,
    mint_cost_eth DECIMAL(20,18),
    royalty_percentage DECIMAL(5,2) DEFAULT 2.5,
    verification_status VARCHAR(20) DEFAULT 'pending' CHECK (verification_status IN ('pending', 'verified', 'failed')),
    last_verified TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(cat_id),
    UNIQUE(network, token_id)
);

-- NFT marketplace listings
CREATE TABLE nft_listings (
    id SERIAL PRIMARY KEY,
    nft_id INTEGER REFERENCES nfts(id) ON DELETE CASCADE,
    seller_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    price DECIMAL(20,8) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'ETH',
    duration_days INTEGER NOT NULL DEFAULT 30,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'sold', 'expired', 'cancelled')),
    expires_at TIMESTAMP NOT NULL,
    views_count INTEGER DEFAULT 0,
    likes_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- NFT transactions
CREATE TABLE nft_transactions (
    id SERIAL PRIMARY KEY,
    nft_id INTEGER REFERENCES nfts(id) ON DELETE CASCADE,
    from_user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    to_user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    transaction_type VARCHAR(20) NOT NULL CHECK (transaction_type IN ('mint', 'transfer', 'sale', 'auction')),
    network VARCHAR(50) NOT NULL,
    transaction_hash VARCHAR(255) NOT NULL,
    block_number BIGINT,
    gas_used INTEGER,
    transaction_fee_eth DECIMAL(20,18),
    price_eth DECIMAL(20,18),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'failed')),
    confirmed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- NFT ownership verification logs
CREATE TABLE nft_verification_logs (
    id SERIAL PRIMARY KEY,
    nft_id INTEGER REFERENCES nfts(id) ON DELETE CASCADE,
    verification_type VARCHAR(50) NOT NULL,
    blockchain_owner VARCHAR(255),
    database_owner INTEGER REFERENCES users(id) ON DELETE SET NULL,
    verification_result BOOLEAN NOT NULL,
    verification_details JSONB,
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 🌐 Metaverse & VR System
-- =====================================================

-- Metaverse worlds
CREATE TABLE metaverse_worlds (
    id SERIAL PRIMARY KEY,
    world_name VARCHAR(100) NOT NULL,
    world_type VARCHAR(50) NOT NULL CHECK (world_type IN ('cat_park', 'virtual_home', 'adventure_zone', 'social_hub', 'custom')),
    creator_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    world_settings JSONB NOT NULL, -- Graphics, physics, rules, etc.
    max_players INTEGER DEFAULT 50,
    current_players INTEGER DEFAULT 0,
    world_status VARCHAR(20) DEFAULT 'active' CHECK (world_status IN ('active', 'maintenance', 'closed')),
    access_level VARCHAR(20) DEFAULT 'public' CHECK (access_level IN ('public', 'friends', 'private', 'invite_only')),
    world_rating DECIMAL(3,2) DEFAULT 0.0,
    total_visits INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- World instances (for scaling)
CREATE TABLE world_instances (
    id SERIAL PRIMARY KEY,
    world_id INTEGER REFERENCES metaverse_worlds(id) ON DELETE CASCADE,
    instance_name VARCHAR(100) NOT NULL,
    instance_status VARCHAR(20) DEFAULT 'active' CHECK (instance_status IN ('active', 'full', 'maintenance', 'shutdown')),
    current_players INTEGER DEFAULT 0,
    max_players INTEGER NOT NULL,
    server_location VARCHAR(100),
    instance_metrics JSONB, -- Performance, latency, etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User world sessions
CREATE TABLE user_world_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    world_id INTEGER REFERENCES metaverse_worlds(id) ON DELETE CASCADE,
    instance_id INTEGER REFERENCES world_instances(id) ON DELETE SET NULL,
    cat_id INTEGER REFERENCES cats(id) ON DELETE SET NULL,
    session_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_end TIMESTAMP,
    session_duration_seconds INTEGER,
    vr_device_type VARCHAR(50),
    interaction_count INTEGER DEFAULT 0,
    session_data JSONB -- Chat logs, movements, interactions
);

-- VR interactions
CREATE TABLE vr_interactions (
    id SERIAL PRIMARY KEY,
    session_id INTEGER REFERENCES user_world_sessions(id) ON DELETE CASCADE,
    interaction_type VARCHAR(50) NOT NULL CHECK (interaction_type IN ('movement', 'chat', 'gesture', 'object_interaction', 'cat_interaction')),
    interaction_data JSONB NOT NULL,
    coordinates JSONB, -- 3D coordinates
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Social VR spaces
CREATE TABLE social_vr_spaces (
    id SERIAL PRIMARY KEY,
    world_id INTEGER REFERENCES metaverse_worlds(id) ON DELETE CASCADE,
    space_name VARCHAR(100) NOT NULL,
    space_type VARCHAR(50) NOT NULL CHECK (space_type IN ('chat_room', 'gaming_area', 'relaxation_zone', 'event_space')),
    max_capacity INTEGER DEFAULT 20,
    current_occupants INTEGER DEFAULT 0,
    space_rules JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 🔗 Webhook System
-- =====================================================

-- Webhook subscriptions
CREATE TABLE webhook_subscriptions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    webhook_url TEXT NOT NULL,
    events JSONB NOT NULL, -- Array of event types to listen for
    secret_key VARCHAR(255), -- For signature verification
    headers JSONB, -- Custom headers
    is_active BOOLEAN DEFAULT true,
    retry_count INTEGER DEFAULT 0,
    max_retries INTEGER DEFAULT 3,
    last_delivery_attempt TIMESTAMP,
    last_successful_delivery TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Webhook delivery queue
CREATE TABLE webhook_delivery_queue (
    id SERIAL PRIMARY KEY,
    subscription_id INTEGER REFERENCES webhook_subscriptions(id) ON DELETE CASCADE,
    event_type VARCHAR(100) NOT NULL,
    event_data JSONB NOT NULL,
    delivery_status VARCHAR(20) DEFAULT 'pending' CHECK (delivery_status IN ('pending', 'delivering', 'delivered', 'failed', 'retrying')),
    attempt_count INTEGER DEFAULT 0,
    max_attempts INTEGER DEFAULT 3,
    next_retry_at TIMESTAMP,
    last_attempt_at TIMESTAMP,
    last_response_code INTEGER,
    last_response_body TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivered_at TIMESTAMP
);

-- Webhook delivery logs
CREATE TABLE webhook_delivery_logs (
    id SERIAL PRIMARY KEY,
    delivery_id INTEGER REFERENCES webhook_delivery_queue(id) ON DELETE CASCADE,
    subscription_id INTEGER REFERENCES webhook_subscriptions(id) ON DELETE CASCADE,
    event_type VARCHAR(100) NOT NULL,
    delivery_status VARCHAR(20) NOT NULL,
    response_code INTEGER,
    response_body TEXT,
    response_headers JSONB,
    delivery_time_ms INTEGER,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 📊 Advanced Analytics & Metrics
-- =====================================================

-- User behavior analytics
CREATE TABLE user_behavior_analytics (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    session_date DATE NOT NULL,
    total_session_time INTEGER, -- seconds
    page_views INTEGER DEFAULT 0,
    actions_performed INTEGER DEFAULT 0,
    cats_interacted_with INTEGER DEFAULT 0,
    games_played INTEGER DEFAULT 0,
    social_interactions INTEGER DEFAULT 0,
    engagement_score DECIMAL(5,2),
    retention_factor DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cat interaction analytics
CREATE TABLE cat_interaction_analytics (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    interaction_date DATE NOT NULL,
    interaction_type VARCHAR(50) NOT NULL,
    interaction_duration INTEGER, -- seconds
    interaction_quality_score DECIMAL(3,2),
    user_satisfaction_rating INTEGER CHECK (user_satisfaction_rating >= 1 AND user_satisfaction_rating <= 5),
    cat_response_type VARCHAR(50),
    environmental_factors JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- System performance metrics
CREATE TABLE system_performance_metrics (
    id SERIAL PRIMARY KEY,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,4) NOT NULL,
    metric_unit VARCHAR(20),
    metric_category VARCHAR(50),
    server_instance VARCHAR(100),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 🔐 Enhanced Security & Monitoring
-- =====================================================

-- API usage analytics
CREATE TABLE api_usage_analytics (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    api_key_id INTEGER REFERENCES api_keys(id) ON DELETE SET NULL,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    response_time_ms INTEGER,
    response_code INTEGER,
    request_size_bytes INTEGER,
    response_size_bytes INTEGER,
    ip_address INET,
    user_agent TEXT,
    success BOOLEAN NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Security event correlations
CREATE TABLE security_event_correlations (
    id SERIAL PRIMARY KEY,
    primary_event_id INTEGER REFERENCES security_logs(id) ON DELETE CASCADE,
    correlated_event_id INTEGER REFERENCES security_logs(id) ON DELETE CASCADE,
    correlation_type VARCHAR(50) NOT NULL,
    correlation_strength DECIMAL(3,2),
    correlation_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User risk assessment
CREATE TABLE user_risk_assessments (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    risk_score INTEGER CHECK (risk_score >= 0 AND risk_score <= 100),
    risk_factors JSONB,
    risk_level VARCHAR(20) CHECK (risk_level IN ('low', 'medium', 'high', 'critical')),
    mitigation_actions JSONB,
    assessment_date DATE NOT NULL,
    next_assessment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 🎮 Enhanced Gaming & Social Features
-- =====================================================

-- Cat competitions and shows
CREATE TABLE cat_competitions (
    id SERIAL PRIMARY KEY,
    competition_name VARCHAR(200) NOT NULL,
    competition_type VARCHAR(50) NOT NULL CHECK (competition_type IN ('beauty', 'talent', 'agility', 'personality', 'breed_specific')),
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NOT NULL,
    entry_fee DECIMAL(10,2) DEFAULT 0.00,
    max_participants INTEGER,
    current_participants INTEGER DEFAULT 0,
    prize_pool JSONB,
    competition_rules JSONB,
    status VARCHAR(20) DEFAULT 'upcoming' CHECK (status IN ('upcoming', 'registration', 'active', 'judging', 'completed', 'cancelled')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Competition participants
CREATE TABLE competition_participants (
    id SERIAL PRIMARY KEY,
    competition_id INTEGER REFERENCES cat_competitions(id) ON DELETE CASCADE,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    participation_status VARCHAR(20) DEFAULT 'registered' CHECK (participation_status IN ('registered', 'active', 'eliminated', 'winner', 'disqualified')),
    scores JSONB, -- Judging scores
    final_rank INTEGER,
    special_awards JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Real-time multiplayer sessions
CREATE TABLE multiplayer_sessions (
    id SERIAL PRIMARY KEY,
    session_name VARCHAR(100) NOT NULL,
    session_type VARCHAR(50) NOT NULL CHECK (session_type IN ('cat_race', 'hunting_game', 'social_play', 'training_session')),
    max_players INTEGER NOT NULL,
    current_players INTEGER DEFAULT 0,
    session_status VARCHAR(20) DEFAULT 'waiting' CHECK (session_status IN ('waiting', 'active', 'completed', 'cancelled')),
    game_settings JSONB,
    session_data JSONB, -- Game state, scores, etc.
    started_at TIMESTAMP,
    ended_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Multiplayer session participants
CREATE TABLE multiplayer_participants (
    id SERIAL PRIMARY KEY,
    session_id INTEGER REFERENCES multiplayer_sessions(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    join_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leave_time TIMESTAMP,
    final_score INTEGER DEFAULT 0,
    achievements JSONB,
    participation_duration INTEGER -- seconds
);

-- =====================================================
-- 📱 Cross-Platform Integration
-- =====================================================

-- Platform integrations
CREATE TABLE platform_integrations (
    id SERIAL PRIMARY KEY,
    platform_name VARCHAR(100) NOT NULL,
    integration_type VARCHAR(50) NOT NULL CHECK (integration_type IN ('mobile_app', 'desktop_app', 'web_app', 'vr_app', 'third_party')),
    api_endpoint VARCHAR(255),
    authentication_method VARCHAR(50),
    integration_settings JSONB,
    is_active BOOLEAN DEFAULT true,
    last_sync_at TIMESTAMP,
    sync_status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cross-platform user sessions
CREATE TABLE cross_platform_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    platform_id INTEGER REFERENCES platform_integrations(id) ON DELETE CASCADE,
    session_token VARCHAR(255) NOT NULL,
    device_info JSONB,
    location_data JSONB,
    session_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_end TIMESTAMP,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 🔄 Data Synchronization & Backup
-- =====================================================

-- Data sync logs
CREATE TABLE data_sync_logs (
    id SERIAL PRIMARY KEY,
    sync_type VARCHAR(50) NOT NULL CHECK (sync_type IN ('full_backup', 'incremental_sync', 'cross_platform_sync', 'blockchain_sync')),
    source_system VARCHAR(100),
    target_system VARCHAR(100),
    records_processed INTEGER DEFAULT 0,
    records_synced INTEGER DEFAULT 0,
    sync_duration_seconds INTEGER,
    sync_status VARCHAR(20) DEFAULT 'running' CHECK (sync_status IN ('running', 'completed', 'failed', 'partial')),
    error_message TEXT,
    sync_metadata JSONB,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP
);

-- =====================================================
-- 📈 Performance & Scalability
-- =====================================================

-- Database performance metrics
CREATE TABLE database_performance_metrics (
    id SERIAL PRIMARY KEY,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,4) NOT NULL,
    table_name VARCHAR(100),
    index_name VARCHAR(100),
    query_type VARCHAR(50),
    execution_time_ms INTEGER,
    rows_affected INTEGER,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cache performance metrics
CREATE TABLE cache_performance_metrics (
    id SERIAL PRIMARY KEY,
    cache_type VARCHAR(50) NOT NULL,
    operation_type VARCHAR(50) NOT NULL,
    hit_rate DECIMAL(5,2),
    miss_rate DECIMAL(5,2),
    average_response_time_ms INTEGER,
    memory_usage_bytes BIGINT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 🔍 Search & Discovery
-- =====================================================

-- Search queries and results
CREATE TABLE search_queries (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    query_text TEXT NOT NULL,
    search_type VARCHAR(50) NOT NULL CHECK (search_type IN ('cats', 'users', 'content', 'marketplace', 'general')),
    filters_applied JSONB,
    results_count INTEGER DEFAULT 0,
    search_duration_ms INTEGER,
    user_clicked_result BOOLEAN DEFAULT false,
    clicked_result_id INTEGER,
    search_satisfaction_rating INTEGER CHECK (search_satisfaction_rating >= 1 AND search_satisfaction_rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Content discovery recommendations
CREATE TABLE content_recommendations (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    recommendation_type VARCHAR(50) NOT NULL CHECK (recommendation_type IN ('cat_breed', 'game_suggestion', 'social_connection', 'content', 'product')),
    recommended_item_id INTEGER,
    recommended_item_type VARCHAR(50),
    recommendation_score DECIMAL(5,4),
    recommendation_reason TEXT,
    user_action VARCHAR(50), -- clicked, ignored, dismissed, etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP
);

-- =====================================================
-- 🎯 Indexes for Performance
-- =====================================================

-- Performance indexes
CREATE INDEX idx_cat_personality_predictions_cat_id ON cat_personality_predictions(cat_id);
CREATE INDEX idx_cat_behavior_observations_cat_id_date ON cat_behavior_observations(cat_id, observed_at);
CREATE INDEX idx_nfts_user_id_network ON nfts(user_id, network);
CREATE INDEX idx_nft_listings_status_expires ON nft_listings(status, expires_at);
CREATE INDEX idx_user_world_sessions_user_world ON user_world_sessions(user_id, world_id);
CREATE INDEX idx_webhook_delivery_queue_status ON webhook_delivery_queue(delivery_status, next_retry_at);
CREATE INDEX idx_user_behavior_analytics_user_date ON user_behavior_analytics(user_id, session_date);
CREATE INDEX idx_api_usage_analytics_user_endpoint ON api_usage_analytics(user_id, endpoint);
CREATE INDEX idx_multiplayer_sessions_status ON multiplayer_sessions(session_status);
CREATE INDEX idx_search_queries_user_type ON search_queries(user_id, search_type);

-- =====================================================
-- 🔒 Security & Access Control
-- =====================================================

-- Row-level security policies
ALTER TABLE cat_personality_predictions ENABLE ROW LEVEL SECURITY;
ALTER TABLE nfts ENABLE ROW LEVEL SECURITY;
ALTER TABLE user_world_sessions ENABLE ROW LEVEL SECURITY;
ALTER TABLE webhook_subscriptions ENABLE ROW LEVEL SECURITY;

-- =====================================================
-- 📝 Comments & Documentation
-- =====================================================

COMMENT ON TABLE cat_personality_predictions IS 'Machine learning predictions for cat personality traits';
COMMENT ON TABLE nfts IS 'Blockchain-based NFT records for cat ownership';
COMMENT ON TABLE metaverse_worlds IS 'Virtual 3D worlds for cat social interaction';
COMMENT ON TABLE webhook_subscriptions IS 'Webhook endpoints for real-time notifications';
COMMENT ON TABLE user_behavior_analytics IS 'User engagement and behavior tracking data';
COMMENT ON TABLE cat_competitions IS 'Cat show and competition management';
COMMENT ON TABLE multiplayer_sessions IS 'Real-time multiplayer gaming sessions';

-- =====================================================
-- 🚀 Schema Version & Migration Tracking
-- =====================================================

CREATE TABLE schema_migrations (
    id SERIAL PRIMARY KEY,
    migration_name VARCHAR(255) NOT NULL,
    version VARCHAR(20) NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    execution_time_ms INTEGER,
    status VARCHAR(20) DEFAULT 'success' CHECK (status IN ('success', 'failed', 'rolled_back')),
    error_message TEXT
);

-- Insert current schema version
INSERT INTO schema_migrations (migration_name, version) 
VALUES ('advanced_features_schema', '2.0.0');
