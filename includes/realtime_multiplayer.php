<?php
/**
 * 🌐 Purrr.love Real-time Multiplayer System
 * Live interaction between multiple users' cats using WebSocket and real-time updates
 */

// Multiplayer configuration
define('MULTIPLAYER_MAX_ROOM_SIZE', 20);
define('MULTIPLAYER_HEARTBEAT_INTERVAL', 30);
define('MULTIPLAYER_CONNECTION_TIMEOUT', 120);
define('MULTIPLAYER_MAX_MESSAGE_SIZE', 1024);
define('MULTIPLAYER_RATE_LIMIT_PER_MINUTE', 60);

// Room types
define('MULTIPLAYER_ROOM_TYPES', [
    'playground' => [
        'name' => 'Cat Playground',
        'description' => 'Open area for cats to play and socialize',
        'max_cats' => 15,
        'activities' => ['chase', 'play', 'socialize', 'explore']
    ],
    'training_arena' => [
        'name' => 'Training Arena',
        'description' => 'Structured environment for cat training and competitions',
        'max_cats' => 8,
        'activities' => ['training', 'competition', 'skill_demonstration']
    ],
    'social_lounge' => [
        'name' => 'Social Lounge',
        'description' => 'Relaxed environment for cats to hang out and chat',
        'max_cats' => 12,
        'activities' => ['socialize', 'groom', 'nap', 'chat']
    ],
    'adventure_zone' => [
        'name' => 'Adventure Zone',
        'description' => 'Exploration area with obstacles and challenges',
        'max_cats' => 10,
        'activities' => ['explore', 'climb', 'jump', 'solve_puzzles']
    ],
    'party_room' => [
        'name' => 'Party Room',
        'description' => 'Special events and celebrations',
        'max_cats' => 25,
        'activities' => ['celebrate', 'dance', 'play_games', 'socialize']
    ]
]);

// Activity types
define('MULTIPLAYER_ACTIVITIES', [
    'move' => ['name' => 'Movement', 'energy_cost' => 1],
    'play' => ['name' => 'Play', 'energy_cost' => 3],
    'socialize' => ['name' => 'Socialize', 'energy_cost' => 2],
    'train' => ['name' => 'Train', 'energy_cost' => 4],
    'explore' => ['name' => 'Explore', 'energy_cost' => 2],
    'groom' => ['name' => 'Groom', 'energy_cost' => 1],
    'nap' => ['name' => 'Nap', 'energy_cost' => -2],
    'chat' => ['name' => 'Chat', 'energy_cost' => 1]
]);

/**
 * Initialize multiplayer session
 */
function initializeMultiplayerSession($userId, $catId, $roomType = 'playground') {
    // Validate cat ownership
    if (!canUseMultiplayer($userId, $catId)) {
        throw new Exception('Cannot use multiplayer with this cat', 403);
    }
    
    // Get cat details
    $cat = getCatById($catId);
    if (!$cat) {
        throw new Exception('Cat not found', 404);
    }
    
    // Check cat's energy and mood
    if ($cat['energy'] < 20) {
        throw new Exception('Cat is too tired for multiplayer', 400);
    }
    
    if ($cat['mood'] < 30) {
        throw new Exception('Cat is not in the mood for multiplayer', 400);
    }
    
    // Find or create room
    $room = findOrCreateRoom($roomType);
    
    // Join room
    $sessionId = joinMultiplayerRoom($userId, $catId, $room['id']);
    
    // Initialize cat's multiplayer state
    $multiplayerState = initializeCatMultiplayerState($catId, $room['id']);
    
    // Notify other players
    notifyPlayerJoined($room['id'], $userId, $cat);
    
    // Log multiplayer event
    logMultiplayerEvent('session_started', $sessionId, $userId, $catId, $room['id']);
    
    return [
        'session_id' => $sessionId,
        'room' => $room,
        'cat' => $cat,
        'multiplayer_state' => $multiplayerState,
        'room_type' => $roomType,
        'started_at' => date('c')
    ];
}

/**
 * Process multiplayer action
 */
