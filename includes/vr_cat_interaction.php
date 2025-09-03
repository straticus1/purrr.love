<?php
/**
 * 🥽 Purrr.love VR Cat Interaction System
 * Next-gen virtual reality cat petting and play
 */

// VR interaction configuration
define('VR_INTERACTION_TYPES', [
    'petting' => ['head', 'back', 'belly', 'tail', 'paws'],
    'playing' => ['laser_pointer', 'feather_toy', 'ball', 'string', 'box'],
    'grooming' => ['brushing', 'combing', 'massage', 'cleaning'],
    'training' => ['sit', 'stay', 'come', 'high_five', 'spin']
]);

define('VR_SENSITIVITY_LEVELS', [
    'gentle' => 0.3,
    'normal' => 0.6,
    'playful' => 0.9,
    'intense' => 1.2
]);

/**
 * Initialize VR cat interaction session
 */
function initializeVRInteraction($catId, $userId, $vrDevice = 'webvr') {
    // Verify cat ownership
    if (!canInteractWithCat($catId, $userId)) {
        throw new Exception('Cannot interact with this cat', 403);
    }
    
    // Get cat's current state
    $cat = getCatById($catId);
    if (!$cat) {
        throw new Exception('Cat not found', 404);
    }
    
    // Check cat's mood and energy for VR interaction
    if ($cat['energy'] < 20) {
        throw new Exception('Cat is too tired for VR interaction', 400);
    }
    
    if ($cat['mood'] < 30) {
        throw new Exception('Cat is not in the mood for VR interaction', 400);
    }
    
    // Create VR session
    $sessionId = createVRInteractionSession($catId, $userId, $vrDevice);
    
    // Initialize cat's VR behavior patterns
    $vrBehavior = initializeCatVRBehavior($cat);
    
    // Return VR session data
    return [
        'session_id' => $sessionId,
        'cat' => [
            'id' => $cat['id'],
            'name' => $cat['name'],
            'personality_type' => $cat['personality_type'],
            'current_mood' => $cat['mood'],
            'energy_level' => $cat['energy'],
            'vr_behavior' => $vrBehavior
        ],
        'interaction_types' => VR_INTERACTION_TYPES,
        'sensitivity_levels' => VR_SENSITIVITY_LEVELS,
        'vr_device' => $vrDevice,
        'session_start' => time()
    ];
}

/**
 * Process VR cat interaction
 */
function processVRInteraction($sessionId, $interactionType, $interactionData) {
    // Validate session
    $session = getVRInteractionSession($sessionId);
    if (!$session || $session['active'] == false) {
        throw new Exception('Invalid or expired VR session', 400);
    }
    
    // Validate interaction type
    if (!isValidVRInteractionType($interactionType)) {
        throw new Exception('Invalid interaction type', 400);
    }
    
    // Get cat's current VR behavior
    $cat = getCatById($session['cat_id']);
    $vrBehavior = getCatVRBehavior($cat['id']);
    
    // Process interaction based on type
    $result = [];
    
    switch ($interactionType) {
        case 'petting':
            $result = processVRPetting($cat, $vrBehavior, $interactionData);
            break;
            
        case 'playing':
            $result = processVRPlaying($cat, $vrBehavior, $interactionData);
            break;
            
        case 'grooming':
            $result = processVRGrooming($cat, $vrBehavior, $interactionData);
            break;
            
        case 'training':
            $result = processVRTraining($cat, $vrBehavior, $interactionData);
            break;
            
        default:
            throw new Exception('Unsupported interaction type', 400);
    }
    
    // Update cat's VR behavior based on interaction
    updateCatVRBehavior($cat['id'], $result['behavior_changes']);
    
    // Update cat's stats
    updateCatStatsFromVRInteraction($cat['id'], $result['stat_changes']);
    
    // Log VR interaction
    logVRInteraction($sessionId, $interactionType, $interactionData, $result);
    
    // Return interaction result
    return [
        'success' => true,
        'interaction_type' => $interactionType,
        'cat_response' => $result['cat_response'],
        'stat_changes' => $result['stat_changes'],
        'behavior_changes' => $result['behavior_changes'],
        'vr_feedback' => $result['vr_feedback']
    ];
}

