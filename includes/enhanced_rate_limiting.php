<?php
/**
 * 📊 Purrr.love Enhanced Rate Limiting System
 * Advanced rate limiting with Redis backend, burst protection, and intelligent throttling
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Enhanced rate limiting with Redis backend
 */
class EnhancedRateLimiter {
    private $redis;
    private $config;
    
    public function __construct() {
        $this->config = [
            'enabled' => getConfig('security.rate_limiting.enabled', true),
            'default_limit' => getConfig('security.rate_limiting.default_limit', 1000),
            'burst_limit' => getConfig('security.rate_limiting.burst_limit', 2000),
            'window_seconds' => getConfig('security.rate_limiting.window_seconds', 3600),
            'free_tier_limit' => getConfig('security.rate_limiting.free_tier_limit', 100),
            'premium_tier_limit' => getConfig('security.rate_limiting.premium_tier_limit', 1000),
            'enterprise_tier_limit' => getConfig('security.rate_limiting.enterprise_tier_limit', 10000)
        ];
        
        $this->initializeRedis();
    }
    
    /**
     * Initialize Redis connection
     */
    private function initializeRedis() {
        try {
            $this->redis = new Redis();
            $this->redis->connect(
                getConfig('redis.host', 'localhost'),
                getConfig('redis.port', 6379),
                getConfig('redis.timeout', 5)
            );
            
            if (getConfig('redis.password')) {
                $this->redis->auth(getConfig('redis.password'));
            }
            
            $this->redis->select(getConfig('redis.database', 0));
            
        } catch (Exception $e) {
            error_log("Redis connection failed: " . $e->getMessage());
            $this->redis = null;
        }
    }
    
    /**
     * Check rate limit for request
     */
    public function checkRateLimit($request) {
        if (!$this->config['enabled'] || !$this->redis) {
            return $this->getDefaultResponse(true);
        }
        
        $identifier = $this->getRateLimitIdentifier($request);
        $endpoint = $request['path'];
        $method = $request['method'];
        
        // Get rate limit for endpoint
        $endpointLimit = $this->getEndpointRateLimit($endpoint, $method);
        
        // Check current usage
        $currentUsage = $this->getCurrentRateLimitUsage($identifier, $endpoint);
        
        // Check if limit exceeded
        $allowed = $currentUsage['count'] < $endpointLimit;
        
        // Check burst limit
        $burstAllowed = $currentUsage['burst_count'] < $this->config['burst_limit'];
        
        // Final decision
        $finalAllowed = $allowed && $burstAllowed;
        
        // Update rate limit counter
        $this->updateRateLimitCounter($identifier, $endpoint, $method);
        
        // Log violation if applicable
        if (!$finalAllowed) {
            $this->logRateLimitViolation($identifier, $endpoint, $method, $currentUsage);
        }
        
        return [
            'allowed' => $finalAllowed,
            'limit' => $endpointLimit,
            'remaining' => max(0, $endpointLimit - $currentUsage['count']),
            'burst_remaining' => max(0, $this->config['burst_limit'] - $currentUsage['burst_count']),
            'reset' => $currentUsage['window_start'] + $this->config['window_seconds'],
            'identifier' => $identifier,
            'endpoint' => $endpoint,
            'method' => $method
        ];
    }
    
