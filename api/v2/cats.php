<?php
/**
 * 🐱 Purrr.love API v2 - Enhanced Cat Management
 * Advanced cat-specific features and operations
 */

require_once '../../includes/functions.php';
require_once '../../includes/cat_behavior.php';
require_once '../../includes/vr_cat_interaction.php';
require_once '../../includes/ai_cat_behavior.php';
require_once '../../includes/cat_trading.php';
require_once '../../includes/cat_shows.php';
require_once '../../includes/realtime_multiplayer.php';
require_once '../../includes/cat_health_monitoring.php';

// API version
define('API_VERSION', '2.0.0');

/**
 * Enhanced cat management endpoints
 */
function handleEnhancedCatEndpoints($action, $id, $params, $user) {
    switch ($action) {
        case 'create':
            return createEnhancedCat($user['id'], $params);
            
        case 'get':
            return getEnhancedCat($id, $user['id']);
            
        case 'update':
            return updateEnhancedCat($id, $user['id'], $params);
            
        case 'delete':
            return deleteEnhancedCat($id, $user['id']);
            
        case 'list':
            return listEnhancedCats($user['id'], $params);
            
        case 'breed':
            return breedEnhancedCats($user['id'], $params);
            
        case 'train':
            return trainEnhancedCat($id, $user['id'], $params);
            
        case 'play':
            return playWithEnhancedCat($id, $user['id'], $params);
            
        case 'care':
            return careForEnhancedCat($id, $user['id'], $params);
            
        case 'personality':
            return getCatPersonality($id, $user['id']);
            
        case 'genetics':
            return getCatGenetics($id, $user['id']);
            
        case 'health':
            return getCatHealth($id, $user['id']);
            
        case 'vr_interaction':
            return handleVRInteraction($id, $user['id'], $params);
            
        case 'ai_learning':
            return handleAILearning($id, $user['id'], $params);
            
        case 'trading':
            return handleCatTrading($id, $user['id'], $params);
            
        case 'shows':
            return handleCatShows($id, $user['id'], $params);
            
        case 'multiplayer':
            return handleMultiplayer($id, $user['id'], $params);
            
        case 'health_monitoring':
            return handleHealthMonitoring($id, $user['id'], $params);
            
        case 'analytics':
            return getCatAnalytics($id, $user['id'], $params);
            
        default:
            throw new Exception('Invalid action', 400);
    }
}

/**
 * Create enhanced cat with advanced features
 */
