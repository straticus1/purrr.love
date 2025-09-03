<?php
/**
 * 🚀 Purrr.love Advanced Features API v2
 * Blockchain Ownership, ML Personality, Metaverse, Webhooks
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

// Include required systems
require_once '../../includes/blockchain_ownership.php';
require_once '../../includes/ml_cat_personality.php';
require_once '../../includes/metaverse_system.php';
require_once '../../includes/webhook_system.php';

/**
 * Advanced Features API Router
 */
class AdvancedFeaturesAPI {
    private $pdo;
    private $requestId;
    
    public function __construct() {
        $this->pdo = get_db();
        $this->requestId = generateRequestId();
    }
    
    /**
     * Route API requests
     */
    public function routeRequest($path, $method, $data) {
        try {
            // Set secure headers
            setSecureHeaders();
            
            // Log API request
            $this->logAPIRequest($path, $method, $data);
            
            // Route to appropriate handler
            switch ($path) {
                case 'blockchain':
                    return $this->handleBlockchainEndpoints($method, $data);
                case 'ml-personality':
                    return $this->handleMLPersonalityEndpoints($method, $data);
                case 'metaverse':
                    return $this->handleMetaverseEndpoints($method, $data);
                case 'webhooks':
                    return $this->handleWebhookEndpoints($method, $data);
                case 'analytics':
                    return $this->handleAnalyticsEndpoints($method, $data);
                case 'competitions':
                    return $this->handleCompetitionEndpoints($method, $data);
                case 'multiplayer':
                    return $this->handleMultiplayerEndpoints($method, $data);
                default:
                    throw new Exception('Endpoint not found', 404);
            }
            
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
    
    /**
     * Handle blockchain-related endpoints
     */
    private function handleBlockchainEndpoints($method, $data) {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'mint-nft':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->mintCatNFT($data);
                
            case 'transfer-nft':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->transferNFTOwnership($data);
                
            case 'verify-ownership':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->verifyNFTOwnership($data);
                
            case 'user-collection':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getUserNFTCollection($data);
                
            case 'marketplace':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getNFTMarketplaceListings($data);
                
            case 'create-listing':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->createNFTListing($data);
                
            case 'stats':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getBlockchainStats();
                
            default:
                throw new Exception('Invalid blockchain action', 400);
        }
    }
    
    /**
     * Handle ML personality endpoints
     */
    private function handleMLPersonalityEndpoints($method, $data) {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'predict':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->predictCatPersonality($data);
                
            case 'insights':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getPersonalityInsights($data);
                
            case 'behavior-observation':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->recordBehaviorObservation($data);
                
            case 'genetic-data':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->updateGeneticData($data);
                
            case 'training-status':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getMLTrainingStatus();
                
            default:
                throw new Exception('Invalid ML personality action', 400);
        }
    }
    