    /**
     * Get rate limit identifier
     */
    private function getRateLimitIdentifier($request) {
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
    private function getEndpointRateLimit($endpoint, $method) {
        try {
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
            $patternEndpoints = $this->getPatternEndpoints($endpoint, $method);
            if (!empty($patternEndpoints)) {
                return $patternEndpoints[0]['rate_limit_per_hour'];
            }
            
            // Return default limit
            return $this->config['default_limit'];
            
        } catch (Exception $e) {
            error_log("Error getting endpoint rate limit: " . $e->getMessage());
            return $this->config['default_limit'];
        }
    }
    
    /**
     * Get current rate limit usage from Redis
     */
    private function getCurrentRateLimitUsage($identifier, $endpoint) {
        $windowStart = time() - (time() % $this->config['window_seconds']);
        $key = "rate_limit:{$identifier}:{$endpoint}:{$windowStart}";
        $burstKey = "rate_limit_burst:{$identifier}:{$windowStart}";
        
        $count = (int) $this->redis->get($key);
        $burstCount = (int) $this->redis->get($burstKey);
        
        return [
            'count' => $count,
            'burst_count' => $burstCount,
            'window_start' => $windowStart
        ];
    }
    
    /**
     * Update rate limit counter in Redis
     */
    private function updateRateLimitCounter($identifier, $endpoint, $method) {
        $windowStart = time() - (time() % $this->config['window_seconds']);
        $key = "rate_limit:{$identifier}:{$endpoint}:{$windowStart}";
        $burstKey = "rate_limit_burst:{$identifier}:{$windowStart}";
        
        // Increment main counter
        $this->redis->incr($key);
        $this->redis->expire($key, $this->config['window_seconds'] + 60); // Extra minute buffer
        
        // Increment burst counter
        $this->redis->incr($burstKey);
        $this->redis->expire($burstKey, $this->config['window_seconds'] + 60);
        
        // Store in database for analytics
        $this->storeRateLimitInDatabase($identifier, $endpoint, $method, $windowStart);
    }
    
    /**
     * Store rate limit data in database
     */
    private function storeRateLimitInDatabase($identifier, $endpoint, $method, $windowStart) {
        try {
            $pdo = get_db();
            $stmt = $pdo->prepare("
                INSERT INTO rate_limits (identifier, endpoint, method, requests_count, window_start)
                VALUES (?, ?, ?, 1, ?)
                ON CONFLICT (identifier, endpoint, method, window_start) 
                DO UPDATE SET requests_count = rate_limits.requests_count + 1
            ");
            
            $stmt->execute([
                $identifier,
                $endpoint,
                $method,
                date('Y-m-d H:i:s', $windowStart)
            ]);
            
        } catch (Exception $e) {
            error_log("Failed to store rate limit in database: " . $e->getMessage());
        }
    }
    
    /**
     * Log rate limit violation
     */
    private function logRateLimitViolation($identifier, $endpoint, $method, $currentUsage) {
        try {
            $pdo = get_db();
            
            // Check if violation already exists
            $stmt = $pdo->prepare("
                SELECT id, violation_count FROM rate_limit_violations 
                WHERE identifier = ? AND endpoint = ? AND method = ?
            ");
            $stmt->execute([$identifier, $endpoint, $method]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing violation
                $stmt = $pdo->prepare("
                    UPDATE rate_limit_violations 
                    SET violation_count = violation_count + 1, 
                        last_violation_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([$existing['id']]);
            } else {
                // Create new violation record
                $stmt = $pdo->prepare("
                    INSERT INTO rate_limit_violations 
                    (identifier, endpoint, method, ip_address, user_id, violation_count)
                    VALUES (?, ?, ?, ?, ?, 1)
                ");
                
                $ipAddress = getClientIP();
                $userId = $this->extractUserIdFromIdentifier($identifier);
                
                $stmt->execute([$identifier, $endpoint, $method, $ipAddress, $userId]);
            }
            
            // Log security event
            logSecurityEvent('rate_limit_violation', [
                'identifier' => $identifier,
                'endpoint' => $endpoint,
                'method' => $method,
                'current_count' => $currentUsage['count'],
                'burst_count' => $currentUsage['burst_count'],
                'ip_address' => getClientIP()
            ]);
            
        } catch (Exception $e) {
            error_log("Failed to log rate limit violation: " . $e->getMessage());
        }
    }
    
    /**
     * Extract user ID from identifier
     */
    private function extractUserIdFromIdentifier($identifier) {
        if (strpos($identifier, 'user_') === 0) {
            return (int) substr($identifier, 5);
        }
        return null;
    }
    
    /**
     * Get pattern endpoints for dynamic routes
     */
    private function getPatternEndpoints($endpoint, $method) {
        try {
            $pdo = get_db();
            
            // Get endpoints with parameters (e.g., /api/v1/cats/{id})
            $stmt = $pdo->prepare("
                SELECT path, rate_limit_per_hour 
                FROM api_endpoints 
                WHERE method = ? AND active = 1 AND path LIKE '%{%'
                ORDER BY priority DESC
            ");
            
            $stmt->execute([$method]);
            $patternEndpoints = $stmt->fetchAll();
            
            // Find matching pattern
            foreach ($patternEndpoints as $patternEndpoint) {
                if ($this->matchPattern($patternEndpoint['path'], $endpoint)) {
                    return [$patternEndpoint];
                }
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log("Error getting pattern endpoints: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Match endpoint pattern
     */
    private function matchPattern($pattern, $endpoint) {
        // Convert pattern to regex
        $regex = preg_replace('/\{[^}]+\}/', '[^/]+', $pattern);
        $regex = '#^' . $regex . '$#';
        
        return preg_match($regex, $endpoint);
    }
    
    /**
     * Get default response when rate limiting is disabled
     */
    private function getDefaultResponse($allowed) {
        return [
            'allowed' => $allowed,
            'limit' => $this->config['default_limit'],
            'remaining' => $this->config['default_limit'],
            'burst_remaining' => $this->config['burst_limit'],
            'reset' => time() + $this->config['window_seconds'],
            'identifier' => 'default',
            'endpoint' => 'unknown',
            'method' => 'unknown'
        ];
    }
    
    /**
     * Get rate limit statistics
     */
    public function getRateLimitStats($identifier = null) {
        if (!$this->redis) {
            return [];
        }
        
        $stats = [];
        
        if ($identifier) {
            // Get stats for specific identifier
            $pattern = "rate_limit:{$identifier}:*";
            $keys = $this->redis->keys($pattern);
            
            foreach ($keys as $key) {
                $parts = explode(':', $key);
                $endpoint = $parts[2] ?? 'unknown';
                $count = (int) $this->redis->get($key);
                
                $stats[$endpoint] = $count;
            }
        } else {
            // Get global stats
            $pattern = "rate_limit:*";
            $keys = $this->redis->keys($pattern);
            
            $stats['total_keys'] = count($keys);
            $stats['total_requests'] = 0;
            
            foreach ($keys as $key) {
                $stats['total_requests'] += (int) $this->redis->get($key);
            }
        }
        
        return $stats;
    }
    
    /**
     * Reset rate limit for identifier
     */
    public function resetRateLimit($identifier, $endpoint = null) {
        if (!$this->redis) {
            return false;
        }
        
        if ($endpoint) {
            // Reset specific endpoint
            $pattern = "rate_limit:{$identifier}:{$endpoint}:*";
        } else {
            // Reset all endpoints for identifier
            $pattern = "rate_limit:{$identifier}:*";
        }
        
        $keys = $this->redis->keys($pattern);
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
        
        return true;
    }
    
    /**
     * Get user tier limits
     */
    public function getUserTierLimits($userId) {
        try {
            $pdo = get_db();
            $stmt = $pdo->prepare("
                SELECT subscription_tier FROM users WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $tier = $stmt->fetchColumn() ?: 'free';
            
            $tierLimits = [
                'free' => $this->config['free_tier_limit'],
                'premium' => $this->config['premium_tier_limit'],
                'enterprise' => $this->config['enterprise_tier_limit']
            ];
            
            return $tierLimits[$tier] ?? $this->config['free_tier_limit'];
            
        } catch (Exception $e) {
            error_log("Error getting user tier limits: " . $e->getMessage());
            return $this->config['free_tier_limit'];
        }
    }
}

/**
 * Global rate limiter instance
 */
$globalRateLimiter = new EnhancedRateLimiter();

/**
 * Check rate limit (wrapper function)
 */
function checkEnhancedRateLimit($request) {
    global $globalRateLimiter;
    return $globalRateLimiter->checkRateLimit($request);
}

/**
 * Add enhanced rate limit headers
 */
function addEnhancedRateLimitHeaders($rateLimitResult) {
    header('X-RateLimit-Limit: ' . $rateLimitResult['limit']);
    header('X-RateLimit-Remaining: ' . $rateLimitResult['remaining']);
    header('X-RateLimit-Burst-Remaining: ' . $rateLimitResult['burst_remaining']);
    header('X-RateLimit-Reset: ' . $rateLimitResult['reset']);
    header('X-RateLimit-Identifier: ' . $rateLimitResult['identifier']);
    
    if (!$rateLimitResult['allowed']) {
        header('Retry-After: ' . ($rateLimitResult['reset'] - time()));
    }
}

/**
 * Get rate limit statistics
 */
function getRateLimitStats($identifier = null) {
    global $globalRateLimiter;
    return $globalRateLimiter->getRateLimitStats($identifier);
}

/**
 * Reset rate limit for identifier
 */
function resetRateLimit($identifier, $endpoint = null) {
    global $globalRateLimiter;
    return $globalRateLimiter->resetRateLimit($identifier, $endpoint);
}
?>