function createEnhancedCat($userId, $params) {
    // Validate required parameters
    $required = ['name', 'species', 'personality_type'];
    foreach ($required as $field) {
        if (empty($params[$field])) {
            throw new Exception("Missing required field: $field", 400);
        }
    }
    
    // Validate personality type
    $validPersonalities = ['playful', 'aloof', 'curious', 'lazy', 'territorial', 'social_butterfly'];
    if (!in_array($params['personality_type'], $validPersonalities)) {
        throw new Exception('Invalid personality type', 400);
    }
    
    // Create base cat
    $catData = [
        'name' => $params['name'],
        'species' => $params['species'],
        'breed' => $params['breed'] ?? 'mixed',
        'personality_type' => $params['personality_type'],
        'owner_id' => $userId,
        'level' => 1,
        'experience' => 0,
        'happiness' => 50,
        'energy' => 100,
        'mood' => 50,
        'health' => 100,
        'hunger' => 0,
        'cleanliness' => 100,
        'social' => 50,
        'training' => 0,
        'intelligence' => 50,
        'strength' => 50,
        'agility' => 50,
        'charisma' => 50,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Create cat record
    $catId = createCatRecord($catData);
    
    // Initialize advanced features
    initializeCatAdvancedFeatures($catId);
    
    // Get created cat
    $cat = getCatById($catId);
    
    return [
        'success' => true,
        'cat' => $cat,
        'advanced_features' => [
            'personality_system' => true,
            'ai_learning' => true,
            'vr_interaction' => true,
            'health_monitoring' => true,
            'multiplayer' => true
        ],
        'created_at' => date('c')
    ];
}

/**
 * Get enhanced cat with all features
 */
function getEnhancedCat($catId, $userId) {
    // Verify ownership
    if (!canAccessCat($catId, $userId)) {
        throw new Exception('Cannot access this cat', 403);
    }
    
    // Get base cat data
    $cat = getCatById($catId);
    if (!$cat) {
        throw new Exception('Cat not found', 404);
    }
    
    // Get personality data
    $personality = getCatPersonalityData($catId);
    
    // Get AI learning data
    $aiLearning = getAICatLearningInsights($catId);
    
    // Get VR behavior data
    $vrBehavior = getCatVRBehavior($catId);
    
    // Get health monitoring data
    $healthData = getCatHealthSummary($catId);
    
    // Get trading status
    $tradingStatus = getCatTradingStatus($catId);
    
    // Get show participation
    $showParticipation = getCatShowParticipation($catId);
    
    // Get multiplayer status
    $multiplayerStatus = getCatMultiplayerStatus($catId);
    
    return [
        'success' => true,
        'cat' => $cat,
        'personality' => $personality,
        'ai_learning' => $aiLearning,
        'vr_behavior' => $vrBehavior,
        'health_monitoring' => $healthData,
        'trading' => $tradingStatus,
        'shows' => $showParticipation,
        'multiplayer' => $multiplayerStatus
    ];
}

/**
 * Train enhanced cat
 */
function trainEnhancedCat($catId, $userId, $params) {
    // Verify ownership
    if (!canAccessCat($catId, $userId)) {
        throw new Exception('Cannot access this cat', 403);
    }
    
    // Validate training parameters
    if (empty($params['command'])) {
        throw new Exception('Training command is required', 400);
    }
    
    $validCommands = ['sit', 'stay', 'come', 'high_five', 'spin', 'jump', 'fetch'];
    if (!in_array($params['command'], $validCommands)) {
        throw new Exception('Invalid training command', 400);
    }
    
    // Get cat's current state
    $cat = getCatById($catId);
    if ($cat['energy'] < 20) {
        throw new Exception('Cat is too tired for training', 400);
    }
    
    // Process training
    $trainingResult = processCatTraining($catId, $params['command'], $params['difficulty'] ?? 'normal');
    
    // Update cat stats
    updateCatStatsFromTraining($catId, $trainingResult);
    
    // Process AI learning
    $aiResult = processInteractionForAILearning($catId, 'training', $params, [], $trainingResult);
    
    return [
        'success' => true,
        'training_result' => $trainingResult,
        'ai_learning' => $aiResult,
        'cat_updated' => true,
        'trained_at' => date('c')
    ];
}

/**
 * Play with enhanced cat
 */
function playWithEnhancedCat($catId, $userId, $params) {
    // Verify ownership
    if (!canAccessCat($catId, $userId)) {
        throw new Exception('Cannot access this cat', 403);
    }
    
    // Validate play parameters
    if (empty($params['game_type'])) {
        throw new Exception('Game type is required', 400);
    }
    
    $validGames = ['laser_pointer', 'feather_toy', 'ball', 'string', 'box', 'puzzle'];
    if (!in_array($params['game_type'], $validGames)) {
        throw new Exception('Invalid game type', 400);
    }
    
    // Get cat's current state
    $cat = getCatById($catId);
    if ($cat['energy'] < 30) {
        throw new Exception('Cat is too tired to play', 400);
    }
    
    // Process play session
    $playResult = processCatPlay($catId, $params['game_type'], $params['duration'] ?? 10);
    
    // Update cat stats
    updateCatStatsFromPlay($catId, $playResult);
    
    // Process AI learning
    $aiResult = processInteractionForAILearning($catId, 'playing', $params, [], $playResult);
    
    return [
        'success' => true,
        'play_result' => $playResult,
        'ai_learning' => $aiResult,
        'cat_updated' => true,
        'played_at' => date('c')
    ];
}

/**
 * Care for enhanced cat
 */
function careForEnhancedCat($catId, $userId, $params) {
    // Verify ownership
    if (!canAccessCat($catId, $userId)) {
        throw new Exception('Cannot access this cat', 403);
    }
    
    // Validate care parameters
    if (empty($params['care_type'])) {
        throw new Exception('Care type is required', 400);
    }
    
    $validCareTypes = ['feed', 'groom', 'clean', 'rest', 'socialize'];
    if (!in_array($params['care_type'], $validCareTypes)) {
        throw new Exception('Invalid care type', 400);
    }
    
    // Process care action
    $careResult = processCatCare($catId, $params['care_type'], $params);
    
    // Update cat stats
    updateCatStatsFromCare($catId, $careResult);
    
    // Process AI learning
    $aiResult = processInteractionForAILearning($catId, 'care', $params, [], $careResult);
    
    return [
        'success' => true,
        'care_result' => $careResult,
        'ai_learning' => $aiResult,
        'cat_updated' => true,
        'cared_at' => date('c')
    ];
}

/**
 * Handle VR interaction
 */
function handleVRInteraction($catId, $userId, $params) {
    // Verify ownership
    if (!canAccessCat($catId, $userId)) {
        throw new Exception('Cannot access this cat', 403);
    }
    
    // Validate VR parameters
    if (empty($params['interaction_type'])) {
        throw new Exception('Interaction type is required', 400);
    }
    
    $validInteractions = ['petting', 'playing', 'grooming', 'training'];
    if (!in_array($params['interaction_type'], $validInteractions)) {
        throw new Exception('Invalid interaction type', 400);
    }
    
    // Initialize VR session
    $vrSession = initializeVRInteraction($catId, $userId, $params['vr_device'] ?? 'webvr');
    
    // Process VR interaction
    $interactionResult = processVRInteraction($vrSession['session_id'], $params['interaction_type'], $params);
    
    // Process AI learning
    $aiResult = processInteractionForAILearning($catId, 'vr_' . $params['interaction_type'], $params, [], $interactionResult);
    
    // End VR session
    endVRInteractionSession($vrSession['session_id']);
    
    return [
        'success' => true,
        'vr_session' => $vrSession,
        'interaction_result' => $interactionResult,
        'ai_learning' => $aiResult,
        'session_ended' => true
    ];
}

/**
 * Handle AI learning
 */
function handleAILearning($catId, $userId, $params) {
    // Verify ownership
    if (!canAccessCat($catId, $userId)) {
        throw new Exception('Cannot access this cat', 403);
    }
    
    // Initialize AI learning if not already done
    $aiProfile = initializeAICatLearning($catId);
    
    // Get AI learning insights
    $insights = getAICatLearningInsights($catId);
    
    // Get learning recommendations
    $recommendations = generateLearningRecommendations($catId);
    
    return [
        'success' => true,
        'ai_profile' => $aiProfile,
        'insights' => $insights,
        'recommendations' => $recommendations,
        'learning_enabled' => true
    ];
}

/**
 * Handle cat trading
 */
function handleCatTrading($catId, $userId, $params) {
    // Verify ownership
    if (!canAccessCat($catId, $userId)) {
        throw new Exception('Cannot access this cat', 403);
    }
    
    $action = $params['trading_action'] ?? 'status';
    
    switch ($action) {
        case 'create_offer':
            return createCatTradingOffer($userId, $catId, $params);
            
        case 'get_offers':
            return getAvailableTradingOffers($params['filters'] ?? []);
            
        case 'get_history':
            return getUserTradingHistory($userId, $params['limit'] ?? 50);
            
        case 'search':
            return searchTradingCats($params['search_criteria'] ?? []);
            
        default:
            return getCatTradingStatus($catId);
    }
}

/**
 * Handle cat shows
 */
function handleCatShows($catId, $userId, $params) {
    // Verify ownership
    if (!canAccessCat($catId, $userId)) {
        throw new Exception('Cannot access this cat', 403);
    }
    
    $action = $params['show_action'] ?? 'status';
    
    switch ($action) {
        case 'register':
            return registerCatForShow($userId, $catId, $params['show_id'], $params['categories'] ?? []);
            
        case 'get_available':
            return getAvailableCatShows($params['filters'] ?? []);
            
        case 'get_participation':
            return getCatShowParticipation($catId);
            
        case 'submit_entry':
            return submitCatShowEntry($userId, $catId, $params['show_id'], $params['category'], $params['entry_data'] ?? []);
            
        default:
            return getCatShowParticipation($catId);
    }
}

/**
 * Handle multiplayer
 */
function handleMultiplayer($catId, $userId, $params) {
    // Verify ownership
    if (!canAccessCat($catId, $userId)) {
        throw new Exception('Cannot access this cat', 403);
    }
    
    $action = $params['multiplayer_action'] ?? 'status';
    
    switch ($action) {
        case 'join':
            return initializeMultiplayerSession($userId, $catId, $params['room_type'] ?? 'playground');
            
        case 'action':
            return processMultiplayerAction($params['session_id'], $params['action_type'], $params['action_data'] ?? []);
            
        case 'get_rooms':
            return getAvailableMultiplayerRooms($params['filters'] ?? []);
            
        case 'get_status':
            return getCatMultiplayerStatus($catId);
            
        default:
            return getCatMultiplayerStatus($catId);
    }
}

/**
 * Handle health monitoring
 */
function handleHealthMonitoring($catId, $userId, $params) {
    // Verify ownership
    if (!canAccessCat($catId, $userId)) {
        throw new Exception('Cannot access this cat', 403);
    }
    
    $action = $params['health_action'] ?? 'summary';
    
    switch ($action) {
        case 'register_device':
            return registerHealthDevice($userId, $catId, $params['device_data'] ?? []);
            
        case 'get_summary':
            return getCatHealthSummary($catId, $params['timeframe'] ?? '7d');
            
        case 'get_devices':
            return getCatHealthDevices($catId);
            
        case 'get_alerts':
            return getCatHealthAlerts($catId);
            
        default:
            return getCatHealthSummary($catId);
    }
}

/**
 * Get cat analytics
 */
function getCatAnalytics($catId, $userId, $params) {
    // Verify ownership
    if (!canAccessCat($catId, $userId)) {
        throw new Exception('Cannot access this cat', 403);
    }
    
    $timeframe = $params['timeframe'] ?? '30d';
    $metrics = $params['metrics'] ?? ['all'];
    
    // Get analytics data
    $analytics = [
        'activity_trends' => getCatActivityTrends($catId, $timeframe),
        'health_trends' => getCatHealthTrends($catId, $timeframe),
        'behavior_patterns' => getCatBehaviorPatterns($catId, $timeframe),
        'social_interactions' => getCatSocialInteractions($catId, $timeframe),
        'training_progress' => getCatTrainingProgress($catId, $timeframe),
        'ai_learning_progress' => getAICatLearningProgress($catId, $timeframe),
        'vr_interaction_stats' => getCatVRInteractionStats($catId, $timeframe),
        'multiplayer_stats' => getCatMultiplayerStats($catId, $timeframe),
        'trading_history' => getCatTradingHistory($catId, $timeframe),
        'show_participation' => getCatShowParticipationStats($catId, $timeframe)
    ];
    
    // Filter metrics if specific ones requested
    if ($metrics !== ['all']) {
        $filteredAnalytics = [];
        foreach ($metrics as $metric) {
            if (isset($analytics[$metric])) {
                $filteredAnalytics[$metric] = $analytics[$metric];
            }
        }
        $analytics = $filteredAnalytics;
    }
    
    return [
        'success' => true,
        'cat_id' => $catId,
        'timeframe' => $timeframe,
        'analytics' => $analytics,
        'generated_at' => date('c')
    ];
}

/**
 * Initialize cat advanced features
 */
function initializeCatAdvancedFeatures($catId) {
    // Initialize AI learning
    initializeAICatLearning($catId);
    
    // Initialize VR behavior
    initializeCatVRBehavior(getCatById($catId));
    
    // Initialize health monitoring profile
    initializeCatHealthProfile($catId);
    
    // Initialize multiplayer state
    initializeCatMultiplayerState($catId, null);
    
    return true;
}

/**
 * Get cat trading status
 */
function getCatTradingStatus($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT status, created_at FROM trading_offers 
        WHERE cat_id = ? AND status = 'pending'
        ORDER BY created_at DESC
        LIMIT 1
    ");
    
    $stmt->execute([$catId]);
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'trading_enabled' => true,
        'current_offer' => $offer,
        'can_trade' => !$offer,
        'last_traded' => getCatLastTraded($catId)
    ];
}

