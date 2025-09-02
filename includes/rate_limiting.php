<?php
/**
 * 📊 Purrr.love Rate Limiting System
 * Comprehensive rate limiting with user, IP, and API key support
 */

// Rate limiting configuration
define('RATE_LIMIT_WINDOW', 3600); // 1 hour in seconds
define('DEFAULT_RATE_LIMIT', 1000); // Default requests per hour
define('FREE_TIER_LIMIT', 100); // Free tier requests per hour
define('PREMIUM_TIER_LIMIT', 1000); // Premium tier requests per hour
define('ENTERPRISE_TIER_LIMIT', 10000); // Enterprise tier requests per hour

/**
 * Check rate limit for request
 */
function checkRateLimit($request) {
    $identifier = getRateLimitIdentifier($request);
    $endpoint = $request['path'];
    $method = $request['method'];
    
    // Get rate limit for endpoint
    $endpointLimit = getEndpointRateLimit($endpoint, $method);
    
    // Check current usage
    $currentUsage = getCurrentRateLimitUsage($identifier, $endpoint);
    
    // Check if limit exceeded
    $allowed = $currentUsage['count'] < $endpointLimit;
    
    // Update rate limit counter
    updateRateLimitCounter($identifier, $endpoint, $method);
    
    return [
        'allowed' => $allowed,
        'limit' => $endpointLimit,
        'remaining' => max(0, $endpointLimit - $currentUsage['count']),
        'reset' => $currentUsage['window_start'] + RATE_LIMIT_WINDOW,
        'identifier' => $identifier
    ];
}

/**
 * Add rate limit headers to response
 */
function addRateLimitHeaders($rateLimitResult) {
    header('X-RateLimit-Limit: ' . $rateLimitResult['limit']);
    header('X-RateLimit-Remaining: ' . $rateLimitResult['remaining']);
    header('X-RateLimit-Reset: ' . $rateLimitResult['reset']);
    
    if (!$rateLimitResult['allowed']) {
        header('Retry-After: ' . ($rateLimitResult['reset'] - time()));
    }
}

/**
 * Get rate limit identifier
 */
function getRateLimitIdentifier($request) {
    // Try to get user ID from authentication
    $user = authenticateRequest($request['headers']);
    if ($user) {
        return 'user_' . $user['id'];
    }
    
    // Fall back to IP address
    return 'ip_' . getClientIP();
}

/**
 * Get endpoint rate limit
 */
