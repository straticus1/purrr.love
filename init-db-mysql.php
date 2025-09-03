<?php
/**
 * 🗃️ Web-accessible Database Initialization (MySQL)
 * Initialize database schema via HTTP request using MySQL
 */

// Security check - only allow from specific IPs or with token
$allowed_ips = ['127.0.0.1', '::1']; // Add your IP if needed
$secret_token = 'init_db_' . md5('purrr_love_secret_2024');

$client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
$provided_token = $_GET['token'] ?? '';

// Allow access if coming from allowed IP or with correct token
$access_granted = in_array($client_ip, $allowed_ips) || $provided_token === $secret_token;

if (!$access_granted && !isset($_GET['force'])) {
    http_response_code(403);
    die('Access denied. Use ?token=' . $secret_token . ' or ?force=1 to proceed.');
}

// Define secure access for includes
define('SECURE_ACCESS', true);

// Set content type
header('Content-Type: text/plain; charset=utf-8');

echo "🚀 Purrr.love Database Initialization (MySQL)\n";
echo "============================================\n\n";

// Include required files
try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/includes/functions.php';
} catch (Exception $e) {
    echo "❌ Failed to load configuration: " . $e->getMessage() . "\n";
    exit(1);
}

// Schema files to execute in order (MySQL versions)
$schemaFiles = [
    'core_schema_mysql.sql'
];

$baseDir = __DIR__ . '/database/';

try {
    // Test database connection
    echo "Testing database connection...\n";
    $pdo = get_db();
    echo "✅ Database connection successful!\n\n";

    // Check if tables already exist (MySQL version)
    echo "Checking existing tables...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($existingTables)) {
        echo "⚠️  Found existing tables: " . implode(', ', $existingTables) . "\n";
        if (!isset($_GET['force'])) {
            echo "Use ?force=1 to continue with existing tables.\n";
            exit(0);
        }
    }
    echo "\n";

    // Execute schema files
    $totalExecuted = 0;
    $totalErrors = 0;
    
    foreach ($schemaFiles as $schemaFile) {
        $filePath = $baseDir . $schemaFile;
        
        if (!file_exists($filePath)) {
            echo "⚠️  Schema file not found: $schemaFile (skipping)\n";
            continue;
        }
        
        echo "📁 Executing schema: $schemaFile\n";
        
        $sql = file_get_contents($filePath);
        
        if (empty($sql)) {
            echo "⚠️  Schema file is empty: $schemaFile (skipping)\n";
            continue;
        }
        
        // For MySQL, we need to handle DELIMITER statements specially
        $statements = [];
        $currentStatement = '';
        $inDelimiter = false;
        $delimiter = ';';
        
        $lines = explode("\n", $sql);
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and comments
            if (empty($line) || strpos($line, '--') === 0) {
                continue;
            }
            
            // Handle DELIMITER statements
            if (strpos($line, 'DELIMITER') === 0) {
                if (!$inDelimiter) {
                    $delimiter = trim(substr($line, 9));
                    $inDelimiter = true;
                } else {
                    $delimiter = ';';
                    $inDelimiter = false;
                }
                continue;
            }
            
            $currentStatement .= $line . "\n";
            
            // Check if statement ends with current delimiter
            if (substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
                $statements[] = substr($currentStatement, 0, -strlen($delimiter));
                $currentStatement = '';
            }
        }
        
        // Add remaining statement if any
        if (!empty(trim($currentStatement))) {
            $statements[] = $currentStatement;
        }
        
        $executedCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            
            if (empty($statement)) {
                continue;
            }
            
            try {
                $pdo->exec($statement);
                $executedCount++;
            } catch (PDOException $e) {
                // Skip certain expected errors
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate') === false &&
                    strpos($e->getMessage(), 'Table') === false) {
                    echo "   ⚠️  Error: " . $e->getMessage() . "\n";
                    echo "       Statement: " . substr($statement, 0, 100) . "...\n";
                    $errorCount++;
                }
            }
        }
        
        echo "   ✅ Executed $executedCount statements";
        if ($errorCount > 0) {
            echo " ($errorCount errors)";
        }
        echo "\n";
        
        $totalExecuted += $executedCount;
        $totalErrors += $errorCount;
    }

    echo "\n";

    // Verify core tables exist
    echo "Verifying core tables...\n";
    $coreTables = ['users', 'cats', 'site_statistics'];
    $verified = 0;
    
    foreach ($coreTables as $table) {
        try {
            $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
            echo "✅ Table '$table' exists and is accessible\n";
            $verified++;
        } catch (Exception $e) {
            echo "❌ Table '$table' is not accessible: " . $e->getMessage() . "\n";
        }
    }

    echo "\n";

    // Update statistics if possible
    echo "Updating statistics...\n";
    try {
        $stmt = $pdo->prepare("UPDATE site_statistics SET stat_value = (SELECT COUNT(*) FROM users), last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_users'");
        $stmt->execute();
        
        $stmt = $pdo->prepare("UPDATE site_statistics SET stat_value = (SELECT COUNT(*) FROM cats), last_updated = CURRENT_TIMESTAMP WHERE stat_name = 'total_cats'");  
        $stmt->execute();
        
        echo "✅ Statistics updated\n";
    } catch (Exception $e) {
        echo "⚠️  Failed to update statistics: " . $e->getMessage() . "\n";
    }

    echo "\n";
    echo "🎉 Database initialization completed!\n";
    echo "📊 Summary: $totalExecuted statements executed, $totalErrors errors\n";
    echo "📋 Core tables verified: $verified/" . count($coreTables) . "\n";
    echo "\n";
    echo "🔗 Test the application:\n";
    echo "- Admin panel: https://app.purrr.me/web/admin.php\n";  
    echo "- Setup page: https://app.purrr.me/web/setup.php\n";
    echo "- API health: https://api.purrr.love/health\n";
    echo "\n";
    
    if ($verified === count($coreTables)) {
        echo "✅ SUCCESS: Database is fully operational!\n";
    } else {
        echo "⚠️  WARNING: Some core tables may need attention.\n";
    }

} catch (Exception $e) {
    echo "❌ Database initialization failed: " . $e->getMessage() . "\n";
    
    // Show some debugging info
    echo "\nDebugging Information:\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "Current Directory: " . __DIR__ . "\n";
    echo "Database Config: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . ":" . (defined('DB_PORT') ? DB_PORT : 'Not defined') . "\n";
    exit(1);
}
?>