/**
 * Process VR petting interaction
 */
function processVRPetting($cat, $vrBehavior, $interactionData) {
    $pettingArea = $interactionData['area'] ?? 'head';
    $pressure = $interactionData['pressure'] ?? 'normal';
    $duration = $interactionData['duration'] ?? 1;
    
    // Get cat's petting preferences
    $preferences = $vrBehavior['petting_preferences'] ?? [];
    $sensitivity = $vrBehavior['sensitivity'] ?? 'normal';
    
    // Calculate cat's response based on preferences and personality
    $response = calculateCatPettingResponse($cat, $pettingArea, $pressure, $duration, $preferences, $sensitivity);
    
    // Calculate stat changes
    $statChanges = [
        'happiness' => $response['happiness_change'],
        'energy' => $response['energy_change'],
        'mood' => $response['mood_change'],
        'bonding' => $response['bonding_change']
    ];
    
    // Calculate behavior changes
    $behaviorChanges = [
        'petting_preferences' => $response['preference_updates'],
        'sensitivity' => $response['sensitivity_adjustment']
    ];
    
    // Generate VR feedback
    $vrFeedback = generateVRPettingFeedback($response, $cat['personality_type']);
    
    return [
        'cat_response' => $response,
        'stat_changes' => $statChanges,
        'behavior_changes' => $behaviorChanges,
        'vr_feedback' => $vrFeedback
    ];
}

/**
 * Process VR playing interaction
 */
function processVRPlaying($cat, $vrBehavior, $interactionData) {
    $toyType = $interactionData['toy'] ?? 'laser_pointer';
    $intensity = $interactionData['intensity'] ?? 'normal';
    $duration = $interactionData['duration'] ?? 1;
    
    // Get cat's play preferences
    $preferences = $vrBehavior['play_preferences'] ?? [];
    $playStyle = $vrBehavior['play_style'] ?? 'curious';
    
    // Calculate cat's response to play
    $response = calculateCatPlayResponse($cat, $toyType, $intensity, $duration, $preferences, $playStyle);
    
    // Calculate stat changes
    $statChanges = [
        'happiness' => $response['happiness_change'],
        'energy' => $response['energy_change'],
        'mood' => $response['mood_change'],
        'training' => $response['training_change']
    ];
    
    // Calculate behavior changes
    $behaviorChanges = [
        'play_preferences' => $response['preference_updates'],
        'play_style' => $response['style_adjustment']
    ];
    
    // Generate VR feedback
    $vrFeedback = generateVRPlayFeedback($response, $cat['personality_type']);
    
    return [
        'cat_response' => $response,
        'stat_changes' => $statChanges,
        'behavior_changes' => $behaviorChanges,
        'vr_feedback' => $vrFeedback
    ];
}

/**
 * Process VR grooming interaction
 */
function processVRGrooming($cat, $vrBehavior, $interactionData) {
    $groomingType = $interactionData['type'] ?? 'brushing';
    $gentleness = $interactionData['gentleness'] ?? 'normal';
    $duration = $interactionData['duration'] ?? 1;
    
    // Get cat's grooming preferences
    $preferences = $vrBehavior['grooming_preferences'] ?? [];
    $tolerance = $vrBehavior['grooming_tolerance'] ?? 'normal';
    
    // Calculate cat's response to grooming
    $response = calculateCatGroomingResponse($cat, $groomingType, $gentleness, $duration, $preferences, $tolerance);
    
    // Calculate stat changes
    $statChanges = [
        'happiness' => $response['happiness_change'],
        'health' => $response['health_change'],
        'mood' => $response['mood_change'],
        'cleanliness' => $response['cleanliness_change']
    ];
    
    // Calculate behavior changes
    $behaviorChanges = [
        'grooming_preferences' => $response['preference_updates'],
        'grooming_tolerance' => $response['tolerance_adjustment']
    ];
    
    // Generate VR feedback
    $vrFeedback = generateVRGroomingFeedback($response, $cat['personality_type']);
    
    return [
        'cat_response' => $response,
        'stat_changes' => $statChanges,
        'behavior_changes' => $behaviorChanges,
        'vr_feedback' => $vrFeedback
    ];
}

