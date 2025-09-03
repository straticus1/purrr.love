<?php
/**
 * 🛡️ Purrr.love CSRF Protection System
 * Comprehensive CSRF protection for web forms and API endpoints
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Generate CSRF token for form
 */
function generateCSRFToken($formId = 'default') {
    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    
    // Generate new token if not exists or expired
    if (!isset($_SESSION['csrf_tokens'][$formId]) || 
        !isset($_SESSION['csrf_tokens'][$formId]['expires']) ||
        time() > $_SESSION['csrf_tokens'][$formId]['expires']) {
        
        $_SESSION['csrf_tokens'][$formId] = [
            'token' => bin2hex(random_bytes(32)),
            'expires' => time() + 3600, // 1 hour expiration
            'created' => time()
        ];
    }
    
    return $_SESSION['csrf_tokens'][$formId]['token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token, $formId = 'default') {
    if (!isset($_SESSION['csrf_tokens'][$formId])) {
        logSecurityEvent('csrf_token_missing', [
            'form_id' => $formId,
            'ip_address' => getClientIP()
        ]);
        return false;
    }
    
    $storedToken = $_SESSION['csrf_tokens'][$formId];
    
    // Check if token is expired
    if (time() > $storedToken['expires']) {
        logSecurityEvent('csrf_token_expired', [
            'form_id' => $formId,
            'ip_address' => getClientIP()
        ]);
        unset($_SESSION['csrf_tokens'][$formId]);
        return false;
    }
    
    // Validate token
    if (!hash_equals($storedToken['token'], $token)) {
        logSecurityEvent('csrf_token_mismatch', [
            'form_id' => $formId,
            'ip_address' => getClientIP(),
            'expected_token' => $storedToken['token'],
            'received_token' => $token
        ]);
        return false;
    }
    
    // Token is valid - regenerate for next use
    $_SESSION['csrf_tokens'][$formId]['token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$formId]['created'] = time();
    
    return true;
}

/**
 * Generate CSRF token field for HTML forms
 */
function generateCSRFTokenField($formId = 'default') {
    $token = generateCSRFToken($formId);
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Validate CSRF token from POST request
 */
function validateCSRFPostRequest($formId = 'default') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }
    
    $token = $_POST['_csrf_token'] ?? '';
    if (empty($token)) {
        logSecurityEvent('csrf_token_missing_post', [
            'form_id' => $formId,
            'ip_address' => getClientIP(),
            'post_data' => array_keys($_POST)
        ]);
        return false;
    }
    
    return validateCSRFToken($token, $formId);
}

/**
 * Validate CSRF token from JSON request
 */
function validateCSRFJsonRequest($formId = 'default') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }
    
    $input = file_get_contents('php://input');
    $jsonData = json_decode($input, true);
    
    if (!$jsonData || !isset($jsonData['_csrf_token'])) {
        logSecurityEvent('csrf_token_missing_json', [
            'form_id' => $formId,
            'ip_address' => getClientIP(),
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'unknown'
        ]);
        return false;
    }
    
    return validateCSRFToken($jsonData['_csrf_token'], $formId);
}

/**
 * Validate CSRF token from header
 */
function validateCSRFHeaderRequest($formId = 'default') {
    $headers = getallheaders();
    $token = $headers['X-CSRF-Token'] ?? '';
    
    if (empty($token)) {
        logSecurityEvent('csrf_token_missing_header', [
            'form_id' => $formId,
            'ip_address' => getClientIP(),
            'headers' => array_keys($headers)
        ]);
        return false;
    }
    
    return validateCSRFToken($token, $formId);
}

/**
 * Require CSRF validation for endpoint
 */
function requireCSRFValidation($formId = 'default', $method = 'auto') {
    $valid = false;
    
    switch ($method) {
        case 'post':
            $valid = validateCSRFPostRequest($formId);
            break;
            
        case 'json':
            $valid = validateCSRFJsonRequest($formId);
            break;
            
        case 'header':
            $valid = validateCSRFHeaderRequest($formId);
            break;
            
        case 'auto':
        default:
            // Try all methods
            $valid = validateCSRFPostRequest($formId) ||
                     validateCSRFJsonRequest($formId) ||
                     validateCSRFHeaderRequest($formId);
            break;
    }
    
    if (!$valid) {
        logSecurityEvent('csrf_validation_failed', [
            'form_id' => $formId,
            'method' => $method,
            'ip_address' => getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
        
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'CSRF_VALIDATION_FAILED',
                'message' => 'CSRF token validation failed'
            ]
        ]);
        exit;
    }
    
    return true;
}

/**
 * Clean up expired CSRF tokens
 */
function cleanupExpiredCSRFTokens() {
    if (!isset($_SESSION['csrf_tokens'])) {
        return;
    }
    
    $currentTime = time();
    foreach ($_SESSION['csrf_tokens'] as $formId => $tokenData) {
        if ($currentTime > $tokenData['expires']) {
            unset($_SESSION['csrf_tokens'][$formId]);
        }
    }
}

/**
 * Get CSRF token statistics
 */
function getCSRFTokenStats() {
    if (!isset($_SESSION['csrf_tokens'])) {
        return [
            'total_tokens' => 0,
            'active_tokens' => 0,
            'expired_tokens' => 0
        ];
    }
    
    $currentTime = time();
    $total = count($_SESSION['csrf_tokens']);
    $active = 0;
    $expired = 0;
    
    foreach ($_SESSION['csrf_tokens'] as $tokenData) {
        if ($currentTime > $tokenData['expires']) {
            $expired++;
        } else {
            $active++;
        }
    }
    
    return [
        'total_tokens' => $total,
        'active_tokens' => $active,
        'expired_tokens' => $expired
    ];
}

/**
 * Generate CSRF token for AJAX requests
 */
function generateCSRFAjaxToken() {
    return generateCSRFToken('ajax');
}

/**
 * Validate CSRF token for AJAX requests
 */
function validateCSRFAjaxRequest() {
    return requireCSRFValidation('ajax', 'header');
}

/**
 * Generate CSRF token for file uploads
 */
function generateCSRFUploadToken() {
    return generateCSRFToken('upload');
}

/**
 * Validate CSRF token for file uploads
 */
function validateCSRFUploadRequest() {
    return requireCSRFValidation('upload', 'post');
}

/**
 * Generate CSRF token for API endpoints
 */
function generateCSRFAPIToken() {
    return generateCSRFToken('api');
}

/**
 * Validate CSRF token for API endpoints
 */
function validateCSRFAPIRequest() {
    return requireCSRFValidation('api', 'json');
}

/**
 * Add CSRF protection to form
 */
function addCSRFProtection($formId = 'default') {
    // Clean up expired tokens
    cleanupExpiredCSRFTokens();
    
    // Generate token field
    return generateCSRFTokenField($formId);
}

/**
 * Verify CSRF protection for form submission
 */
function verifyCSRFProtection($formId = 'default') {
    return requireCSRFValidation($formId, 'auto');
}

// Clean up expired tokens on script start
cleanupExpiredCSRFTokens();
?>
