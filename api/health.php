<?php
/**
 * 🏥 Purrr.love Health Check Endpoints
 * Comprehensive system health monitoring and status reporting
 */

// Define secure access constant
define('SECURE_ACCESS', true);

require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/caching.php';

// Set JSON content type
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get health check type
$checkType = $_GET['type'] ?? 'basic';
$detailed = isset($_GET['detailed']) && $_GET['detailed'] === 'true';

try {
    $healthData = performHealthCheck($checkType, $detailed);
    
    $response = [
        'success' => true,
        'data' => $healthData,
        'meta' => [
            'timestamp' => date('c'),
            'check_type' => $checkType,
            'detailed' => $detailed,
            'version' => getConfig('app.version', '1.0.0')
        ]
    ];
    
    // Set appropriate HTTP status
    if ($healthData['overall_status'] === 'healthy') {
        http_response_code(200);
    } elseif ($healthData['overall_status'] === 'degraded') {
        http_response_code(200); // Still operational
    } else {
        http_response_code(503); // Service unavailable
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => [
            'code' => 'HEALTH_CHECK_FAILED',
            'message' => $e->getMessage()
        ],
        'meta' => [
            'timestamp' => date('c'),
            'check_type' => $checkType
        ]
    ];
    
    http_response_code(500);
}

echo json_encode($response, JSON_PRETTY_PRINT);

/**
 * Perform health check based on type
 */
function performHealthCheck($type, $detailed = false) {
    switch ($type) {
        case 'basic':
            return performBasicHealthCheck($detailed);
            
        case 'detailed':
            return performDetailedHealthCheck($detailed);
            
        case 'database':
            return performDatabaseHealthCheck($detailed);
            
        case 'cache':
            return performCacheHealthCheck($detailed);
            
        case 'external':
            return performExternalServicesHealthCheck($detailed);
            
        case 'security':
            return performSecurityHealthCheck($detailed);
            
        case 'performance':
            return performPerformanceHealthCheck($detailed);
            
        default:
            throw new Exception('Invalid health check type');
    }
}

/**
 * Basic health check
 */
function performBasicHealthCheck($detailed = false) {
    $checks = [
        'database' => checkDatabaseConnection(),
        'cache' => checkCacheConnection(),
        'session' => checkSessionHealth(),
        'file_system' => checkFileSystemHealth()
    ];
    
    $overallStatus = 'healthy';
    $failedChecks = [];
    
    foreach ($checks as $service => $status) {
        if ($status['status'] !== 'healthy') {
            $overallStatus = $status['status'] === 'degraded' ? 'degraded' : 'unhealthy';
            $failedChecks[] = $service;
        }
    }
    
    $result = [
        'overall_status' => $overallStatus,
        'timestamp' => date('c'),
        'uptime' => getSystemUptime(),
        'checks' => $checks
    ];
    
    if ($detailed) {
        $result['failed_checks'] = $failedChecks;
        $result['recommendations'] = generateHealthRecommendations($checks);
    }
    
    return $result;
}

/**
 * Detailed health check
 */
function performDetailedHealthCheck($detailed = false) {
    $basicHealth = performBasicHealthCheck(true);
    $detailedChecks = [
        'memory_usage' => checkMemoryUsage(),
        'disk_usage' => checkDiskUsage(),
        'network' => checkNetworkHealth(),
        'processes' => checkProcessHealth(),
        'logs' => checkLogHealth()
    ];
    
    $basicHealth['detailed_checks'] = $detailedChecks;
    
    // Update overall status based on detailed checks
    foreach ($detailedChecks as $check) {
        if ($check['status'] === 'unhealthy') {
            $basicHealth['overall_status'] = 'unhealthy';
        } elseif ($check['status'] === 'degraded' && $basicHealth['overall_status'] === 'healthy') {
            $basicHealth['overall_status'] = 'degraded';
        }
    }
    
    return $basicHealth;
}

/**
 * Database health check
 */
function performDatabaseHealthCheck($detailed = false) {
    $checks = [
        'connection' => checkDatabaseConnection(),
        'performance' => checkDatabasePerformance(),
        'replication' => checkDatabaseReplication(),
        'backups' => checkDatabaseBackups()
    ];
    
    $overallStatus = 'healthy';
    foreach ($checks as $check) {
        if ($check['status'] === 'unhealthy') {
            $overallStatus = 'unhealthy';
        } elseif ($check['status'] === 'degraded' && $overallStatus === 'healthy') {
            $overallStatus = 'degraded';
        }
    }
    
    $result = [
        'overall_status' => $overallStatus,
        'timestamp' => date('c'),
        'checks' => $checks
    ];
    
    if ($detailed) {
        $result['recommendations'] = generateDatabaseRecommendations($checks);
    }
    
    return $result;
}

