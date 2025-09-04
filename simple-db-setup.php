<?php
// Ultra-simple database setup using mysqli
if (!isset($_GET['key']) || $_GET['key'] !== 'setup123') {
    die('Access denied');
}

header('Content-Type: text/plain');
echo "Starting database setup...\n";

// Use mysqli which should be available
$conn = @mysqli_connect('localhost', 'root', '');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . "\n");
}

echo "Connected to MySQL!\n";

// Create database
if (mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS purrr_love")) {
    echo "Database created successfully\n";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "\n";
}

// Select database
mysqli_select_db($conn, 'purrr_love');

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    role ENUM('user','admin','moderator') DEFAULT 'user',
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Users table created successfully\n";
} else {
    echo "Error creating users table: " . mysqli_error($conn) . "\n";
}

// Create cats table
$sql = "CREATE TABLE IF NOT EXISTS cats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    owner_id INT,
    name VARCHAR(255) NOT NULL,
    breed VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Cats table created successfully\n";
} else {
    echo "Error creating cats table: " . mysqli_error($conn) . "\n";
}

// Create statistics table
$sql = "CREATE TABLE IF NOT EXISTS site_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_name VARCHAR(100) UNIQUE NOT NULL,
    stat_value BIGINT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Statistics table created successfully\n";
} else {
    echo "Error creating statistics table: " . mysqli_error($conn) . "\n";
}

// Insert admin user with proper password
$admin_password_hash = password_hash('admin123456789!', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, email, password_hash, name, role, active) 
        VALUES ('admin', 'admin@purrr.love', '$admin_password_hash', 'System Administrator', 'admin', true)
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role)";

if (mysqli_query($conn, $sql)) {
    echo "Admin user created/updated successfully\n";
} else {
    echo "Error creating admin user: " . mysqli_error($conn) . "\n";
}

// Create test user
$test_password_hash = password_hash('testpass123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, email, password_hash, name, role, active) 
        VALUES ('testuser', 'testuser@example.com', '$test_password_hash', 'Test User', 'user', true)
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)";

if (mysqli_query($conn, $sql)) {
    echo "Test user created/updated successfully\n";
} else {
    echo "Error creating test user: " . mysqli_error($conn) . "\n";
}

// Insert statistics
$stats = [
    ['total_users', 1],
    ['total_cats', 0],
    ['total_logins', 0],
    ['active_sessions', 0]
];

foreach ($stats as $stat) {
    $sql = "INSERT IGNORE INTO site_statistics (stat_name, stat_value) VALUES ('{$stat[0]}', {$stat[1]})";
    mysqli_query($conn, $sql);
}

echo "Statistics initialized\n";

// Verify
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$row = mysqli_fetch_assoc($result);
echo "Users in database: " . $row['count'] . "\n";

$result = mysqli_query($conn, "SELECT * FROM site_statistics");
echo "Statistics:\n";
while ($row = mysqli_fetch_assoc($result)) {
    echo "  {$row['stat_name']}: {$row['stat_value']}\n";
}

mysqli_close($conn);
echo "\nDATABASE SETUP COMPLETE!\n";
echo "\n🔐 LOGIN CREDENTIALS:\n";
echo "====================\n";
echo "🔴 ADMIN LOGIN:\n";
echo "   Email: admin@purrr.love\n";
echo "   Password: admin123456789!\n\n";
echo "🔵 TEST USER LOGIN:\n";
echo "   Email: testuser@example.com\n";
echo "   Password: testpass123\n\n";
echo "✅ Both accounts are ready to use!\n";
?>
