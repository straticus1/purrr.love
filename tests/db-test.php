<?php
/**
 * 🔍 Database Driver Test Script
 */

// Security check
if (!isset($_GET['test_token']) || $_GET['test_token'] !== 'test_db_' . date('Ymd')) {
    http_response_code(403);
    die("Access denied. Use token: test_db_" . date('Ymd'));
}

header('Content-Type: text/plain; charset=utf-8');
echo "🔍 Database Driver Test\n";
echo "======================\n\n";

echo "Available PDO drivers:\n";
$drivers = PDO::getAvailableDrivers();
foreach ($drivers as $driver) {
    echo "  • $driver\n";
}

echo "\nPHP Extensions loaded:\n";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (stripos($ext, 'sql') !== false || stripos($ext, 'pdo') !== false) {
        echo "  • $ext\n";
    }
}

// Test MySQL connection with environment variables
echo "\nTesting MySQL connection...\n";
$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = $_ENV['DB_NAME'] ?? 'nitetext';
$db_user = $_ENV['DB_USER'] ?? 'postgres';
$db_pass = $_ENV['DB_PASS'] ?? '';
$db_port = $_ENV['DB_PORT'] ?? '5432';

try {
    // Try MySQL first
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ MySQL connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ MySQL query test: " . $result['test'] . "\n";
    
} catch (Exception $e) {
    echo "❌ MySQL connection failed: " . $e->getMessage() . "\n";
}

// Test PostgreSQL connection
echo "\nTesting PostgreSQL connection...\n";
try {
    $pdo = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ PostgreSQL connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ PostgreSQL query test: " . $result['test'] . "\n";
    
} catch (Exception $e) {
    echo "❌ PostgreSQL connection failed: " . $e->getMessage() . "\n";
}

echo "\nEnvironment variables:\n";
echo "DB_HOST: " . ($db_host ?? 'Not set') . "\n";
echo "DB_NAME: " . ($db_name ?? 'Not set') . "\n";
echo "DB_USER: " . ($db_user ?? 'Not set') . "\n";
echo "DB_PORT: " . ($db_port ?? 'Not set') . "\n";

echo "\nDone!\n";
?>