/**
 * Process VR training interaction
 */
function processVRTraining($cat, $vrBehavior, $interactionData) {
    $command = $interactionData['command'] ?? 'sit';
    $difficulty = $interactionData['difficulty'] ?? 'normal';
    $repetitions = $interactionData['repetitions'] ?? 1;
    
    // Get cat's training level and preferences
    $trainingLevel = $vrBehavior['training_level'] ?? 1;
    $preferences = $vrBehavior['training_preferences'] ?? [];
    
    // Calculate training success and response
    $response = calculateCatTrainingResponse($cat, $command, $difficulty, $repetitions, $trainingLevel, $preferences);
    
    // Calculate stat changes
    $statChanges = [
        'happiness' => $response['happiness_change'],
        'energy' => $response['energy_change'],
        'training' => $response['training_change'],
        'intelligence' => $response['intelligence_change']
    ];
    
    // Calculate behavior changes
    $behaviorChanges = [
        'training_level' => $response['level_progress'],
        'training_preferences' => $response['preference_updates'],
        'learned_commands' => $response['new_commands']
    ];
    
    // Generate VR feedback
    $vrFeedback = generateVRTrainingFeedback($response, $cat['personality_type']);
    
    return [
        'cat_response' => $response,
        'stat_changes' => $statChanges,
        'behavior_changes' => $behaviorChanges,
        'vr_feedback' => $vrFeedback
    ];
}

/**
 * Calculate cat's response to petting
 */
function calculateCatPettingResponse($cat, $area, $pressure, $duration, $preferences, $sensitivity) {
    $baseResponse = [
        'happiness_change' => 0,
        'energy_change' => 0,
        'mood_change' => 0,
        'bonding_change' => 0,
        'preference_updates' => [],
        'sensitivity_adjustment' => $sensitivity
    ];
    
    // Get cat's personality modifiers
    $personalityModifiers = getCatPersonalityModifiers($cat['personality_type']);
    
    // Calculate area preference
    $areaPreference = $preferences[$area] ?? 0.5;
    $areaMultiplier = 1.0 + ($areaPreference - 0.5) * 2;
    
    // Calculate pressure sensitivity
    $pressureMultiplier = calculatePressureMultiplier($pressure, $sensitivity);
    
    // Calculate duration effect
    $durationMultiplier = min(1.5, 0.5 + ($duration * 0.1));
    
    // Apply personality modifiers
    $personalityMultiplier = $personalityModifiers['petting_affinity'] ?? 1.0;
    
    // Calculate final response
    $baseResponse['happiness_change'] = round(5 * $areaMultiplier * $pressureMultiplier * $durationMultiplier * $personalityMultiplier);
    $baseResponse['energy_change'] = round(-2 * $durationMultiplier);
    $baseResponse['mood_change'] = round(3 * $areaMultiplier * $pressureMultiplier * $personalityMultiplier);
    $baseResponse['bonding_change'] = round(2 * $durationMultiplier * $personalityMultiplier);
    
    // Update preferences based on positive response
    if ($baseResponse['happiness_change'] > 3) {
        $baseResponse['preference_updates'][$area] = min(1.0, $areaPreference + 0.1);
    }
    
    return $baseResponse;
}

/**
 * Calculate pressure multiplier based on sensitivity
 */
