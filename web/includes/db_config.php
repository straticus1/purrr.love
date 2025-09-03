<?php
/**
 * 🗄️ Database Configuration for Web Interface
 * Simplified database connection for web pages
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'purrr_love');
define('DB_USER', 'purrr_user');
define('DB_PASS', '');
define('DB_PORT', 5432);

/**
 * Get database connection
 */
function get_web_db() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception('Database connection failed', 500);
        }
    }
    
    return $pdo;
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
            WHERE email = ? AND active = 1
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
        return null;
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
            WHERE id = ? AND active = 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
        
    } catch (Exception $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}

/**
 * Create new user for web interface
 */
function create_web_user($name, $email, $password) {
    try {
        $pdo = get_web_db();
        
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
            VALUES (?, ?, ?, 'user', 1, 1, 0, NOW())
        ");
        $stmt->execute([$name, $email, $passwordHash]);
        
        return $pdo->lastInsertId();
        
    } catch (Exception $e) {
        error_log("Create user error: " . $e->getMessage());
        throw $e;
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
?>
