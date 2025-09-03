<?php
/**
 * 🔐 Purrr.love Configuration
 * Environment-based configuration with security settings
 */

// Prevent direct access (only if not already defined)
if (!defined('SECURE_ACCESS') && basename($_SERVER['PHP_SELF']) == 'config.php') {
    http_response_code(403);
    exit('Direct access not allowed');
}

// Allow secure access by default for includes
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// Environment detection
$environment = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'production';

// Base configuration
$config = [
    'app' => [
        'name' => 'Purrr.love',
        'version' => '1.0.0',
        'environment' => $environment,
        'debug' => $environment === 'development',
        'timezone' => 'UTC',
        'locale' => 'en_US',
        'url' => $_ENV['APP_URL'] ?? 'https://purrr.love',
        'api_url' => $_ENV['API_URL'] ?? 'https://api.purrr.love'
    ],
    
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'purrr_love',
        'user' => $_ENV['DB_USER'] ?? 'purrr_user',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? 5432,
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true
        ]
    ],
    
    'redis' => [
        'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
        'port' => $_ENV['REDIS_PORT'] ?? 6379,
        'password' => $_ENV['REDIS_PASSWORD'] ?? null,
        'database' => $_ENV['REDIS_DB'] ?? 0,
        'timeout' => 5
    ],
    
    'security' => [
        'session' => [
            'lifetime' => 3600, // 1 hour
            'regenerate_interval' => 300, // 5 minutes
            'cookie_secure' => $environment === 'production',
            'cookie_httponly' => true,
            'cookie_samesite' => 'Strict'
        ],
        'password' => [
            'min_length' => 12,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special' => true
        ],
        'rate_limiting' => [
            'enabled' => true,
            'default_limit' => 1000,
            'burst_limit' => 2000,
            'window_seconds' => 3600,
            'free_tier_limit' => 100,
            'premium_tier_limit' => 1000,
            'enterprise_tier_limit' => 10000
        ],
        'cors' => [
            'allowed_origins' => [
                'https://purrr.love',
                'https://www.purrr.love',
                'https://app.purrr.love',
                'https://api.purrr.love'
            ],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-API-Key'],
            'allow_credentials' => true,
            'max_age' => 86400 // 24 hours
        ],
        'headers' => [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()'
        ]
    ],
    
    'oauth2' => [
        'access_token_lifetime' => 3600, // 1 hour
        'refresh_token_lifetime' => 2592000, // 30 days
        'authorization_code_lifetime' => 600, // 10 minutes
        'pkce_required' => true,
        'scopes' => ['read', 'write', 'admin', 'client']
    ],
    
    'api' => [
        'version' => '1.0.0',
        'default_limit' => 50,
        'max_limit' => 1000,
        'pagination_enabled' => true,
        'caching_enabled' => true,
        'cache_ttl' => 300 // 5 minutes
    ],
    
    'logging' => [
        'level' => $environment === 'production' ? 'ERROR' : 'DEBUG',
        'security_logging' => true,
        'performance_logging' => true,
        'error_logging' => true,
        'max_log_size' => '100MB',
        'retention_days' => 90
    ],
    
    'file_uploads' => [
        'enabled' => true,
        'max_size' => 5242880, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],
        'upload_path' => '../uploads/',
        'virus_scanning' => $environment === 'production'
    ],
    
    'external_services' => [
        'openai' => [
            'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
            'model' => 'gpt-4-vision-preview',
            'max_tokens' => 1000
        ],
        'stability_ai' => [
            'api_key' => $_ENV['STABILITY_AI_API_KEY'] ?? '',
            'model' => 'sdxl-1.0'
        ],
        'coinbase' => [
            'api_key' => $_ENV['COINBASE_API_KEY'] ?? '',
            'webhook_secret' => $_ENV['COINBASE_WEBHOOK_SECRET'] ?? ''
        ]
    ]
];

// Set constants for backward compatibility
define('DB_HOST', $config['database']['host']);
define('DB_NAME', $config['database']['name']);
define('DB_USER', $config['database']['user']);
define('DB_PASS', $config['database']['pass']);
define('DB_PORT', $config['database']['port']);