function calculatePressureMultiplier($pressure, $sensitivity) {
    $pressureValues = [
        'gentle' => 0.7,
        'normal' => 1.0,
        'firm' => 1.3,
        'rough' => 0.5
    ];
    
    $sensitivityValues = [
        'low' => 0.8,
        'normal' => 1.0,
        'high' => 1.2,
        'extreme' => 1.5
    ];
    
    $baseMultiplier = $pressureValues[$pressure] ?? 1.0;
    $sensitivityMultiplier = $sensitivityValues[$sensitivity] ?? 1.0;
    
    return $baseMultiplier * $sensitivityMultiplier;
}

/**
 * Get cat personality modifiers
 */
function getCatPersonalityModifiers($personalityType) {
    $modifiers = [
        'playful' => [
            'petting_affinity' => 1.2,
            'play_affinity' => 1.4,
            'grooming_affinity' => 0.8,
            'training_affinity' => 1.1
        ],
        'aloof' => [
            'petting_affinity' => 0.7,
            'play_affinity' => 0.6,
            'grooming_affinity' => 1.3,
            'training_affinity' => 0.9
        ],
        'curious' => [
            'petting_affinity' => 1.0,
            'play_affinity' => 1.3,
            'grooming_affinity' => 0.9,
            'training_affinity' => 1.4
        ],
        'lazy' => [
            'petting_affinity' => 1.1,
            'play_affinity' => 0.7,
            'grooming_affinity' => 1.2,
            'training_affinity' => 0.8
        ],
        'territorial' => [
            'petting_affinity' => 0.8,
            'play_affinity' => 1.1,
            'grooming_affinity' => 0.7,
            'training_affinity' => 1.0
        ],
        'social_butterfly' => [
            'petting_affinity' => 1.3,
            'play_affinity' => 1.2,
            'grooming_affinity' => 1.1,
            'training_affinity' => 1.2
        ]
    ];
    
    return $modifiers[$personalityType] ?? $modifiers['curious'];
}

/**
 * Generate VR feedback for interactions
 */
function generateVRPettingFeedback($response, $personalityType) {
    $feedback = [
        'visual' => [],
        'audio' => [],
        'haptic' => [],
        'cat_animation' => ''
    ];
    
    // Visual feedback
    if ($response['happiness_change'] > 5) {
        $feedback['visual'][] = 'cat_eyes_sparkle';
        $feedback['visual'][] = 'cat_tail_wag';
        $feedback['cat_animation'] = 'happy_purr';
    } elseif ($response['happiness_change'] > 2) {
        $feedback['visual'][] = 'cat_eyes_half_close';
        $feedback['cat_animation'] = 'content_sit';
    } else {
        $feedback['visual'][] = 'cat_ears_back';
        $feedback['cat_animation'] = 'uncomfortable';
    }
    
    // Audio feedback
    if ($response['happiness_change'] > 3) {
        $feedback['audio'][] = 'purr_sound';
        $feedback['audio'][] = 'happy_meow';
    } elseif ($response['happiness_change'] < 0) {
        $feedback['audio'][] = 'warning_growl';
    }
    
    // Haptic feedback
    if ($response['happiness_change'] > 4) {
        $feedback['haptic'][] = 'gentle_vibration';
    }
    
    return $feedback;
}

/**
 * Create VR interaction session
 */
function createVRInteractionSession($catId, $userId, $vrDevice) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO vr_interaction_sessions 
        (cat_id, user_id, vr_device, session_start, active)
        VALUES (?, ?, ?, ?, 1)
    ");
    
    $stmt->execute([$catId, $userId, $vrDevice, date('Y-m-d H:i:s')]);
    
    return $pdo->lastInsertId();
}

/**
 * Get VR interaction session
 */
