<?php
/**
 * 🚀 Simple Database Setup via Web
 * Run once to initialize the database
 */

// Simple security - check for secret parameter
if (!isset($_GET['secret']) || $_GET['secret'] !== 'purrr123setup') {
    http_response_code(403);
    die('Access denied. Use ?secret=purrr123setup');
}

header('Content-Type: text/plain; charset=utf-8');
echo "🚀 Starting Database Setup...\n\n";

try {
    // Use direct MySQL connection without PDO
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    
    // Try to connect to MySQL
    $conn = @mysql_connect($host, $user, $pass);
    if (!$conn) {
        // Try alternative connection
        echo "Trying alternative database connection...\n";
        $dsn = "mysql:host=localhost;charset=utf8mb4";
        $pdo = new PDO($dsn, 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database and tables
        $pdo->exec("CREATE DATABASE IF NOT EXISTS purrr_love CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE purrr_love");
        
        echo "✅ Database created!\n";
        
        // Create tables
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            name VARCHAR(255),
            role ENUM('user','admin','moderator') DEFAULT 'user',
            active BOOLEAN DEFAULT true,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");
        
        echo "✅ Users table created!\n";
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS cats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            owner_id INT,
            name VARCHAR(255) NOT NULL,
            breed VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");
        
        echo "✅ Cats table created!\n";
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_statistics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stat_name VARCHAR(100) UNIQUE NOT NULL,
            stat_value BIGINT DEFAULT 0,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");
        
        echo "✅ Statistics table created!\n";
        
        // Insert admin user
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password_hash, name, role, active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@purrr.love', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', true]);
        
        echo "✅ Admin user created! (username: admin, password: password)\n";
        
        // Insert statistics
        $stmt = $pdo->prepare("INSERT IGNORE INTO site_statistics (stat_name, stat_value) VALUES (?, ?)");
        $stmt->execute(['total_users', 1]);
        $stmt->execute(['total_cats', 0]);
        $stmt->execute(['total_logins', 0]);
        $stmt->execute(['active_sessions', 0]);
        
        echo "✅ Statistics initialized!\n";
        
        // Verify
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $userCount = $stmt->fetch()['count'];
        
        $stmt = $pdo->query("SELECT * FROM site_statistics");
        $stats = $stmt->fetchAll();
        
        echo "\n📊 Verification:\n";
        echo "Users in database: $userCount\n";
        echo "Statistics:\n";
        foreach ($stats as $stat) {
            echo "  {$stat['stat_name']}: {$stat['stat_value']}\n";
        }
        
        echo "\n🎉 DATABASE SETUP COMPLETE!\n";
        echo "Admin login: username = admin, password = password\n";
        echo "You can now access the admin panel!\n";
        
    } else {
        echo "❌ Database connection methods failed\n";
        echo "MySQL connection: " . ($conn ? 'OK' : 'FAILED') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "Check error logs for additional details.\n";
    error_log("Database setup error: " . $e->getMessage() . " - " . $e->getTraceAsString());
}
?>
