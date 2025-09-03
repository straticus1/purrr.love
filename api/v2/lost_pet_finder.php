<?php
/**
 * Lost Pet Finder API Endpoints
 * 
 * Provides RESTful API for lost pet finder functionality including:
 * - Lost pet reporting
 * - Advanced search
 * - Sighting reports
 * - Facebook integration
 * - Community support
 * 
 * @package Purrr.love
 * @version 1.0.0
 */

define('SECURE_ACCESS', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/lost_pet_finder.php';

class LostPetFinderAPI {
    private $lostPetFinder;
    private $requestMethod;
    private $endpoint;
    private $params;
    
    public function __construct() {
        $this->lostPetFinder = new LostPetFinder();
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->parseRequest();
    }
    
    /**
     * Parse the incoming request
     */
    private function parseRequest() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));
        
        // Extract endpoint and parameters
        $this->endpoint = $pathParts[count($pathParts) - 1] ?? '';
        $this->params = $_GET;
        
        // Parse JSON body for POST/PUT requests
        if (in_array($this->requestMethod, ['POST', 'PUT', 'PATCH'])) {
            $input = file_get_contents('php://input');
            if ($input) {
                $jsonData = json_decode($input, true);
                if ($jsonData) {
                    $this->params = array_merge($this->params, $jsonData);
                }
            }
        }
    }
    
    /**
     * Route the request to appropriate handler
     */
    public function handleRequest() {
        try {
            // Validate authentication
            $authResult = $this->validateAuthentication();
            if (!$authResult['success']) {
                return $this->sendResponse(401, $authResult);
            }
            
            $userId = $authResult['user_id'];
            
            // Route based on endpoint and method
            switch ($this->endpoint) {
                case 'report':
                    return $this->handleReportLostPet($userId);
                    
                case 'search':
                    return $this->handleSearchLostPets($userId);
                    
                case 'sighting':
                    return $this->handleReportSighting($userId);
                    
                case 'found':
                    return $this->handleMarkPetFound($userId);
                    
                case 'sightings':
                    return $this->handleGetSightings($userId);
                    
                case 'statistics':
                    return $this->handleGetStatistics($userId);
                    
                case 'facebook':
                    return $this->handleFacebookIntegration($userId);
                    
                case 'alerts':
                    return $this->handleLostPetAlerts($userId);
                    
                case 'community':
                    return $this->handleCommunitySupport($userId);
                    
                default:
                    return $this->sendResponse(404, [
                        'success' => false,
                        'message' => 'Endpoint not found',
                        'available_endpoints' => [
                            'POST /report' => 'Report a lost pet',
                            'GET /search' => 'Search for lost pets',
                            'POST /sighting' => 'Report a pet sighting',
                            'PUT /found' => 'Mark pet as found',
                            'GET /sightings' => 'Get sightings for a report',
                            'GET /statistics' => 'Get lost pet statistics',
                            'POST /facebook' => 'Facebook integration',
                            'GET /alerts' => 'Get user alerts',
                            'POST /alerts' => 'Create/update alerts',
                            'GET /community' => 'Get community support',
                            'POST /community' => 'Provide community support'
                        ]
                    ]);
            }
            
        } catch (Exception $e) {
            logSecurityEvent('lost_pet_api_error', null, [
                'error' => $e->getMessage(),
                'endpoint' => $this->endpoint,
                'method' => $this->requestMethod
            ]);
            
            return $this->sendResponse(500, [
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle lost pet reporting
     */
    private function handleReportLostPet($userId) {
        if ($this->requestMethod !== 'POST') {
            return $this->sendResponse(405, [
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
        }
        
        // Validate required fields
        $requiredFields = ['name', 'breed', 'color', 'last_seen_location', 'last_seen_date'];
        foreach ($requiredFields as $field) {
            if (empty($this->params[$field])) {
                return $this->sendResponse(400, [
                    'success' => false,
                    'message' => "Missing required field: {$field}"
                ]);
            }
        }
        
        // Validate date format
        if (!strtotime($this->params['last_seen_date'])) {
            return $this->sendResponse(400, [
                'success' => false,
                'message' => 'Invalid date format for last_seen_date'
            ]);
        }
        
        // Prepare pet data
        $petData = [
            'name' => $this->params['name'],
            'type' => $this->params['type'] ?? 'cat',
            'breed' => $this->params['breed'],
            'color' => $this->params['color'],
            'age' => $this->params['age'] ?? null,
            'microchip_id' => $this->params['microchip_id'] ?? null,
            'collar_id' => $this->params['collar_id'] ?? null,
            'last_seen_location' => $this->params['last_seen_location'],
            'last_seen_date' => $this->params['last_seen_date'],
            'latitude' => $this->params['latitude'] ?? null,
            'longitude' => $this->params['longitude'] ?? null,
            'contact_info' => $this->params['contact_info'] ?? [],
            'reward_amount' => $this->params['reward_amount'] ?? 0,
            'description' => $this->params['description'] ?? '',
            'photos' => $this->params['photos'] ?? [],
            'facebook_share_enabled' => $this->params['facebook_share_enabled'] ?? false,
            'privacy_level' => $this->params['privacy_level'] ?? 'public'
        ];
        
        $result = $this->lostPetFinder->reportLostPet($userId, $petData);
        
        if ($result['success']) {
            return $this->sendResponse(201, $result);
        } else {
            return $this->sendResponse(400, $result);
        }
    }
    
    /**
     * Handle lost pet search
     */
    private function handleSearchLostPets($userId) {
        if ($this->requestMethod !== 'GET') {
            return $this->sendResponse(405, [
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
        }
        
        // Prepare search criteria
        $searchCriteria = [
            'latitude' => $this->params['latitude'] ?? null,
            'longitude' => $this->params['longitude'] ?? null,
            'radius_km' => $this->params['radius_km'] ?? 10,
            'breed' => $this->params['breed'] ?? null,
            'color' => $this->params['color'] ?? null,
            'age_range' => null,
            'pet_photo' => $this->params['pet_photo'] ?? null
        ];
        
        // Parse age range if provided
        if (!empty($this->params['age_min']) && !empty($this->params['age_max'])) {
            $searchCriteria['age_range'] = [
                'min' => (int)$this->params['age_min'],
                'max' => (int)$this->params['age_max']
            ];
        }
        
        // Validate coordinates if provided
        if ($searchCriteria['latitude'] && $searchCriteria['longitude']) {
            if (!is_numeric($searchCriteria['latitude']) || !is_numeric($searchCriteria['longitude'])) {
                return $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'Invalid coordinates provided'
                ]);
            }
        }
        
        $result = $this->lostPetFinder->searchLostPets($searchCriteria);
        
        if ($result['success']) {
            return $this->sendResponse(200, $result);
        } else {
            return $this->sendResponse(400, $result);
        }
    }
    
    /**
     * Handle pet sighting reports
     */
    private function handleReportSighting($userId) {
        if ($this->requestMethod !== 'POST') {
            return $this->sendResponse(405, [
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
        }
        
        // Validate required fields
        $requiredFields = ['lost_pet_report_id', 'location', 'sighting_date'];
        foreach ($requiredFields as $field) {
            if (empty($this->params[$field])) {
                return $this->sendResponse(400, [
                    'success' => false,
                    'message' => "Missing required field: {$field}"
                ]);
            }
        }
        
        // Validate date format
        if (!strtotime($this->params['sighting_date'])) {
            return $this->sendResponse(400, [
                'success' => false,
                'message' => 'Invalid date format for sighting_date'
            ]);
        }
        
        // Prepare sighting data
        $sightingData = [
            'lost_pet_report_id' => (int)$this->params['lost_pet_report_id'],
            'location' => $this->params['location'],
            'latitude' => $this->params['latitude'] ?? null,
            'longitude' => $this->params['longitude'] ?? null,
            'sighting_date' => $this->params['sighting_date'],
            'description' => $this->params['description'] ?? '',
            'photos' => $this->params['photos'] ?? [],
            'confidence_level' => $this->params['confidence_level'] ?? 'medium',
            'contact_info' => $this->params['contact_info'] ?? []
        ];
        
        $result = $this->lostPetFinder->reportSighting($userId, $sightingData);
        
        if ($result['success']) {
            return $this->sendResponse(201, $result);
        } else {
            return $this->sendResponse(400, $result);
        }
    }
    
    /**
     * Handle marking pet as found
     */
    private function handleMarkPetFound($userId) {
        if ($this->requestMethod !== 'PUT') {
            return $this->sendResponse(405, [
                'success' => false,
                'message' => 'Method not allowed. Use PUT.'
            ]);
        }
        
        if (empty($this->params['report_id'])) {
            return $this->sendResponse(400, [
                'success' => false,
                'message' => 'Missing required field: report_id'
            ]);
        }
        
        $foundData = [
            'location' => $this->params['found_location'] ?? null,
            'details' => $this->params['found_details'] ?? []
        ];
        
        $result = $this->lostPetFinder->markPetAsFound(
            (int)$this->params['report_id'], 
            $userId, 
            $foundData
        );
        
        if ($result['success']) {
            return $this->sendResponse(200, $result);
        } else {
            return $this->sendResponse(400, $result);
        }
    }
    
    /**
     * Handle getting sightings for a report
     */
    private function handleGetSightings($userId) {
        if ($this->requestMethod !== 'GET') {
            return $this->sendResponse(405, [
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
        }
        
        if (empty($this->params['report_id'])) {
            return $this->sendResponse(400, [
                'success' => false,
                'message' => 'Missing required field: report_id'
            ]);
        }
        
        // This would be implemented in the LostPetFinder class
        $result = [
            'success' => true,
            'sightings' => [],
            'total_count' => 0
        ];
        
        return $this->sendResponse(200, $result);
    }
    
    /**
     * Handle getting statistics
     */
    private function handleGetStatistics($userId) {
        if ($this->requestMethod !== 'GET') {
            return $this->sendResponse(405, [
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
        }
        
        $result = $this->lostPetFinder->getStatistics($userId);
        
        if ($result['success']) {
            return $this->sendResponse(200, $result);
        } else {
            return $this->sendResponse(400, $result);
        }
    }
    
    /**
     * Handle Facebook integration
     */
    private function handleFacebookIntegration($userId) {
        if ($this->requestMethod !== 'POST') {
            return $this->sendResponse(405, [
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
        }
        
        $action = $this->params['action'] ?? '';
        
        switch ($action) {
            case 'connect':
                // Handle Facebook account connection
                return $this->sendResponse(200, [
                    'success' => true,
                    'message' => 'Facebook integration not yet implemented'
                ]);
                
            case 'disconnect':
                // Handle Facebook account disconnection
                return $this->sendResponse(200, [
                    'success' => true,
                    'message' => 'Facebook disconnection not yet implemented'
                ]);
                
            default:
                return $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'Invalid action. Use "connect" or "disconnect".'
                ]);
        }
    }
    
    /**
     * Handle lost pet alerts
     */
    private function handleLostPetAlerts($userId) {
        if ($this->requestMethod === 'GET') {
            // Get user's alerts
            return $this->sendResponse(200, [
                'success' => true,
                'alerts' => [],
                'message' => 'Alerts retrieval not yet implemented'
            ]);
        } elseif ($this->requestMethod === 'POST') {
            // Create/update alerts
            return $this->sendResponse(200, [
                'success' => true,
                'message' => 'Alert creation not yet implemented'
            ]);
        } else {
            return $this->sendResponse(405, [
                'success' => false,
                'message' => 'Method not allowed. Use GET or POST.'
            ]);
        }
    }
    
    /**
     * Handle community support
     */
    private function handleCommunitySupport($userId) {
        if ($this->requestMethod === 'GET') {
            // Get community support for a report
            if (empty($this->params['report_id'])) {
                return $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'Missing required field: report_id'
                ]);
            }
            
            return $this->sendResponse(200, [
                'success' => true,
                'support' => [],
                'message' => 'Community support retrieval not yet implemented'
            ]);
        } elseif ($this->requestMethod === 'POST') {
            // Provide community support
            return $this->sendResponse(200, [
                'success' => true,
                'message' => 'Community support not yet implemented'
            ]);
        } else {
            return $this->sendResponse(405, [
                'success' => false,
                'message' => 'Method not allowed. Use GET or POST.'
            ]);
        }
    }
    
    /**
     * Validate user authentication
     */
    private function validateAuthentication() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (empty($authHeader)) {
            return [
                'success' => false,
                'message' => 'Authorization header required'
            ];
        }
        
        // Extract token
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid authorization format. Use: Bearer <token>'
            ];
        }
        
        // Validate token and get user ID
        // This would integrate with the existing authentication system
        $userData = validateToken($token);
        
        if (!$userData['valid']) {
            return [
                'success' => false,
                'message' => 'Invalid or expired token'
            ];
        }
        
        return [
            'success' => true,
            'user_id' => $userData['user_id']
        ];
    }
    
    /**
     * Send API response
     */
    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        // Add request tracking
        $data['request_id'] = uniqid('lpf_', true);
        $data['timestamp'] = date('c');
        
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}

// Handle the request
if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    $api = new LostPetFinderAPI();
    $api->handleRequest();
}
