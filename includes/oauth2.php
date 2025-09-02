<?php
/**
 * 🔐 Purrr.love OAuth2 Implementation
 * Complete OAuth2 server with PKCE support
 */

// OAuth2 configuration
define('OAUTH2_ACCESS_TOKEN_LIFETIME', 3600); // 1 hour
define('OAUTH2_REFRESH_TOKEN_LIFETIME', 2592000); // 30 days
define('OAUTH2_AUTHORIZATION_CODE_LIFETIME', 600); // 10 minutes

/**
 * Handle OAuth2 authorization request
 */
function handleOAuth2Authorize($params) {
    // Validate required parameters
    $required = ['client_id', 'redirect_uri', 'response_type', 'scope'];
    foreach ($required as $param) {
        if (!isset($params[$param])) {
            throw new Exception("Missing required parameter: $param", 400);
        }
    }
    
    $clientId = $params['client_id'];
    $redirectUri = $params['redirect_uri'];
    $responseType = $params['response_type'];
    $scope = $params['scope'] ?? 'read';
    $state = $params['state'] ?? '';
    $codeChallenge = $params['code_challenge'] ?? '';
    $codeChallengeMethod = $params['code_challenge_method'] ?? '';
    
    // Validate client
    $client = getOAuth2Client($clientId);
    if (!$client) {
        throw new Exception('Invalid client ID', 400);
    }
    
    // Validate redirect URI
    if (!validateRedirectUri($clientId, $redirectUri)) {
        throw new Exception('Invalid redirect URI', 400);
    }
    
    // Validate response type
    if ($responseType !== 'code') {
        throw new Exception('Unsupported response type', 400);
    }
    
    // Validate scope
    if (!validateScope($scope)) {
        throw new Exception('Invalid scope', 400);
    }
    
    // Generate authorization code
    $authCode = generateAuthorizationCode($clientId, $redirectUri, $scope, $state, $codeChallenge, $codeChallengeMethod);
    
    // Store authorization code
    storeAuthorizationCode($authCode);
    
    // Return redirect URL
    $redirectUrl = $redirectUri . '?code=' . urlencode($authCode['code']);
    if ($state) {
        $redirectUrl .= '&state=' . urlencode($state);
    }
    
    return [
        'redirect_url' => $redirectUrl,
        'authorization_code' => $authCode['code'],
        'expires_in' => OAUTH2_AUTHORIZATION_CODE_LIFETIME
    ];
}

/**
 * Handle OAuth2 token exchange
 */
function handleOAuth2Token($params) {
    // Validate required parameters
    $required = ['grant_type', 'client_id', 'client_secret'];
    foreach ($required as $param) {
        if (!isset($params[$param])) {
            throw new Exception("Missing required parameter: $param", 400);
        }
    }
    
    $grantType = $params['grant_type'];
    $clientId = $params['client_id'];
    $clientSecret = $params['client_secret'];
    
    // Validate client credentials
    $client = validateOAuth2Client($clientId, $clientSecret);
    if (!$client) {
        throw new Exception('Invalid client credentials', 401);
    }
    
    switch ($grantType) {
        case 'authorization_code':
            return handleAuthorizationCodeGrant($params, $client);
            
        case 'refresh_token':
            return handleRefreshTokenGrant($params, $client);
            
        case 'client_credentials':
            return handleClientCredentialsGrant($params, $client);
            
        default:
            throw new Exception('Unsupported grant type', 400);
    }
}

/**
 * Handle authorization code grant
 */
