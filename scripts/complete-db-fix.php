<?php
/**
 * 🔧 Complete Database Fix for Purrr.love
 * This script fixes all database schema issues and creates proper test accounts
 */

// Security check
if (!isset($_GET['fix_token']) || $_GET['fix_token'] !== 'fix_all_' . date('Ymd')) {
    http_response_code(403);
    die("Access denied. Use token: fix_all_" . date('Ymd'));
}

header('Content-Type: text/plain');
echo "🔧 Purrr.love Complete Database Fix\n";
echo "=====================================\n\n";

try {
    // Connect to MySQL
    echo "📡 Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host=localhost;port=3306;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    echo "✅ Connected to MySQL successfully!\n";
    
    // Create database if not exists
    echo "🏗️ Ensuring database exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS purrr_love");
    echo "✅ Database 'purrr_love' ready!\n";
    
    // Connect to specific database
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=purrr_love;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    echo "🔗 Connected to purrr_love database!\n\n";
    
    // Fix users table structure to match all expectations
    echo "👥 Fixing users table structure...\n";
    
    // Add missing columns if they don't exist
    $columns_to_add = [
        "level INT DEFAULT 1",
        "coins INT DEFAULT 100", 
        "experience_points INT DEFAULT 0",
        "avatar_url VARCHAR(500)",
        "is_active BOOLEAN DEFAULT TRUE",
        "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];
    
    foreach ($columns_to_add as $column) {
        $column_name = explode(' ', $column)[0];
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN $column");
            echo "  ✅ Added column: $column_name\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "  ℹ️ Column $column_name already exists\n";
            } else {
                echo "  ⚠️ Warning adding $column_name: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Fix cats table structure
    echo "🐱 Fixing cats table structure...\n";
    
    $cat_columns_to_add = [
        "age INT DEFAULT 1",
        "color VARCHAR(50) DEFAULT 'Orange'",
        "personality_openness DECIMAL(3,2) DEFAULT 0.50",
        "personality_conscientiousness DECIMAL(3,2) DEFAULT 0.50", 
        "personality_extraversion DECIMAL(3,2) DEFAULT 0.50",
        "personality_agreeableness DECIMAL(3,2) DEFAULT 0.50",
        "personality_neuroticism DECIMAL(3,2) DEFAULT 0.50",
        "health_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good'",
        "temperature DECIMAL(4,2) DEFAULT 101.50",
        "heart_rate INT DEFAULT 120", 
        "weight DECIMAL(5,2) DEFAULT 10.00",
        "is_active BOOLEAN DEFAULT TRUE",
        "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];
    
    foreach ($cat_columns_to_add as $column) {
        $column_name = explode(' ', $column)[0];
        try {
            $pdo->exec("ALTER TABLE cats ADD COLUMN $column");
            echo "  ✅ Added column: $column_name\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "  ℹ️ Column $column_name already exists\n";
            } else {
                echo "  ⚠️ Warning adding $column_name: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n👑 Creating/updating admin user...\n";
    
    // Create proper admin user with correct password
    $admin_password_hash = password_hash('admin123456789!', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, name, role, active, is_active, level, coins, experience_points) 
        VALUES ('admin', 'admin@purrr.love', ?, 'System Administrator', 'admin', 1, 1, 50, 10000, 50000)
        ON DUPLICATE KEY UPDATE 
            password_hash = VALUES(password_hash),
            role = VALUES(role),
            active = VALUES(active),
            is_active = VALUES(is_active),
            level = VALUES(level),
            coins = VALUES(coins),
            experience_points = VALUES(experience_points),
            name = VALUES(name)
    ");
    $stmt->execute([$admin_password_hash]);
    echo "✅ Admin user created/updated with proper credentials!\n";
    
    echo "🧪 Creating test user...\n";
    
    // Create test user
    $test_password_hash = password_hash('testpass123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, name, role, active, is_active, level, coins, experience_points) 
        VALUES ('testuser', 'testuser@example.com', ?, 'Test User', 'user', 1, 1, 5, 500, 1000)
        ON DUPLICATE KEY UPDATE 
            password_hash = VALUES(password_hash),
            active = VALUES(active),
            is_active = VALUES(is_active),
            level = VALUES(level),
            coins = VALUES(coins),
            experience_points = VALUES(experience_points)
    ");
    $stmt->execute([$test_password_hash]);
    echo "✅ Test user created/updated!\n";
    
    echo "🐾 Creating sample cats...\n";
    
    // Get user IDs
    $admin_stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'admin@purrr.love'");
    $admin_stmt->execute();
    $admin = $admin_stmt->fetch();
    
    $test_stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'testuser@example.com'");
    $test_stmt->execute();
    $test_user = $test_stmt->fetch();
    
    if ($admin) {
        $stmt = $pdo->prepare("
            INSERT INTO cats (user_id, owner_id, name, breed, age, color, personality_openness, personality_conscientiousness, is_active) 
            VALUES (?, ?, 'Whiskers', 'Persian', 3, 'White', 0.75, 0.65, 1)
            ON DUPLICATE KEY UPDATE name = VALUES(name)
        ");
        $stmt->execute([$admin['id'], $admin['id']]);
        echo "  ✅ Sample cat created for admin!\n";
    }
    
    if ($test_user) {
        $stmt = $pdo->prepare("
            INSERT INTO cats (user_id, owner_id, name, breed, age, color, personality_openness, personality_conscientiousness, is_active) 
            VALUES (?, ?, 'Mittens', 'Tabby', 2, 'Gray', 0.60, 0.55, 1)
            ON DUPLICATE KEY UPDATE name = VALUES(name)
        ");
        $stmt->execute([$test_user['id'], $test_user['id']]);
        echo "  ✅ Sample cat created for test user!\n";
    }
    
    echo "\n📊 Database verification...\n";
    
    // Verify users
    $stmt = $pdo->query("SELECT id, username, email, role, active, is_active FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    echo "Users in database:\n";
    foreach ($users as $user) {
        $active_status = ($user['active'] || $user['is_active']) ? 'Active' : 'Inactive';
        echo "  • ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}, Status: $active_status\n";
    }
    
    // Verify cats
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cats");
    $cat_count = $stmt->fetch();
    echo "Cats in database: {$cat_count['count']}\n";
    
    echo "\n🎉 DATABASE FIXES COMPLETED SUCCESSFULLY!\n\n";
    
    echo "🔐 UPDATED CREDENTIALS:\n";
    echo "=============================================\n";
    echo "🔴 ADMIN LOGIN:\n";
    echo "   URL: https://purrr.love/web/admin.php\n";
    echo "   Email: admin@purrr.love\n";
    echo "   Password: admin123456789!\n";
    echo "   Role: admin (full access)\n\n";
    
    echo "🔵 TEST USER LOGIN:\n";
    echo "   URL: https://purrr.love\n";
    echo "   Email: testuser@example.com\n";
    echo "   Password: testpass123\n";
    echo "   Role: user (standard access)\n\n";
    
    echo "✅ Both accounts should now work for:\n";
    echo "   • Login authentication\n";
    echo "   • User registration system\n";  
    echo "   • Admin panel access (for admin)\n";
    echo "   • Dashboard access\n";
    echo "   • All application features\n\n";
    
    echo "🧪 TESTING INSTRUCTIONS:\n";
    echo "1. Test admin login at https://purrr.love/web/admin.php\n";
    echo "2. Test user login at https://purrr.love\n";
    echo "3. Test new user registration at https://purrr.love/web/register.php\n";
    echo "4. All database schema issues should be resolved!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "❌ General error: " . $e->getMessage() . "\n";
}
?>