    /**
     * Handle metaverse endpoints
     */
    private function handleMetaverseEndpoints($method, $data) {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'create-world':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->createMetaverseWorld($data);
                
            case 'join-world':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->joinMetaverseWorld($data);
                
            case 'leave-world':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->leaveMetaverseWorld($data);
                
            case 'active-worlds':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getActiveMetaverseWorlds($data);
                
            case 'world-players':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getWorldPlayers($data);
                
            case 'vr-interaction':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->performVRInteraction($data);
                
            case 'social-space':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->createSocialVRSpace($data);
                
            case 'stats':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getMetaverseStats();
                
            default:
                throw new Exception('Invalid metaverse action', 400);
        }
    }
    
    /**
     * Handle webhook endpoints
     */
    private function handleWebhookEndpoints($method, $data) {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'create':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->createWebhook($data);
                
            case 'update':
                if ($method !== 'PUT') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->updateWebhook($data);
                
            case 'delete':
                if ($method !== 'DELETE') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->deleteWebhook($data);
                
            case 'list':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->listWebhooks($data);
                
            case 'test':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->testWebhook($data);
                
            case 'delivery-logs':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getWebhookDeliveryLogs($data);
                
            default:
                throw new Exception('Invalid webhook action', 400);
        }
    }
    
    /**
     * Handle analytics endpoints
     */
    private function handleAnalyticsEndpoints($method, $data) {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'user-behavior':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getUserBehaviorAnalytics($data);
                
            case 'cat-interactions':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getCatInteractionAnalytics($data);
                
            case 'system-performance':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getSystemPerformanceMetrics($data);
                
            case 'engagement-metrics':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getEngagementMetrics($data);
                
            case 'export':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->exportAnalyticsData($data);
                
            default:
                throw new Exception('Invalid analytics action', 400);
        }
    }
    
    /**
     * Handle competition endpoints
     */
    private function handleCompetitionEndpoints($method, $data) {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'create':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->createCompetition($data);
                
            case 'register':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->registerForCompetition($data);
                
            case 'list':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->listCompetitions($data);
                
            case 'participants':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getCompetitionParticipants($data);
                
            case 'submit-score':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->submitCompetitionScore($data);
                
            case 'results':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getCompetitionResults($data);
                
            default:
                throw new Exception('Invalid competition action', 400);
        }
    }
    
    /**
     * Handle multiplayer endpoints
     */
    private function handleMultiplayerEndpoints($method, $data) {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'create-session':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->createMultiplayerSession($data);
                
            case 'join-session':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->joinMultiplayerSession($data);
                
            case 'leave-session':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->leaveMultiplayerSession($data);
                
            case 'active-sessions':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getActiveMultiplayerSessions($data);
                
            case 'session-status':
                if ($method !== 'GET') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->getMultiplayerSessionStatus($data);
                
            case 'update-game-state':
                if ($method !== 'POST') {
                    throw new Exception('Method not allowed', 405);
                }
                return $this->updateMultiplayerGameState($data);
                
            default:
                throw new Exception('Invalid multiplayer action', 400);
        }
    }
    
    /**
     * Blockchain NFT Methods
     */
    private function mintCatNFT($data) {
        $userId = requireAuthentication(['nft:write']);
        
        $catId = sanitizeInput($data['cat_id'] ?? '');
        $network = sanitizeInput($data['network'] ?? '');
        $metadata = $data['metadata'] ?? [];
        
        if (!$catId) {
            throw new Exception('Cat ID is required', 400);
        }
        
        $result = mintCatNFT($catId, $userId, $network, $metadata);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'NFT minted successfully'
        ];
    }
    
    private function transferNFTOwnership($data) {
        $userId = requireAuthentication(['nft:write']);
        
        $nftId = sanitizeInput($data['nft_id'] ?? '');
        $toUserId = sanitizeInput($data['to_user_id'] ?? '');
        $network = sanitizeInput($data['network'] ?? '');
        
        if (!$nftId || !$toUserId) {
            throw new Exception('NFT ID and recipient user ID are required', 400);
        }
        
        $result = transferNFTOwnership($nftId, $userId, $toUserId, $network);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'NFT ownership transferred successfully'
        ];
    }
    
    private function verifyNFTOwnership($data) {
        $userId = requireAuthentication(['nft:read']);
        
        $nftId = sanitizeInput($_GET['nft_id'] ?? '');
        
        if (!$nftId) {
            throw new Exception('NFT ID is required', 400);
        }
        
        $result = verifyNFTOwnership($nftId, $userId);
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    private function getUserNFTCollection($data) {
        $userId = requireAuthentication(['nft:read']);
        
        $network = sanitizeInput($_GET['network'] ?? '');
        
        $result = getUserNFTCollection($userId, $network);
        
        return [
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ];
    }
    
    private function getNFTMarketplaceListings($data) {
        $filters = [];
        
        if (isset($_GET['network'])) {
            $filters['network'] = sanitizeInput($_GET['network']);
        }
        if (isset($_GET['min_price'])) {
            $filters['min_price'] = floatval($_GET['min_price']);
        }
        if (isset($_GET['max_price'])) {
            $filters['max_price'] = floatval($_GET['max_price']);
        }
        if (isset($_GET['breed'])) {
            $filters['breed'] = sanitizeInput($_GET['breed']);
        }
        
        $result = getNFTMarketplaceListings($filters);
        
        return [
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ];
    }
    
    private function createNFTListing($data) {
        $userId = requireAuthentication(['nft:write']);
        
        $nftId = sanitizeInput($data['nft_id'] ?? '');
        $price = floatval($data['price'] ?? 0);
        $currency = sanitizeInput($data['currency'] ?? 'ETH');
        $duration = intval($data['duration_days'] ?? 30);
        
        if (!$nftId || $price <= 0) {
            throw new Exception('Valid NFT ID and price are required', 400);
        }
        
        $result = createNFTListing($nftId, $userId, $price, $currency, $duration);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'NFT listing created successfully'
        ];
    }
    
    private function getBlockchainStats() {
        $result = getBlockchainStats();
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    /**
     * ML Personality Methods
     */
    private function predictCatPersonality($data) {
        $userId = requireAuthentication(['ml:read']);
        
        $catId = sanitizeInput($data['cat_id'] ?? '');
        $includeConfidence = boolval($data['include_confidence'] ?? true);
        
        if (!$catId) {
            throw new Exception('Cat ID is required', 400);
        }
        
        $result = predictCatPersonality($catId, $includeConfidence);
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    private function getPersonalityInsights($data) {
        $userId = requireAuthentication(['ml:read']);
        
        $catId = sanitizeInput($_GET['cat_id'] ?? '');
        
        if (!$catId) {
            throw new Exception('Cat ID is required', 400);
        }
        
        $result = getPersonalityInsights($catId);
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    private function recordBehaviorObservation($data) {
        $userId = requireAuthentication(['ml:write']);
        
        $catId = sanitizeInput($data['cat_id'] ?? '');
        $behaviorType = sanitizeInput($data['behavior_type'] ?? '');
        $intensityLevel = intval($data['intensity_level'] ?? 5);
        $duration = intval($data['duration_seconds'] ?? 0);
        $environmentalContext = $data['environmental_context'] ?? [];
        
        if (!$catId || !$behaviorType) {
            throw new Exception('Cat ID and behavior type are required', 400);
        }
        
        $result = $this->storeBehaviorObservation($catId, $userId, $behaviorType, $intensityLevel, $duration, $environmentalContext);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Behavior observation recorded successfully'
        ];
    }
    
    private function updateGeneticData($data) {
        $userId = requireAuthentication(['ml:write']);
        
        $catId = sanitizeInput($data['cat_id'] ?? '');
        $geneticMarkers = $data['genetic_markers'] ?? [];
        $heritageScore = intval($data['heritage_score'] ?? 0);
        $coatPattern = sanitizeInput($data['coat_pattern'] ?? '');
        
        if (!$catId) {
            throw new Exception('Cat ID is required', 400);
        }
        
        $result = $this->storeGeneticData($catId, $userId, $geneticMarkers, $heritageScore, $coatPattern);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Genetic data updated successfully'
        ];
    }
    
    private function getMLTrainingStatus() {
        $result = $this->getTrainingStatus();
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    /**
     * Metaverse Methods
     */
    private function createMetaverseWorld($data) {
        $userId = requireAuthentication(['metaverse:write']);
        
        $worldName = sanitizeInput($data['world_name'] ?? '');
        $worldType = sanitizeInput($data['world_type'] ?? '');
        $worldSettings = $data['world_settings'] ?? [];
        $maxPlayers = intval($data['max_players'] ?? 50);
        $accessLevel = sanitizeInput($data['access_level'] ?? 'public');
        
        if (!$worldName || !$worldType) {
            throw new Exception('World name and type are required', 400);
        }
        
        $result = createWorldInstance($worldType, $userId, [
            'world_name' => $worldName,
            'world_settings' => $worldSettings,
            'max_players' => $maxPlayers,
            'access_level' => $accessLevel
        ]);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Metaverse world created successfully'
        ];
    }
    
    private function joinMetaverseWorld($data) {
        $userId = requireAuthentication(['metaverse:write']);
        
        $worldId = sanitizeInput($data['world_id'] ?? '');
        $catId = sanitizeInput($data['cat_id'] ?? '');
        
        if (!$worldId) {
            throw new Exception('World ID is required', 400);
        }
        
        $result = joinWorld($worldId, $userId, $catId);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Joined metaverse world successfully'
        ];
    }
    
    private function leaveMetaverseWorld($data) {
        $userId = requireAuthentication(['metaverse:write']);
        
        $worldId = sanitizeInput($data['world_id'] ?? '');
        
        if (!$worldId) {
            throw new Exception('World ID is required', 400);
        }
        
        $result = leaveWorld($worldId, $userId);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Left metaverse world successfully'
        ];
    }
    
    private function getActiveMetaverseWorlds($data) {
        $result = getActiveWorlds();
        
        return [
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ];
    }
    
    private function getWorldPlayers($data) {
        $worldId = sanitizeInput($_GET['world_id'] ?? '');
        
        if (!$worldId) {
            throw new Exception('World ID is required', 400);
        }
        
        $result = getWorldPlayers($worldId);
        
        return [
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ];
    }
    
    private function performVRInteraction($data) {
        $userId = requireAuthentication(['metaverse:write']);
        
        $worldId = sanitizeInput($data['world_id'] ?? '');
        $interactionType = sanitizeInput($data['interaction_type'] ?? '');
        $targetData = $data['target_data'] ?? [];
        
        if (!$worldId || !$interactionType) {
            throw new Exception('World ID and interaction type are required', 400);
        }
        
        $result = performVRInteraction($worldId, $userId, $interactionType, $targetData);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'VR interaction performed successfully'
        ];
    }
    
    private function createSocialVRSpace($data) {
        $userId = requireAuthentication(['metaverse:write']);
        
        $worldId = sanitizeInput($data['world_id'] ?? '');
        $spaceName = sanitizeInput($data['space_name'] ?? '');
        $spaceType = sanitizeInput($data['space_type'] ?? '');
        $maxCapacity = intval($data['max_capacity'] ?? 20);
        
        if (!$worldId || !$spaceName || !$spaceType) {
            throw new Exception('World ID, space name, and space type are required', 400);
        }
        
        $result = createSocialVRSpace($worldId, $spaceName, $spaceType, $maxCapacity);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Social VR space created successfully'
        ];
    }
    
    private function getMetaverseStats() {
        $result = getMetaverseStats();
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    /**
     * Webhook Methods
     */
    private function createWebhook($data) {
        $userId = requireAuthentication(['webhook:write']);
        
        $webhookUrl = sanitizeInput($data['webhook_url'] ?? '');
        $events = $data['events'] ?? [];
        $secretKey = sanitizeInput($data['secret_key'] ?? '');
        $headers = $data['headers'] ?? [];
        
        if (!$webhookUrl || empty($events)) {
            throw new Exception('Webhook URL and events are required', 400);
        }
        
        $result = createWebhook($userId, $webhookUrl, $events, $secretKey, $headers);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Webhook created successfully'
        ];
    }
    
    private function updateWebhook($data) {
        $userId = requireAuthentication(['webhook:write']);
        
        $webhookId = sanitizeInput($data['webhook_id'] ?? '');
        $updates = $data['updates'] ?? [];
        
        if (!$webhookId) {
            throw new Exception('Webhook ID is required', 400);
        }
        
        $result = updateWebhook($webhookId, $userId, $updates);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Webhook updated successfully'
        ];
    }
    
    private function deleteWebhook($data) {
        $userId = requireAuthentication(['webhook:write']);
        
        $webhookId = sanitizeInput($data['webhook_id'] ?? '');
        
        if (!$webhookId) {
            throw new Exception('Webhook ID is required', 400);
        }
        
        $result = deleteWebhook($webhookId, $userId);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Webhook deleted successfully'
        ];
    }
    
    private function listWebhooks($data) {
        $userId = requireAuthentication(['webhook:read']);
        
        $result = $this->getUserWebhooks($userId);
        
        return [
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ];
    }
    
    private function testWebhook($data) {
        $userId = requireAuthentication(['webhook:write']);
        
        $webhookId = sanitizeInput($data['webhook_id'] ?? '');
        
        if (!$webhookId) {
            throw new Exception('Webhook ID is required', 400);
        }
        
        $result = $this->testWebhookDelivery($webhookId, $userId);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Webhook test completed'
        ];
    }
    
    private function getWebhookDeliveryLogs($data) {
        $userId = requireAuthentication(['webhook:read']);
        
        $webhookId = sanitizeInput($_GET['webhook_id'] ?? '');
        $limit = intval($_GET['limit'] ?? 50);
        
        $result = $this->getWebhookLogs($webhookId, $userId, $limit);
        
        return [
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ];
    }
    
    /**
     * Analytics Methods
     */
    private function getUserBehaviorAnalytics($data) {
        $userId = requireAuthentication(['analytics:read']);
        
        $startDate = sanitizeInput($_GET['start_date'] ?? '');
        $endDate = sanitizeInput($_GET['end_date'] ?? '');
        
        $result = $this->getUserBehaviorData($userId, $startDate, $endDate);
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    private function getCatInteractionAnalytics($data) {
        $userId = requireAuthentication(['analytics:read']);
        
        $catId = sanitizeInput($_GET['cat_id'] ?? '');
        $startDate = sanitizeInput($_GET['start_date'] ?? '');
        $endDate = sanitizeInput($_GET['end_date'] ?? '');
        
        $result = $this->getCatInteractionData($userId, $catId, $startDate, $endDate);
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    private function getSystemPerformanceMetrics($data) {
        $result = $this->getPerformanceData();
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    private function getEngagementMetrics($data) {
        $userId = requireAuthentication(['analytics:read']);
        
        $result = $this->getEngagementData($userId);
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    private function exportAnalyticsData($data) {
        $userId = requireAuthentication(['analytics:export']);
        
        $dataType = sanitizeInput($_GET['type'] ?? '');
        $format = sanitizeInput($_GET['format'] ?? 'json');
        $startDate = sanitizeInput($_GET['start_date'] ?? '');
        $endDate = sanitizeInput($_GET['end_date'] ?? '');
        
        $result = $this->exportAnalytics($userId, $dataType, $format, $startDate, $endDate);
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    /**
     * Competition Methods
     */
    private function createCompetition($data) {
        $userId = requireAuthentication(['competition:write']);
        
        $competitionName = sanitizeInput($data['competition_name'] ?? '');
        $competitionType = sanitizeInput($data['competition_type'] ?? '');
        $startDate = sanitizeInput($data['start_date'] ?? '');
        $endDate = sanitizeInput($data['end_date'] ?? '');
        $entryFee = floatval($data['entry_fee'] ?? 0);
        $maxParticipants = intval($data['max_participants'] ?? 0);
        $prizePool = $data['prize_pool'] ?? [];
        $competitionRules = $data['competition_rules'] ?? [];
        
        if (!$competitionName || !$competitionType || !$startDate || !$endDate) {
            throw new Exception('Competition name, type, start date, and end date are required', 400);
        }
        
        $result = $this->createCompetitionRecord($userId, $competitionName, $competitionType, $startDate, $endDate, $entryFee, $maxParticipants, $prizePool, $competitionRules);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Competition created successfully'
        ];
    }
    
    private function registerForCompetition($data) {
        $userId = requireAuthentication(['competition:write']);
        
        $competitionId = sanitizeInput($data['competition_id'] ?? '');
        $catId = sanitizeInput($data['cat_id'] ?? '');
        
        if (!$competitionId || !$catId) {
            throw new Exception('Competition ID and cat ID are required', 400);
        }
        
        $result = $this->registerCompetitionParticipant($competitionId, $catId, $userId);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Registered for competition successfully'
        ];
    }
    
    private function listCompetitions($data) {
        $status = sanitizeInput($_GET['status'] ?? '');
        $type = sanitizeInput($_GET['type'] ?? '');
        $limit = intval($_GET['limit'] ?? 50);
        
        $result = $this->getCompetitionsList($status, $type, $limit);
        
        return [
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ];
    }
    
    private function getCompetitionParticipants($data) {
        $competitionId = sanitizeInput($_GET['competition_id'] ?? '');
        
        if (!$competitionId) {
            throw new Exception('Competition ID is required', 400);
        }
        
        $result = $this->getCompetitionParticipantsList($competitionId);
        
        return [
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ];
    }
    
    private function submitCompetitionScore($data) {
        $userId = requireAuthentication(['competition:write']);
        
        $competitionId = sanitizeInput($data['competition_id'] ?? '');
        $catId = sanitizeInput($data['cat_id'] ?? '');
        $scores = $data['scores'] ?? [];
        
        if (!$competitionId || !$catId || empty($scores)) {
            throw new Exception('Competition ID, cat ID, and scores are required', 400);
        }
        
        $result = $this->submitCompetitionScores($competitionId, $catId, $userId, $scores);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Competition scores submitted successfully'
        ];
    }
    
    private function getCompetitionResults($data) {
        $competitionId = sanitizeInput($_GET['competition_id'] ?? '');
        
        if (!$competitionId) {
            throw new Exception('Competition ID is required', 400);
        }
        
        $result = $this->getCompetitionResultsData($competitionId);
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    /**
     * Multiplayer Methods
     */
    private function createMultiplayerSession($data) {
        $userId = requireAuthentication(['multiplayer:write']);
        
        $sessionName = sanitizeInput($data['session_name'] ?? '');
        $sessionType = sanitizeInput($data['session_type'] ?? '');
        $maxPlayers = intval($data['max_players'] ?? 4);
        $gameSettings = $data['game_settings'] ?? [];
        
        if (!$sessionName || !$sessionType) {
            throw new Exception('Session name and type are required', 400);
        }
        
        $result = $this->createMultiplayerSessionRecord($userId, $sessionName, $sessionType, $maxPlayers, $gameSettings);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Multiplayer session created successfully'
        ];
    }
    
    private function joinMultiplayerSession($data) {
        $userId = requireAuthentication(['multiplayer:write']);
        
        $sessionId = sanitizeInput($data['session_id'] ?? '');
        $catId = sanitizeInput($data['cat_id'] ?? '');
        
        if (!$sessionId) {
            throw new Exception('Session ID is required', 400);
        }
        
        $result = $this->joinMultiplayerSessionRecord($sessionId, $userId, $catId);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Joined multiplayer session successfully'
        ];
    }
    
    private function leaveMultiplayerSession($data) {
        $userId = requireAuthentication(['multiplayer:write']);
        
        $sessionId = sanitizeInput($data['session_id'] ?? '');
        
        if (!$sessionId) {
            throw new Exception('Session ID is required', 400);
        }
        
        $result = $this->leaveMultiplayerSessionRecord($sessionId, $userId);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Left multiplayer session successfully'
        ];
    }
    
    private function getActiveMultiplayerSessions($data) {
        $sessionType = sanitizeInput($_GET['type'] ?? '');
        $limit = intval($_GET['limit'] ?? 50);
        
        $result = $this->getActiveMultiplayerSessionsList($sessionType, $limit);
        
        return [
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ];
    }
    
    private function getMultiplayerSessionStatus($data) {
        $sessionId = sanitizeInput($_GET['session_id'] ?? '');
        
        if (!$sessionId) {
            throw new Exception('Session ID is required', 400);
        }
        
        $result = $this->getMultiplayerSessionStatusData($sessionId);
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    private function updateMultiplayerGameState($data) {
        $userId = requireAuthentication(['multiplayer:write']);
        
        $sessionId = sanitizeInput($data['session_id'] ?? '');
        $gameState = $data['game_state'] ?? [];
        
        if (!$sessionId || empty($gameState)) {
            throw new Exception('Session ID and game state are required', 400);
        }
        
        $result = $this->updateMultiplayerGameStateData($sessionId, $userId, $gameState);
        
        return [
            'success' => true,
            'data' => $result,
            'message' => 'Game state updated successfully'
        ];
    }
    
    /**
     * Helper Methods
     */
    private function logAPIRequest($path, $method, $data) {
        logSecurityEvent('api_request', [
            'endpoint' => "advanced_features/$path",
            'method' => $method,
            'request_id' => $this->requestId,
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => getClientIP()
        ]);
    }
    
    private function handleError($exception) {
        $errorCode = $exception->getCode() ?: 500;
        
        // Log security event for errors
        logSecurityEvent('api_error', [
            'endpoint' => $_SERVER['REQUEST_URI'],
            'error_message' => $exception->getMessage(),
            'error_code' => $errorCode,
            'request_id' => $this->requestId
        ], 'ERROR');
        
        http_response_code($errorCode);
        
        return [
            'success' => false,
            'error' => [
                'code' => getErrorCode($errorCode),
                'message' => defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE ? $exception->getMessage() : 'An error occurred',
                'request_id' => $this->requestId
            ]
        ];
    }
    
    // Placeholder methods for database operations
    private function storeBehaviorObservation($catId, $userId, $behaviorType, $intensityLevel, $duration, $environmentalContext) {
        // Implementation would go here
        return ['id' => uniqid(), 'status' => 'recorded'];
    }
    
    private function storeGeneticData($catId, $userId, $geneticMarkers, $heritageScore, $coatPattern) {
        // Implementation would go here
        return ['id' => uniqid(), 'status' => 'updated'];
    }
    
    private function getTrainingStatus() {
        // Implementation would go here
        return ['status' => 'active', 'models' => []];
    }
    
    private function getUserWebhooks($userId) {
        // Implementation would go here
        return [];
    }
    
    private function testWebhookDelivery($webhookId, $userId) {
        // Implementation would go here
        return ['status' => 'tested'];
    }
    
    private function getWebhookLogs($webhookId, $userId, $limit) {
        // Implementation would go here
        return [];
    }
    
    private function getUserBehaviorData($userId, $startDate, $endDate) {
        // Implementation would go here
        return [];
    }
    
    private function getCatInteractionData($userId, $catId, $startDate, $endDate) {
        // Implementation would go here
        return [];
    }
    
    private function getPerformanceData() {
        // Implementation would go here
        return [];
    }
    
    private function getEngagementData($userId) {
        // Implementation would go here
        return [];
    }
    
    private function exportAnalytics($userId, $dataType, $format, $startDate, $endDate) {
        // Implementation would go here
        return ['export_url' => 'placeholder'];
    }
    
    private function createCompetitionRecord($userId, $competitionName, $competitionType, $startDate, $endDate, $entryFee, $maxParticipants, $prizePool, $competitionRules) {
        // Implementation would go here
        return ['id' => uniqid(), 'status' => 'created'];
    }
    
    private function registerCompetitionParticipant($competitionId, $catId, $userId) {
        // Implementation would go here
        return ['id' => uniqid(), 'status' => 'registered'];
    }
    
    private function getCompetitionsList($status, $type, $limit) {
        // Implementation would go here
        return [];
    }
    
    private function getCompetitionParticipantsList($competitionId) {
        // Implementation would go here
        return [];
    }
    
    private function submitCompetitionScores($competitionId, $catId, $userId, $scores) {
        // Implementation would go here
        return ['id' => uniqid(), 'status' => 'submitted'];
    }
    
    private function getCompetitionResultsData($competitionId) {
        // Implementation would go here
        return [];
    }
    
    private function createMultiplayerSessionRecord($userId, $sessionName, $sessionType, $maxPlayers, $gameSettings) {
        // Implementation would go here
        return ['id' => uniqid(), 'status' => 'created'];
    }
    
    private function joinMultiplayerSessionRecord($sessionId, $userId, $catId) {
        // Implementation would go here
        return ['id' => uniqid(), 'status' => 'joined'];
    }
    
    private function leaveMultiplayerSessionRecord($sessionId, $userId) {
        // Implementation would go here
        return ['id' => uniqid(), 'status' => 'left'];
    }
    
    private function getActiveMultiplayerSessionsList($sessionType, $limit) {
        // Implementation would go here
        return [];
    }
    
    private function getMultiplayerSessionStatusData($sessionId) {
        // Implementation would go here
        return [];
    }
    
    private function updateMultiplayerGameStateData($sessionId, $userId, $gameState) {
        // Implementation would go here
        return ['id' => uniqid(), 'status' => 'updated'];
    }
}

// Initialize API
$advancedFeaturesAPI = new AdvancedFeaturesAPI();

// Handle request
$path = $_GET['feature'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true) ?: [];

$response = $advancedFeaturesAPI->routeRequest($path, $method, $data);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>
