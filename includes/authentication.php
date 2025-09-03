<?php
/**
 * 🔐 Purrr.love Authentication Middleware
 * Centralized authentication and authorization system
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Authenticate request using OAuth2 token or API key
 */
function authenticateRequest($headers) {
    // Check for API key first (higher priority)
    $apiKey = extractApiKey($headers);
    if ($apiKey) {
        return authenticateApiKey($apiKey);
    }
    
    // Check for OAuth2 token
    $token = extractOAuth2Token($headers);
    if ($token) {
        return authenticateOAuth2Token($token);
    }
    
    // No valid authentication found
    return null;
}

/**
 * Extract API key from headers
 */
function extractApiKey($headers) {
    $apiKey = null;
    
    // Check X-API-Key header
    if (isset($headers['X-API-Key'])) {
        $apiKey = $headers['X-API-Key'];
    }
    
    // Check Authorization header for Bearer token
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            $apiKey = $matches[1];
        }
    }
    
    return $apiKey;
}

/**
 * Extract OAuth2 token from headers
 */
function extractOAuth2Token($headers) {
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

/**
 * Authenticate API key
 */
function authenticateApiKey($apiKey) {
    try {
        $pdo = get_db();
        
        // Get API key details
        $stmt = $pdo->prepare("
            SELECT ak.*, u.username, u.email, u.role, u.active as user_active
            FROM api_keys ak
            JOIN users u ON ak.user_id = u.id
            WHERE ak.key_hash = ? AND ak.active = 1
        ");
        $stmt->execute([hash('sha256', $apiKey)]);
        $key = $stmt->fetch();
        
        if (!$key) {
            logSecurityEvent('invalid_api_key_attempt', ['api_key_hash' => hash('sha256', $apiKey)]);
            return null;
        }
        
        // Check if user is active
        if (!$key['user_active']) {
            logSecurityEvent('inactive_user_api_key_attempt', ['user_id' => $key['user_id']]);
            return null;
        }
        
        // Check expiration
        if ($key['expires_at'] && time() > strtotime($key['expires_at'])) {
            logSecurityEvent('expired_api_key_attempt', ['user_id' => $key['user_id'], 'key_id' => $key['id']]);
            return null;
        }
        
        // Check IP whitelist
        $ipWhitelist = json_decode($key['ip_whitelist'], true);
        if (!empty($ipWhitelist) && !in_array(getClientIP(), $ipWhitelist)) {
            logSecurityEvent('unauthorized_ip_api_key_attempt', [
                'user_id' => $key['user_id'],
                'key_id' => $key['id'],
                'ip_address' => getClientIP()
            ]);
            return null;
        }
        
        // Update last used timestamp
        $stmt = $pdo->prepare("UPDATE api_keys SET last_used_at = ? WHERE id = ?");
        $stmt->execute([date('Y-m-d H:i:s'), $key['id']]);
        
        // Log successful authentication
        logSecurityEvent('api_key_authentication_success', [
            'user_id' => $key['user_id'],
            'key_id' => $key['id'],
            'ip_address' => getClientIP()
        ]);
        
        return [
            'id' => $key['user_id'],
            'username' => $key['username'],
            'email' => $key['email'],
            'role' => $key['role'],
            'api_key_id' => $key['id'],
            'scopes' => json_decode($key['scopes'], true),
            'auth_method' => 'api_key'
        ];
        
    } catch (Exception $e) {
        error_log("API key authentication error: " . $e->getMessage());
        logSecurityEvent('api_key_authentication_error', ['error' => $e->getMessage()]);
        return null;
    }
}

/**
 * Authenticate OAuth2 token
 */
function authenticateOAuth2Token($token) {
    try {
        $pdo = get_db();
        
        // Get token details
        $stmt = $pdo->prepare("
            SELECT at.*, u.username, u.email, u.role, u.active as user_active
            FROM oauth2_access_tokens at
            JOIN users u ON at.user_id = u.id
            WHERE at.token = ? AND at.revoked = FALSE
        ");
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch();
        
        if (!$tokenData) {
            logSecurityEvent('invalid_oauth2_token_attempt', ['token_hash' => hash('sha256', $token)]);
            return null;
        }
        
        // Check if user is active
        if (!$tokenData['user_active']) {
            logSecurityEvent('inactive_user_oauth2_attempt', ['user_id' => $tokenData['user_id']]);
            return null;
        }
        
        // Check expiration
        if (time() > strtotime($tokenData['expires_at'])) {
            logSecurityEvent('expired_oauth2_token_attempt', ['user_id' => $tokenData['user_id']]);
            return null;
        }
        
        // Log successful authentication
        logSecurityEvent('oauth2_authentication_success', [
            'user_id' => $tokenData['user_id'],
            'token_id' => $tokenData['id'],
            'ip_address' => getClientIP()
        ]);
        
        return [
            'id' => $tokenData['user_id'],
            'username' => $tokenData['username'],
            'email' => $tokenData['email'],
            'role' => $tokenData['role'],
            'token_id' => $tokenData['id'],
            'scopes' => explode(' ', $tokenData['scope']),
            'auth_method' => 'oauth2'
        ];
        
    } catch (Exception $e) {
        error_log("OAuth2 token authentication error: " . $e->getMessage());
        logSecurityEvent('oauth2_authentication_error', ['error' => $e->getMessage()]);
        return null;
    }
}

/**
 * Check if user has required scopes
 */
function hasRequiredScopes($user, $requiredScopes) {
    if (!$user || !isset($user['scopes'])) {
        return false;
    }
    
    $userScopes = $user['scopes'];
    
    // Admin users have all scopes
    if ($user['role'] === 'admin') {
        return true;
    }
    
    // Check if user has all required scopes
    foreach ($requiredScopes as $scope) {
        if (!in_array($scope, $userScopes)) {
            return false;
        }
    }
    
    return true;
}

/**
 * Require authentication for endpoint
 */
function requireAuthentication($requiredScopes = []) {
    $user = authenticateRequest(getallheaders());
    
    if (!$user) {
        logSecurityEvent('unauthenticated_access_attempt', [
            'endpoint' => $_SERVER['REQUEST_URI'],
            'ip_address' => getClientIP()
        ]);
        
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentication required'
            ]
        ]);
        exit;
    }
    
    if (!empty($requiredScopes) && !hasRequiredScopes($user, $requiredScopes)) {
        logSecurityEvent('insufficient_scope_access_attempt', [
            'user_id' => $user['id'],
            'endpoint' => $_SERVER['REQUEST_URI'],
            'required_scopes' => $requiredScopes,
            'user_scopes' => $user['scopes']
        ]);
        
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INSUFFICIENT_SCOPE',
                'message' => 'Insufficient permissions'
            ]
        ]);
        exit;
    }
    
    return $user;
}

