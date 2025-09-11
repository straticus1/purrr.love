<?php
// Web-based database initialization for Purrr.love
// This script uses the existing MySQL connection from the LAMP container

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🚀 Purrr.love Database Initialization</h1>\n";

// Security check - only allow access with a secret key
if (!isset($_GET['init']) || $_GET['init'] !== 'setup123') {
    echo "<p>❌ Access denied. Please provide the correct initialization key.</p>";
    exit;
}

echo "<h2>Starting Database Setup...</h2>\n";

try {
    // Connect to MySQL using mysqli (should be available in the LAMP container)
    $connection = new mysqli('127.0.0.1', 'root', 'purrr123', '', 3306);
    
    if ($connection->connect_error) {
        echo "<p>❌ Database connection failed: " . $connection->connect_error . "</p>";
        exit;
    }
    
    echo "<p>✅ Connected to MySQL successfully!</p>\n";
    
    // Create database
    echo "<h3>Creating Database...</h3>\n";
    $sql = "CREATE DATABASE IF NOT EXISTS `purrrlove` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($connection->query($sql)) {
        echo "<p>✅ Database 'purrrlove' created/verified!</p>\n";
    } else {
        echo "<p>❌ Error creating database: " . $connection->error . "</p>";
        exit;
    }
    
    // Select database
    $connection->select_db('purrrlove');
    echo "<p>✅ Selected database 'purrrlove'</p>\n";
    
    // Create tables
    echo "<h3>Creating Tables...</h3>\n";
    
    // Users table
    $usersSql = "
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($connection->query($usersSql)) {
        echo "<p>✅ Users table created!</p>\n";
    } else {
        echo "<p>❌ Error creating users table: " . $connection->error . "</p>";
    }
    
    // Cats table
    $catsSql = "
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($connection->query($catsSql)) {
        echo "<p>✅ Cats table created!</p>\n";
    } else {
        echo "<p>❌ Error creating cats table: " . $connection->error . "</p>";
    }
    
    // Activities table
    $activitiesSql = "
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($connection->query($activitiesSql)) {
        echo "<p>✅ Activities table created!</p>\n";
    } else {
        echo "<p>❌ Error creating activities table: " . $connection->error . "</p>";
    }
    
    echo "<h3>Creating Default Data...</h3>\n";
    
    // Create admin user
    $adminPassword = password_hash('password', PASSWORD_DEFAULT);
    $adminSql = "INSERT IGNORE INTO users (username, email, password_hash, role) VALUES ('admin', 'admin@purrr.love', ?, 'admin')";
    $stmt = $connection->prepare($adminSql);
    $stmt->bind_param('s', $adminPassword);
    
    if ($stmt->execute()) {
        echo "<p>✅ Admin user created! (username: admin, password: password)</p>\n";
    } else {
        echo "<p>⚠️ Admin user might already exist: " . $connection->error . "</p>\n";
    }
    
    // Create sample cat
    $catSql = "INSERT IGNORE INTO cats (user_id, name, breed, color, age, personality) VALUES (1, 'Whiskers', 'Persian', 'Orange', 3, 'Playful and curious digital cat')";
    if ($connection->query($catSql)) {
        echo "<p>✅ Sample cat 'Whiskers' created!</p>\n";
    } else {
        echo "<p>⚠️ Sample cat might already exist: " . $connection->error . "</p>\n";
    }
    
    // Create some sample activities
    $activitySql = "INSERT IGNORE INTO activities (cat_id, activity_type, description, happiness_change, energy_change) VALUES (1, 'play', 'Playing with a feather toy', 10, -5)";
    if ($connection->query($activitySql)) {
        echo "<p>✅ Sample activity created!</p>\n";
    } else {
        echo "<p>⚠️ Sample activity might already exist: " . $connection->error . "</p>\n";
    }
    
    echo "<h2>🎉 Database Setup Complete!</h2>\n";
    echo "<div style='background:#e7f5e7; padding:15px; border-radius:5px; margin:20px 0;'>\n";
    echo "<h3>✅ Setup Successful!</h3>\n";
    echo "<p><strong>Your Purrr.love application is now fully configured!</strong></p>\n";
    echo "<p>Login credentials:</p>\n";
    echo "<ul>\n";
    echo "<li><strong>Username:</strong> admin</li>\n";
    echo "<li><strong>Password:</strong> password</li>\n";
    echo "</ul>\n";
    echo "<p>You can now:</p>\n";
    echo "<ul>\n";
    echo "<li>Visit the homepage at <a href='https://app.purrr.me'>app.purrr.me</a></li>\n";
    echo "<li>Access the admin panel at <a href='https://app.purrr.me/admin.php'>app.purrr.me/admin.php</a></li>\n";
    echo "<li>Start managing your virtual cats!</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
    $connection->close();
    
} catch (Exception $e) {
    echo "<p>❌ Fatal error: " . $e->getMessage() . "</p>";
}

echo "<hr><p><em>Database initialization completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>