/**
 * Get cat show participation
 */
function getCatShowParticipation($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT s.title, s.status, sr.categories, sr.created_at
        FROM show_registrations sr
        JOIN cat_shows s ON sr.show_id = s.id
        WHERE sr.cat_id = ?
        ORDER BY sr.created_at DESC
        LIMIT 10
    ");
    
    $stmt->execute([$catId]);
    $participations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'shows_enabled' => true,
        'current_participations' => $participations,
        'total_shows' => count($participations),
        'can_register' => true
    ];
}

/**
 * Get cat multiplayer status
 */
function getCatMultiplayerStatus($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT ms.*, mr.room_type, mr.name as room_name
        FROM multiplayer_sessions ms
        JOIN multiplayer_rooms mr ON ms.room_id = mr.id
        WHERE ms.cat_id = ? AND ms.active = 1
        ORDER BY ms.joined_at DESC
        LIMIT 1
    ");
    
    $stmt->execute([$catId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'multiplayer_enabled' => true,
        'current_session' => $session,
        'can_join' => !$session,
        'available_rooms' => getAvailableMultiplayerRooms([])
    ];
}

/**
 * Get available multiplayer rooms
 */
function getAvailableMultiplayerRooms($filters) {
    $pdo = get_db();
    
    $whereConditions = ['active = 1'];
    $params = [];
    
    if (isset($filters['room_type'])) {
        $whereConditions[] = 'room_type = ?';
        $params[] = $filters['room_type'];
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $stmt = $pdo->prepare("
        SELECT * FROM multiplayer_rooms 
        WHERE $whereClause AND current_cats < max_cats
        ORDER BY current_cats ASC
        LIMIT 20
    ");
    
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get cat health devices
 */
function getCatHealthDevices($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM health_devices 
        WHERE cat_id = ? AND active = 1
        ORDER BY created_at DESC
    ");
    
    $stmt->execute([$catId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get cat health alerts
 */
function getCatHealthAlerts($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM health_alerts 
        WHERE cat_id = ? AND resolved = 0
        ORDER BY created_at DESC
        LIMIT 20
    ");
    
    $stmt->execute([$catId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Generate learning recommendations
 */
function generateLearningRecommendations($catId) {
    // Get cat's current stats
    $cat = getCatById($catId);
    
    $recommendations = [];
    
    // Activity recommendations
    if ($cat['energy'] > 80) {
        $recommendations[] = [
            'type' => 'activity',
            'priority' => 'medium',
            'title' => 'High Energy Level',
            'description' => 'Your cat has high energy. Consider active play sessions.',
            'suggestions' => ['Interactive toys', 'Laser pointer games', 'Climbing activities']
        ];
    }
    
    // Training recommendations
    if ($cat['training'] < 30) {
        $recommendations[] = [
            'type' => 'training',
            'priority' => 'high',
            'title' => 'Training Opportunity',
            'description' => 'Your cat could benefit from training sessions.',
            'suggestions' => ['Basic commands', 'Clicker training', 'Positive reinforcement']
        ];
    }
    
    // Social recommendations
    if ($cat['social'] < 40) {
        $recommendations[] = [
            'type' => 'social',
            'priority' => 'medium',
            'title' => 'Social Development',
            'description' => 'Your cat could use more social interaction.',
            'suggestions' => ['Gentle petting', 'Multiplayer sessions', 'Social grooming']
        ];
    }
    
    return $recommendations;
}
