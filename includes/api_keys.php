<?php
/**
 * 🔑 Purrr.love API Key Management
 * Complete API key system with scopes and usage tracking
 */

// API key configuration
define('API_KEY_LENGTH', 64);
define('API_KEY_PREFIX', 'pk_');
define('API_KEY_SCOPES', ['read', 'write', 'admin', 'client']);

/**
 * Generate new API key for user
 */
function generateApiKey($userId, $params) {
    // Validate parameters
    $name = $params['name'] ?? 'Default API Key';
    $scopes = $params['scopes'] ?? ['read'];
    $expiresAt = $params['expires_at'] ?? null;
    $ipWhitelist = $params['ip_whitelist'] ?? [];
    
    // Validate scopes
    if (!validateApiKeyScopes($scopes)) {
        throw new Exception('Invalid API key scopes', 400);
    }
    
    // Generate unique API key
    $apiKey = generateUniqueApiKey();
    
    // Create API key record
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO api_keys 
        (user_id, name, key_hash, scopes, expires_at, ip_whitelist, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $userId,
        $name,
        hash('sha256', $apiKey),
        json_encode($scopes),
        $expiresAt,
        json_encode($ipWhitelist),
        time()
    ]);
    
    $keyId = $pdo->lastInsertId();
    
    // Log API key generation
    logApiKeyEvent('key_generated', [
        'user_id' => $userId,
        'key_id' => $keyId,
        'scopes' => $scopes
    ]);
    
    return [
        'id' => $keyId,
        'name' => $name,
        'api_key' => $apiKey, // Only returned once
        'scopes' => $scopes,
        'expires_at' => $expiresAt,
        'ip_whitelist' => $ipWhitelist,
        'created_at' => date('c')
    ];
}

/**
 * Get user's API keys
 */
function getUserApiKeys($userId) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT id, name, scopes, expires_at, ip_whitelist, created_at, last_used_at, active
        FROM api_keys 
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    
    $stmt->execute([$userId]);
    $keys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process keys
    foreach ($keys as &$key) {
        $key['scopes'] = json_decode($key['scopes'], true);
        $key['ip_whitelist'] = json_decode($key['ip_whitelist'], true);
        $key['expires_at'] = $key['expires_at'] ? date('c', $key['expires_at']) : null;
        $key['created_at'] = date('c', $key['created_at']);
        $key['last_used_at'] = $key['last_used_at'] ? date('c', $key['last_used_at']) : null;
    }
    
    return $keys;
}

/**
 * Update API key
 */
function updateApiKey($keyId, $userId, $params) {
    // Verify ownership
    $key = getApiKeyById($keyId, $userId);
    if (!$key) {
        throw new Exception('API key not found', 404);
    }
    
    // Update fields
    $updates = [];
    $values = [];
    
    if (isset($params['name'])) {
        $updates[] = 'name = ?';
        $values[] = $params['name'];
    }
    
    if (isset($params['scopes'])) {
        if (!validateApiKeyScopes($params['scopes'])) {
            throw new Exception('Invalid API key scopes', 400);
        }
        $updates[] = 'scopes = ?';
        $values[] = json_encode($params['scopes']);
    }
    
    if (isset($params['expires_at'])) {
        $updates[] = 'expires_at = ?';
        $values[] = $params['expires_at'];
    }
    
    if (isset($params['ip_whitelist'])) {
        $updates[] = 'ip_whitelist = ?';
        $values[] = json_encode($params['ip_whitelist']);
    }
    
    if (isset($params['active'])) {
        $updates[] = 'active = ?';
        $values[] = $params['active'] ? 1 : 0;
    }
    
    if (empty($updates)) {
        throw new Exception('No fields to update', 400);
    }
    
    // Build and execute update query
    $values[] = $keyId;
    $values[] = $userId;
    
    $pdo = get_db();
    $stmt = $pdo->prepare("
        UPDATE api_keys 
        SET " . implode(', ', $updates) . "
        WHERE id = ? AND user_id = ?
    ");
    
    $stmt->execute($values);
    
    // Log API key update
    logApiKeyEvent('key_updated', [
        'user_id' => $userId,
        'key_id' => $keyId,
        'updates' => $params
    ]);
    
    return ['success' => true];
}

/**
 * Revoke API key
 */
function revokeApiKey($keyId, $userId) {
    // Verify ownership
    $key = getApiKeyById($keyId, $userId);
    if (!$key) {
        throw new Exception('API key not found', 404);
    }
    
    // Mark as inactive
    $pdo = get_db();
    $stmt = $pdo->prepare("UPDATE api_keys SET active = 0 WHERE id = ? AND user_id = ?");
    $stmt->execute([$keyId, $userId]);
    
    // Log API key revocation
    logApiKeyEvent('key_revoked', [
        'user_id' => $userId,
        'key_id' => $keyId
    ]);
    
    return ['success' => true];
}

/**
 * Get API key usage statistics
 */
function getApiKeyUsage($keyId, $userId) {
    // Verify ownership
    $key = getApiKeyById($keyId, $userId);
    if (!$key) {
        throw new Exception('API key not found', 404);
    }
    
    $pdo = get_db();
    
    // Get usage statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_requests,
            COUNT(DISTINCT DATE(FROM_UNIXTIME(created_at))) as days_active,
            COUNT(DISTINCT ip_address) as unique_ips,
            MAX(created_at) as last_request,
            MIN(created_at) as first_request
        FROM api_key_usage 
        WHERE api_key_id = ?
    ");
    
    $stmt->execute([$keyId]);
    $usage = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent requests
    $stmt = $pdo->prepare("
        SELECT endpoint, method, status_code, ip_address, created_at
        FROM api_key_usage 
        WHERE api_key_id = ?
        ORDER BY created_at DESC
        LIMIT 50
    ");
    
    $stmt->execute([$keyId]);
    $recentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process timestamps
    foreach ($recentRequests as &$request) {
        $request['created_at'] = date('c', $request['created_at']);
    }
    
    $usage['recent_requests'] = $recentRequests;
    $usage['last_request'] = $usage['last_request'] ? date('c', $usage['last_request']) : null;
    $usage['first_request'] = $usage['first_request'] ? date('c', $usage['first_request']) : null;
    
    return $usage;
}

/**
 * Authenticate request using API key
 */
function authenticateApiKey($apiKey, $ipAddress = null) {
    if (!$apiKey) {
        return null;
    }
    
    $pdo = get_db();
    $keyHash = hash('sha256', $apiKey);
    
    // Get API key
    $stmt = $pdo->prepare("
        SELECT ak.*, u.username, u.email, u.role
        FROM api_keys ak
        JOIN users u ON ak.user_id = u.id
        WHERE ak.key_hash = ? AND ak.active = 1
    ");
    
    $stmt->execute([$keyHash]);
    $key = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$key) {
        return null;
    }
    
    // Check expiration
    if ($key['expires_at'] && time() > $key['expires_at']) {
        return null;
    }
    
    // Check IP whitelist
    if (!empty($key['ip_whitelist'])) {
        $ipWhitelist = json_decode($key['ip_whitelist'], true);
        if (!in_array($ipAddress, $ipWhitelist)) {
            return null;
        }
    }
    
    // Update last used timestamp
    $stmt = $pdo->prepare("UPDATE api_keys SET last_used_at = ? WHERE id = ?");
    $stmt->execute([time(), $key['id']]);
    
    // Return user info
    return [
        'id' => $key['user_id'],
        'username' => $key['username'],
        'email' => $key['email'],
        'role' => $key['role'],
        'api_key_id' => $key['id'],
        'scopes' => json_decode($key['scopes'], true)
    ];
}

/**
 * Check if API key has required scope
 */
function checkApiKeyScope($user, $requiredScope) {
    if (!isset($user['scopes'])) {
        return false;
    }
    
    $userScopes = $user['scopes'];
    
    // Admin scope grants all permissions
    if (in_array('admin', $userScopes)) {
        return true;
    }
    
    // Check specific scope
    return in_array($requiredScope, $userScopes);
}

/**
 * Log API key usage
 */
function logApiKeyUsage($apiKeyId, $endpoint, $method, $statusCode, $ipAddress) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO api_key_usage 
        (api_key_id, endpoint, method, status_code, ip_address, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $apiKeyId,
        $endpoint,
        $method,
        $statusCode,
        $ipAddress,
        time()
    ]);
}

