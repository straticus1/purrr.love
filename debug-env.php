<?php
header('Content-Type: application/json');
echo json_encode([
    'env_vars_ENV' => [
        'DB_HOST' => $_ENV['DB_HOST'] ?? 'NOT SET',
        'DB_PORT' => $_ENV['DB_PORT'] ?? 'NOT SET',
        'DB_USER' => $_ENV['DB_USER'] ?? 'NOT SET',
        'DB_PASS' => $_ENV['DB_PASS'] ?? 'NOT SET',
        'DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? 'NOT SET',
        'DB_NAME' => $_ENV['DB_NAME'] ?? 'NOT SET'
    ],
    'env_vars_SERVER' => [
        'DB_HOST' => $_SERVER['DB_HOST'] ?? 'NOT SET',
        'DB_PORT' => $_SERVER['DB_PORT'] ?? 'NOT SET',
        'DB_USER' => $_SERVER['DB_USER'] ?? 'NOT SET',
        'DB_PASS' => $_SERVER['DB_PASS'] ?? 'NOT SET',
        'DB_PASSWORD' => $_SERVER['DB_PASSWORD'] ?? 'NOT SET',
        'DB_NAME' => $_SERVER['DB_NAME'] ?? 'NOT SET'
    ],
    'env_vars_getenv' => [
        'DB_HOST' => getenv('DB_HOST') ?: 'NOT SET',
        'DB_PORT' => getenv('DB_PORT') ?: 'NOT SET',
        'DB_USER' => getenv('DB_USER') ?: 'NOT SET',
        'DB_PASS' => getenv('DB_PASS') ?: 'NOT SET',
        'DB_PASSWORD' => getenv('DB_PASSWORD') ?: 'NOT SET',
        'DB_NAME' => getenv('DB_NAME') ?: 'NOT SET'
    ]
], JSON_PRETTY_PRINT);
?>