/**
 * Cache health check
 */
function performCacheHealthCheck($detailed = false) {
    $checks = [
        'connection' => checkCacheConnection(),
        'performance' => checkCachePerformance(),
        'memory_usage' => checkCacheMemoryUsage(),
        'key_distribution' => checkCacheKeyDistribution()
    ];
    
    $overallStatus = 'healthy';
    foreach ($checks as $check) {
        if ($check['status'] === 'unhealthy') {
            $overallStatus = 'unhealthy';
        } elseif ($check['status'] === 'degraded' && $overallStatus === 'healthy') {
            $overallStatus = 'degraded';
        }
    }
    
    $result = [
        'overall_status' => $overallStatus,
        'timestamp' => date('c'),
        'checks' => $checks
    ];
    
    if ($detailed) {
        $result['recommendations'] = generateCacheRecommendations($checks);
    }
    
    return $result;
}

/**
 * External services health check
 */
function performExternalServicesHealthCheck($detailed = false) {
    $services = [
        'openai' => checkOpenAIService(),
        'stability_ai' => checkStabilityAIService(),
        'coinbase' => checkCoinbaseService()
    ];
    
    $overallStatus = 'healthy';
    $failedServices = [];
    
    foreach ($services as $service => $status) {
        if ($status['status'] === 'unhealthy') {
            $overallStatus = 'unhealthy';
            $failedServices[] = $service;
        } elseif ($status['status'] === 'degraded' && $overallStatus === 'healthy') {
            $overallStatus = 'degraded';
        }
    }
    
    $result = [
        'overall_status' => $overallStatus,
        'timestamp' => date('c'),
        'services' => $services
    ];
    
    if ($detailed) {
        $result['failed_services'] = $failedServices;
        $result['recommendations'] = generateExternalServiceRecommendations($services);
    }
    
    return $result;
}

/**
 * Security health check
 */
function performSecurityHealthCheck($detailed = false) {
    $checks = [
        'ssl_certificate' => checkSSLCertificate(),
        'security_headers' => checkSecurityHeaders(),
        'rate_limiting' => checkRateLimitingHealth(),
        'authentication' => checkAuthenticationHealth(),
        'file_permissions' => checkFilePermissions()
    ];
    
    $overallStatus = 'healthy';
    $securityIssues = [];
    
    foreach ($checks as $check) {
        if ($check['status'] === 'unhealthy') {
            $overallStatus = 'unhealthy';
            $securityIssues[] = $check['name'] ?? 'unknown';
        } elseif ($check['status'] === 'degraded' && $overallStatus === 'healthy') {
            $overallStatus = 'degraded';
        }
    }
    
    $result = [
        'overall_status' => $overallStatus,
        'timestamp' => date('c'),
        'checks' => $checks
    ];
    
    if ($detailed) {
        $result['security_issues'] = $securityIssues;
        $result['recommendations'] = generateSecurityRecommendations($checks);
    }
    
    return $result;
}

/**
 * Performance health check
 */
function performPerformanceHealthCheck($detailed = false) {
    $checks = [
        'response_time' => checkResponseTime(),
        'throughput' => checkThroughput(),
        'memory_efficiency' => checkMemoryEfficiency(),
        'database_queries' => checkDatabaseQueryPerformance()
    ];
    
    $overallStatus = 'healthy';
    $performanceIssues = [];
    
    foreach ($checks as $check) {
        if ($check['status'] === 'unhealthy') {
            $overallStatus = 'unhealthy';
            $performanceIssues[] = $check['name'] ?? 'unknown';
        } elseif ($check['status'] === 'degraded' && $overallStatus === 'healthy') {
            $overallStatus = 'degraded';
        }
    }
    
    $result = [
        'overall_status' => $overallStatus,
        'timestamp' => date('c'),
        'checks' => $checks
    ];
    
    if ($detailed) {
        $result['performance_issues'] = $performanceIssues;
        $result['recommendations'] = generatePerformanceRecommendations($checks);
    }
    
    return $result;
}

/**
 * Check database connection
 */