/**
 * Generate unique API key
 */
function generateUniqueApiKey() {
    $maxAttempts = 10;
    $attempts = 0;
    
    do {
        $apiKey = API_KEY_PREFIX . bin2hex(random_bytes(API_KEY_LENGTH / 2));
        $attempts++;
        
        // Check if key already exists
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM api_keys WHERE key_hash = ?");
        $stmt->execute([hash('sha256', $apiKey)]);
        $exists = $stmt->fetchColumn() > 0;
        
        if (!$exists) {
            return $apiKey;
        }
    } while ($attempts < $maxAttempts);
    
    throw new Exception('Failed to generate unique API key', 500);
}

/**
 * Validate API key scopes
 */
function validateApiKeyScopes($scopes) {
    if (!is_array($scopes)) {
        return false;
    }
    
    foreach ($scopes as $scope) {
        if (!in_array($scope, API_KEY_SCOPES)) {
            return false;
        }
    }
    
    return true;
}

/**
 * Get API key by ID
 */
function getApiKeyById($keyId, $userId) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT * FROM api_keys 
        WHERE id = ? AND user_id = ?
    ");
    
    $stmt->execute([$keyId, $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Log API key event
 */
function logApiKeyEvent($event, $data) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO api_key_events 
        (event, data, ip_address, user_agent, created_at)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $event,
        json_encode($data),
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? '',
        time()
    ]);
}

/**
 * Clean up expired API keys
 */
function cleanupExpiredApiKeys() {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        UPDATE api_keys 
        SET active = 0 
        WHERE expires_at IS NOT NULL AND expires_at < ?
    ");
    
    $stmt->execute([time()]);
    
    return $stmt->rowCount();
}

/**
 * Get API key statistics for user
 */
function getApiKeyStats($userId) {
    $pdo = get_db();
    
    // Total keys
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM api_keys WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalKeys = $stmt->fetchColumn();
    
    // Active keys
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM api_keys WHERE user_id = ? AND active = 1");
    $stmt->execute([$userId]);
    $activeKeys = $stmt->fetchColumn();
    
    // Total requests
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM api_key_usage aku
        JOIN api_keys ak ON aku.api_key_id = ak.id
        WHERE ak.user_id = ?
    ");
    $stmt->execute([$userId]);
    $totalRequests = $stmt->fetchColumn();
    
    // Recent activity
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM api_key_usage aku
        JOIN api_keys ak ON aku.api_key_id = ak.id
        WHERE ak.user_id = ? AND aku.created_at > ?
    ");
    $stmt->execute([$userId, time() - 86400]); // Last 24 hours
    $recentRequests = $stmt->fetchColumn();
    
    return [
        'total_keys' => $totalKeys,
        'active_keys' => $activeKeys,
        'total_requests' => $totalRequests,
        'recent_requests_24h' => $recentRequests
    ];
}