function processMultiplayerAction($sessionId, $actionType, $actionData) {
    // Validate session
    $session = getMultiplayerSession($sessionId);
    if (!$session || $session['active'] == false) {
        throw new Exception('Invalid or expired session', 400);
    }
    
    // Validate action type
    if (!isValidMultiplayerAction($actionType)) {
        throw new Exception('Invalid action type', 400);
    }
    
    // Check rate limiting
    if (isActionRateLimited($sessionId, $actionType)) {
        throw new Exception('Action rate limit exceeded', 429);
    }
    
    // Get cat's current state
    $cat = getCatById($session['cat_id']);
    $multiplayerState = getCatMultiplayerState($cat['id']);
    
    // Process action based on type
    $result = processMultiplayerActionByType($actionType, $actionData, $cat, $multiplayerState);
    
    // Update cat's multiplayer state
    updateCatMultiplayerState($cat['id'], $result['state_changes']);
    
    // Update cat's stats
    updateCatStatsFromMultiplayer($cat['id'], $result['stat_changes']);
    
    // Broadcast action to other players
    broadcastMultiplayerAction($session['room_id'], $sessionId, $actionType, $result);
    
    // Log action
    logMultiplayerAction($sessionId, $actionType, $actionData, $result);
    
    return [
        'success' => true,
        'action_type' => $actionType,
        'result' => $result,
        'broadcasted' => true
    ];
}

/**
 * Process multiplayer action by type
 */
function processMultiplayerActionByType($actionType, $actionData, $cat, $multiplayerState) {
    $result = [
        'state_changes' => [],
        'stat_changes' => [],
        'effects' => [],
        'interactions' => []
    ];
    
    switch ($actionType) {
        case 'move':
            $result = processMovementAction($actionData, $cat, $multiplayerState);
            break;
            
        case 'play':
            $result = processPlayAction($actionData, $cat, $multiplayerState);
            break;
            
        case 'socialize':
            $result = processSocializeAction($actionData, $cat, $multiplayerState);
            break;
            
        case 'train':
            $result = processTrainingAction($actionData, $cat, $multiplayerState);
            break;
            
        case 'explore':
            $result = processExploreAction($actionData, $cat, $multiplayerState);
            break;
            
        case 'groom':
            $result = processGroomingAction($actionData, $cat, $multiplayerState);
            break;
            
        case 'nap':
            $result = processNappingAction($actionData, $cat, $multiplayerState);
            break;
            
        case 'chat':
            $result = processChatAction($actionData, $cat, $multiplayerState);
            break;
            
        default:
            throw new Exception('Unsupported action type', 400);
    }
    
    return $result;
}

/**
 * Process movement action
 */
function processMovementAction($actionData, $cat, $multiplayerState) {
    $x = $actionData['x'] ?? 0;
    $y = $actionData['y'] ?? 0;
    $direction = $actionData['direction'] ?? 'idle';
    
    // Validate movement coordinates
    if (!isValidMovement($x, $y, $multiplayerState)) {
        throw new Exception('Invalid movement coordinates', 400);
    }
    
    // Calculate energy cost
    $energyCost = calculateMovementEnergyCost($direction, $multiplayerState);
    
    // Check if cat has enough energy
    if ($cat['energy'] < $energyCost) {
        throw new Exception('Insufficient energy for movement', 400);
    }
    
    // Update position
    $newPosition = [
        'x' => $x,
        'y' => $y,
        'direction' => $direction,
        'timestamp' => time()
    ];
    
    // Check for interactions with other cats
    $interactions = checkForCatInteractions($x, $y, $multiplayerState['room_id'], $cat['id']);
    
    // Calculate stat changes
    $statChanges = [
        'energy' => -$energyCost,
        'happiness' => 1,
        'mood' => 1
    ];
    
    // Apply personality bonuses
    $personalityBonuses = getCatPersonalityMovementBonuses($cat['personality_type']);
    foreach ($personalityBonuses as $stat => $bonus) {
        if (isset($statChanges[$stat])) {
            $statChanges[$stat] += $bonus;
        }
    }
    
    return [
        'state_changes' => [
            'position' => $newPosition,
            'last_action' => 'move',
            'last_action_time' => time()
        ],
        'stat_changes' => $statChanges,
        'effects' => ['position_updated', 'energy_consumed'],
        'interactions' => $interactions
    ];
}

/**
 * Process play action
 */
