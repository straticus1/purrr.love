<?php
/**
 * 🚀 Purrr.love API Entry Point
 * Complete API ecosystem with OAuth2 and API key authentication
 */

// Define secure access constant
define('SECURE_ACCESS', true);

require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/crypto.php';
require_once '../includes/cat_behavior.php';
require_once '../includes/oauth2.php';
require_once '../includes/api_keys.php';
require_once '../includes/rate_limiting.php';
require_once '../includes/authentication.php';

// Set JSON content type
header('Content-Type: application/json');

// Secure CORS configuration
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (isAllowedOrigin($origin)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    // Log unauthorized origin attempt
    logSecurityEvent('unauthorized_cors_attempt', ['origin' => $origin, 'ip' => getClientIP()]);
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize API response
$response = [
    'success' => false,
    'data' => null,
    'error' => null,
    'meta' => [
        'timestamp' => date('c'),
        'request_id' => generateRequestId(),
        'version' => '1.0.0'
    ]
];

try {
    // Parse request
    $request = parseRequest();
    $path = $request['path'];
    $method = $request['method'];
    $params = $request['params'];
    $headers = $request['headers'];
    
    // Rate limiting check
    $rateLimitResult = checkRateLimit($request);
    if (!$rateLimitResult['allowed']) {
        throw new Exception('Rate limit exceeded', 429);
    }
    
    // Add rate limit headers
    addRateLimitHeaders($rateLimitResult);
    
    // Route the request
    $result = routeRequest($path, $method, $params, $headers);
    
    // Success response
    $response['success'] = true;
    $response['data'] = $result;
    
} catch (Exception $e) {
    // Log the error securely
    logSecurityEvent('api_error', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'endpoint' => $path ?? 'unknown',
        'method' => $method ?? 'unknown'
    ], 'ERROR');
    
    // Error response (production-safe)
    $response['success'] = false;
    $response['error'] = [
        'code' => getErrorCode($e->getCode()),
        'message' => defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE ? $e->getMessage() : 'An error occurred',
        'details' => null // Never expose stack traces in production
    ];
    
    // Set HTTP status code
    http_response_code($e->getCode() ?: 500);
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);

/**
 * Parse incoming request
 */
function parseRequest() {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/api', '', $path);
    $path = trim($path, '/');
    
    $method = $_SERVER['REQUEST_METHOD'];
    $params = [];
    
    // Parse and validate query parameters
    if (isset($_GET)) {
        $params = array_merge($params, array_map('sanitizeInput', $_GET));
    }
    
    // Parse and validate POST/PUT data
    if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $input = file_get_contents('php://input');
        $jsonData = json_decode($input, true);
        if ($jsonData) {
            $params = array_merge($params, array_map('sanitizeInput', $jsonData));
        } else {
            $params = array_merge($params, array_map('sanitizeInput', $_POST));
        }
    }
    
    // Parse headers
    $headers = getallheaders();
    
    return [
        'path' => $path,
        'method' => $method,
        'params' => $params,
        'headers' => $headers
    ];
}

/**
 * Route request to appropriate handler
 */
function routeRequest($path, $method, $params, $headers) {
    $segments = explode('/', $path);
    $version = $segments[0] ?? 'v1';
    $resource = $segments[1] ?? '';
    $action = $segments[2] ?? '';
    $id = $segments[3] ?? null;
    
    // Validate API version
    if (!in_array($version, ['v1'])) {
        throw new Exception('Unsupported API version', 400);
    }
    
    // Handle OAuth2 endpoints
    if ($resource === 'oauth') {
        return handleOAuth2($action, $params, $headers);
    }
    
    // Authenticate request
    $user = authenticateRequest($headers);
    
    // Route to resource handlers
    switch ($resource) {
        case 'auth':
            return handleAuth($action, $params, $user);
            
        case 'keys':
            return handleApiKeys($action, $id, $params, $user);
            
        case 'cats':
            return handleCats($action, $id, $params, $user);
            
        case 'games':
            return handleGames($action, $id, $params, $user);
            
        case 'breeding':
            return handleBreeding($action, $id, $params, $user);
            
        case 'quests':
            return handleQuests($action, $id, $params, $user);
            
        case 'store':
            return handleStore($action, $id, $params, $user);
            
        case 'economy':
            return handleEconomy($action, $id, $params, $user);
            
        case 'social':
            return handleSocial($action, $id, $params, $user);
            
        case 'genetics':
            return handleGenetics($action, $id, $params, $user);
            
        default:
            throw new Exception('Resource not found', 404);
    }
}

/**
 * Handle OAuth2 endpoints
 */
function handleOAuth2($action, $params, $headers) {
    switch ($action) {
        case 'authorize':
            return handleOAuth2Authorize($params);
            
        case 'token':
            return handleOAuth2Token($params);
            
        case 'revoke':
            return handleOAuth2Revoke($params);
            
        case 'userinfo':
            return handleOAuth2UserInfo($params);
            
        default:
            throw new Exception('OAuth2 endpoint not found', 404);
    }
}

/**
 * Handle authentication endpoints
 */
function handleAuth($action, $params, $user) {
    switch ($action) {
        case 'profile':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getUserProfile($user['id']);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                return updateUserProfile($user['id'], $params);
            }
            break;
            
        case 'logout':
            return logoutUser($user['id']);
            
        default:
            throw new Exception('Auth endpoint not found', 404);
    }
}

/**
 * Handle API key management
 */
