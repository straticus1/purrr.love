<?php
/**
 * 🗃️ Purrr.love Database Initialization Script
 * Runs all database schema files and sets up the initial database
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

define('SECURE_ACCESS', true);

echo "🚀 Purrr.love Database Initialization\n";
echo "=====================================\n\n";

// Schema files to execute in order
$schemaFiles = [
    'core_schema.sql',
    'security_schema.sql', 
    'api_schema.sql',
    'advanced_features_schema.sql',
    'lost_pet_finder_schema.sql',
    'night_watch_schema.sql'
];

$baseDir = __DIR__ . '/../database/';

try {
    // Test database connection
    echo "Testing database connection...\n";
    $pdo = get_db();
    echo "✅ Database connection successful!\n\n";

    // Check if tables already exist
    echo "Checking existing tables...\n";
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($existingTables)) {
        echo "⚠️  Found existing tables: " . implode(', ', $existingTables) . "\n";
        echo "Do you want to continue? This might create duplicate tables. (y/N): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) !== 'y') {
            echo "❌ Setup cancelled.\n";
            exit(1);
        }
    }
    echo "\n";

    // Execute schema files
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
        
        // Split by semicolon and execute each statement
        $statements = explode(';', $sql);
        $executedCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            
            if (empty($statement) || $statement === '--') {
                continue;
            }
            
            try {
                $pdo->exec($statement);
                $executedCount++;
            } catch (PDOException $e) {
                // Skip errors for tables/functions that already exist
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'duplicate key') === false) {
                    echo "   ⚠️  Error in statement: " . substr($statement, 0, 100) . "...\n";
                    echo "       Error: " . $e->getMessage() . "\n";
                    $errorCount++;
                }
            }
        }
        
        echo "   ✅ Executed $executedCount statements";
        if ($errorCount > 0) {
            echo " ($errorCount errors)";
        }
        echo "\n";
    }

    echo "\n";

    // Verify core tables exist
    echo "Verifying core tables...\n";
    $coreTables = ['users', 'cats', 'site_statistics'];
    
    foreach ($coreTables as $table) {
        try {
            $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
            echo "✅ Table '$table' exists and is accessible\n";
        } catch (Exception $e) {
            echo "❌ Table '$table' is not accessible: " . $e->getMessage() . "\n";
        }
    }

    echo "\n";

    // Update statistics
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
    echo "🎉 Database initialization completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Test the web interface at your domain\n";
    echo "2. Login with admin credentials if needed\n";
    echo "3. Configure any additional settings\n\n";

} catch (Exception $e) {
    echo "❌ Database initialization failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
