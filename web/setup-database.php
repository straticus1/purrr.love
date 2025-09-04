<?php
/**
 * Database Setup API Endpoint
 * Accessible via web/setup-database.php
 */

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');

// Database configuration
$dbConfig = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'name' => $_ENV['DB_NAME'] ?? 'purrr_love',
    'user' => $_ENV['DB_USER'] ?? 'root',
    'pass' => $_ENV['DB_PASS'] ?? '',
];

$response = [];

try {
    // Connect to MySQL server (without database)
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    $response['connection'] = 'success';
    
    // Read and execute the complete initialization script
    $sqlFile = __DIR__ . '/../database/init_mysql_complete.sql';
    if (!file_exists($sqlFile)) {
        // Use the embedded SQL schema if file doesn't exist
        $sql = '
CREATE DATABASE IF NOT EXISTS purrr_love;
USE purrr_love;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM("user", "admin") DEFAULT "user",
    level INT DEFAULT 1,
    coins INT DEFAULT 100,
    experience_points INT DEFAULT 0,
    avatar_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    breed VARCHAR(100) DEFAULT "Mixed",
    age INT DEFAULT 1,
    color VARCHAR(50) DEFAULT "Orange",
    personality_openness DECIMAL(3,2) DEFAULT 0.50,
    personality_conscientiousness DECIMAL(3,2) DEFAULT 0.50,
    personality_extraversion DECIMAL(3,2) DEFAULT 0.50,
    personality_agreeableness DECIMAL(3,2) DEFAULT 0.50,
    personality_neuroticism DECIMAL(3,2) DEFAULT 0.50,
    health_status ENUM("excellent", "good", "fair", "poor") DEFAULT "good",
    temperature DECIMAL(4,2) DEFAULT 101.50,
    heart_rate INT DEFAULT 120,
    weight DECIMAL(5,2) DEFAULT 10.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type ENUM("login_success", "login_failed", "password_change", "account_locked", "api_access", "suspicious_activity") NOT NULL,
    user_id INT,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    details JSON,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp),
    INDEX idx_user_id (user_id),
    INDEX idx_ip_address (ip_address),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM("open", "in_progress", "resolved", "closed") DEFAULT "open",
    priority ENUM("low", "medium", "high", "urgent") DEFAULT "medium",
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_key VARCHAR(128) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    permissions JSON DEFAULT "[]",
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (username, email, password_hash, role, level, coins, experience_points) VALUES
("admin", "admin@purrr.love", "$2y$12$rOx1XaOV.gV4j2V5qnQjzOXJB5Uy2P8H9m.W4nJ.H6mZ8kR4L1N7C", "admin", 50, 10000, 50000)
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash);

-- Insert sample cat
INSERT INTO cats (user_id, name, breed, age, color, personality_openness, personality_conscientiousness) VALUES
(1, "Whiskers", "Persian", 3, "White", 0.75, 0.65)
ON DUPLICATE KEY UPDATE name = VALUES(name);
';
    } else {
        $sql = file_get_contents($sqlFile);
    }
    
    if (!$sql) {
        throw new Exception("Failed to load SQL schema");
    }
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt) && !preg_match('/^\/\*/', $stmt);
        }
    );
    
    $response['statements_count'] = count($statements);
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($statements as $statement) {
        try {
            if (trim($statement)) {
                $pdo->exec($statement);
                $successCount++;
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate entry') === false) {
                $errors[] = $e->getMessage();
                $errorCount++;
            } else {
                $successCount++;
            }
        }
    }
    
    $response['setup'] = 'completed';
    $response['successful_statements'] = $successCount;
    $response['error_count'] = $errorCount;
    $response['errors'] = $errors;
    
    // Test the database connection
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset=utf8mb4";
    $testPdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Test basic queries
    $tables = ['users', 'cats', 'security_logs', 'support_tickets', 'api_keys'];
    $tableStatus = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $testPdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            $tableStatus[$table] = ['status' => 'ok', 'records' => $count];
        } catch (Exception $e) {
            $tableStatus[$table] = ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    $response['tables'] = $tableStatus;
    
    // Test admin user
    try {
        $stmt = $testPdo->prepare("SELECT username, email, role FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch();
        if ($admin) {
            $response['admin_user'] = ['found' => true, 'username' => $admin['username'], 'email' => $admin['email']];
        } else {
            $response['admin_user'] = ['found' => false];
        }
    } catch (Exception $e) {
        $response['admin_user'] = ['found' => false, 'error' => $e->getMessage()];
    }
    
    $response['status'] = 'success';
    $response['database'] = $dbConfig['name'];
    $response['host'] = $dbConfig['host'] . ':' . $dbConfig['port'];
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['error'] = $e->getMessage();
    $response['config'] = [
        'host' => $dbConfig['host'],
        'port' => $dbConfig['port'],
        'database' => $dbConfig['name'],
        'user' => $dbConfig['user'],
        'password' => empty($dbConfig['pass']) ? '(empty)' : '***'
    ];
    http_response_code(500);
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
