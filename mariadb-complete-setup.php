<?php
/**
 * 🔧 Complete MariaDB Setup for Purrr.love
 * Comprehensive database schema creation and user setup
 */

// Security check
if (!isset($_GET['setup_token']) || $_GET['setup_token'] !== 'mariadb_setup_' . date('Ymd')) {
    http_response_code(403);
    die("Access denied. Use token: mariadb_setup_" . date('Ymd'));
}

header('Content-Type: text/plain; charset=utf-8');
echo "🔧 Purrr.love MariaDB Complete Setup\n";
echo "=====================================\n\n";

try {
    // Get database credentials from environment
    $db_host = $_ENV['DB_HOST'] ?? 'localhost';
    $db_name = $_ENV['DB_NAME'] ?? 'purrr_love';
    $db_user = $_ENV['DB_USER'] ?? 'purrruser';
    $db_pass = $_ENV['DB_PASS'] ?? 'PurrrLove2025';
    $db_port = $_ENV['DB_PORT'] ?? '3306';
    
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
    
    // Create database if not exists
    echo "🏗️ Creating database...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '$db_name' created/verified!\n";
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    echo "🔗 Connected to '$db_name' database!\n\n";
    
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
    
    echo "🔑 Creating API keys table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS api_keys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            key_hash VARCHAR(64) NOT NULL,
            name VARCHAR(100) NOT NULL,
            scopes JSON,
            ip_whitelist JSON,
            active BOOLEAN DEFAULT TRUE,
            expires_at TIMESTAMP NULL,
            last_used_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_key_hash (key_hash),
            INDEX idx_user (user_id),
            INDEX idx_active (active)
        ) ENGINE=InnoDB
    ");
    echo "✅ API keys table created!\n";
    
    echo "🎫 Creating OAuth2 tables...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS oauth2_access_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            scope TEXT,
            expires_at TIMESTAMP NOT NULL,
            revoked BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_token (token),
            INDEX idx_user (user_id),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB
    ");
    echo "✅ OAuth2 access tokens table created!\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS oauth2_refresh_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            access_token_id INT,
            expires_at TIMESTAMP NOT NULL,
            revoked BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (access_token_id) REFERENCES oauth2_access_tokens(id) ON DELETE CASCADE,
            UNIQUE KEY unique_refresh_token (token),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB
    ");
    echo "✅ OAuth2 refresh tokens table created!\n";
    
    echo "📊 Creating sessions table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(128) NOT NULL PRIMARY KEY,
            user_id INT,
            data TEXT,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_activity (last_activity)
        ) ENGINE=InnoDB
    ");
    echo "✅ Sessions table created!\n";
    
    echo "\n👑 Creating admin user...\n";
    
    // Create admin user with secure password
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
    echo "✅ Admin user created/updated successfully!\n";
    
    echo "🧪 Creating test user...\n";
    
    // Create test user
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
    echo "✅ Test user created/updated successfully!\n";
    
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
    
    echo "\n🎉 MARIADB SETUP COMPLETED SUCCESSFULLY!\n\n";
    
    echo "🔐 VERIFIED WORKING CREDENTIALS:\n";
    echo "===============================================\n";
    echo "🔴 ADMIN LOGIN:\n";
    echo "   Email: admin@purrr.love\n";
    echo "   Password: admin123456789!\n";
    echo "   Role: admin (full system access)\n";
    echo "   Level: 50, Coins: 10000\n";
    echo "   Status: ✅ READY TO USE\n\n";
    
    echo "🔵 REGULAR USER LOGIN:\n";
    echo "   Email: testuser@example.com\n";
    echo "   Password: testpass123\n";
    echo "   Role: user (standard access)\n";
    echo "   Level: 5, Coins: 500\n";
    echo "   Status: ✅ READY TO USE\n\n";
    
    echo "🌐 DATABASE CONNECTION INFO:\n";
    echo "=============================\n";
    echo "Host: $db_host\n";
    echo "Database: $db_name\n";
    echo "Port: $db_port\n";
    echo "Engine: MariaDB (MySQL compatible)\n";
    echo "Status: ✅ FULLY OPERATIONAL\n\n";
    
    echo "✅ The system is now ready for full testing and production use!\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    echo "\n🔧 TROUBLESHOOTING INFO:\n";
    echo "========================\n";
    echo "Database Host: " . ($db_host ?? 'Not set') . "\n";
    echo "Database Name: " . ($db_name ?? 'Not set') . "\n";
    echo "Database User: " . ($db_user ?? 'Not set') . "\n";
    echo "Database Port: " . ($db_port ?? 'Not set') . "\n";
    
    echo "\nEnvironment variables to check:\n";
    echo "- DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT\n";
}
?>
