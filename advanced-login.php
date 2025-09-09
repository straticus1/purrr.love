<?php
/**
 * 🔐 Advanced Login System with MariaDB Integration
 * Full-featured authentication with database integration
 */

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
    // Get database credentials from environment
    $db_host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'purrr-mariadb-ecs.c3iuy64is41m.us-east-1.rds.amazonaws.com');
    $db_name = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'purrr_love');
    $db_user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'purrruser');
    $db_pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? 'PurrrLove2025');
    $db_port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306');
    
    // Connect to MariaDB
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    $raw_input = file_get_contents('php://input');
    $input = json_decode($raw_input, true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid JSON input',
            'debug' => [
                'raw_input' => $raw_input,
                'json_error' => json_last_error_msg(),
                'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set'
            ]
        ]);
        exit;
    }
    
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required']);
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
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
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