function handleAuthorizationCodeGrant($params, $client) {
    $required = ['code', 'redirect_uri'];
    foreach ($required as $param) {
        if (!isset($params[$param])) {
            throw new Exception("Missing required parameter: $param", 400);
        }
    }
    
    $code = $params['code'];
    $redirectUri = $params['redirect_uri'];
    $codeVerifier = $params['code_verifier'] ?? '';
    
    // Get stored authorization code
    $authCode = getAuthorizationCode($code);
    if (!$authCode) {
        throw new Exception('Invalid authorization code', 400);
    }
    
    // Validate redirect URI
    if ($authCode['redirect_uri'] !== $redirectUri) {
        throw new Exception('Redirect URI mismatch', 400);
    }
    
    // Validate PKCE if used
    if ($authCode['code_challenge']) {
        if (!$codeVerifier) {
            throw new Exception('Code verifier required', 400);
        }
        
        if (!validatePKCE($authCode['code_challenge'], $authCode['code_challenge_method'], $codeVerifier)) {
            throw new Exception('Invalid code verifier', 400);
        }
    }
    
    // Check if code is expired
    if (time() > $authCode['expires_at']) {
        throw new Exception('Authorization code expired', 400);
    }
    
    // Check if code was already used
    if ($authCode['used']) {
        throw new Exception('Authorization code already used', 400);
    }
    
    // Mark code as used
    markAuthorizationCodeUsed($code);
    
    // Generate access token
    $accessToken = generateAccessToken($authCode['user_id'], $client['id'], $authCode['scope']);
    
    // Generate refresh token
    $refreshToken = generateRefreshToken($authCode['user_id'], $client['id']);
    
    // Store tokens
    storeAccessToken($accessToken);
    storeRefreshToken($refreshToken);
    
    // Log token generation
    logOAuth2Event('token_generated', [
        'user_id' => $authCode['user_id'],
        'client_id' => $client['id'],
        'scope' => $authCode['scope']
    ]);
    
    return [
        'access_token' => $accessToken['token'],
        'token_type' => 'Bearer',
        'expires_in' => OAUTH2_ACCESS_TOKEN_LIFETIME,
        'refresh_token' => $refreshToken['token'],
        'scope' => $authCode['scope']
    ];
}

/**
 * Handle refresh token grant
 */
function handleRefreshTokenGrant($params, $client) {
    if (!isset($params['refresh_token'])) {
        throw new Exception('Missing refresh token', 400);
    }
    
    $refreshToken = $params['refresh_token'];
    
    // Get stored refresh token
    $storedToken = getRefreshToken($refreshToken);
    if (!$storedToken) {
        throw new Exception('Invalid refresh token', 400);
    }
    
    // Check if token is expired
    if (time() > $storedToken['expires_at']) {
        throw new Exception('Refresh token expired', 400);
    }
    
    // Check if token was revoked
    if ($storedToken['revoked']) {
        throw new Exception('Refresh token revoked', 400);
    }
    
    // Revoke old access token
    revokeAccessToken($storedToken['access_token_id']);
    
    // Generate new access token
    $accessToken = generateAccessToken($storedToken['user_id'], $client['id'], $storedToken['scope']);
    
    // Generate new refresh token
    $newRefreshToken = generateRefreshToken($storedToken['user_id'], $client['id']);
    
    // Store new tokens
    storeAccessToken($accessToken);
    storeRefreshToken($newRefreshToken);
    
    // Revoke old refresh token
    revokeRefreshToken($refreshToken);
    
    // Log token refresh
    logOAuth2Event('token_refreshed', [
        'user_id' => $storedToken['user_id'],
        'client_id' => $client['id']
    ]);
    
    return [
        'access_token' => $accessToken['token'],
        'token_type' => 'Bearer',
        'expires_in' => OAUTH2_ACCESS_TOKEN_LIFETIME,
        'refresh_token' => $newRefreshToken['token'],
        'scope' => $storedToken['scope']
    ];
}

/**
 * Handle client credentials grant
 */
function handleClientCredentialsGrant($params, $client) {
    // Generate access token for client
    $accessToken = generateAccessToken(null, $client['id'], 'client');
    
    // Store token
    storeAccessToken($accessToken);
    
    // Log client credentials grant
    logOAuth2Event('client_credentials_grant', [
        'client_id' => $client['id']
    ]);
    
    return [
        'access_token' => $accessToken['token'],
        'token_type' => 'Bearer',
        'expires_in' => OAUTH2_ACCESS_TOKEN_LIFETIME,
        'scope' => 'client'
    ];
}

/**
 * Handle OAuth2 token revocation
 */
