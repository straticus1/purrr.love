<?php
// Emergency database setup - this will run the database initialization
// This script bypasses the usual setup and directly initializes the database

header('Content-Type: text/html; charset=UTF-8');
echo "<!DOCTYPE html><html><head><title>Database Emergency Setup</title></head><body>";
echo "<h1>🚑 Emergency Database Setup for Purrr.love</h1>";

// Security: Only allow with secret parameter
if (!isset($_GET['emergency']) || $_GET['emergency'] !== 'purrr123') {
    echo "<p>❌ Access denied. Provide emergency key.</p></body></html>";
    exit;
}

echo "<h2>🚀 Starting Emergency Database Initialization...</h2>";

try {
    // Connect using mysqli with container credentials
    $host = '127.0.0.1';
    $user = 'root'; 
    $pass = 'purrr123';
    $port = 3306;
    
    echo "<p>Connecting to MySQL at {$host}:{$port}...</p>";
    
    $conn = new mysqli($host, $user, $pass, '', $port);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Connected to MySQL!</p>";
    
    // Create database
    $conn->query("CREATE DATABASE IF NOT EXISTS purrrlove CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db('purrrlove');
    echo "<p>✅ Database 'purrrlove' ready!</p>";
    
    // Create users table
    $users_sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin', 'moderator') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_email (email)
    ) ENGINE=InnoDB";
    
    if ($conn->query($users_sql)) {
        echo "<p>✅ Users table created!</p>";
    } else {
        echo "<p>⚠️ Users table: " . $conn->error . "</p>";
    }
    
    // Create cats table
    $cats_sql = "CREATE TABLE IF NOT EXISTS cats (
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
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB";
    
    if ($conn->query($cats_sql)) {
        echo "<p>✅ Cats table created!</p>";
    } else {
        echo "<p>⚠️ Cats table: " . $conn->error . "</p>";
    }
    
    // Create activities table  
    $activities_sql = "CREATE TABLE IF NOT EXISTS activities (
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
        INDEX idx_cat_id (cat_id)
    ) ENGINE=InnoDB";
    
    if ($conn->query($activities_sql)) {
        echo "<p>✅ Activities table created!</p>";
    } else {
        echo "<p>⚠️ Activities table: " . $conn->error . "</p>";
    }
    
    // Create admin user
    $admin_pass = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT IGNORE INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
    $username = 'admin';
    $email = 'admin@purrr.love';
    $stmt->bind_param('sss', $username, $email, $admin_pass);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<p>✅ Admin user created!</p>";
        } else {
            echo "<p>⚠️ Admin user already exists</p>";
        }
    } else {
        echo "<p>❌ Error creating admin user: " . $conn->error . "</p>";
    }
    
    // Create sample cat
    $stmt2 = $conn->prepare("INSERT IGNORE INTO cats (user_id, name, breed, color, age, personality) VALUES (1, ?, ?, ?, 3, ?)");
    $name = 'Whiskers';
    $breed = 'Persian';
    $color = 'Orange';
    $personality = 'Playful and curious digital companion';
    $stmt2->bind_param('ssss', $name, $breed, $color, $personality);
    
    if ($stmt2->execute()) {
        if ($stmt2->affected_rows > 0) {
            echo "<p>✅ Sample cat 'Whiskers' created!</p>";
        } else {
            echo "<p>⚠️ Sample cat already exists</p>";
        }
    }
    
    // Add sample activity
    $conn->query("INSERT IGNORE INTO activities (cat_id, activity_type, description, happiness_change) VALUES (1, 'play', 'Playing with a feather toy', 10)");
    echo "<p>✅ Sample activity added!</p>";
    
    echo "<div style='background:#e7f5e7; padding:20px; margin:20px 0; border-radius:8px;'>";
    echo "<h2>🎉 SUCCESS! Database Setup Complete!</h2>";
    echo "<p><strong>Your Purrr.love application is now fully operational!</strong></p>";
    echo "<h3>Login Details:</h3>";
    echo "<ul><li><strong>Username:</strong> admin</li><li><strong>Password:</strong> password</li></ul>";
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li>✅ Visit <a href='https://app.purrr.me' target='_blank'>Homepage</a></li>";
    echo "<li>✅ Access <a href='https://app.purrr.me/admin.php' target='_blank'>Admin Panel</a></li>";
    echo "<li>✅ Check <a href='https://app.purrr.me/web/' target='_blank'>Web Interface</a></li>";
    echo "</ul></div>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p>❌ Fatal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><p><em>Emergency setup completed at " . date('Y-m-d H:i:s') . " UTC</em></p>";
echo "</body></html>";
?>
