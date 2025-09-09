<?php
/**
 * Test MariaDB Connection from Production Containers
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

try {
    // Get database credentials from environment
    $db_host = $_ENV['DB_HOST'] ?? 'purrr-mariadb-ecs.c3iuy64is41m.us-east-1.rds.amazonaws.com';
    $db_port = $_ENV['DB_PORT'] ?? '3306';
    $db_user = $_ENV['DB_USER'] ?? 'purrruser';
    $db_pass = $_ENV['DB_PASS'] ?? 'PurrrLove2025';
    $db_name = $_ENV['DB_NAME'] ?? 'purrr_love';
    
    $response = [
        'test' => 'MariaDB Connection Test',
        'timestamp' => date('Y-m-d H:i:s T'),
        'environment' => [
            'DB_HOST' => $db_host,
            'DB_PORT' => $db_port,
            'DB_USER' => $db_user,
            'DB_NAME' => $db_name,
            'DB_PASS' => $db_pass ? 'Set (' . strlen($db_pass) . ' chars)' : 'Not set'
        ]
    ];
    
    // Test basic connection (without database)
    try {
        $pdo = new PDO("mysql:host=$db_host;port=$db_port;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5
        ]);
        $response['basic_connection'] = 'SUCCESS';
    } catch (PDOException $e) {
        $response['basic_connection'] = 'FAILED';
        $response['basic_error'] = $e->getMessage();
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Test database connection
    try {
        $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5
        ]);
        $response['database_connection'] = 'SUCCESS';
    } catch (PDOException $e) {
        $response['database_connection'] = 'FAILED';
        $response['database_error'] = $e->getMessage();
        
        // Try to create the database if it doesn't exist
        if (strpos($e->getMessage(), 'Unknown database') !== false) {
            try {
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $response['database_creation'] = 'SUCCESS';
                $response['database_connection'] = 'RETRY_SUCCESS';
            } catch (PDOException $e2) {
                $response['database_creation'] = 'FAILED';
                $response['database_creation_error'] = $e2->getMessage();
            }
        }
    }
    
    // Test table existence if connected
    if ($response['database_connection'] === 'SUCCESS' || $response['database_connection'] === 'RETRY_SUCCESS') {
        try {
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $response['tables'] = $tables;
            $response['table_count'] = count($tables);
        } catch (PDOException $e) {
            $response['tables_error'] = $e->getMessage();
        }
        
        // Test if users table exists and has data
        if (in_array('users', $tables ?? [])) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                $count = $stmt->fetchColumn();
                $response['user_count'] = $count;
                
                // Get sample user data (without passwords)
                $stmt = $pdo->query("SELECT id, username, email, role, active, created_at FROM users LIMIT 3");
                $response['sample_users'] = $stmt->fetchAll();
            } catch (PDOException $e) {
                $response['users_error'] = $e->getMessage();
            }
        }
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'General error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s T')
    ], JSON_PRETTY_PRINT);
}
?>