function handleOAuth2Revoke($params) {
    if (!isset($params['token'])) {
        throw new Exception('Missing token', 400);
    }
    
    $token = $params['token'];
    $tokenTypeHint = $params['token_type_hint'] ?? 'access_token';
    
    // Revoke token based on type hint
    switch ($tokenTypeHint) {
        case 'access_token':
            revokeAccessToken($token);
            break;
            
        case 'refresh_token':
            revokeRefreshToken($token);
            break;
            
        default:
            // Try both types
            if (!revokeAccessToken($token)) {
                revokeRefreshToken($token);
            }
    }
    
    // Log token revocation
    logOAuth2Event('token_revoked', [
        'token' => $token,
        'token_type' => $tokenTypeHint
    ]);
    
    return ['success' => true];
}

/**
 * Handle OAuth2 user info
 */
function handleOAuth2UserInfo($params) {
    // Get user from access token
    $accessToken = getAccessTokenFromHeader();
    if (!$accessToken) {
        throw new Exception('Missing access token', 401);
    }
    
    $tokenData = getAccessToken($accessToken);
    if (!$tokenData) {
        throw new Exception('Invalid access token', 401);
    }
    
    // Check if token is expired
    if (time() > $tokenData['expires_at']) {
        throw new Exception('Access token expired', 401);
    }
    
    // Get user information
    $user = getUserById($tokenData['user_id']);
    if (!$user) {
        throw new Exception('User not found', 404);
    }
    
    // Return user info based on scope
    $scope = $tokenData['scope'] ?? 'read';
    return getUserInfoForScope($user, $scope);
}

/**
 * Generate authorization code
 */
function generateAuthorizationCode($clientId, $redirectUri, $scope, $state, $codeChallenge, $codeChallengeMethod) {
    $code = bin2hex(random_bytes(32));
    
    return [
        'code' => $code,
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'scope' => $scope,
        'state' => $state,
        'code_challenge' => $codeChallenge,
        'code_challenge_method' => $codeChallengeMethod,
        'user_id' => $_SESSION['user_id'] ?? null,
        'expires_at' => time() + OAUTH2_AUTHORIZATION_CODE_LIFETIME,
        'used' => false
    ];
}

/**
 * Generate access token
 */
function generateAccessToken($userId, $clientId, $scope) {
    $token = bin2hex(random_bytes(32));
    
    return [
        'token' => $token,
        'user_id' => $userId,
        'client_id' => $clientId,
        'scope' => $scope,
        'expires_at' => time() + OAUTH2_ACCESS_TOKEN_LIFETIME,
        'created_at' => time()
    ];
}

/**
 * Generate refresh token
 */
function generateRefreshToken($userId, $clientId) {
    $token = bin2hex(random_bytes(32));
    
    return [
        'token' => $token,
        'user_id' => $userId,
        'client_id' => $clientId,
        'scope' => 'refresh',
        'expires_at' => time() + OAUTH2_REFRESH_TOKEN_LIFETIME,
        'created_at' => time()
    ];
}

/**
 * Validate PKCE code challenge
 */
function validatePKCE($codeChallenge, $method, $codeVerifier) {
    switch ($method) {
        case 'S256':
            $expectedChallenge = base64url_encode(hash('sha256', $codeVerifier, true));
            return hash_equals($codeChallenge, $expectedChallenge);
            
        case 'plain':
            return hash_equals($codeChallenge, $codeVerifier);
            
        default:
            return false;
    }
}

/**
 * Base64URL encode
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Get OAuth2 client
 */
