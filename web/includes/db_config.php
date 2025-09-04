<?php
/**
 * 🗄️ Database Configuration for Web Interface
 * Simplified database connection for web pages
 */

// Database configuration - MySQL/MariaDB
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'purrr_love');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');

/**
 * Get database connection
 */
function get_web_db() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * Initialize database tables if they don't exist
 */
function init_web_database() {
    try {
        $pdo = get_web_db();
        
        // Create users table if it doesn't exist
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
                coins INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Create cats table if it doesn't exist
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
        
        // Create health_logs table if it doesn't exist
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
        
        return true;
        
    } catch (Exception $e) {
        error_log("Database initialization failed: " . $e->getMessage());
        throw new Exception('Database initialization failed: ' . $e->getMessage());
    }
}

/**
 * Simple user authentication for web interface
 */
function authenticate_web_user($email, $password) {
    try {
        $pdo = get_web_db();
        
        $stmt = $pdo->prepare("
            SELECT id, name, email, password_hash, role, active, level, experience
            FROM users 
            WHERE email = ? AND active = true
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']); // Don't return password hash
            return $user;
        }
        
        return null;
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        throw new Exception('Authentication failed: ' . $e->getMessage());
    }
}

/**
 * Get user by ID for web interface
 */
function get_web_user_by_id($userId) {
    try {
        $pdo = get_web_db();
        
        $stmt = $pdo->prepare("
            SELECT id, name, email, role, active, level, experience, created_at
            FROM users 
            WHERE id = ? AND active = true
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
        
    } catch (Exception $e) {
        error_log("Get user error: " . $e->getMessage());
        throw new Exception('Failed to get user: ' . $e->getMessage());
    }
}

/**
 * Create new user for web interface
 */
function create_web_user($name, $email, $password) {
    try {
        $pdo = get_web_db();
        
        // Initialize database tables if they don't exist
        init_web_database();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email already exists');
        }
        
        // Create user
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, role, active, level, experience, created_at)
            VALUES (?, ?, ?, 'user', true, 1, 0, NOW())
        ");
        $stmt->execute([$name, $email, $passwordHash]);
        
        return $pdo->lastInsertId();
        
    } catch (Exception $e) {
        error_log("Create user error: " . $e->getMessage());
        throw new Exception('Failed to create user: ' . $e->getMessage());
    }
}

/**
 * Get user's cats for web interface
 */
function get_web_user_cats($userId) {
    try {
        $pdo = get_web_db();
        
        $stmt = $pdo->prepare("
            SELECT id, name, breed, color, age, health, happiness, energy, hunger, cleanliness, created_at
            FROM cats 
            WHERE owner_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Get cats error: " . $e->getMessage());
        return [];
    }
}

/**
 * Test database connection
 */
function test_web_database() {
    try {
        $pdo = get_web_db();
        $pdo->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