/**
 * Check if user can access resource
 */
function canAccessResource($user, $resourceType, $resourceId) {
    if (!$user) {
        return false;
    }
    
    // Admin users can access everything
    if ($user['role'] === 'admin') {
        return true;
    }
    
    try {
        $pdo = get_db();
        
        switch ($resourceType) {
            case 'cat':
                // Users can only access their own cats
                $stmt = $pdo->prepare("SELECT user_id FROM cats WHERE id = ?");
                $stmt->execute([$resourceId]);
                $cat = $stmt->fetch();
                return $cat && $cat['user_id'] == $user['id'];
                
            case 'user':
                // Users can only access their own profile
                return $user['id'] == $resourceId;
                
            case 'api_key':
                // Users can only access their own API keys
                $stmt = $pdo->prepare("SELECT user_id FROM api_keys WHERE id = ?");
                $stmt->execute([$resourceId]);
                $key = $stmt->fetch();
                return $key && $key['user_id'] == $user['id'];
                
            default:
                return false;
        }
        
    } catch (Exception $e) {
        error_log("Resource access check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate session and regenerate if needed
 */
function validateSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if session is expired
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
        session_unset();
        session_destroy();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    return true;
}

/**
 * Create user session
 */
function createUserSession($user) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Set session data
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    $_SESSION['last_regeneration'] = time();
    $_SESSION['ip_address'] = getClientIP();
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    // Log successful login
    logSecurityEvent('user_session_created', [
        'user_id' => $user['id'],
        'username' => $user['username'],
        'ip_address' => getClientIP()
    ]);
}

/**
 * Destroy user session
 */
function destroyUserSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? 'Unknown';
        
        // Log logout
        if ($userId) {
            logSecurityEvent('user_session_destroyed', [
                'user_id' => $userId,
                'username' => $username,
                'ip_address' => getClientIP()
            ]);
        }
        
        // Destroy session
        session_unset();
        session_destroy();
    }
}

/**
 * Check if user is logged in via session
 */
function isUserLoggedIn() {
    if (!validateSession()) {
        return false;
    }
    
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user from session
 */
function getCurrentUser() {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("
            SELECT id, username, email, role, created_at, active
            FROM users 
            WHERE id = ? AND active = 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // User no longer exists or is inactive
            destroyUserSession();
            return null;
        }
        
        return $user;
        
    } catch (Exception $e) {
        error_log("Error getting current user: " . $e->getMessage());
        return null;
    }
}

/**
 * Rate limit check for authentication attempts
 */
function checkAuthRateLimit($identifier, $action = 'login') {
    try {
        $pdo = get_db();
        
        // Get current attempts in the last hour
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as attempt_count
            FROM failed_login_attempts
            WHERE ip_address = ? AND created_at > NOW() - INTERVAL '1 hour'
        ");
        $stmt->execute([$identifier]);
        $result = $stmt->fetch();
        
        $maxAttempts = 5; // 5 attempts per hour
        
        if ($result['attempt_count'] >= $maxAttempts) {
            logSecurityEvent('authentication_rate_limit_exceeded', [
                'identifier' => $identifier,
                'action' => $action,
                'attempt_count' => $result['attempt_count']
            ]);
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Auth rate limit check error: " . $e->getMessage());
        return true; // Allow on error
    }
}

/**
 * Record failed authentication attempt
 */
function recordFailedAuthAttempt($identifier, $username = null, $action = 'login') {
    try {
        $pdo = get_db();
        
        // Use the database function to record failed login
        $stmt = $pdo->prepare("SELECT record_failed_login(?, ?, ?)");
        $stmt->execute([$identifier, $username, $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown']);
        
        // Log security event
        logSecurityEvent('failed_authentication_attempt', [
            'identifier' => $identifier,
            'username' => $username,
            'action' => $action,
            'ip_address' => getClientIP()
        ]);
        
    } catch (Exception $e) {
        error_log("Failed to record auth attempt: " . $e->getMessage());
    }
}
?>