function getOAuth2Client($clientId) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM oauth2_clients WHERE client_id = ? AND active = 1");
    $stmt->execute([$clientId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Validate OAuth2 client credentials
 */
function validateOAuth2Client($clientId, $clientSecret) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM oauth2_clients WHERE client_id = ? AND client_secret = ? AND active = 1");
    $stmt->execute([$clientId, $clientSecret]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Validate redirect URI
 */
function validateRedirectUri($clientId, $redirectUri) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM oauth2_client_redirect_uris WHERE client_id = ? AND redirect_uri = ?");
    $stmt->execute([$clientId, $redirectUri]);
    return $stmt->fetch() !== false;
}

/**
 * Validate scope
 */
function validateScope($scope) {
    $validScopes = ['read', 'write', 'admin', 'client'];
    $requestedScopes = explode(' ', $scope);
    
    foreach ($requestedScopes as $requestedScope) {
        if (!in_array($requestedScope, $validScopes)) {
            return false;
        }
    }
    
    return true;
}

/**
 * Store authorization code
 */
function storeAuthorizationCode($authCode) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO oauth2_authorization_codes 
        (code, client_id, user_id, redirect_uri, scope, state, code_challenge, code_challenge_method, expires_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $authCode['code'],
        $authCode['client_id'],
        $authCode['user_id'],
        $authCode['redirect_uri'],
        $authCode['scope'],
        $authCode['state'],
        $authCode['code_challenge'],
        $authCode['code_challenge_method'],
        $authCode['expires_at']
    ]);
}

/**
 * Get authorization code
 */
function getAuthorizationCode($code) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM oauth2_authorization_codes WHERE code = ?");
    $stmt->execute([$code]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Mark authorization code as used
 */
function markAuthorizationCodeUsed($code) {
    $pdo = get_db();
    $stmt = $pdo->prepare("UPDATE oauth2_authorization_codes SET used = 1 WHERE code = ?");
    $stmt->execute([$code]);
}

/**
 * Store access token
 */
function storeAccessToken($accessToken) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO oauth2_access_tokens 
        (token, user_id, client_id, scope, expires_at, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $accessToken['token'],
        $accessToken['user_id'],
        $accessToken['client_id'],
        $accessToken['scope'],
        $accessToken['expires_at'],
        $accessToken['created_at']
    ]);
}

/**
 * Store refresh token
 */
function storeRefreshToken($refreshToken) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO oauth2_refresh_tokens 
        (token, user_id, client_id, scope, expires_at, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $refreshToken['token'],
        $refreshToken['user_id'],
        $refreshToken['client_id'],
        $refreshToken['scope'],
        $refreshToken['expires_at'],
        $refreshToken['created_at']
    ]);
}

/**
 * Get access token from header
 */
function getAccessTokenFromHeader() {
    $headers = getallheaders();
    
    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
        if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

/**
 * Get access token data
 */
function getAccessToken($token) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM oauth2_access_tokens WHERE token = ?");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get refresh token data
 */
function getRefreshToken($token) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM oauth2_refresh_tokens WHERE token = ?");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Revoke access token
 */
function revokeAccessToken($token) {
    $pdo = get_db();
    $stmt = $pdo->prepare("UPDATE oauth2_access_tokens SET revoked = 1 WHERE token = ?");
    return $stmt->execute([$token]);
}

/**
 * Revoke refresh token
 */
function revokeRefreshToken($token) {
    $pdo = get_db();
    $stmt = $pdo->prepare("UPDATE oauth2_refresh_tokens SET revoked = 1 WHERE token = ?");
    return $stmt->execute([$token]);
}

/**
 * Get user info for specific scope
 */
function getUserInfoForScope($user, $scope) {
    $scopes = explode(' ', $scope);
    $userInfo = [];
    
    if (in_array('read', $scopes)) {
        $userInfo['id'] = $user['id'];
        $userInfo['username'] = $user['username'];
        $userInfo['email'] = $user['email'];
        $userInfo['created_at'] = $user['created_at'];
    }
    
    if (in_array('write', $scopes)) {
        $userInfo['profile'] = [
            'bio' => $user['bio'] ?? '',
            'avatar' => $user['avatar'] ?? '',
            'preferences' => json_decode($user['preferences'] ?? '{}', true)
        ];
    }
    
    if (in_array('admin', $scopes)) {
        $userInfo['admin'] = [
            'role' => $user['role'] ?? 'user',
            'permissions' => json_decode($user['permissions'] ?? '[]', true)
        ];
    }
    
    return $userInfo;
}

/**
 * Log OAuth2 event
 */
function logOAuth2Event($event, $data) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO oauth2_events 
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
