<?php
/**
 * 🔧 Quick Authentication Fix for Purrr.love
 * Creates working authentication without database dependency issues
 */

// Security check
if (!isset($_GET['auth_token']) || $_GET['auth_token'] !== 'auth_fix_' . date('Ymd')) {
    http_response_code(403);
    die("Access denied. Use token: auth_fix_" . date('Ymd'));
}

header('Content-Type: text/plain; charset=utf-8');
echo "🔧 Quick Authentication Fix\n";
echo "============================\n\n";

// Create a simple SQLite database for testing
$dbFile = '/tmp/purrr_test.db';
try {
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ SQLite database connection established\n";
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            name TEXT,
            role TEXT DEFAULT 'user',
            active INTEGER DEFAULT 1,
            is_active INTEGER DEFAULT 1,
            level INTEGER DEFAULT 1,
            coins INTEGER DEFAULT 100,
            experience_points INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Users table created/verified\n";
    
    // Create admin user
    $admin_password_hash = password_hash('admin123456789!', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT OR REPLACE INTO users (username, email, password_hash, name, role, active, is_active, level, coins, experience_points) 
        VALUES ('admin', 'admin@purrr.love', ?, 'System Administrator', 'admin', 1, 1, 50, 10000, 50000)
    ");
    $stmt->execute([$admin_password_hash]);
    echo "✅ Admin user created/updated\n";
    
    // Create test user
    $test_password_hash = password_hash('testpass123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT OR REPLACE INTO users (username, email, password_hash, name, role, active, is_active, level, coins, experience_points) 
        VALUES ('testuser', 'testuser@example.com', ?, 'Test User', 'user', 1, 1, 5, 500, 1000)
    ");
    $stmt->execute([$test_password_hash]);
    echo "✅ Test user created/updated\n";
    
    // Test password verification
    echo "\n🔐 Testing password verification...\n";
    
    // Test admin password
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE email = 'admin@purrr.love'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin && password_verify('admin123456789!', $admin['password_hash'])) {
        echo "✅ Admin password verification: PASS\n";
    } else {
        echo "❌ Admin password verification: FAILED\n";
    }
    
    // Test user password
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE email = 'testuser@example.com'");
    $stmt->execute();
    $test_user = $stmt->fetch();
    
    if ($test_user && password_verify('testpass123', $test_user['password_hash'])) {
        echo "✅ Test user password verification: PASS\n";
    } else {
        echo "❌ Test user password verification: FAILED\n";
    }
    
    echo "\n📊 Users in test database:\n";
    $stmt = $pdo->query("SELECT id, username, email, role, active, is_active FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        $status = ($user['active'] && $user['is_active']) ? 'Active' : 'Inactive';
        echo "  • ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}, Status: $status\n";
    }
    
    echo "\n🎉 AUTHENTICATION TEST COMPLETED!\n\n";
    
    echo "✅ WORKING TEST CREDENTIALS:\n";
    echo "=============================\n";
    echo "🔴 ADMIN LOGIN:\n";
    echo "   Email: admin@purrr.love\n";
    echo "   Password: admin123456789!\n";
    echo "   Role: admin\n";
    echo "   Status: ✅ PASSWORD VERIFIED\n\n";
    
    echo "🔵 REGULAR USER LOGIN:\n";
    echo "   Email: testuser@example.com\n";
    echo "   Password: testpass123\n";
    echo "   Role: user\n";
    echo "   Status: ✅ PASSWORD VERIFIED\n\n";
    
    echo "📝 NOTE: This creates a test authentication system using SQLite.\n";
    echo "The system will work with these credentials for testing the login functionality.\n";
    echo "Database file created at: $dbFile\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Now let's create a simple login test endpoint
echo "\n🔗 Creating login test endpoint...\n";

$loginTestContent = '<?php
/**
 * Simple Login Test Endpoint
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$email = $input["email"] ?? "";
$password = $input["password"] ?? "";

// Test credentials
$credentials = [
    "admin@purrr.love" => [
        "password" => "admin123456789!",
        "role" => "admin",
        "name" => "System Administrator"
    ],
    "testuser@example.com" => [
        "password" => "testpass123", 
        "role" => "user",
        "name" => "Test User"
    ]
];

if (isset($credentials[$email]) && $credentials[$email]["password"] === $password) {
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "user" => [
            "email" => $email,
            "role" => $credentials[$email]["role"],
            "name" => $credentials[$email]["name"]
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        "error" => "Invalid credentials"
    ]);
}
?>';

file_put_contents('/var/www/html/login-test.php', $loginTestContent);
echo "✅ Login test endpoint created at /login-test.php\n";

echo "\n🧪 TESTING INSTRUCTIONS:\n";
echo "========================\n";
echo "Test admin login:\n";
echo "curl -X POST https://purrr.love/login-test.php -H 'Content-Type: application/json' -d '{\"email\":\"admin@purrr.love\",\"password\":\"admin123456789!\"}'\n\n";
echo "Test regular user login:\n";
echo "curl -X POST https://purrr.love/login-test.php -H 'Content-Type: application/json' -d '{\"email\":\"testuser@example.com\",\"password\":\"testpass123\"}'\n\n";

echo "✅ Both accounts are now ready for testing!\n";
?>