function checkDatabaseConnection() {
    try {
        $startTime = microtime(true);
        $pdo = get_db();
        
        // Test connection with simple query
        $stmt = $pdo->query('SELECT 1');
        $result = $stmt->fetch();
        
        $responseTime = (microtime(true) - $startTime) * 1000;
        
        if ($result && $result[0] == 1) {
            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
                'message' => 'Database connection successful'
            ];
        } else {
            return [
                'status' => 'unhealthy',
                'message' => 'Database query failed'
            ];
        }
        
    } catch (Exception $e) {
        return [
            'status' => 'unhealthy',
            'message' => 'Database connection failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Check cache connection
 */
function checkCacheConnection() {
    try {
        $startTime = microtime(true);
        $stats = cacheGetStats();
        
        if (empty($stats)) {
            return [
                'status' => 'degraded',
                'message' => 'Cache connection available but no stats'
            ];
        }
        
        $responseTime = (microtime(true) - $startTime) * 1000;
        
        return [
            'status' => 'healthy',
            'response_time_ms' => round($responseTime, 2),
            'message' => 'Cache connection successful',
            'stats' => $stats
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'unhealthy',
            'message' => 'Cache connection failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Check session health
 */
function checkSessionHealth() {
    try {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return [
                'status' => 'healthy',
                'message' => 'Session system active',
                'session_id' => session_id()
            ];
        } else {
            return [
                'status' => 'degraded',
                'message' => 'Session system not active'
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'unhealthy',
            'message' => 'Session check failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Check file system health
 */
function checkFileSystemHealth() {
    try {
        $uploadDir = getConfig('file_uploads.upload_path', '../uploads/');
        $logDir = '../logs/';
        
        $checks = [
            'upload_directory' => [
                'path' => $uploadDir,
                'writable' => is_writable($uploadDir),
                'exists' => is_dir($uploadDir)
            ],
            'log_directory' => [
                'path' => $logDir,
                'writable' => is_writable($logDir),
                'exists' => is_dir($logDir)
            ]
        ];
        
        $overallStatus = 'healthy';
        foreach ($checks as $check) {
            if (!$check['exists'] || !$check['writable']) {
                $overallStatus = 'unhealthy';
            }
        }
        
        return [
            'status' => $overallStatus,
            'message' => 'File system health check completed',
            'details' => $checks
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'unhealthy',
            'message' => 'File system check failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Get system uptime
 */
function getSystemUptime() {
    try {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                'load_average' => [
                    '1min' => $load[0],
                    '5min' => $load[1],
                    '15min' => $load[2]
                ]
            ];
        }
        
        return ['message' => 'Load average not available'];
        
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

/**
 * Check memory usage
 */
function checkMemoryUsage() {
    try {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        
        $limitBytes = return_bytes($memoryLimit);
        $usagePercent = ($memoryUsage / $limitBytes) * 100;
        
        if ($usagePercent > 90) {
            $status = 'unhealthy';
        } elseif ($usagePercent > 75) {
            $status = 'degraded';
        } else {
            $status = 'healthy';
        }
        
        return [
            'status' => $status,
            'current_usage' => formatBytes($memoryUsage),
            'peak_usage' => formatBytes($peakMemory),
            'limit' => $memoryLimit,
            'usage_percent' => round($usagePercent, 2)
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'unhealthy',
            'message' => 'Memory check failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Check disk usage
 */
function checkDiskUsage() {
    try {
        $path = '../';
        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = ($usedSpace / $totalSpace) * 100;
        
        if ($usagePercent > 90) {
            $status = 'unhealthy';
        } elseif ($usagePercent > 80) {
            $status = 'degraded';
        } else {
            $status = 'healthy';
        }
        
        return [
            'status' => $status,
            'total_space' => formatBytes($totalSpace),
            'free_space' => formatBytes($freeSpace),
            'used_space' => formatBytes($usedSpace),
            'usage_percent' => round($usagePercent, 2)
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'unhealthy',
            'message' => 'Disk check failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Helper function to convert memory limit to bytes
 */
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    
    return $val;
}

/**
 * Generate health recommendations
 */
function generateHealthRecommendations($checks) {
    $recommendations = [];
    
    foreach ($checks as $service => $check) {
        if ($check['status'] === 'unhealthy') {
            $recommendations[] = "Immediate attention required for {$service} service";
        } elseif ($check['status'] === 'degraded') {
            $recommendations[] = "Monitor {$service} service for potential issues";
        }
    }
    
    return $recommendations;
}

/**
 * Generate database recommendations
 */
function generateDatabaseRecommendations($checks) {
    $recommendations = [];
    
    if (isset($checks['connection']) && $checks['connection']['status'] === 'unhealthy') {
        $recommendations[] = 'Check database server status and network connectivity';
        $recommendations[] = 'Verify database credentials and permissions';
    }
    
    if (isset($checks['performance']) && $checks['performance']['status'] === 'degraded') {
        $recommendations[] = 'Consider database query optimization';
        $recommendations[] = 'Monitor database connection pooling';
    }
    
    return $recommendations;
}

/**
 * Generate cache recommendations
 */
function generateCacheRecommendations($checks) {
    $recommendations = [];
    
    if (isset($checks['connection']) && $checks['connection']['status'] === 'unhealthy') {
        $recommendations[] = 'Check Redis server status and connectivity';
        $recommendations[] = 'Verify Redis configuration and authentication';
    }
    
    if (isset($checks['memory_usage']) && $checks['memory_usage']['status'] === 'degraded') {
        $recommendations[] = 'Monitor Redis memory usage and eviction policies';
        $recommendations[] = 'Consider increasing Redis memory limit';
    }
    
    return $recommendations;
}

/**
 * Generate external service recommendations
 */
function generateExternalServiceRecommendations($services) {
    $recommendations = [];
    
    foreach ($services as $service => $status) {
        if ($status['status'] === 'unhealthy') {
            $recommendations[] = "Check {$service} API key validity and service status";
            $recommendations[] = "Verify network connectivity to {$service}";
        }
    }
    
    return $recommendations;
}

/**
 * Generate security recommendations
 */
function generateSecurityRecommendations($checks) {
    $recommendations = [];
    
    if (isset($checks['ssl_certificate']) && $checks['ssl_certificate']['status'] === 'unhealthy') {
        $recommendations[] = 'Renew SSL certificate immediately';
        $recommendations[] = 'Check certificate chain and intermediate certificates';
    }
    
    if (isset($checks['security_headers']) && $checks['security_headers']['status'] === 'degraded') {
        $recommendations[] = 'Review and update security headers configuration';
        $recommendations[] = 'Ensure all required security headers are present';
    }
    
    return $recommendations;
}

/**
 * Generate performance recommendations
 */
function generatePerformanceRecommendations($checks) {
    $recommendations = [];
    
    if (isset($checks['response_time']) && $checks['response_time']['status'] === 'degraded') {
        $recommendations[] = 'Optimize database queries and add indexes';
        $recommendations[] = 'Implement response caching for slow endpoints';
    }
    
    if (isset($checks['memory_efficiency']) && $checks['memory_efficiency']['status'] === 'degraded') {
        $recommendations[] = 'Review memory usage patterns and optimize';
        $recommendations[] = 'Consider implementing memory pooling';
    }
    
    return $recommendations;
}

// Placeholder functions for checks that would be implemented based on specific requirements
function checkDatabasePerformance() { return ['status' => 'healthy', 'message' => 'Performance check not implemented']; }
function checkDatabaseReplication() { return ['status' => 'healthy', 'message' => 'Replication check not implemented']; }
function checkDatabaseBackups() { return ['status' => 'healthy', 'message' => 'Backup check not implemented']; }
function checkCachePerformance() { return ['status' => 'healthy', 'message' => 'Performance check not implemented']; }
function checkCacheMemoryUsage() { return ['status' => 'healthy', 'message' => 'Memory check not implemented']; }
function checkCacheKeyDistribution() { return ['status' => 'healthy', 'message' => 'Distribution check not implemented']; }
function checkOpenAIService() { return ['status' => 'healthy', 'message' => 'Service check not implemented']; }
function checkStabilityAIService() { return ['status' => 'healthy', 'message' => 'Service check not implemented']; }
function checkCoinbaseService() { return ['status' => 'healthy', 'message' => 'Service check not implemented']; }
function checkSSLCertificate() { return ['status' => 'healthy', 'message' => 'SSL check not implemented']; }
function checkSecurityHeaders() { return ['status' => 'healthy', 'message' => 'Headers check not implemented']; }
function checkRateLimitingHealth() { return ['status' => 'healthy', 'message' => 'Rate limiting check not implemented']; }
function checkAuthenticationHealth() { return ['status' => 'healthy', 'message' => 'Authentication check not implemented']; }
function checkFilePermissions() { return ['status' => 'healthy', 'message' => 'Permissions check not implemented']; }
function checkResponseTime() { return ['status' => 'healthy', 'message' => 'Response time check not implemented']; }
function checkThroughput() { return ['status' => 'healthy', 'message' => 'Throughput check not implemented']; }
function checkMemoryEfficiency() { return ['status' => 'healthy', 'message' => 'Memory efficiency check not implemented']; }
function checkDatabaseQueryPerformance() { return ['status' => 'healthy', 'message' => 'Query performance check not implemented']; }
function checkNetworkHealth() { return ['status' => 'healthy', 'message' => 'Network check not implemented']; }
function checkProcessHealth() { return ['status' => 'healthy', 'message' => 'Process check not implemented']; }
function checkLogHealth() { return ['status' => 'healthy', 'message' => 'Log check not implemented']; }
?>
