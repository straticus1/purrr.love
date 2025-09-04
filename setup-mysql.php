<?php
/**
 * 🚀 Purrr.love MySQL Database Setup Script
 * Easy database initialization for MySQL/MariaDB
 */

// Allow direct access for setup
define('SECURE_ACCESS', true);

// Database configuration
$dbConfig = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'name' => $_ENV['DB_NAME'] ?? 'purrr_love',
    'user' => $_ENV['DB_USER'] ?? 'root',
    'pass' => $_ENV['DB_PASS'] ?? '',
];

echo "<h1>🚀 Purrr.love MySQL Database Setup</h1>\n";

try {
    // Connect to MySQL server (without database)
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<p>✅ Connected to MySQL server successfully</p>\n";
    
    // Read and execute the complete initialization script
    $sqlFile = __DIR__ . '/database/init_mysql_complete.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    if (!$sql) {
        throw new Exception("Failed to read SQL file");
    }
    
    echo "<p>📖 Reading database schema...</p>\n";
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt) && !preg_match('/^\/\*/', $stmt);
        }
    );
    
    echo "<p>🔧 Executing " . count($statements) . " SQL statements...</p>\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        try {
            if (trim($statement)) {
                $pdo->exec($statement);
                $successCount++;
            }
        } catch (PDOException $e) {
            // Some statements might fail if they already exist, that's okay
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate entry') === false) {
                echo "<p>⚠️ Warning: " . htmlspecialchars($e->getMessage()) . "</p>\n";
                $errorCount++;
            } else {
                $successCount++;
            }
        }
    }
    
    echo "<p>✅ Database setup completed!</p>\n";
    echo "<p>📊 Successfully executed: $successCount statements</p>\n";
    if ($errorCount > 0) {
        echo "<p>⚠️ Warnings: $errorCount statements</p>\n";
    }
    
    // Test the database connection
    echo "<h2>🧪 Testing Database Connection</h2>\n";
    
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset=utf8mb4";
    $testPdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Test basic queries
    $tables = ['users', 'cats', 'security_logs', 'support_tickets', 'api_keys'];
    foreach ($tables as $table) {
        try {
            $stmt = $testPdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "<p>✅ Table '$table': $count records</p>\n";
        } catch (Exception $e) {
            echo "<p>❌ Table '$table': " . htmlspecialchars($e->getMessage()) . "</p>\n";
        }
    }
    
    // Test admin user
    try {
        $stmt = $testPdo->prepare("SELECT username, email, role FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch();
        if ($admin) {
            echo "<p>✅ Admin user found: " . htmlspecialchars($admin['username']) . " (" . htmlspecialchars($admin['email']) . ")</p>\n";
        } else {
            echo "<p>⚠️ No admin user found</p>\n";
        }
    } catch (Exception $e) {
        echo "<p>❌ Admin user test failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
    
    // Test stored procedures
    try {
        $stmt = $testPdo->prepare("CALL record_failed_login('127.0.0.1', 'test_user', 'Test User Agent')");
        $stmt->execute();
        echo "<p>✅ Stored procedure 'record_failed_login' working</p>\n";
    } catch (Exception $e) {
        echo "<p>❌ Stored procedure test failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
    
    echo "<h2>🎉 Setup Complete!</h2>\n";
    echo "<p><strong>Database:</strong> {$dbConfig['name']} on {$dbConfig['host']}:{$dbConfig['port']}</p>\n";
    echo "<p><strong>Default Admin:</strong> admin@purrr.love (password: admin123456789!)</p>\n";
    echo "<p><a href='web/admin.php'>Go to Admin Panel</a> | <a href='web/register.php'>Register New User</a> | <a href='index.php'>Go to Home</a></p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ Setup failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Please check your database configuration and try again.</p>\n";
    
    echo "<h3>Configuration:</h3>\n";
    echo "<pre>\n";
    echo "Host: " . htmlspecialchars($dbConfig['host']) . "\n";
    echo "Port: " . htmlspecialchars($dbConfig['port']) . "\n";
    echo "Database: " . htmlspecialchars($dbConfig['name']) . "\n";
    echo "User: " . htmlspecialchars($dbConfig['user']) . "\n";
    echo "Password: " . (empty($dbConfig['pass']) ? '(empty)' : '***') . "\n";
    echo "</pre>\n";
}
?>
