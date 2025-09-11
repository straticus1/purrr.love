<?php
/**
 * 🔧 Secure Configuration Manager for Purrr.love
 * Handles environment variables and secure configuration loading
 */

if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

class Config {
    private static $instance = null;
    private $config = [];
    private $loaded = false;
    
    private function __construct() {
        $this->loadEnvironment();
        $this->setDefaults();
        $this->validateRequired();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load environment variables from .env file and system
     */
    private function loadEnvironment() {
        // Load from .env file if it exists
        $envFile = dirname(__DIR__) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue; // Skip comments
                
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
        
        // Load system environment variables
        $this->config = $_ENV;
        $this->loaded = true;
    }
    
    /**
     * Set default configuration values
     */
    private function setDefaults() {
        $defaults = [
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'DB_PORT' => '3306',
            'DB_CHARSET' => 'utf8mb4',
            'API_VERSION' => '2.0.0',
            'SESSION_LIFETIME' => '86400', // 24 hours
            'RATE_LIMIT_REQUESTS' => '1000',
            'RATE_LIMIT_WINDOW' => '3600', // 1 hour
            'BCRYPT_ROUNDS' => '12',
            'JWT_EXPIRE' => '7200', // 2 hours
            'CACHE_TTL' => '3600', // 1 hour
        ];
        
        foreach ($defaults as $key => $value) {
            if (!isset($this->config[$key])) {
                $this->config[$key] = $value;
            }
        }
    }
    
    /**
     * Validate required configuration values
     */
    private function validateRequired() {
        $required = [
            'DB_HOST' => 'Database host is required',
            'DB_NAME' => 'Database name is required',
            'DB_USER' => 'Database user is required',
            'DB_PASS' => 'Database password is required'
        ];
        
        foreach ($required as $key => $message) {
            if (empty($this->config[$key])) {
                throw new Exception("Configuration Error: $message. Please set $key in your .env file.");
            }
        }
        
        // Validate encryption key
        if (empty($this->config['ENCRYPTION_KEY'])) {
            $this->config['ENCRYPTION_KEY'] = hash('sha256', 'purrr_love_default_key_' . $this->config['DB_NAME']);
            error_log('WARNING: Using default encryption key. Set ENCRYPTION_KEY in .env for production.');
        }
        
        // Generate JWT secret if not set
        if (empty($this->config['JWT_SECRET'])) {
            $this->config['JWT_SECRET'] = hash('sha256', 'purrr_love_jwt_' . $this->config['DB_NAME']);
            error_log('WARNING: Using default JWT secret. Set JWT_SECRET in .env for production.');
        }
    }
    
    /**
     * Get configuration value
     */
    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Check if configuration key exists
     */
    public function has($key) {
        return isset($this->config[$key]);
    }
    
    /**
     * Get database configuration array
     */
    public function getDatabase() {
        return [
            'host' => $this->get('DB_HOST'),
            'name' => $this->get('DB_NAME'),
            'user' => $this->get('DB_USER'),
            'pass' => $this->get('DB_PASS'),
            'port' => $this->get('DB_PORT'),
            'charset' => $this->get('DB_CHARSET')
        ];
    }
    
    /**
     * Check if running in development mode
     */
    public function isDevelopment() {
        return $this->get('APP_ENV') === 'development';
    }
    
    /**
     * Check if debug mode is enabled
     */
    public function isDebug() {
        return filter_var($this->get('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Get all configuration (for debugging - removes sensitive data)
     */
    public function getAll($includeSensitive = false) {
        if ($includeSensitive) {
            return $this->config;
        }
        
        $safe = $this->config;
        $sensitive = ['DB_PASS', 'ENCRYPTION_KEY', 'JWT_SECRET', 'SESSION_SECRET'];
        
        foreach ($sensitive as $key) {
            if (isset($safe[$key])) {
                $safe[$key] = '***HIDDEN***';
            }
        }
        
        return $safe;
    }
}

/**
 * Global configuration access functions
 */
function config($key = null, $default = null) {
    $config = Config::getInstance();
    
    if ($key === null) {
        return $config;
    }
    
    return $config->get($key, $default);
}

function isDevelopment() {
    return Config::getInstance()->isDevelopment();
}

function isDebug() {
    return Config::getInstance()->isDebug();
}

/**
 * Secure database connection factory
 */
function getSecureDatabase() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dbConfig = Config::getInstance()->getDatabase();
        
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
        ];
        
        try {
            $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception('Database connection failed', 500);
        }
    }
    
    return $pdo;
}

// Backward compatibility
function get_db() {
    return getSecureDatabase();
}

// Initialize configuration
Config::getInstance();