function getEndpointRateLimit($endpoint, $method) {
    $pdo = get_db();
    
    // Try to get specific endpoint limit
    $stmt = $pdo->prepare("
        SELECT rate_limit_per_hour 
        FROM api_endpoints 
        WHERE path = ? AND method = ? AND active = 1
    ");
    
    $stmt->execute([$endpoint, $method]);
    $endpointLimit = $stmt->fetchColumn();
    
    if ($endpointLimit) {
        return $endpointLimit;
    }
    
    // Try to match pattern endpoints (with parameters)
    $patternEndpoints = getPatternEndpoints($endpoint, $method);
    if (!empty($patternEndpoints)) {
        return $patternEndpoints[0]['rate_limit_per_hour'];
    }
    
    // Return default limit
    return DEFAULT_RATE_LIMIT;
}

/**
 * Get pattern endpoints (for dynamic routes)
 */
function getPatternEndpoints($endpoint, $method) {
    $pdo = get_db();
    
    // Convert endpoint to pattern for matching
    $pattern = convertEndpointToPattern($endpoint);
    
    $stmt = $pdo->prepare("
        SELECT * FROM api_endpoints 
        WHERE method = ? AND active = 1
        ORDER BY 
            CASE WHEN path = ? THEN 1 ELSE 2 END,
            LENGTH(path) DESC
    ");
    
    $stmt->execute([$method, $endpoint]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Convert endpoint to pattern for matching
 */
function convertEndpointToPattern($endpoint) {
    // Replace numeric IDs with pattern
    $pattern = preg_replace('/\/\d+/', '/{id}', $endpoint);
    
    // Replace UUIDs with pattern
    $pattern = preg_replace('/\/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/i', '/{uuid}', $pattern);
    
    return $pattern;
}

/**
 * Get current rate limit usage
 */
function getCurrentRateLimitUsage($identifier, $endpoint) {
    $pdo = get_db();
    
    $windowStart = time() - (time() % RATE_LIMIT_WINDOW);
    
    $stmt = $pdo->prepare("
        SELECT requests_count, window_start
        FROM rate_limits 
        WHERE identifier = ? AND endpoint = ? AND window_start = ?
    ");
    
    $stmt->execute([$identifier, $endpoint, date('Y-m-d H:i:s', $windowStart)]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return [
            'count' => $result['requests_count'],
            'window_start' => strtotime($result['window_start'])
        ];
    }
    
    return [
        'count' => 0,
        'window_start' => $windowStart
    ];
}

/**
 * Update rate limit counter
 */
function updateRateLimitCounter($identifier, $endpoint, $method) {
    $pdo = get_db();
    
    $windowStart = time() - (time() % RATE_LIMIT_WINDOW);
    $windowStartStr = date('Y-m-d H:i:s', $windowStart);
    
    // Try to insert new record
    $stmt = $pdo->prepare("
        INSERT INTO rate_limits (identifier, endpoint, requests_count, window_start)
        VALUES (?, ?, 1, ?)
        ON CONFLICT (identifier, endpoint, window_start)
        DO UPDATE SET requests_count = rate_limits.requests_count + 1
    ");
    
    $stmt->execute([$identifier, $endpoint, $windowStartStr]);
}

/**
 * Get user tier rate limit
 */
function getUserTierRateLimit($userId) {
    $pdo = get_db();
    
    // Get user's subscription tier
    $stmt = $pdo->prepare("
        SELECT subscription_tier 
        FROM users 
        WHERE id = ?
    ");
    
    $stmt->execute([$userId]);
    $tier = $stmt->fetchColumn();
    
    switch ($tier) {
        case 'enterprise':
            return ENTERPRISE_TIER_LIMIT;
        case 'premium':
            return PREMIUM_TIER_LIMIT;
        default:
            return FREE_TIER_LIMIT;
    }
}

/**
 * Check if user has exceeded their tier limit
 */
function checkUserTierLimit($userId, $endpoint) {
    $tierLimit = getUserTierRateLimit($userId);
    $identifier = 'user_' . $userId;
    
    $currentUsage = getCurrentRateLimitUsage($identifier, $endpoint);
    
    return [
        'allowed' => $currentUsage['count'] < $tierLimit,
        'limit' => $tierLimit,
        'remaining' => max(0, $tierLimit - $currentUsage['count']),
        'reset' => $currentUsage['window_start'] + RATE_LIMIT_WINDOW
    ];
}

/**
 * Get rate limit statistics for user
 */
function getUserRateLimitStats($userId, $days = 7) {
    $pdo = get_db();
    
    $identifier = 'user_' . $userId;
    
    $stmt = $pdo->prepare("
        SELECT 
            endpoint,
            method,
            SUM(requests_count) as total_requests,
            COUNT(*) as windows_used,
            MAX(requests_count) as peak_requests_per_hour
        FROM rate_limits 
        WHERE identifier = ? 
        AND window_start >= CURRENT_DATE - INTERVAL '1 day' * ?
        GROUP BY endpoint, method
        ORDER BY total_requests DESC
    ");
    
    $stmt->execute([$identifier, $days]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get rate limit statistics for endpoint
 */
function getEndpointRateLimitStats($endpoint, $method, $days = 7) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT 
            identifier,
            SUM(requests_count) as total_requests,
            COUNT(*) as windows_used,
            MAX(requests_count) as peak_requests_per_hour
        FROM rate_limits 
        WHERE endpoint = ? AND method = ?
        AND window_start >= CURRENT_DATE - INTERVAL '1 day' * ?
        GROUP BY identifier
        ORDER BY total_requests DESC
        LIMIT 100
    ");
    
    $stmt->execute([$endpoint, $method, $days]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Clean up old rate limit records
 */
function cleanupRateLimits() {
    $pdo = get_db();
    
    // Remove records older than 24 hours
    $stmt = $pdo->prepare("
        DELETE FROM rate_limits 
        WHERE window_start < CURRENT_TIMESTAMP - INTERVAL '24 hours'
    ");
    
    $stmt->execute();
    
    return $stmt->rowCount();
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Check if IP is whitelisted
 */
function isIPWhitelisted($ip, $whitelist) {
    if (empty($whitelist)) {
        return true; // No whitelist means all IPs allowed
    }
    
    foreach ($whitelist as $allowedIP) {
        if (isIPInRange($ip, $allowedIP)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Check if IP is in range (supports CIDR notation)
 */
function isIPInRange($ip, $range) {
    if (strpos($range, '/') !== false) {
        // CIDR notation
        list($subnet, $mask) = explode('/', $range);
        
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - $mask);
        
        return ($ipLong & $maskLong) == ($subnetLong & $maskLong);
    } else {
        // Single IP
        return $ip === $range;
    }
}

/**
 * Get rate limit headers for response
 */
function getRateLimitHeaders($identifier, $endpoint) {
    $currentUsage = getCurrentRateLimitUsage($identifier, $endpoint);
    $endpointLimit = getEndpointRateLimit($endpoint, 'GET'); // Default to GET for header calculation
    
    return [
        'X-RateLimit-Limit' => $endpointLimit,
        'X-RateLimit-Remaining' => max(0, $endpointLimit - $currentUsage['count']),
        'X-RateLimit-Reset' => $currentUsage['window_start'] + RATE_LIMIT_WINDOW
    ];
}

/**
 * Log rate limit violation
 */
function logRateLimitViolation($identifier, $endpoint, $method, $ip) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO api_error_logs 
        (error_code, error_message, error_details, ip_address)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        'RATE_LIMIT_EXCEEDED',
        'Rate limit exceeded for endpoint',
        json_encode([
            'identifier' => $identifier,
            'endpoint' => $endpoint,
            'method' => $method,
            'timestamp' => time()
        ]),
        $ip
    ]);
}

/**
 * Get rate limit recommendations
 */
function getRateLimitRecommendations($userId) {
    $stats = getUserRateLimitStats($userId, 30);
    $recommendations = [];
    
    foreach ($stats as $stat) {
        if ($stat['peak_requests_per_hour'] > $stat['total_requests'] / $stat['windows_used'] * 1.5) {
            $recommendations[] = [
                'endpoint' => $stat['endpoint'],
                'issue' => 'High peak usage detected',
                'suggestion' => 'Consider implementing caching or request batching'
            ];
        }
        
        if ($stat['total_requests'] > getUserTierRateLimit($userId) * 0.8) {
            $recommendations[] = [
                'endpoint' => $stat['endpoint'],
                'issue' => 'Approaching tier limit',
                'suggestion' => 'Consider upgrading to higher tier or optimizing requests'
            ];
        }
    }
    
    return $recommendations;
}