function processPlayAction($actionData, $cat, $multiplayerState) {
    $playType = $actionData['play_type'] ?? 'general';
    $targetCatId = $actionData['target_cat_id'] ?? null;
    $intensity = $actionData['intensity'] ?? 'normal';
    
    // Calculate energy cost
    $energyCost = MULTIPLAYER_ACTIVITIES['play']['energy_cost'];
    if ($intensity === 'high') {
        $energyCost *= 1.5;
    }
    
    // Check if cat has enough energy
    if ($cat['energy'] < $energyCost) {
        throw new Exception('Insufficient energy for play', 400);
    }
    
    // Check if target cat is available for play
    if ($targetCatId) {
        $targetCat = getCatById($targetCatId);
        if (!$targetCat || !isCatAvailableForPlay($targetCatId)) {
            throw new Exception('Target cat not available for play', 400);
        }
        
        // Check if target cat is in the same room
        if (!isCatInSameRoom($targetCatId, $multiplayerState['room_id'])) {
            throw new Exception('Target cat not in the same room', 400);
        }
    }
    
    // Calculate stat changes
    $statChanges = [
        'energy' => -$energyCost,
        'happiness' => 5,
        'mood' => 3,
        'social' => 2
    ];
    
    // Apply play type bonuses
    $playTypeBonuses = getPlayTypeBonuses($playType);
    foreach ($playTypeBonuses as $stat => $bonus) {
        if (isset($statChanges[$stat])) {
            $statChanges[$stat] += $bonus;
        }
    }
    
    // Apply personality bonuses
    $personalityBonuses = getCatPersonalityPlayBonuses($cat['personality_type']);
    foreach ($personalityBonuses as $stat => $bonus) {
        if (isset($statChanges[$stat])) {
            $statChanges[$stat] += $bonus;
        }
    }
    
    // Generate play effects
    $effects = ['play_animation', 'energy_consumed'];
    if ($targetCatId) {
        $effects[] = 'social_interaction';
    }
    
    return [
        'state_changes' => [
            'last_action' => 'play',
            'last_action_time' => time(),
            'play_count' => ($multiplayerState['play_count'] ?? 0) + 1
        ],
        'stat_changes' => $statChanges,
        'effects' => $effects,
        'interactions' => $targetCatId ? [['type' => 'play', 'target_cat_id' => $targetCatId]] : []
    ];
}

/**
 * Process socialize action
 */
function processSocializeAction($actionData, $cat, $multiplayerState) {
    $socialType = $actionData['social_type'] ?? 'general';
    $targetCatId = $actionData['target_cat_id'] ?? null;
    
    // Calculate energy cost
    $energyCost = MULTIPLAYER_ACTIVITIES['socialize']['energy_cost'];
    
    // Check if cat has enough energy
    if ($cat['energy'] < $energyCost) {
        throw new Exception('Insufficient energy for socializing', 400);
    }
    
    // Check if target cat is available
    if ($targetCatId) {
        $targetCat = getCatById($targetCatId);
        if (!$targetCat || !isCatAvailableForSocializing($targetCatId)) {
            throw new Exception('Target cat not available for socializing', 400);
        }
        
        // Check if target cat is in the same room
        if (!isCatInSameRoom($targetCatId, $multiplayerState['room_id'])) {
            throw new Exception('Target cat not in the same room', 400);
        }
    }
    
    // Calculate stat changes
    $statChanges = [
        'energy' => -$energyCost,
        'happiness' => 3,
        'mood' => 4,
        'social' => 5
    ];
    
    // Apply social type bonuses
    $socialTypeBonuses = getSocialTypeBonuses($socialType);
    foreach ($socialTypeBonuses as $stat => $bonus) {
        if (isset($statChanges[$stat])) {
            $statChanges[$stat] += $bonus;
        }
    }
    
    // Apply personality bonuses
    $personalityBonuses = getCatPersonalitySocialBonuses($cat['personality_type']);
    foreach ($personalityBonuses as $stat => $bonus) {
        if (isset($statChanges[$stat])) {
            $statChanges[$stat] += $bonus;
        }
    }
    
    return [
        'state_changes' => [
            'last_action' => 'socialize',
            'last_action_time' => time(),
            'social_count' => ($multiplayerState['social_count'] ?? 0) + 1
        ],
        'stat_changes' => $statChanges,
        'effects' => ['social_animation', 'energy_consumed', 'social_interaction'],
        'interactions' => $targetCatId ? [['type' => 'socialize', 'target_cat_id' => $targetCatId]] : []
    ];
}