function handleApiKeys($action, $id, $params, $user) {
    switch ($action) {
        case '':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getUserApiKeys($user['id']);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return generateApiKey($user['id'], $params);
            }
            break;
            
        case $id:
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                return updateApiKey($id, $user['id'], $params);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                return revokeApiKey($id, $user['id']);
            }
            break;
            
        default:
            if ($action === 'usage' && $id) {
                return getApiKeyUsage($id, $user['id']);
            }
            throw new Exception('API key endpoint not found', 404);
    }
}

/**
 * Handle cat management
 */
function handleCats($action, $id, $params, $user) {
    switch ($action) {
        case '':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getUserCats($user['id']);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return createCat($user['id'], $params);
            }
            break;
            
        case $id:
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getCatDetails($id, $user['id']);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                return updateCat($id, $user['id'], $params);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                return deleteCat($id, $user['id']);
            }
            break;
            
        default:
            if ($action === 'stats' && $id) {
                return getCatStats($id, $user['id']);
            } elseif ($action === 'personality' && $id) {
                return getCatPersonality($id, $user['id']);
            } elseif ($action === 'feed' && $id) {
                return feedCat($id, $user['id'], $params);
            } elseif ($action === 'play' && $id) {
                return playWithCat($id, $user['id'], $params);
            } elseif ($action === 'groom' && $id) {
                return groomCat($id, $user['id'], $params);
            }
            throw new Exception('Cat endpoint not found', 404);
    }
}

/**
 * Handle gaming endpoints
 */
function handleGames($action, $id, $params, $user) {
    switch ($action) {
        case '':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getAvailableGames();
            }
            break;
            
        case 'play':
            if ($id && $_SERVER['REQUEST_METHOD'] === 'POST') {
                return playGame($id, $user['id'], $params);
            }
            break;
            
        case 'history':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getGameHistory($user['id'], $params);
            }
            break;
            
        case 'leaderboard':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getGameLeaderboard($params);
            }
            break;
            
        default:
            throw new Exception('Game endpoint not found', 404);
    }
}

/**
 * Handle breeding endpoints
 */
function handleBreeding($action, $id, $params, $user) {
    switch ($action) {
        case 'pairs':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getBreedingPairs($user['id']);
            }
            break;
            
        case 'breed':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return startBreeding($user['id'], $params);
            }
            break;
            
        case 'history':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getBreedingHistory($user['id']);
            }
            break;
            
        case 'offspring':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getBreedingOffspring($user['id']);
            }
            break;
            
        default:
            throw new Exception('Breeding endpoint not found', 404);
    }
}

/**
 * Handle quest endpoints
 */
function handleQuests($action, $id, $params, $user) {
    switch ($action) {
        case '':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getAvailableQuests($user['id']);
            }
            break;
            
        case 'start':
            if ($id && $_SERVER['REQUEST_METHOD'] === 'POST') {
                return startQuest($id, $user['id']);
            }
            break;
            
        case 'progress':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getQuestProgress($user['id']);
            }
            break;
            
        default:
            throw new Exception('Quest endpoint not found', 404);
    }
}

/**
 * Handle store endpoints
 */
function handleStore($action, $id, $params, $user) {
    switch ($action) {
        case 'items':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getStoreItems();
            }
            break;
            
        case 'purchase':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return purchaseItem($user['id'], $params);
            }
            break;
            
        case 'inventory':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getUserInventory($user['id']);
            }
            break;
            
        default:
            throw new Exception('Store endpoint not found', 404);
    }
}

/**
 * Handle economy endpoints
 */
function handleEconomy($action, $id, $params, $user) {
    switch ($action) {
        case 'balance':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getUserCryptoBalance($user['id']);
            }
            break;
            
        case 'deposit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return createCryptoDeposit($user['id'], $params);
            }
            break;
            
        case 'withdraw':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return createCryptoWithdrawal($user['id'], $params);
            }
            break;
            
        default:
            throw new Exception('Economy endpoint not found', 404);
    }
}

/**
 * Handle social endpoints
 */
function handleSocial($action, $id, $params, $user) {
    switch ($action) {
        case 'friends':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getUserFriends($user['id']);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return addFriend($user['id'], $params);
            }
            break;
            
        case 'messages':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getUserMessages($user['id']);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return sendMessage($user['id'], $params);
            }
            break;
            
        case 'neighborhoods':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getNeighborhoods($user['id']);
            }
            break;
            
        default:
            throw new Exception('Social endpoint not found', 404);
    }
}

/**
 * Handle genetics endpoints
 */
function handleGenetics($action, $id, $params, $user) {
    switch ($action) {
        case 'traits':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getGeneticTraits();
            }
            break;
            
        case 'predictions':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return getBreedingPredictions($params);
            }
            break;
            
        default:
            throw new Exception('Genetics endpoint not found', 404);
    }
}

/**
 * Generate unique request ID
 */
function generateRequestId() {
    return 'req_' . uniqid() . '_' . substr(md5(microtime()), 0, 8);
}

/**
 * Get error code from HTTP status
 */
function getErrorCode($httpCode) {
    $errorCodes = [
        400 => 'BAD_REQUEST',
        401 => 'UNAUTHORIZED',
        403 => 'FORBIDDEN',
        404 => 'NOT_FOUND',
        422 => 'VALIDATION_ERROR',
        429 => 'RATE_LIMIT_EXCEEDED',
        500 => 'INTERNAL_SERVER_ERROR'
    ];
    
    return $errorCodes[$httpCode] ?? 'UNKNOWN_ERROR';
}
