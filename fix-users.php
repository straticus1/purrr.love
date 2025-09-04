<?php
// Simple script to fix user accounts
if (!isset($_GET['fix']) || $_GET['fix'] !== 'users_now') {
    die('Access denied. Use ?fix=users_now');
}

header('Content-Type: text/plain');
echo "Fixing user accounts...\n";

$conn = @mysqli_connect('localhost', 'root', '', 'purrr_love');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . "\n");
}

echo "Connected to purrr_love database!\n";

// Update admin password to the correct one
echo "Updating admin password...\n";
$admin_password_hash = password_hash('admin123456789!', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password_hash = '$admin_password_hash' WHERE email = 'admin@purrr.love'";

if (mysqli_query($conn, $sql)) {
    echo "Admin password updated successfully!\n";
} else {
    echo "Error updating admin password: " . mysqli_error($conn) . "\n";
}

// Create test user with correct table structure
echo "Creating test user...\n";
$test_password_hash = password_hash('testpass123', PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (username, email, password_hash, name, role, active) 
        VALUES ('testuser', 'testuser@example.com', '$test_password_hash', 'Test User', 'user', true)";

if (mysqli_query($conn, $sql)) {
    echo "Test user created successfully!\n";
} else {
    echo "Error creating test user: " . mysqli_error($conn) . "\n";
}

// Create sample cat for test user
echo "Creating sample cat for test user...\n";
$sql = "SELECT id FROM users WHERE email = 'testuser@example.com'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($user) {
    $user_id = $user['id'];
    $sql = "INSERT IGNORE INTO cats (user_id, owner_id, name, breed) 
            VALUES ($user_id, $user_id, 'Mittens', 'Tabby')";
    
    if (mysqli_query($conn, $sql)) {
        echo "Sample cat created for test user!\n";
    } else {
        echo "Error creating sample cat: " . mysqli_error($conn) . "\n";
    }
}

// Verify users
echo "\nVerifying users in database:\n";
$result = mysqli_query($conn, "SELECT id, username, email, role FROM users");
while ($row = mysqli_fetch_assoc($result)) {
    echo "  ID: {$row['id']}, Username: {$row['username']}, Email: {$row['email']}, Role: {$row['role']}\n";
}

echo "\n✅ USER SETUP COMPLETE!\n\n";
echo "🔴 ADMIN CREDENTIALS:\n";
echo "   URL: https://purrr.love/web/admin.php\n";
echo "   Email: admin@purrr.love\n";
echo "   Password: admin123456789!\n\n";

echo "🔵 TEST USER CREDENTIALS:\n";  
echo "   URL: https://purrr.love\n";
echo "   Email: testuser@example.com\n";
echo "   Password: testpass123\n\n";

echo "🎯 You can now test both login accounts!\n";

mysqli_close($conn);
?>
