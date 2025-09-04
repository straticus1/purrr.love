<?php
// Local database initialization script for Purrr.love production MySQL database
// This script connects to the production database and initializes the schema

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🚀 Starting Purrr.love Database Initialization...\n\n";

// Production database connection details
$host = 'localhost';  // We'll need to get this from the running containers
$database = 'purrrlove';
$username = 'root';
$password = 'purrr123'; // From environment variable MYSQL_ADMIN_PASS

// For production, we'd normally get these from environment or config
// But since we're doing a local init, let's use the container's MySQL directly
$host = '127.0.0.1';
$port = 3306;

echo "Connecting to MySQL at {$host}:{$port}...\n";

try {
    // Create connection using mysqli (most likely to be available)
    $connection = new mysqli($host, $username, $password, '', $port);
    
    if ($connection->connect_error) {
        die("❌ Connection failed: " . $connection->connect_error . "\n");
    }
    
    echo "✅ Connected to MySQL successfully!\n\n";
    
    // Create database if it doesn't exist
    echo "Creating database '{$database}'...\n";
    $sql = "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($connection->query($sql)) {
        echo "✅ Database created/verified successfully!\n";
    } else {
        echo "❌ Error creating database: " . $connection->error . "\n";
        exit(1);
    }
    
    // Select the database
    $connection->select_db($database);
    echo "✅ Selected database '{$database}'\n\n";
    
    // Core schema
    echo "Creating core tables...\n";
    $coreSchema = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin', 'moderator') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    
    CREATE TABLE IF NOT EXISTS cats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        breed VARCHAR(50),
        color VARCHAR(50),
        age INT,
        personality TEXT,
        happiness INT DEFAULT 50,
        energy INT DEFAULT 50,
        hunger INT DEFAULT 50,
        health INT DEFAULT 100,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    
    CREATE TABLE IF NOT EXISTS activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cat_id INT NOT NULL,
        activity_type ENUM('feed', 'play', 'groom', 'sleep', 'adventure') NOT NULL,
        description TEXT,
        happiness_change INT DEFAULT 0,
        energy_change INT DEFAULT 0,
        hunger_change INT DEFAULT 0,
        health_change INT DEFAULT 0,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
        INDEX idx_cat_id (cat_id),
        INDEX idx_activity_type (activity_type),
        INDEX idx_timestamp (timestamp)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    // Execute each statement
    $statements = explode(';', $coreSchema);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if ($connection->query($statement)) {
                echo "✅ Executed: " . substr($statement, 0, 50) . "...\n";
            } else {
                echo "❌ Error executing statement: " . $connection->error . "\n";
                echo "Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }
    
    echo "\n";
    
    // Create default admin user
    echo "Creating default admin user...\n";
    $adminPassword = password_hash('password', PASSWORD_DEFAULT);
    $adminSql = "INSERT IGNORE INTO users (username, email, password_hash, role) VALUES ('admin', 'admin@purrr.love', ?, 'admin')";
    $stmt = $connection->prepare($adminSql);
    $stmt->bind_param('s', $adminPassword);
    if ($stmt->execute()) {
        echo "✅ Admin user created successfully (username: admin, password: password)\n";
    } else {
        echo "⚠️  Admin user might already exist: " . $connection->error . "\n";
    }
    
    // Create sample cat for admin
    echo "Creating sample cat...\n";
    $catSql = "INSERT IGNORE INTO cats (user_id, name, breed, color, age, personality) VALUES (1, 'Whiskers', 'Persian', 'Orange', 3, 'Playful and curious')";
    if ($connection->query($catSql)) {
        echo "✅ Sample cat created successfully\n";
    } else {
        echo "⚠️  Sample cat might already exist: " . $connection->error . "\n";
    }
    
    echo "\n🎉 Database initialization completed successfully!\n";
    echo "You can now log in with:\n";
    echo "- Username: admin\n";
    echo "- Password: password\n";
    
    $connection->close();
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