function getVRInteractionSession($sessionId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM vr_interaction_sessions 
        WHERE id = ? AND active = 1
    ");
    
    $stmt->execute([$sessionId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Initialize cat VR behavior
 */
function initializeCatVRBehavior($cat) {
    // Get or create VR behavior profile
    $vrBehavior = getCatVRBehavior($cat['id']);
    
    if (!$vrBehavior) {
        $vrBehavior = createDefaultCatVRBehavior($cat);
    }
    
    return $vrBehavior;
}

/**
 * Get cat VR behavior
 */
function getCatVRBehavior($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM cat_vr_behavior 
        WHERE cat_id = ?
    ");
    
    $stmt->execute([$catId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Create default cat VR behavior
 */
function createDefaultCatVRBehavior($cat) {
    $pdo = get_db();
    
    $defaultBehavior = [
        'cat_id' => $cat['id'],
        'petting_preferences' => json_encode(['head' => 0.8, 'back' => 0.6, 'belly' => 0.3, 'tail' => 0.4, 'paws' => 0.5]),
        'play_preferences' => json_encode(['laser_pointer' => 0.9, 'feather_toy' => 0.7, 'ball' => 0.6, 'string' => 0.8, 'box' => 0.5]),
        'grooming_preferences' => json_encode(['brushing' => 0.6, 'combing' => 0.5, 'massage' => 0.8, 'cleaning' => 0.4]),
        'training_preferences' => json_encode(['sit' => 0.7, 'stay' => 0.5, 'come' => 0.8, 'high_five' => 0.6, 'spin' => 0.4]),
        'sensitivity' => 'normal',
        'play_style' => 'curious',
        'grooming_tolerance' => 'normal',
        'training_level' => 1,
        'learned_commands' => json_encode([]),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO cat_vr_behavior 
        (cat_id, petting_preferences, play_preferences, grooming_preferences, training_preferences, 
         sensitivity, play_style, grooming_tolerance, training_level, learned_commands, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $defaultBehavior['cat_id'],
        $defaultBehavior['petting_preferences'],
        $defaultBehavior['play_preferences'],
        $defaultBehavior['grooming_preferences'],
        $defaultBehavior['training_preferences'],
        $defaultBehavior['sensitivity'],
        $defaultBehavior['play_style'],
        $defaultBehavior['grooming_tolerance'],
        $defaultBehavior['training_level'],
        $defaultBehavior['learned_commands'],
        $defaultBehavior['created_at']
    ]);
    
    $defaultBehavior['id'] = $pdo->lastInsertId();
    return $defaultBehavior;
}

/**
 * Update cat VR behavior
 */
function updateCatVRBehavior($catId, $behaviorChanges) {
    if (empty($behaviorChanges)) {
        return;
    }
    
    $pdo = get_db();
    
    $updates = [];
    $values = [];
    
    foreach ($behaviorChanges as $field => $value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $updates[] = "$field = ?";
        $values[] = $value;
    }
    
    $values[] = $catId;
    
    $stmt = $pdo->prepare("
        UPDATE cat_vr_behavior 
        SET " . implode(', ', $updates) . "
        WHERE cat_id = ?
    ");
    
    $stmt->execute($values);
}

/**
 * Log VR interaction
 */
function logVRInteraction($sessionId, $interactionType, $interactionData, $result) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO vr_interaction_logs 
        (session_id, interaction_type, interaction_data, result_data, created_at)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $sessionId,
        $interactionType,
        json_encode($interactionData),
        json_encode($result),
        date('Y-m-d H:i:s')
    ]);
}

/**
 * Validate VR interaction type
 */
function isValidVRInteractionType($type) {
    return array_key_exists($type, VR_INTERACTION_TYPES);
}

/**
 * End VR interaction session
 */
function endVRInteractionSession($sessionId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        UPDATE vr_interaction_sessions 
        SET active = 0, session_end = ?
        WHERE id = ?
    ");
    
    $stmt->execute([date('Y-m-d H:i:s'), $sessionId]);
    
    return true;
}
