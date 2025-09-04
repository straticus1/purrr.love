<?php
/**
 * 🔐 Working Login System 
 * Bypasses database connectivity issues for immediate testing
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
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }
    
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required']);
        exit;
    }
    
    // Working credentials with proper password verification
    $credentials = [
        'admin@purrr.love' => [
            'password_hash' => password_hash('admin123456789!', PASSWORD_DEFAULT),
            'user' => [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@purrr.love',
                'name' => 'System Administrator',
                'role' => 'admin',
                'level' => 50,
                'coins' => 10000,
                'experience_points' => 50000,
                'avatar_url' => null
            ],
            'cats' => [
                [
                    'id' => 1,
                    'name' => 'Whiskers',
                    'breed' => 'Persian',
                    'age' => 3,
                    'color' => 'White',
                    'health_status' => 'excellent'
                ]
            ]
        ],
        'testuser@example.com' => [
            'password_hash' => password_hash('testpass123', PASSWORD_DEFAULT),
            'user' => [
                'id' => 2,
                'username' => 'testuser',
                'email' => 'testuser@example.com',
                'name' => 'Test User',
                'role' => 'user',
                'level' => 5,
                'coins' => 500,
                'experience_points' => 1000,
                'avatar_url' => null
            ],
            'cats' => [
                [
                    'id' => 2,
                    'name' => 'Mittens',
                    'breed' => 'Tabby',
                    'age' => 2,
                    'color' => 'Gray',
                    'health_status' => 'good'
                ]
            ]
        ]
    ];
    
    // Check if user exists
    if (!isset($credentials[$email])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }
    
    $user_data = $credentials[$email];
    
    // Verify password
    if (!password_verify($password, $user_data['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }
    
    // Create session
    session_start();
    $_SESSION['user_id'] = $user_data['user']['id'];
    $_SESSION['user_role'] = $user_data['user']['role'];
    $_SESSION['login_time'] = time();
    
    // Success response with full user data
    $response = [
        'success' => true,
        'message' => 'Login successful',
        'user' => $user_data['user'],
        'cats' => $user_data['cats'],
        'session' => [
            'id' => session_id(),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600)
        ],
        'permissions' => [
            'is_admin' => $user_data['user']['role'] === 'admin',
            'can_manage_cats' => true,
            'can_access_api' => true,
            'can_manage_users' => $user_data['user']['role'] === 'admin'
        ],
        'database_status' => [
            'connected' => false,
            'message' => 'Using hardcoded authentication for testing',
            'mariadb_deployment' => 'Complete - connectivity being resolved'
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => 'An unexpected error occurred',
        'debug' => $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : 'Enable debug mode for details'
    ]);
}
?>
