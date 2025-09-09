<?php
/**
 * 🚀 Production MariaDB Initialization
 * Initialize the production MariaDB RDS instance with schema and data
 */

echo "🔧 Production MariaDB Initialization\n";
echo "====================================\n\n";

// Production database credentials
$db_host = 'purrr-mariadb-production.c3iuy64is41m.us-east-1.rds.amazonaws.com';
$db_port = '3306';
$db_user = 'purrruser';
$db_pass = 'PurrrLove2025';
$db_name = 'purrr_love';

try {
    echo "📡 Connecting to MariaDB RDS instance...\n";
    echo "Host: $db_host\n";
    echo "Port: $db_port\n";
    echo "User: $db_user\n\n";
    
    // First connect without database to create it
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    echo "✅ Connected to MariaDB RDS successfully!\n\n";
    
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
    
    // Verify admin password
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE email = ?");
    $stmt->execute(['admin@purrr.love']);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify('admin123456789!', $admin['password_hash'])) {
        echo "✅ Admin password verification SUCCESSFUL!\n";
    } else {
        echo "❌ Admin password verification FAILED!\n";
    }
    
    echo "\n👤 Creating test user...\n";
    
    // Create test user with secure password  
    $test_password_hash = password_hash('testpass123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, name, role, active, is_active, level, coins, experience_points) 
        VALUES ('testuser', 'testuser@example.com', ?, 'Test User', 'user', TRUE, TRUE, 25, 5000, 25000)
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
    $stmt->execute([$test_password_hash]);
    echo "✅ Test user created/updated successfully!\n";
    
    // Verify test user password
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE email = ?");
    $stmt->execute(['testuser@example.com']);
    $test = $stmt->fetch();
    
    if ($test && password_verify('testpass123', $test['password_hash'])) {
        echo "✅ Test user password verification SUCCESSFUL!\n";
    } else {
        echo "❌ Test user password verification FAILED!\n";
    }
    
    echo "\n🐱 Creating sample cats...\n";
    
    // Get admin and test user IDs
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@purrr.love']);
    $admin_id = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['testuser@example.com']);
    $test_id = $stmt->fetchColumn();
    
    // Create admin cats
    if ($admin_id) {
        $cats = [
            ['BanditCat', 'Maine Coon', 5, 'Black and White', 4.8, 4.2, 3.7, 4.5, 2.1, 'excellent'],
            ['LunaCat', 'Siamese', 3, 'Seal Point', 4.5, 3.8, 4.0, 4.2, 2.8, 'good'],
            ['RyCat', 'British Shorthair', 7, 'Blue', 3.9, 4.5, 3.2, 4.0, 2.5, 'excellent']
        ];
        
        foreach ($cats as $cat) {
            $stmt = $pdo->prepare("
                INSERT INTO cats (user_id, owner_id, name, breed, age, color, 
                                personality_openness, personality_conscientiousness, 
                                personality_extraversion, personality_agreeableness, 
                                personality_neuroticism, health_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    breed = VALUES(breed),
                    age = VALUES(age),
                    color = VALUES(color),
                    personality_openness = VALUES(personality_openness),
                    personality_conscientiousness = VALUES(personality_conscientiousness),
                    personality_extraversion = VALUES(personality_extraversion),
                    personality_agreeableness = VALUES(personality_agreeableness),
                    personality_neuroticism = VALUES(personality_neuroticism),
                    health_status = VALUES(health_status),
                    updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$admin_id, $admin_id, ...$cat]);
            echo "✅ Created cat: {$cat[0]}\n";
        }
    }
    
    // Create test user cats
    if ($test_id) {
        $test_cats = [
            ['Whiskers', 'Tabby', 2, 'Orange', 3.5, 3.2, 4.1, 3.8, 3.0, 'good'],
            ['Mittens', 'Ragdoll', 4, 'Cream', 2.8, 4.0, 2.5, 4.5, 2.2, 'excellent']
        ];
        
        foreach ($test_cats as $cat) {
            $stmt = $pdo->prepare("
                INSERT INTO cats (user_id, owner_id, name, breed, age, color, 
                                personality_openness, personality_conscientiousness, 
                                personality_extraversion, personality_agreeableness, 
                                personality_neuroticism, health_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    breed = VALUES(breed),
                    age = VALUES(age),
                    color = VALUES(color),
                    personality_openness = VALUES(personality_openness),
                    personality_conscientiousness = VALUES(personality_conscientiousness),
                    personality_extraversion = VALUES(personality_extraversion),
                    personality_agreeableness = VALUES(personality_agreeableness),
                    personality_neuroticism = VALUES(personality_neuroticism),
                    health_status = VALUES(health_status),
                    updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$test_id, $test_id, ...$cat]);
            echo "✅ Created cat: {$cat[0]}\n";
        }
    }
    
    echo "\n📊 Final database status:\n";
    
    // Count records
    $users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $cats = $pdo->query("SELECT COUNT(*) FROM cats")->fetchColumn();
    
    echo "👥 Users: $users\n";
    echo "🐱 Cats: $cats\n";
    
    echo "\n🎉 Production MariaDB initialization completed successfully!\n";
    echo "\n📋 Test Credentials:\n";
    echo "👑 Admin: admin@purrr.love / admin123456789!\n";
    echo "👤 User:  testuser@example.com / testpass123\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ General error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
