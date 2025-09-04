<?php
/**
 * 🗄️ Remote Database Setup for Purrr.love
 * This script creates the database and initial admin user
 */

// Simple security check
$token = $_GET['token'] ?? '';
if ($token !== 'setup_db_remote_' . date('Y-m-d')) {
    http_response_code(403);
    die("Access denied. Use token: setup_db_remote_" . date('Y-m-d'));
}

echo "<h1>🗄️ Purrr.love Database Setup</h1>\n";
echo "<pre>\n";

try {
    // First, connect to MySQL without specifying a database
    $dsn = "mysql:host=localhost;port=3306;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5
    ];
    
    echo "📡 Connecting to MySQL server...\n";
    $pdo = new PDO($dsn, 'root', '', $options);
    echo "✅ Connected to MySQL server successfully!\n";
    
    // Create the database
    echo "🏗️ Creating database 'purrr_love'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS purrr_love");
    echo "✅ Database 'purrr_love' created or already exists!\n";
    
    // Now connect to the specific database
    echo "🔗 Connecting to purrr_love database...\n";
    $dsn = "mysql:host=localhost;port=3306;dbname=purrr_love;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', '', $options);
    echo "✅ Connected to purrr_love database successfully!\n";
    
    // Create users table
    echo "👥 Creating users table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin', 'moderator') DEFAULT 'user',
            level INT DEFAULT 1,
            coins INT DEFAULT 100,
            experience_points INT DEFAULT 0,
            avatar_url VARCHAR(500),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Users table created!\n";
    
    // Create cats table
    echo "🐱 Creating cats table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            breed VARCHAR(100) DEFAULT 'Mixed',
            age INT DEFAULT 1,
            color VARCHAR(50) DEFAULT 'Orange',
            personality_openness DECIMAL(3,2) DEFAULT 0.50,
            personality_conscientiousness DECIMAL(3,2) DEFAULT 0.50,
            personality_extraversion DECIMAL(3,2) DEFAULT 0.50,
            personality_agreeableness DECIMAL(3,2) DEFAULT 0.50,
            personality_neuroticism DECIMAL(3,2) DEFAULT 0.50,
            health_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
            temperature DECIMAL(4,2) DEFAULT 101.50,
            heart_rate INT DEFAULT 120,
            weight DECIMAL(5,2) DEFAULT 10.00,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Cats table created!\n";
    
    // Create admin user
    echo "👑 Creating admin user...\n";
    $adminPasswordHash = password_hash('admin123456789!', PASSWORD_DEFAULT);
    
    // Try to insert admin user, update if exists
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, role, level, coins, experience_points) 
        VALUES ('admin', 'admin@purrr.love', ?, 'admin', 50, 10000, 50000)
        ON DUPLICATE KEY UPDATE 
        password_hash = VALUES(password_hash),
        role = VALUES(role),
        level = VALUES(level),
        coins = VALUES(coins),
        experience_points = VALUES(experience_points)
    ");
    $stmt->execute([$adminPasswordHash]);
    echo "✅ Admin user created/updated!\n";
    
    // Create sample cat for admin
    echo "🐾 Creating sample cat for admin...\n";
    $stmt = $pdo->prepare("
        INSERT INTO cats (user_id, name, breed, age, color, personality_openness, personality_conscientiousness) 
        VALUES (1, 'Whiskers', 'Persian', 3, 'White', 0.75, 0.65)
        ON DUPLICATE KEY UPDATE name = VALUES(name)
    ");
    $stmt->execute();
    echo "✅ Sample cat created!\n";
    
    // Create test user
    echo "🧪 Creating test user...\n";
    $testPasswordHash = password_hash('testpass123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, role, level, coins, experience_points) 
        VALUES ('testuser', 'testuser@example.com', ?, 'user', 5, 500, 1000)
        ON DUPLICATE KEY UPDATE 
        password_hash = VALUES(password_hash),
        level = VALUES(level),
        coins = VALUES(coins),
        experience_points = VALUES(experience_points)
    ");
    $stmt->execute([$testPasswordHash]);
    echo "✅ Test user created/updated!\n";
    
    // Get test user ID and create a cat for them
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'testuser@example.com'");
    $stmt->execute();
    $testUser = $stmt->fetch();
    
    if ($testUser) {
        echo "🐱 Creating sample cat for test user...\n";
        $stmt = $pdo->prepare("
            INSERT INTO cats (user_id, name, breed, age, color, personality_openness, personality_conscientiousness) 
            VALUES (?, 'Mittens', 'Tabby', 2, 'Gray', 0.60, 0.55)
            ON DUPLICATE KEY UPDATE name = VALUES(name)
        ");
        $stmt->execute([$testUser['id']]);
        echo "✅ Sample cat created for test user!\n";
    }
    
    echo "\n🎉 Database setup completed successfully!\n\n";
    echo "📋 CREDENTIALS:\n";
    echo "==========================================\n";
    echo "🔴 ADMIN LOGIN:\n";
    echo "   URL: https://purrr.love/web/admin.php\n";
    echo "   Email: admin@purrr.love\n";
    echo "   Password: admin123456789!\n\n";
    echo "🔵 TEST USER LOGIN:\n";
    echo "   URL: https://purrr.love\n";
    echo "   Email: testuser@example.com\n";
    echo "   Password: testpass123\n\n";
    echo "✅ You can now test both accounts!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Connection info: localhost:3306\n";
    
    // Additional debugging
    echo "\n🔍 Debug Info:\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "PDO Available: " . (class_exists('PDO') ? 'Yes' : 'No') . "\n";
    echo "MySQL Extension: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "❌ General error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