/**
 * Find or create multiplayer room
 */
function findOrCreateRoom($roomType) {
    $pdo = get_db();
    
    // Try to find an available room
    $stmt = $pdo->prepare("
        SELECT * FROM multiplayer_rooms 
        WHERE room_type = ? AND active = 1 AND current_cats < max_cats
        ORDER BY current_cats ASC
        LIMIT 1
    ");
    
    $stmt->execute([$roomType]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($room) {
        return $room;
    }
    
    // Create new room if none available
    $roomConfig = MULTIPLAYER_ROOM_TYPES[$roomType];
    
    $stmt = $pdo->prepare("
        INSERT INTO multiplayer_rooms 
        (room_type, name, description, max_cats, current_cats, active, created_at)
        VALUES (?, ?, ?, ?, 0, 1, ?)
    ");
    
    $stmt->execute([
        $roomType,
        $roomConfig['name'],
        $roomConfig['description'],
        $roomConfig['max_cats'],
        date('Y-m-d H:i:s')
    ]);
    
    $roomId = $pdo->lastInsertId();
    
    return [
        'id' => $roomId,
        'room_type' => $roomType,
        'name' => $roomConfig['name'],
        'description' => $roomConfig['description'],
        'max_cats' => $roomConfig['max_cats'],
        'current_cats' => 0,
        'active' => 1
    ];
}

/**
 * Join multiplayer room
 */
function joinMultiplayerRoom($userId, $catId, $roomId) {
    $pdo = get_db();
    
    // Create session
    $stmt = $pdo->prepare("
        INSERT INTO multiplayer_sessions 
        (user_id, cat_id, room_id, active, joined_at)
        VALUES (?, ?, ?, 1, ?)
    ");
    
    $stmt->execute([$userId, $catId, $roomId, date('Y-m-d H:i:s')]);
    $sessionId = $pdo->lastInsertId();
    
    // Update room cat count
    $stmt = $pdo->prepare("
        UPDATE multiplayer_rooms 
        SET current_cats = current_cats + 1 
        WHERE id = ?
    ");
    
    $stmt->execute([$roomId]);
    
    return $sessionId;
}

/**
 * Get multiplayer session
 */
function getMultiplayerSession($sessionId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM multiplayer_sessions 
        WHERE id = ? AND active = 1
    ");
    
    $stmt->execute([$sessionId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Initialize cat multiplayer state
 */
function initializeCatMultiplayerState($catId, $roomId) {
    $pdo = get_db();
    
    // Get or create multiplayer state
    $stmt = $pdo->prepare("
        SELECT * FROM cat_multiplayer_states 
        WHERE cat_id = ? AND room_id = ?
    ");
    
    $stmt->execute([$catId, $roomId]);
    $state = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$state) {
        // Create new state
        $stmt = $pdo->prepare("
            INSERT INTO cat_multiplayer_states 
            (cat_id, room_id, position_x, position_y, direction, 
             play_count, social_count, training_count, explore_count, created_at)
            VALUES (?, ?, 0, 0, 'idle', 0, 0, 0, 0, ?)
        ");
        
        $stmt->execute([$catId, $roomId, date('Y-m-d H:i:s')]);
        
        $state = [
            'cat_id' => $catId,
            'room_id' => $roomId,
            'position_x' => 0,
            'position_y' => 0,
            'direction' => 'idle',
            'play_count' => 0,
            'social_count' => 0,
            'training_count' => 0,
            'explore_count' => 0
        ];
    }
    
    return $state;
}

/**
 * Get cat multiplayer state
 */
function getCatMultiplayerState($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM cat_multiplayer_states 
        WHERE cat_id = ?
        ORDER BY updated_at DESC
        LIMIT 1
    ");
    
    $stmt->execute([$catId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update cat multiplayer state
 */
function updateCatMultiplayerState($catId, $stateChanges) {
    if (empty($stateChanges)) {
        return;
    }
    
    $pdo = get_db();
    
    $updates = [];
    $values = [];
    
    foreach ($stateChanges as $field => $value) {
        $updates[] = "$field = ?";
        $values[] = $value;
    }
    
    $values[] = $catId;
    
    $stmt = $pdo->prepare("
        UPDATE cat_multiplayer_states 
        SET " . implode(', ', $updates) . ", updated_at = ?
        WHERE cat_id = ?
    ");
    
    $values[] = date('Y-m-d H:i:s');
    $stmt->execute($values);
}

/**
 * Check if user can use multiplayer
 */
function canUseMultiplayer($userId, $catId) {
    // Check cat ownership
    $cat = getCatById($catId);
    if (!$cat || $cat['owner_id'] !== $userId) {
        return false;
    }
    
    // Check if cat is already in multiplayer
    if (isCatInMultiplayer($catId)) {
        return false;
    }
    
    return true;
}

/**
 * Check if cat is in multiplayer
 */
function isCatInMultiplayer($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM multiplayer_sessions 
        WHERE cat_id = ? AND active = 1
    ");
    
    $stmt->execute([$catId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] > 0;
}

/**
 * Validate multiplayer action
 */
function isValidMultiplayerAction($actionType) {
    return array_key_exists($actionType, MULTIPLAYER_ACTIVITIES);
}

/**
 * Check action rate limiting
 */
function isActionRateLimited($sessionId, $actionType) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM multiplayer_actions 
        WHERE session_id = ? AND action_type = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
    ");
    
    $stmt->execute([$sessionId, $actionType]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] >= MULTIPLAYER_RATE_LIMIT_PER_MINUTE;
}

/**
 * Log multiplayer event
 */
function logMultiplayerEvent($eventType, $sessionId, $userId, $catId, $roomId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO multiplayer_events 
        (event_type, session_id, user_id, cat_id, room_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$eventType, $sessionId, $userId, $catId, $roomId, date('Y-m-d H:i:s')]);
}

/**
 * Log multiplayer action
 */
function logMultiplayerAction($sessionId, $actionType, $actionData, $result) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO multiplayer_actions 
        (session_id, action_type, action_data, result_data, created_at)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $sessionId,
        $actionType,
        json_encode($actionData),
        json_encode($result),
        date('Y-m-d H:i:s')
    ]);
}

/**
 * Get cat personality movement bonuses
 */
function getCatPersonalityMovementBonuses($personalityType) {
    $bonuses = [
        'playful' => ['happiness' => 2, 'mood' => 1],
        'curious' => ['happiness' => 1, 'mood' => 2],
        'lazy' => ['happiness' => 0, 'mood' => 0],
        'territorial' => ['happiness' => 1, 'mood' => 1],
        'social_butterfly' => ['happiness' => 2, 'mood' => 2]
    ];
    
    return $bonuses[$personalityType] ?? ['happiness' => 0, 'mood' => 0];
}

/**
 * Get cat personality play bonuses
 */
function getCatPersonalityPlayBonuses($personalityType) {
    $bonuses = [
        'playful' => ['happiness' => 3, 'mood' => 2, 'social' => 2],
        'curious' => ['happiness' => 2, 'mood' => 3, 'social' => 1],
        'lazy' => ['happiness' => 1, 'mood' => 1, 'social' => 0],
        'territorial' => ['happiness' => 1, 'mood' => 1, 'social' => 1],
        'social_butterfly' => ['happiness' => 2, 'mood' => 2, 'social' => 3]
    ];
    
    return $bonuses[$personalityType] ?? ['happiness' => 1, 'mood' => 1, 'social' => 1];
}

/**
 * Get cat personality social bonuses
 */
function getCatPersonalitySocialBonuses($personalityType) {
    $bonuses = [
        'playful' => ['happiness' => 2, 'mood' => 2, 'social' => 3],
        'curious' => ['happiness' => 2, 'mood' => 3, 'social' => 2],
        'lazy' => ['happiness' => 1, 'mood' => 1, 'social' => 1],
        'territorial' => ['happiness' => 1, 'mood' => 1, 'social' => 2],
        'social_butterfly' => ['happiness' => 3, 'mood' => 3, 'social' => 4]
    ];
    
    return $bonuses[$personalityType] ?? ['happiness' => 1, 'mood' => 1, 'social' => 1];
}
