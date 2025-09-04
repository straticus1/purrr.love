<?php
/**
 * 🔧 Direct MariaDB Database Creation
 * Creates the purrr_love database and sets up all tables
 */

// Security check
if (!isset($_GET['create_token']) || $_GET['create_token'] !== 'create_db_' . date('Ymd')) {
    http_response_code(403);
    die("Access denied. Use token: create_db_" . date('Ymd'));
}

header('Content-Type: text/plain; charset=utf-8');
echo "🔧 MariaDB Database Creation\n";
echo "=============================\n\n";

try {
    // Direct connection to MariaDB RDS
    $db_host = 'purrr-mariadb-production.c3iuy64is41m.us-east-1.rds.amazonaws.com';
    $db_user = 'purrruser';
    $db_pass = 'PurrrLove2025';
    $db_port = '3306';
    $db_name = 'purrr_love';
    
    echo "📡 Connecting to MariaDB server...\n";
    echo "Host: $db_host\n";
    echo "Port: $db_port\n";
    echo "User: $db_user\n\n";
    
    // First connect without database to create it
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    echo "✅ Connected to MariaDB successfully!\n\n";
    
    // Create database
    echo "🏗️ Creating database '$db_name'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '$db_name' created successfully!\n";
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    echo "🔗 Connected to '$db_name' database!\n\n";
    
    // Create users table
    echo "👥 Creating users table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            name VARCHAR(100),
            role VARCHAR(20) DEFAULT 'user',
            active BOOLEAN DEFAULT TRUE,
            is_active BOOLEAN DEFAULT TRUE,
            level INT DEFAULT 1,
            coins INT DEFAULT 100,
            experience_points INT DEFAULT 0,
            avatar_url VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_username (username),
            INDEX idx_role (role),
            INDEX idx_active (active, is_active)
        ) ENGINE=InnoDB
    ");
    echo "✅ Users table created!\n";
    
    // Create cats table
    echo "🐱 Creating cats table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            owner_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            breed VARCHAR(50) DEFAULT 'Mixed',
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
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_owner (owner_id),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB
    ");
    echo "✅ Cats table created!\n";
    
    // Create admin user
    echo "\n👑 Creating admin user...\n";
    $admin_password_hash = password_hash('admin123456789!', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, name, role, active, is_active, level, coins, experience_points) 
        VALUES ('admin', 'admin@purrr.love', ?, 'System Administrator', 'admin', TRUE, TRUE, 50, 10000, 50000)
        ON DUPLICATE KEY UPDATE 
            password_hash = VALUES(password_hash),
            role = VALUES(role),
            active = VALUES(active),
            is_active = VALUES(is_active),
            level = VALUES(level),
            coins = VALUES(coins),
            experience_points = VALUES(experience_points),
            name = VALUES(name),
            updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$admin_password_hash]);
    echo "✅ Admin user created successfully!\n";
    
    // Create test user
    echo "🧪 Creating test user...\n";
    $test_password_hash = password_hash('testpass123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, name, role, active, is_active, level, coins, experience_points) 
        VALUES ('testuser', 'testuser@example.com', ?, 'Test User', 'user', TRUE, TRUE, 5, 500, 1000)
        ON DUPLICATE KEY UPDATE 
            password_hash = VALUES(password_hash),
            active = VALUES(active),
            is_active = VALUES(is_active),
            level = VALUES(level),
            coins = VALUES(coins),
            experience_points = VALUES(experience_points),
            updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$test_password_hash]);
    echo "✅ Test user created successfully!\n";
    
    // Verify password hashes
    echo "\n🔐 Verifying password authentication...\n";
    
    // Test admin password
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = 'admin@purrr.love'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin && password_verify('admin123456789!', $admin['password_hash'])) {
        echo "✅ Admin password verification: PASS\n";
    } else {
        echo "❌ Admin password verification: FAILED\n";
    }
    
    // Test user password
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = 'testuser@example.com'");
    $stmt->execute();
    $test_user = $stmt->fetch();
    
    if ($test_user && password_verify('testpass123', $test_user['password_hash'])) {
        echo "✅ Test user password verification: PASS\n";
    } else {
        echo "❌ Test user password verification: FAILED\n";
    }
    
    // Create sample cats
    echo "\n🐾 Creating sample cats...\n";
    
    if ($admin) {
        $stmt = $pdo->prepare("
            INSERT INTO cats (user_id, owner_id, name, breed, age, color, personality_openness, personality_conscientiousness, is_active) 
            VALUES (?, ?, 'Whiskers', 'Persian', 3, 'White', 0.75, 0.65, TRUE)
            ON DUPLICATE KEY UPDATE 
                breed = VALUES(breed),
                age = VALUES(age),
                color = VALUES(color),
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$admin['id'], $admin['id']]);
        echo "  ✅ Sample cat 'Whiskers' created for admin!\n";
    }
    
    if ($test_user) {
        $stmt = $pdo->prepare("
            INSERT INTO cats (user_id, owner_id, name, breed, age, color, personality_openness, personality_conscientiousness, is_active) 
            VALUES (?, ?, 'Mittens', 'Tabby', 2, 'Gray', 0.60, 0.55, TRUE)
            ON DUPLICATE KEY UPDATE 
                breed = VALUES(breed),
                age = VALUES(age),
                color = VALUES(color),
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$test_user['id'], $test_user['id']]);
        echo "  ✅ Sample cat 'Mittens' created for test user!\n";
    }
    
    // Final verification
    echo "\n📊 Database verification...\n";
    
    // Show all users
    $stmt = $pdo->query("SELECT id, username, email, role, active, is_active, level, coins FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    echo "Users in database:\n";
    foreach ($users as $user) {
        $status = ($user['active'] && $user['is_active']) ? 'Active' : 'Inactive';
        echo "  • ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}, Level: {$user['level']}, Coins: {$user['coins']}, Status: $status\n";
    }
    
    // Show cats
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cats WHERE is_active = TRUE");
    $cat_count = $stmt->fetch();
    echo "Active cats in database: {$cat_count['count']}\n";
    
    echo "\n🎉 DATABASE SETUP COMPLETED SUCCESSFULLY!\n\n";
    
    echo "🔐 VERIFIED WORKING CREDENTIALS:\n";
    echo "===============================================\n";
    echo "🔴 ADMIN LOGIN:\n";
    echo "   Email: admin@purrr.love\n";
    echo "   Password: admin123456789!\n";
    echo "   Status: ✅ READY TO USE\n\n";
    
    echo "🔵 REGULAR USER LOGIN:\n";
    echo "   Email: testuser@example.com\n";
    echo "   Password: testpass123\n";
    echo "   Status: ✅ READY TO USE\n\n";
    
    echo "🌐 DATABASE CONNECTION INFO:\n";
    echo "=============================\n";
    echo "Host: $db_host\n";
    echo "Database: $db_name\n";
    echo "Port: $db_port\n";
    echo "Status: ✅ FULLY OPERATIONAL\n\n";
    
    echo "✅ You can now login with both accounts!\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
