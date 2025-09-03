<?php
/**
 * 🛠️ Purrr.love Database Setup
 * Simple setup script for initial configuration
 */

// Prevent direct access in production
if (file_exists('.env') && !isset($_GET['force'])) {
    die('Setup already completed. Use ?force=1 to run again.');
}

$errors = [];
$success = [];

// Database configuration
$dbConfig = [
    'host' => $_POST['db_host'] ?? 'localhost',
    'name' => $_POST['db_name'] ?? 'purrr_love',
    'user' => $_POST['db_user'] ?? 'purrr_user',
    'pass' => $_POST['db_pass'] ?? '',
    'port' => $_POST['db_port'] ?? '5432'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_connection'])) {
    try {
        $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']}";
        $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        $success[] = "Database connection successful!";
        
        // Test if tables exist
        $tables = ['users', 'cats', 'api_keys'];
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
                $success[] = "Table '$table' exists and is accessible";
            } catch (Exception $e) {
                $errors[] = "Table '$table' not found or not accessible";
            }
        }
        
    } catch (Exception $e) {
        $errors[] = "Database connection failed: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    try {
        $configContent = "<?php\n";
        $configContent .= "// Database configuration\n";
        $configContent .= "define('DB_HOST', '{$dbConfig['host']}');\n";
        $configContent .= "define('DB_NAME', '{$dbConfig['name']}');\n";
        $configContent .= "define('DB_USER', '{$dbConfig['user']}');\n";
        $configContent .= "define('DB_PASS', '{$dbConfig['pass']}');\n";
        $configContent .= "define('DB_PORT', '{$dbConfig['port']}');\n";
        $configContent .= "?>";
        
        file_put_contents('includes/db_config.php', $configContent);
        $success[] = "Database configuration saved successfully!";
        
    } catch (Exception $e) {
        $errors[] = "Failed to save configuration: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛠️ Purrr.love Setup - Database Configuration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto h-20 w-20 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-cog text-white text-3xl"></i>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Purrr.love Setup
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Configure your database connection
                </p>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Setup Errors
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">
                            Setup Progress
                        </h3>
                        <div class="mt-2 text-sm text-green-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <?php foreach ($success as $msg): ?>
                                <li><?= htmlspecialchars($msg) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="db_host" class="sr-only">Database Host</label>
                        <input id="db_host" name="db_host" type="text" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
                               placeholder="Database Host" value="<?= htmlspecialchars($dbConfig['host']) ?>">
                    </div>
                    <div>
                        <label for="db_port" class="sr-only">Database Port</label>
                        <input id="db_port" name="db_port" type="text" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
                               placeholder="Database Port" value="<?= htmlspecialchars($dbConfig['port']) ?>">
                    </div>
                    <div>
                        <label for="db_name" class="sr-only">Database Name</label>
                        <input id="db_name" name="db_name" type="text" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
                               placeholder="Database Name" value="<?= htmlspecialchars($dbConfig['name']) ?>">
                    </div>
                    <div>
                        <label for="db_user" class="sr-only">Database User</label>
                        <input id="db_user" name="db_user" type="text" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
                               placeholder="Database User" value="<?= htmlspecialchars($dbConfig['user']) ?>">
                    </div>
                    <div>
                        <label for="db_pass" class="sr-only">Database Password</label>
                        <input id="db_pass" name="db_pass" type="password" 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
                               placeholder="Database Password" value="<?= htmlspecialchars($dbConfig['pass']) ?>">
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" name="test_connection" 
                            class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-plug mr-2"></i>Test Connection
                    </button>
                    
                    <button type="submit" name="save_config" 
                            class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-save mr-2"></i>Save Config
                    </button>
                </div>
            </form>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    After setup, you can 
                    <a href="index.php" class="font-medium text-purple-600 hover:text-purple-500">
                        go to the main site
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
