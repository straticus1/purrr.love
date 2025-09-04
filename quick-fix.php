<?php
// Quick fix using existing mysqli connection like simple-db-setup.php
if (!isset($_GET['key']) || $_GET['key'] !== 'quickfix123') {
    die('Access denied');
}

header('Content-Type: text/plain');
echo "🔧 Quick Database Fix for Purrr.love\n";
echo "====================================\n\n";

$conn = @mysqli_connect('localhost', 'root', '', 'purrr_love');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . "\n");
}

echo "✅ Connected to purrr_love database!\n\n";

// Add missing columns to users table
echo "👥 Adding missing columns to users table...\n";

$columns_to_add = [
    "level INT DEFAULT 1",
    "coins INT DEFAULT 100", 
    "experience_points INT DEFAULT 0",
    "avatar_url VARCHAR(500)",
    "is_active BOOLEAN DEFAULT TRUE",
    "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
];

foreach ($columns_to_add as $column) {
    $column_name = explode(' ', $column)[0];
    if (mysqli_query($conn, "ALTER TABLE users ADD COLUMN $column")) {
        echo "  ✅ Added column: $column_name\n";
    } else {
        $error = mysqli_error($conn);
        if (strpos($error, 'Duplicate column name') !== false) {
            echo "  ℹ️ Column $column_name already exists\n";
        } else {
            echo "  ⚠️ Warning adding $column_name: $error\n";
        }
    }
}

// Fix admin user with correct password and role
echo "\n👑 Updating admin user...\n";
$admin_password_hash = password_hash('admin123456789!', PASSWORD_DEFAULT);
$sql = "UPDATE users SET 
    password_hash = '$admin_password_hash',
    role = 'admin',
    active = 1,
    is_active = 1,
    level = 50,
    coins = 10000,
    experience_points = 50000,
    name = 'System Administrator'
    WHERE email = 'admin@purrr.love'";

if (mysqli_query($conn, $sql)) {
    echo "✅ Admin user updated with correct credentials!\n";
} else {
    echo "❌ Error updating admin: " . mysqli_error($conn) . "\n";
}

// Create test user
echo "🧪 Creating test user...\n";
$test_password_hash = password_hash('testpass123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, email, password_hash, name, role, active, is_active, level, coins, experience_points) 
        VALUES ('testuser', 'testuser@example.com', '$test_password_hash', 'Test User', 'user', 1, 1, 5, 500, 1000)
        ON DUPLICATE KEY UPDATE 
            password_hash = VALUES(password_hash),
            active = VALUES(active),
            is_active = VALUES(is_active),
            level = VALUES(level),
            coins = VALUES(coins),
            experience_points = VALUES(experience_points)";

if (mysqli_query($conn, $sql)) {
    echo "✅ Test user created/updated!\n";
} else {
    echo "❌ Error with test user: " . mysqli_error($conn) . "\n";
}

// Verify current users
echo "\n📊 Current users in database:\n";
$result = mysqli_query($conn, "SELECT id, username, email, role, active, is_active FROM users ORDER BY id");
while ($row = mysqli_fetch_assoc($result)) {
    $active_status = ($row['active'] || $row['is_active']) ? 'Active' : 'Inactive';
    echo "  • ID: {$row['id']}, Username: {$row['username']}, Email: {$row['email']}, Role: {$row['role']}, Status: $active_status\n";
}

mysqli_close($conn);

echo "\n🎉 QUICK FIX COMPLETED!\n\n";
echo "🔐 UPDATED CREDENTIALS:\n";
echo "=======================\n";
echo "🔴 ADMIN LOGIN:\n";
echo "   Email: admin@purrr.love\n";
echo "   Password: admin123456789!\n\n";
echo "🔵 TEST USER LOGIN:\n";
echo "   Email: testuser@example.com\n";
echo "   Password: testpass123\n\n";
echo "✅ Try logging in now!\n";
?>
