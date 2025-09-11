<?php
/**
 * 🔐 Advanced Login System with MariaDB Integration
 * Full-featured authentication with database integration
 */

require_once 'includes/config.php';
require_once 'includes/security_utils.php';

// Set security headers
SecurityUtils::setSecurityHeaders();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Initialize security utilities
    $security = SecurityUtils::getInstance();
    
    // Rate limiting for login attempts
    $clientId = $_SERVER['REMOTE_ADDR'] . '_login';
    $security->checkRateLimit($clientId, 10, 300); // 10 attempts per 5 minutes
    
    // Get secure database connection
    $pdo = getSecureDatabase();
    
    $raw_input = file_get_contents('php://input');
    $input = json_decode($raw_input, true);
    
    if (!$input) {
        $security->logSecurityEvent('login_invalid_json', [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ], 'WARNING');
        
        http_response_code(400);
        $response = ['error' => 'Invalid JSON input'];
        
        // Only include debug info in development
        if (isDevelopment()) {
            $response['debug'] = [
                'raw_input' => $raw_input,
                'json_error' => json_last_error_msg(),
                'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set'
            ];
        }
        
        echo json_encode($response);
        exit;
    }
    
    // Sanitize and validate input
    $email = SecurityUtils::sanitizeInput($input['email'] ?? '', 'email');
    $password = $input['password'] ?? '';
    
    // Input validation
    $emailErrors = SecurityUtils::validateInput($email, [
        'required' => ['message' => 'Email is required'],
        'email' => ['message' => 'Must be a valid email address']
    ]);
    
    $passwordErrors = SecurityUtils::validateInput($password, [
        'required' => ['message' => 'Password is required'],
        'min_length' => ['value' => 1, 'message' => 'Password is required']
    ]);
    
    if (!empty($emailErrors) || !empty($passwordErrors)) {
        $security->logSecurityEvent('login_validation_failed', [
            'email' => $email,
            'errors' => array_merge($emailErrors, $passwordErrors)
        ], 'WARNING');
        
        http_response_code(400);
        echo json_encode([
            'error' => 'Validation failed',
            'validation_errors' => array_merge($emailErrors, $passwordErrors)
        ]);
        exit;
    }
    
    // Get user from database
    $stmt = $pdo->prepare("
        SELECT id, username, email, password_hash, name, role, active, is_active, level, coins, experience_points, avatar_url
        FROM users 
        WHERE email = ? AND active = TRUE AND is_active = TRUE
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $security->logSecurityEvent('login_failed', [
            'email' => $email,
            'reason' => 'user_not_found'
        ], 'WARNING');
        
        // Add small delay to prevent timing attacks
        usleep(250000); // 250ms
        
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }
    
    // Verify password with security logging
    if (!SecurityUtils::verifyPassword($password, $user['password_hash'])) {
        $security->logSecurityEvent('login_failed', [
            'email' => $email,
            'user_id' => $user['id'] ?? null,
            'reason' => 'invalid_password'
        ], 'WARNING');
        
        // Add small delay to prevent timing attacks
        usleep(250000); // 250ms
        
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }
    
    // Get user's cats
    $stmt = $pdo->prepare("
        SELECT id, name, breed, age, color, health_status, level, experience_points 
        FROM cats 
        WHERE user_id = ? AND is_active = TRUE 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $cats = $stmt->fetchAll();
    
    // Create session (simplified for now)
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['login_time'] = time();
    
    // Prepare response
    $response = [
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role' => $user['role'],
            'level' => (int)$user['level'],
            'coins' => (int)$user['coins'],
            'experience_points' => (int)$user['experience_points'],
            'avatar_url' => $user['avatar_url']
        ],
        'cats' => array_map(function($cat) {
            return [
                'id' => (int)$cat['id'],
                'name' => $cat['name'],
                'breed' => $cat['breed'],
                'age' => (int)$cat['age'],
                'color' => $cat['color'],
                'health_status' => $cat['health_status']
            ];
        }, $cats),
        'session' => [
            'id' => session_id(),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600)
        ],
        'permissions' => [
            'is_admin' => $user['role'] === 'admin',
            'can_manage_cats' => true,
            'can_access_api' => true
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database connection failed',
        'message' => 'Unable to connect to MariaDB',
        'debug' => $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : null
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => 'An unexpected error occurred',
        'debug' => $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : null
    ]);
}
?>
