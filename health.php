<?php
// Health check endpoint for load balancer
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Simple health check
$status = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'service' => 'purrr.love',
    'version' => '1.0.0'
];

// You can add more health checks here later
// - Database connectivity
// - File system checks
// - External service dependencies

http_response_code(200);
echo json_encode($status, JSON_PRETTY_PRINT);
?>
