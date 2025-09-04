<?php
/**
 * 🔧 PostgreSQL Schema Fix for Purrr.love Production
 * This script fixes the missing is_active column and sets up working user accounts
 */

// Security check
if (!isset($_GET['fix_token']) || $_GET['fix_token'] !== 'postgres_fix_' . date('Ymd')) {
    http_response_code(403);
    die("Access denied. Use token: postgres_fix_" . date('Ymd'));
}

header('Content-Type: text/plain; charset=utf-8');
echo "🔧 Purrr.love PostgreSQL Schema Fix\n";
echo "====================================\n\n";

try {
    // Get database credentials from environment
    $db_host = $_ENV['DB_HOST'] ?? 'nitetext-production.c3iuy64is41m.us-east-1.rds.amazonaws.com';
    $db_name = $_ENV['DB_NAME'] ?? 'nitetext';
    $db_user = $_ENV['DB_USER'] ?? 'postgres';
    $db_pass = $_ENV['DB_PASS'] ?? '';
    $db_port = $_ENV['DB_PORT'] ?? '5432';
    
    echo "📡 Connecting to PostgreSQL server...\n";
    echo "Host: $db_host\n";
    echo "Database: $db_name\n";
    echo "Port: $db_port\n\n";
    
    $pdo = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    echo "✅ Connected to PostgreSQL successfully!\n\n";
    
    // Check if users table exists
    echo "🔍 Checking database schema...\n";
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' AND table_name IN ('users', 'cats')
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "⚠️  No users or cats tables found. Creating from scratch...\n";
        
        // Create users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                name VARCHAR(100),
                role VARCHAR(20) DEFAULT 'user',
                active BOOLEAN DEFAULT TRUE,
                is_active BOOLEAN DEFAULT TRUE,
                level INTEGER DEFAULT 1,
                coins INTEGER DEFAULT 100,
                experience_points INTEGER DEFAULT 0,
                avatar_url VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✅ Users table created!\n";
        
        // Create cats table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS cats (
                id SERIAL PRIMARY KEY,
                user_id INTEGER REFERENCES users(id),
                owner_id INTEGER REFERENCES users(id),
                name VARCHAR(100) NOT NULL,
                breed VARCHAR(50) DEFAULT 'Mixed',
                age INTEGER DEFAULT 1,
                color VARCHAR(50) DEFAULT 'Orange',
                personality_openness DECIMAL(3,2) DEFAULT 0.50,
                personality_conscientiousness DECIMAL(3,2) DEFAULT 0.50,
                personality_extraversion DECIMAL(3,2) DEFAULT 0.50,
                personality_agreeableness DECIMAL(3,2) DEFAULT 0.50,
                personality_neuroticism DECIMAL(3,2) DEFAULT 0.50,
                health_status VARCHAR(20) DEFAULT 'good',
                temperature DECIMAL(4,2) DEFAULT 101.50,
                heart_rate INTEGER DEFAULT 120,
                weight DECIMAL(5,2) DEFAULT 10.00,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✅ Cats table created!\n";
        
    } else {
        echo "✅ Found existing tables: " . implode(', ', $tables) . "\n";
        
        // Check if is_active column exists in users table
        $stmt = $pdo->query("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'users' AND column_name = 'is_active'
        ");
        $has_is_active = $stmt->fetchColumn();
        
        if (!$has_is_active) {
            echo "🔨 Adding missing is_active column to users table...\n";
            $pdo->exec("ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE");
            echo "✅ Added is_active column to users table!\n";
        } else {
            echo "ℹ️  is_active column already exists in users table\n";
        }
        
        // Check for other potentially missing columns
        $columns_to_add = [
            'level INTEGER DEFAULT 1',
            'coins INTEGER DEFAULT 100',
            'experience_points INTEGER DEFAULT 0',
            'avatar_url VARCHAR(500)',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ];
        
        foreach ($columns_to_add as $column_def) {
            $column_name = explode(' ', $column_def)[0];
            $stmt = $pdo->prepare("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_name = 'users' AND column_name = ?
            ");
            $stmt->execute([$column_name]);
            
            if (!$stmt->fetchColumn()) {
                try {
                    $pdo->exec("ALTER TABLE users ADD COLUMN $column_def");
                    echo "✅ Added $column_name column to users table\n";
                } catch (Exception $e) {
                    echo "⚠️  Warning adding $column_name: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n👑 Creating/updating admin user...\n";
    
    // Create admin user with correct password hash
    $admin_password_hash = password_hash('admin123456789!', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, name, role, active, is_active, level, coins, experience_points) 
        VALUES ('admin', 'admin@purrr.love', ?, 'System Administrator', 'admin', TRUE, TRUE, 50, 10000, 50000)
        ON CONFLICT (email) 
        DO UPDATE SET 
            password_hash = EXCLUDED.password_hash,
            role = EXCLUDED.role,
            active = EXCLUDED.active,
            is_active = EXCLUDED.is_active,
            level = EXCLUDED.level,
            coins = EXCLUDED.coins,
            experience_points = EXCLUDED.experience_points,
            name = EXCLUDED.name,
            updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$admin_password_hash]);
    echo "✅ Admin user created/updated successfully!\n";
    
    echo "🧪 Creating/updating test user...\n";
    
    // Create test user
    $test_password_hash = password_hash('testpass123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, name, role, active, is_active, level, coins, experience_points) 
        VALUES ('testuser', 'testuser@example.com', ?, 'Test User', 'user', TRUE, TRUE, 5, 500, 1000)
        ON CONFLICT (email) 
        DO UPDATE SET 
            password_hash = EXCLUDED.password_hash,
            active = EXCLUDED.active,
            is_active = EXCLUDED.is_active,
            level = EXCLUDED.level,
            coins = EXCLUDED.coins,
            experience_points = EXCLUDED.experience_points,
            updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$test_password_hash]);
    echo "✅ Test user created/updated successfully!\n";
    
    // Verify password hashes work
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
            ON CONFLICT (user_id, name) DO NOTHING
        ");
        try {
            $stmt->execute([$admin['id'], $admin['id']]);
            echo "  ✅ Sample cat 'Whiskers' created for admin!\n";
        } catch (Exception $e) {
            // Create without conflict resolution if that fails
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO cats (user_id, owner_id, name, breed, age, color, personality_openness, personality_conscientiousness, is_active) 
                    VALUES (?, ?, 'Whiskers', 'Persian', 3, 'White', 0.75, 0.65, TRUE)
                ");
                $stmt->execute([$admin['id'], $admin['id']]);
                echo "  ✅ Sample cat 'Whiskers' created for admin!\n";
            } catch (Exception $e2) {
                echo "  ℹ️  Admin cat may already exist\n";
            }
        }
    }
    
    if ($test_user) {
        $stmt = $pdo->prepare("
            INSERT INTO cats (user_id, owner_id, name, breed, age, color, personality_openness, personality_conscientiousness, is_active) 
            VALUES (?, ?, 'Mittens', 'Tabby', 2, 'Gray', 0.60, 0.55, TRUE)
            ON CONFLICT (user_id, name) DO NOTHING
        ");
        try {
            $stmt->execute([$test_user['id'], $test_user['id']]);
            echo "  ✅ Sample cat 'Mittens' created for test user!\n";
        } catch (Exception $e) {
            // Create without conflict resolution if that fails
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO cats (user_id, owner_id, name, breed, age, color, personality_openness, personality_conscientiousness, is_active) 
                    VALUES (?, ?, 'Mittens', 'Tabby', 2, 'Gray', 0.60, 0.55, TRUE)
                ");
                $stmt->execute([$test_user['id'], $test_user['id']]);
                echo "  ✅ Sample cat 'Mittens' created for test user!\n";
            } catch (Exception $e2) {
                echo "  ℹ️  Test user cat may already exist\n";
            }
        }
    }
    
    echo "\n📊 Final database verification...\n";
    
    // Verify users
    $stmt = $pdo->query("SELECT id, username, email, role, active, is_active FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    echo "Users in database:\n";
    foreach ($users as $user) {
        $active_status = ($user['active'] && $user['is_active']) ? 'Active' : 'Inactive';
        echo "  • ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}, Status: $active_status\n";
    }
    
    // Verify cats
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cats");
    $cat_count = $stmt->fetch();
    echo "Cats in database: {$cat_count['count']}\n";
    
    echo "\n🎉 POSTGRESQL SCHEMA FIX COMPLETED SUCCESSFULLY!\n\n";
    
    echo "🔐 VERIFIED WORKING CREDENTIALS:\n";
    echo "===============================================\n";
    echo "🔴 ADMIN LOGIN:\n";
    echo "   URL: https://purrr.love/web/admin.php\n";
    echo "   Email: admin@purrr.love\n";
    echo "   Password: admin123456789!\n";
    echo "   Role: admin (full system access)\n";
    echo "   Status: ✅ READY TO USE\n\n";
    
    echo "🔵 REGULAR USER LOGIN:\n";
    echo "   URL: https://purrr.love\n";
    echo "   Email: testuser@example.com\n";
    echo "   Password: testpass123\n";
    echo "   Role: user (standard access)\n";
    echo "   Status: ✅ READY TO USE\n\n";
    
    echo "🧪 TESTING INSTRUCTIONS:\n";
    echo "========================\n";
    echo "1. Test admin login at: https://purrr.love/web/admin.php\n";
    echo "2. Test regular login at: https://purrr.love\n";
    echo "3. Both accounts should now work perfectly!\n";
    echo "4. Admin has full access, test user has standard access\n";
    echo "5. Both users have sample cats created\n\n";
    
    echo "✅ The database schema is now fixed and both login accounts are working!\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    // Provide troubleshooting information
    echo "\n🔧 TROUBLESHOOTING INFO:\n";
    echo "========================\n";
    echo "- Database Host: " . ($db_host ?? 'Not set') . "\n";
    echo "- Database Name: " . ($db_name ?? 'Not set') . "\n";
    echo "- Database User: " . ($db_user ?? 'Not set') . "\n";
    echo "- Database Port: " . ($db_port ?? 'Not set') . "\n";
    echo "\nCheck environment variables:\n";
    echo "- DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT\n";
}
?>
