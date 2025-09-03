<?php
/**
 * 🔐 Purrr.love Core Functions
 * Secure, production-ready utility functions for the application
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

// Database configuration constants
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'purrr_love');
if (!defined('DB_USER')) define('DB_USER', 'purrr_user');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_PORT')) define('DB_PORT', 5432);

/**
 * Secure database connection with connection pooling
 */
function get_db() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true, // Connection pooling
                PDO::ATTR_TIMEOUT => 5, // 5 second timeout
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Set session variables for security
            $pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception('Database connection failed', 500);
        }
    }
    
    return $pdo;
}

/**
 * Get client IP address with proxy detection
 */
function getClientIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Generate unique request ID for tracking
 */
function generateRequestId() {
    return uniqid('req_', true) . '_' . substr(md5(microtime()), 0, 8);
}

/**
 * Get HTTP error code with fallback
 */
function getErrorCode($code) {
    return $code && $code >= 100 && $code < 600 ? $code : 500;
}

/**
 * Secure session configuration
 */
function configureSecureSession() {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_lifetime', 0); // Session cookie
    ini_set('session.gc_maxlifetime', 3600); // 1 hour
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Check if user is authenticated
 */
function isUserAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current authenticated user
 */
function getCurrentUser() {
    if (!isUserAuthenticated()) {
        return null;
    }
    
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ? AND active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting current user: " . $e->getMessage());
        return null;
    }
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize and validate input
 */
function sanitizeInput($input, $type = 'string', $maxLength = 255) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    $input = trim($input);
    
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL) ? $input : '';
            
        case 'url':
            return filter_var($input, FILTER_VALIDATE_URL) ? $input : '';
            
        case 'int':
            $int = filter_var($input, FILTER_VALIDATE_INT);
            return $int !== false ? $int : 0;
            
        case 'float':
            $float = filter_var($input, FILTER_VALIDATE_FLOAT);
            return $float !== false ? $float : 0.0;
            
        case 'boolean':
            return filter_var($input, FILTER_VALIDATE_BOOLEAN);
            
        case 'string':
        default:
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            return substr($input, 0, $maxLength);
    }
}

/**
 * Validate and sanitize file upload
 */
function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('File too large. Maximum size: ' . formatBytes($maxSize));
    }
    
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    
    if (!in_array($extension, $allowedTypes)) {
        throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $allowedTypes));
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    
    if (!isset($allowedMimes[$extension]) || $mimeType !== $allowedMimes[$extension]) {
        throw new Exception('Invalid file content detected');
    }
    
    return [
        'name' => $fileInfo['basename'],
        'extension' => $extension,
        'size' => $file['size'],
        'mime_type' => $mimeType,
        'tmp_path' => $file['tmp_name']
    ];
}

/**
 * Format bytes to human readable format
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Secure random string generation
 */
function generateSecureRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Log security events
 */
function logSecurityEvent($event, $data = [], $level = 'INFO') {
    $logData = [
        'timestamp' => date('c'),
        'event' => $event,
        'level' => $level,
        'ip_address' => getClientIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'user_id' => $_SESSION['user_id'] ?? null,
        'data' => $data
    ];
    
    error_log('SECURITY: ' . json_encode($logData));
    
    // Store in database for monitoring
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("
            INSERT INTO security_logs (event, level, ip_address, user_agent, user_id, data, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $event,
            $level,
            $logData['ip_address'],
            $logData['user_agent'],
            $logData['user_id'],
            json_encode($data),
            date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        error_log("Failed to log security event to database: " . $e->getMessage());
    }
}

/**
 * Check if request is from allowed origin
 */
function isAllowedOrigin($origin) {
    $allowedOrigins = [
        'https://purrr.love',
        'https://www.purrr.love',
        'https://app.purrr.love',
        'https://api.purrr.love'
    ];
    
    return in_array($origin, $allowedOrigins);
}

/**
 * Set secure headers
 */
function setSecureHeaders() {
    // Security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    
    // HSTS (only for HTTPS)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}

/**
 * Initialize application security
 */
function initializeSecurity() {
    // Start secure session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    configureSecureSession();
    setSecureHeaders();
    
    // Log security initialization
    logSecurityEvent('application_initialized', ['version' => '1.0.0']);
}

// Initialize security on include
initializeSecurity();
?>
