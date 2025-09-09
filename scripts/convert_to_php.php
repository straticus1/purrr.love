<?php

/**
 * Script to convert HTML files to PHP and update file references
 */

// Directory to process
$root = realpath(__DIR__ . '/..');

// List of files to skip (like React app files)
$skip_paths = [
    'web/react-app/',
    'vendor/',
    'node_modules/'
];

// Function to check if path should be skipped
function shouldSkipPath($path) {
    global $skip_paths;
    foreach ($skip_paths as $skip) {
        if (strpos($path, $skip) !== false) {
            return true;
        }
    }
    return false;
}

// Function to recursively find files
function findFiles($dir, &$results) {
    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = realpath($dir . '/' . $file);
        
        if (shouldSkipPath($path)) continue;
        
        if (is_dir($path)) {
            findFiles($path, $results);
        } else {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if ($ext === 'html') {
                $results['html'][] = $path;
            } elseif ($ext === 'php') {
                $results['php'][] = $path;
            }
        }
    }
}

// Collect all HTML and PHP files
$files = ['html' => [], 'php' => []];
findFiles($root, $files);

// Convert HTML files to PHP
foreach ($files['html'] as $file) {
    $content = file_get_contents($file);
    $php_file = substr($file, 0, -4) . 'php';
    
    // Wrap the content with PHP header/footer if not already a complete PHP file
    if (strpos($content, '<?php') === false) {
        $content = "<?php\nrequire_once __DIR__ . '/includes/header.php';\n?>\n" . $content . "\n<?php\nrequire_once __DIR__ . '/includes/footer.php';\n?>";
    }
    
    // Replace .html with .php in links
    $content = preg_replace('/href=(["\'])[^"\']*\.html(["\'])/i', 'href=$1$2.php$2', $content);
    
    // Create PHP file
    file_put_contents($php_file, $content);
    echo "Converted: $file -> $php_file\n";
    
    // Delete original HTML file
    unlink($file);
    echo "Deleted: $file\n";
}

// Update references in PHP files
foreach ($files['php'] as $file) {
    $content = file_get_contents($file);
    
    // Replace .html with .php in links
    $content = preg_replace('/href=(["\'])[^"\']*\.html(["\'])/i', 'href=$1$2.php$2', $content);
    
    // Save updated content
    file_put_contents($file, $content);
    echo "Updated references in: $file\n";
}

echo "\nConversion completed!\n";