define('DEVELOPMENT_MODE', $environment === 'development');
define('PRODUCTION_MODE', $environment === 'production');

// Environment-specific overrides
if ($environment === 'development') {
    $config['security']['cors']['allowed_origins'][] = 'http://localhost:3000';
    $config['security']['cors']['allowed_origins'][] = 'http://localhost:8080';
    $config['logging']['level'] = 'DEBUG';
    $config['security']['headers']['X-Frame-Options'] = 'SAMEORIGIN';
}

if ($environment === 'testing') {
    $config['database']['name'] = 'purrr_love_test';
    $config['logging']['level'] = 'DEBUG';
    $config['security']['rate_limiting']['enabled'] = false;
}

// Validate configuration
validateConfiguration($config);

/**
 * Validate configuration values
 */
function validateConfiguration($config) {
    $errors = [];
    
    // Check required database settings
    if (empty($config['database']['host']) || empty($config['database']['name'])) {
        $errors[] = 'Database host and name are required';
    }
    
    // Check security settings
    if ($config['security']['session']['lifetime'] < 300) {
        $errors[] = 'Session lifetime must be at least 5 minutes';
    }
    
    if ($config['security']['rate_limiting']['default_limit'] < 100) {
        $errors[] = 'Default rate limit must be at least 100 requests per hour';
    }
    
    // Check file upload settings
    if ($config['file_uploads']['max_size'] > 10485760) { // 10MB
        $errors[] = 'File upload size cannot exceed 10MB';
    }
    
    if (empty($config['file_uploads']['allowed_types'])) {
        $errors[] = 'At least one file type must be allowed';
    }
    
    // Check external services
    if ($config['external_services']['openai']['api_key'] && strlen($config['external_services']['openai']['api_key']) < 20) {
        $errors[] = 'OpenAI API key appears to be invalid';
    }
    
    if ($config['external_services']['stability_ai']['api_key'] && strlen($config['external_services']['stability_ai']['api_key']) < 20) {
        $errors[] = 'Stability AI API key appears to be invalid';
    }
    
    // Throw exception if validation fails
    if (!empty($errors)) {
        throw new Exception('Configuration validation failed: ' . implode(', ', $errors));
    }
}

/**
 * Get configuration value
 */
function getConfig($key, $default = null) {
    global $config;
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}

/**
 * Set configuration value
 */
function setConfig($key, $value) {
    global $config;
    
    $keys = explode('.', $key);
    $configRef = &$config;
    
    foreach ($keys as $k) {
        if (!isset($configRef[$k])) {
            $configRef[$k] = [];
        }
        $configRef = &$configRef[$k];
    }
    
    $configRef = $value;
}

/**
 * Check if feature is enabled
 */
function isFeatureEnabled($feature) {
    $featureConfigs = [
        'rate_limiting' => getConfig('security.rate_limiting.enabled'),
        'caching' => getConfig('api.caching_enabled'),
        'file_uploads' => getConfig('file_uploads.enabled'),
        'virus_scanning' => getConfig('file_uploads.virus_scanning'),
        'security_logging' => getConfig('logging.security_logging'),
        'performance_logging' => getConfig('logging.performance_logging')
    ];
    
    return $featureConfigs[$feature] ?? false;
}

/**
 * Get environment-specific configuration
 */
function getEnvironmentConfig() {
    return [
        'environment' => getConfig('app.environment'),
        'debug' => getConfig('app.debug'),
        'url' => getConfig('app.url'),
        'api_url' => getConfig('app.api_url')
    ];
}

// Log configuration load
if (function_exists('logSecurityEvent')) {
    logSecurityEvent('configuration_loaded', [
        'environment' => $environment,
        'debug_mode' => getConfig('app.debug'),
        'security_features' => [
            'rate_limiting' => isFeatureEnabled('rate_limiting'),
            'caching' => isFeatureEnabled('caching'),
            'security_logging' => isFeatureEnabled('security_logging')
        ]
    ]);
}
?>
