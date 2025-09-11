<?php
/**
 * 🛡️ Security Utilities for Purrr.love
 * Comprehensive security functions for SQL injection prevention, XSS protection, and input validation
 */

if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

class SecurityUtils {
    private static $instance = null;
    private $pdo;
    private $encryptionKey;
    
    private function __construct() {
        $this->pdo = get_db();
        $this->encryptionKey = $_ENV['ENCRYPTION_KEY'] ?? hash('sha256', 'purrr_love_default_key_change_in_production');
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Execute prepared statement with parameters
     */
    public function executeQuery($sql, $params = [], $fetchMode = PDO::FETCH_ASSOC) {
        try {
            $stmt = $this->pdo->prepare($sql);
            
            // Bind parameters safely
            foreach ($params as $key => $value) {
                if (is_int($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } elseif (is_bool($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_BOOL);
                } elseif (is_null($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
            
            $stmt->execute();
            
            // Return results based on query type
            if (stripos($sql, 'SELECT') === 0) {
                return $stmt->fetchAll($fetchMode);
            } elseif (stripos($sql, 'INSERT') === 0) {
                return $this->pdo->lastInsertId();
            } else {
                return $stmt->rowCount();
            }
            
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            throw new Exception('Database operation failed', 500);
        }
    }
    
    /**
     * Execute query and return single row
     */
    public function fetchRow($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                if (is_int($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
            
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Database fetch error: " . $e->getMessage());
            throw new Exception('Database fetch failed', 500);
        }
    }
    
    /**
     * Sanitize input to prevent XSS attacks
     */
    public static function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        // Remove null bytes and control characters
        $input = str_replace(["\0", "\r"], '', (string)$input);
        
        switch ($type) {
            case 'int':
                return (int)filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            
            case 'float':
                return (float)filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            
            case 'html':
                return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            case 'string':
            default:
                // Strip tags and encode special characters
                $input = strip_tags($input);
                return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    
    /**
     * Validate input with comprehensive checks
     */
    public static function validateInput($input, $rules = []) {
        $errors = [];
        
        foreach ($rules as $rule => $params) {
            switch ($rule) {
                case 'required':
                    if (empty($input) && $input !== '0') {
                        $errors[] = $params['message'] ?? 'This field is required';
                    }
                    break;
                
                case 'min_length':
                    if (strlen($input) < $params['value']) {
                        $errors[] = $params['message'] ?? "Must be at least {$params['value']} characters";
                    }
                    break;
                
                case 'max_length':
                    if (strlen($input) > $params['value']) {
                        $errors[] = $params['message'] ?? "Must not exceed {$params['value']} characters";
                    }
                    break;
                
                case 'email':
                    if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = $params['message'] ?? 'Must be a valid email address';
                    }
                    break;
                
                case 'numeric':
                    if (!is_numeric($input)) {
                        $errors[] = $params['message'] ?? 'Must be a number';
                    }
                    break;
                
                case 'regex':
                    if (!preg_match($params['pattern'], $input)) {
                        $errors[] = $params['message'] ?? 'Invalid format';
                    }
                    break;
            }
        }
        
        return $errors;
    }
    
    /**
     * Generate secure random token
     */
    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,  // 64 MB
            'time_cost' => 4,        // 4 iterations
            'threads' => 3           // 3 threads
        ]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Encrypt sensitive data
     */
    public function encryptData($data) {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    public function decryptData($encryptedData) {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
    }
    
    /**
     * Prevent CSRF attacks
     */
    public static function generateCSRFToken($action = 'default') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = self::generateSecureToken();
        $_SESSION['csrf_tokens'][$action] = [
            'token' => $token,
            'expires' => time() + 3600 // 1 hour
        ];
        
        return $token;
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token, $action = 'default') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_tokens'][$action])) {
            return false;
        }
        
        $storedToken = $_SESSION['csrf_tokens'][$action];
        
        // Check if token expired
        if (time() > $storedToken['expires']) {
            unset($_SESSION['csrf_tokens'][$action]);
            return false;
        }
        
        // Verify token
        $isValid = hash_equals($storedToken['token'], $token);
        
        // Remove token after use (one-time use)
        if ($isValid) {
            unset($_SESSION['csrf_tokens'][$action]);
        }
        
        return $isValid;
    }
    
    /**
     * Set comprehensive security headers
     */
    public static function setSecurityHeaders() {
        // Prevent XSS attacks
        header('X-XSS-Protection: 1; mode=block');
        
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src 'self' fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https:;");
        
        // HSTS (only for HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Feature policy
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    }
    
    /**
     * Rate limiting check
     */
    public function checkRateLimit($identifier, $maxRequests = 100, $timeWindow = 3600) {
        $key = 'rate_limit_' . hash('sha256', $identifier);
        
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as request_count 
            FROM rate_limits 
            WHERE identifier = :key 
            AND created_at > DATE_SUB(NOW(), INTERVAL :window SECOND)
        ");
        
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':window', $timeWindow, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentCount = $result['request_count'] ?? 0;
        
        if ($currentCount >= $maxRequests) {
            throw new Exception('Rate limit exceeded', 429);
        }
        
        // Log this request
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limits (identifier, created_at) 
            VALUES (:key, NOW())
        ");
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        
        return true;
    }
    
    /**
     * Log security events
     */
    public function logSecurityEvent($event, $details = [], $severity = 'INFO') {
        $stmt = $this->pdo->prepare("
            INSERT INTO security_logs (event_type, details, severity, ip_address, user_agent, created_at)
            VALUES (:event, :details, :severity, :ip, :ua, NOW())
        ");
        
        $stmt->execute([
            ':event' => $event,
            ':details' => json_encode($details),
            ':severity' => $severity,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
}

// Convenience functions
function sanitizeInput($input, $type = 'string') {
    return SecurityUtils::sanitizeInput($input, $type);
}

function validateInput($input, $rules = []) {
    return SecurityUtils::validateInput($input, $rules);
}

function setSecureHeaders() {
    SecurityUtils::setSecurityHeaders();
}

function generateCSRFToken($action = 'default') {
    return SecurityUtils::generateCSRFToken($action);
}

function verifyCSRFToken($token, $action = 'default') {
    return SecurityUtils::verifyCSRFToken($token, $action);
}