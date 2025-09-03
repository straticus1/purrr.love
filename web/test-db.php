<?php
/**
 * 🧪 Database Test Script
 * Test database connection and show detailed error information
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🐱 Purrr.love Database Test</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5} .success{color:green} .error{color:red} .info{color:blue} .warning{color:orange}</style>";

// Test 1: Check if PDO extension is available
echo "<h2>1. PDO Extension Check</h2>";
if (extension_loaded('pdo')) {
    echo "<p class='success'>✅ PDO extension is loaded</p>";
    if (extension_loaded('pdo_mysql')) {
        echo "<p class='success'>✅ PDO MySQL driver is loaded</p>";
    } else {
        echo "<p class='error'>❌ PDO MySQL driver is NOT loaded</p>";
        echo "<p class='warning'>You need to install the pdo_mysql extension</p>";
    }
} else {
    echo "<p class='error'>❌ PDO extension is NOT loaded</p>";
    echo "<p class='warning'>You need to install the PDO extension</p>";
}

// Test 2: Check database configuration
echo "<h2>2. Database Configuration</h2>";
$config = [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'purrr_love',
    'DB_USER' => 'purrr_user',
    'DB_PASS' => '',
    'DB_PORT' => '3306'
];

foreach ($config as $key => $value) {
    echo "<p class='info'>📋 $key: " . ($value ?: '<em>empty</em>') . "</p>";
}

// Test 3: Try to connect to database
echo "<h2>3. Database Connection Test</h2>";
try {
    $dsn = "mysql:host={$config['DB_HOST']};port={$config['DB_PORT']};dbname={$config['DB_NAME']};charset=utf8mb4";
    echo "<p class='info'>🔌 Attempting connection to: $dsn</p>";
    
    $pdo = new PDO($dsn, $config['DB_USER'], $config['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "<p class='success'>✅ Database connection successful!</p>";
    
    // Test 4: Check if tables exist
    echo "<h2>4. Database Tables Check</h2>";
    $tables = ['users', 'cats', 'health_logs'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
            echo "<p class='success'>✅ Table '$table' exists and is accessible</p>";
        } catch (Exception $e) {
            echo "<p class='warning'>⚠️ Table '$table' not found: " . $e->getMessage() . "</p>";
            
            // Try to create the table
            try {
                if ($table === 'users') {
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS users (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            name VARCHAR(255) NOT NULL,
                            email VARCHAR(255) UNIQUE NOT NULL,
                            password_hash VARCHAR(255) NOT NULL,
                            role VARCHAR(50) DEFAULT 'user',
                            active BOOLEAN DEFAULT true,
                            level INT DEFAULT 1,
                            experience INT DEFAULT 0,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    echo "<p class='success'>✅ Table 'users' created successfully</p>";
                } elseif ($table === 'cats') {
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS cats (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            name VARCHAR(255) NOT NULL,
                            breed VARCHAR(255),
                            color VARCHAR(100),
                            age INT,
                            health INT DEFAULT 100,
                            happiness INT DEFAULT 100,
                            energy INT DEFAULT 100,
                            hunger INT DEFAULT 0,
                            cleanliness INT DEFAULT 100,
                            owner_id INT,
                            weight DECIMAL(5,2),
                            temperature DECIMAL(4,2),
                            heart_rate INT,
                            last_health_check TIMESTAMP NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    echo "<p class='success'>✅ Table 'cats' created successfully</p>";
                } elseif ($table === 'health_logs') {
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS health_logs (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            cat_id INT,
                            health INT,
                            happiness INT,
                            energy INT,
                            hunger INT,
                            cleanliness INT,
                            weight DECIMAL(5,2),
                            temperature DECIMAL(4,2),
                            heart_rate INT,
                            notes TEXT,
                            recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    echo "<p class='success'>✅ Table 'health_logs' created successfully</p>";
                }
            } catch (Exception $createError) {
                echo "<p class='error'>❌ Failed to create table '$table': " . $createError->getMessage() . "</p>";
            }
        }
    }
    
    // Test 5: Test user creation
    echo "<h2>5. User Creation Test</h2>";
    try {
        $testEmail = 'test_' . time() . '@example.com';
        $passwordHash = password_hash('testpassword123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, role, active, level, experience)
            VALUES (?, ?, ?, 'user', true, 1, 0)
        ");
        $stmt->execute(['Test User', $testEmail, $passwordHash]);
        
        $userId = $pdo->lastInsertId();
        echo "<p class='success'>✅ Test user created successfully (ID: $userId)</p>";
        
        // Clean up test user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        echo "<p class='info'>🧹 Test user cleaned up</p>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ User creation test failed: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    
    // Provide troubleshooting tips
    echo "<h2>🔧 Troubleshooting Tips</h2>";
    echo "<ul>";
    echo "<li>Check if MySQL is running</li>";
    echo "<li>Verify database credentials</li>";
    echo "<li>Ensure database '{$config['DB_NAME']}' exists</li>";
    echo "<li>Check if user '{$config['DB_USER']}' has proper permissions</li>";
    echo "<li>Verify MySQL is listening on port {$config['DB_PORT']}</li>";
    echo "</ul>";
    
    echo "<h3>Common Commands:</h3>";
    echo "<pre>";
    echo "# Start MySQL service\n";
    echo "sudo systemctl start mysql\n\n";
    echo "# Connect to MySQL as root\n";
    echo "sudo mysql -u root -p\n\n";
    echo "# Create database\n";
    echo "CREATE DATABASE {$config['DB_NAME']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n\n";
    echo "# Create user\n";
    echo "CREATE USER '{$config['DB_USER']}'@'localhost' IDENTIFIED BY 'your_password';\n\n";
    echo "# Grant permissions\n";
    echo "GRANT ALL PRIVILEGES ON {$config['DB_NAME']}.* TO '{$config['DB_USER']}'@'localhost';\n";
    echo "FLUSH PRIVILEGES;\n";
    echo "</pre>";
}

// Test 6: Check PHP configuration
echo "<h2>6. PHP Configuration</h2>";
echo "<p class='info'>📋 PHP Version: " . phpversion() . "</p>";
echo "<p class='info'>📋 Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p class='info'>📋 Max Execution Time: " . ini_get('max_execution_time') . "</p>";
echo "<p class='info'>📋 Upload Max Filesize: " . ini_get('upload_max_filesize') . "</p>";

echo "<hr>";
echo "<p><a href='index.php'>← Back to Home</a> | <a href='setup.php'>Database Setup</a></p>";
?>
