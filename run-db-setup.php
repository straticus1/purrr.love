<?php
// Minimal database setup script for emergency use
echo "Starting Purrr.love Database Setup...\n";

try {
    // Wait for MySQL to be ready
    sleep(5);
    
    $conn = new mysqli('127.0.0.1', 'root', 'purrr123', '', 3306);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected to MySQL successfully!\n";
    
    // Create database and tables
    $queries = [
        "CREATE DATABASE IF NOT EXISTS purrrlove CHARACTER SET utf8mb4",
        "USE purrrlove",
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin', 'moderator') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username)
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS cats (
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
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cat_id INT NOT NULL,
            activity_type ENUM('feed', 'play', 'groom', 'sleep', 'adventure') NOT NULL,
            description TEXT,
            happiness_change INT DEFAULT 0,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE
        ) ENGINE=InnoDB"
    ];
    
    foreach ($queries as $query) {
        if (!$conn->query($query)) {
            echo "Warning: " . $conn->error . "\n";
        }
    }
    
    // Create admin user (password is 'password')
    $password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';  // Pre-hashed 'password'
    $stmt = $conn->prepare("INSERT IGNORE INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $username = 'admin';
    $email = 'admin@purrr.love';
    $role = 'admin';
    $stmt->bind_param('ssss', $username, $email, $password_hash, $role);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "Admin user created successfully!\n";
    } else {
        echo "Admin user already exists or creation failed\n";
    }
    
    // Create sample cat
    $stmt2 = $conn->prepare("INSERT IGNORE INTO cats (user_id, name, breed, color, age, personality) VALUES (?, ?, ?, ?, ?, ?)");
    $user_id = 1;
    $name = 'Whiskers';
    $breed = 'Persian';
    $color = 'Orange';
    $age = 3;
    $personality = 'Playful digital companion';
    $stmt2->bind_param('isssiss', $user_id, $name, $breed, $color, $age, $personality);
    
    if ($stmt2->execute() && $stmt2->affected_rows > 0) {
        echo "Sample cat created!\n";
    }
    
    // Create sample activity
    $stmt3 = $conn->prepare("INSERT IGNORE INTO activities (cat_id, activity_type, description, happiness_change) VALUES (?, ?, ?, ?)");
    $cat_id = 1;
    $activity_type = 'play';
    $description = 'Playing with a feather toy';
    $happiness_change = 10;
    $stmt3->bind_param('issi', $cat_id, $activity_type, $description, $happiness_change);
    $stmt3->execute();
    
    echo "DATABASE SETUP COMPLETE!\n";
    echo "Admin login: admin / password\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Setup finished successfully!\n";
exit(0);
?>
