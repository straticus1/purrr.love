<?php
/**
 * 🤖 Purrr.love AI Cat Behavior Learning System
 * Cats learn and adapt from user interactions using machine learning
 */

// AI learning configuration
define('AI_LEARNING_RATE', 0.1);
define('AI_MEMORY_DECAY', 0.95);
define('AI_BEHAVIOR_UPDATE_THRESHOLD', 0.3);
define('AI_MAX_MEMORY_SIZE', 1000);

// Behavior learning categories
define('AI_BEHAVIOR_CATEGORIES', [
    'interaction_preferences' => ['petting', 'playing', 'grooming', 'training'],
    'environmental_responses' => ['noise', 'light', 'temperature', 'crowds'],
    'social_behaviors' => ['friendliness', 'territorial', 'playful', 'shy'],
    'activity_patterns' => ['sleep_cycle', 'feeding_times', 'play_schedule', 'rest_periods']
]);

/**
 * Initialize AI learning for a cat
 */
function initializeAICatLearning($catId) {
    // Check if AI learning is already initialized
    $aiProfile = getAICatProfile($catId);
    if ($aiProfile) {
        return $aiProfile;
    }
    
    // Create new AI learning profile
    $aiProfile = createDefaultAICatProfile($catId);
    
    // Initialize behavior patterns
    initializeCatBehaviorPatterns($catId);
    
    // Initialize learning memory
    initializeCatLearningMemory($catId);
    
    return $aiProfile;
}

/**
 * Process user interaction for AI learning
 */
function processInteractionForAILearning($catId, $interactionType, $interactionData, $userResponse, $catResponse) {
    // Get cat's AI profile
    $aiProfile = getAICatProfile($catId);
    if (!$aiProfile) {
        $aiProfile = initializeAICatLearning($catId);
    }
    
    // Analyze interaction outcome
    $interactionOutcome = analyzeInteractionOutcome($interactionType, $interactionData, $userResponse, $catResponse);
    
    // Update behavior patterns
    updateCatBehaviorPatterns($catId, $interactionType, $interactionOutcome);
    
    // Update learning memory
    updateCatLearningMemory($catId, $interactionType, $interactionOutcome);
    
    // Apply learned behaviors
    applyLearnedBehaviors($catId, $aiProfile);
    
    // Log learning event
    logAILearningEvent($catId, $interactionType, $interactionOutcome);
    
    return [
        'learning_applied' => true,
        'behavior_changes' => $interactionOutcome['behavior_changes'],
        'new_patterns' => $interactionOutcome['new_patterns']
    ];
}

/**
 * Analyze interaction outcome for learning
 */
function analyzeInteractionOutcome($interactionType, $interactionData, $userResponse, $catResponse) {
    $outcome = [
        'success_score' => 0,
        'behavior_changes' => [],
        'new_patterns' => [],
        'learning_opportunities' => []
    ];
    
    // Calculate success score based on cat response
    $outcome['success_score'] = calculateInteractionSuccessScore($catResponse);
    
    // Identify behavior changes
    $outcome['behavior_changes'] = identifyBehaviorChanges($interactionType, $interactionData, $catResponse);
    
    // Detect new behavior patterns
    $outcome['new_patterns'] = detectNewBehaviorPatterns($interactionType, $interactionData, $catResponse);
    
    // Identify learning opportunities
    $outcome['learning_opportunities'] = identifyLearningOpportunities($interactionType, $outcome);
    
    return $outcome;
}

/**
 * Calculate interaction success score
 */
function calculateInteractionSuccessScore($catResponse) {
    $score = 0;
    
    // Happiness change
    if (isset($catResponse['happiness_change'])) {
        $score += max(0, $catResponse['happiness_change']) * 0.4;
    }
    
    // Mood change
    if (isset($catResponse['mood_change'])) {
        $score += max(0, $catResponse['mood_change']) * 0.3;
    }
    
    // Energy change (negative is good for some interactions)
    if (isset($catResponse['energy_change'])) {
        $score += max(0, -$catResponse['energy_change']) * 0.2;
    }
    
    // Bonding change
    if (isset($catResponse['bonding_change'])) {
        $score += max(0, $catResponse['bonding_change']) * 0.1;
    }
    
    return min(10, $score);
}

/**
 * Identify behavior changes from interaction
 */
function identifyBehaviorChanges($interactionType, $interactionData, $catResponse) {
    $changes = [];
    
    // Analyze interaction data for patterns
    foreach ($interactionData as $key => $value) {
        if (is_numeric($value)) {
            // Look for significant changes in numeric values
            $changes[$key] = [
                'value' => $value,
                'significance' => calculateValueSignificance($value)
            ];
        }
    }
    
    // Analyze cat response for behavioral indicators
    if (isset($catResponse['vr_feedback'])) {
        $feedback = $catResponse['vr_feedback'];
        
        // Visual feedback analysis
        if (isset($feedback['visual'])) {
            $changes['visual_response'] = analyzeVisualFeedback($feedback['visual']);
        }
        
        // Audio feedback analysis
        if (isset($feedback['audio'])) {
            $changes['audio_response'] = analyzeAudioFeedback($feedback['audio']);
        }
        
        // Animation analysis
        if (isset($feedback['cat_animation'])) {
            $changes['animation_response'] = analyzeAnimationFeedback($feedback['cat_animation']);
        }
    }
    
    return $changes;
}

/**
 * Detect new behavior patterns
 */
function detectNewBehaviorPatterns($interactionType, $interactionData, $catResponse) {
    $patterns = [];
    
    // Time-based patterns
    $currentTime = time();
    $hour = (int)date('H', $currentTime);
    $dayOfWeek = (int)date('N', $currentTime);
    
    // Detect time-based preferences
    $patterns['time_preferences'] = [
        'hour' => $hour,
        'day_of_week' => $dayOfWeek,
        'interaction_type' => $interactionType
    ];
    
    // Duration patterns
    if (isset($interactionData['duration'])) {
        $patterns['duration_preferences'] = [
            'preferred_duration' => $interactionData['duration'],
            'interaction_type' => $interactionType
        ];
    }
    
    // Intensity patterns
    if (isset($interactionData['intensity']) || isset($interactionData['pressure'])) {
        $intensity = $interactionData['intensity'] ?? $interactionData['pressure'];
        $patterns['intensity_preferences'] = [
            'preferred_intensity' => $intensity,
            'interaction_type' => $interactionType
        ];
    }
    
    // Response patterns
    if (isset($catResponse['happiness_change'])) {
        $patterns['response_patterns'] = [
            'happiness_threshold' => $catResponse['happiness_change'],
            'interaction_type' => $interactionType
        ];
    }
    
    return $patterns;
}

/**
 * Update cat behavior patterns
 */
function updateCatBehaviorPatterns($catId, $interactionType, $interactionOutcome) {
    $pdo = get_db();
    
    // Get current patterns
    $currentPatterns = getCatBehaviorPatterns($catId);
    
    // Update patterns based on interaction outcome
    $updatedPatterns = [];
    
    foreach ($currentPatterns as $category => $patterns) {
        $updatedPatterns[$category] = $patterns;
        
        if (isset($interactionOutcome['new_patterns'][$category])) {
            $newPatterns = $interactionOutcome['new_patterns'][$category];
            
            // Merge new patterns with existing ones
            foreach ($newPatterns as $patternKey => $patternValue) {
                if (isset($updatedPatterns[$category][$patternKey])) {
                    // Update existing pattern with learning
                    $updatedPatterns[$category][$patternKey] = updatePatternWithLearning(
                        $updatedPatterns[$category][$patternKey],
                        $patternValue,
                        $interactionOutcome['success_score']
                    );
                } else {
                    // Add new pattern
                    $updatedPatterns[$category][$patternKey] = $patternValue;
                }
            }
        }
    }
    
    // Save updated patterns
    $stmt = $pdo->prepare("
        UPDATE cat_ai_behavior_patterns 
        SET patterns = ?, updated_at = ?
        WHERE cat_id = ?
    ");
    
    $stmt->execute([
        json_encode($updatedPatterns),
        date('Y-m-d H:i:s'),
        $catId
    ]);
    
    return $updatedPatterns;
}

/**
 * Update pattern with learning
 */
function updatePatternWithLearning($existingPattern, $newPattern, $successScore) {
    // Apply learning rate based on success
    $learningRate = AI_LEARNING_RATE * ($successScore / 10);
    
    if (is_numeric($existingPattern) && is_numeric($newPattern)) {
        // Numeric pattern update
        return $existingPattern + ($newPattern - $existingPattern) * $learningRate;
    } elseif (is_array($existingPattern) && is_array($newPattern)) {
        // Array pattern update
        $updated = [];
        foreach ($newPattern as $key => $value) {
            if (isset($existingPattern[$key])) {
                $updated[$key] = updatePatternWithLearning($existingPattern[$key], $value, $successScore);
            } else {
                $updated[$key] = $value;
            }
        }
        return $updated;
    } else {
        // String or other pattern types
        return $successScore > 7 ? $newPattern : $existingPattern;
    }
}

/**
 * Update cat learning memory
 */
function updateCatLearningMemory($catId, $interactionType, $interactionOutcome) {
    $pdo = get_db();
    
    // Get current memory
    $currentMemory = getCatLearningMemory($catId);
    
    // Add new interaction to memory
    $newMemoryEntry = [
        'interaction_type' => $interactionType,
        'outcome' => $interactionOutcome,
        'timestamp' => time(),
        'success_score' => $interactionOutcome['success_score']
    ];
    
    // Add to memory
    $currentMemory[] = $newMemoryEntry;
    
    // Apply memory decay
    $currentMemory = applyMemoryDecay($currentMemory);
    
    // Limit memory size
    if (count($currentMemory) > AI_MAX_MEMORY_SIZE) {
        $currentMemory = array_slice($currentMemory, -AI_MAX_MEMORY_SIZE);
    }
    
    // Save updated memory
    $stmt = $pdo->prepare("
        UPDATE cat_ai_learning_memory 
        SET memory_data = ?, updated_at = ?
        WHERE cat_id = ?
    ");
    
    $stmt->execute([
        json_encode($currentMemory),
        date('Y-m-d H:i:s'),
        $catId
    ]);
    
    return $currentMemory;
}

/**
 * Apply memory decay to learning memory
 */
function applyMemoryDecay($memory) {
    $currentTime = time();
    
    foreach ($memory as &$entry) {
        $age = $currentTime - $entry['timestamp'];
        $decayFactor = pow(AI_MEMORY_DECAY, $age / 86400); // Daily decay
        
        // Apply decay to success score
        $entry['success_score'] *= $decayFactor;
        
        // Remove very old memories with low scores
        if ($age > 30 * 86400 && $entry['success_score'] < 0.1) {
            $entry = null;
        }
    }
    
    // Remove null entries
    return array_filter($memory);
}

/**
 * Apply learned behaviors to cat
 */
function applyLearnedBehaviors($catId, $aiProfile) {
    // Get current behavior patterns
    $patterns = getCatBehaviorPatterns($catId);
    
    // Get learning memory
    $memory = getCatLearningMemory($catId);
    
    // Analyze patterns for significant changes
    $significantChanges = analyzeSignificantChanges($patterns, $memory);
    
    // Apply changes that meet threshold
    foreach ($significantChanges as $change) {
        if ($change['significance'] >= AI_BEHAVIOR_UPDATE_THRESHOLD) {
            applyBehaviorChange($catId, $change);
        }
    }
    
    return $significantChanges;
}

/**
 * Analyze significant changes in behavior patterns
 */
function analyzeSignificantChanges($patterns, $memory) {
    $changes = [];
    
    // Analyze recent memory for patterns
    $recentMemory = array_slice($memory, -50); // Last 50 interactions
    
    foreach ($recentMemory as $entry) {
        $interactionType = $entry['interaction_type'];
        $successScore = $entry['success_score'];
        
        // Look for high-success patterns
        if ($successScore > 7) {
            $changes[] = [
                'type' => 'positive_reinforcement',
                'interaction_type' => $interactionType,
                'significance' => $successScore / 10,
                'data' => $entry['outcome']
            ];
        }
        
        // Look for low-success patterns to avoid
        if ($successScore < 3) {
            $changes[] = [
                'type' => 'negative_reinforcement',
                'interaction_type' => $interactionType,
                'significance' => (10 - $successScore) / 10,
                'data' => $entry['outcome']
            ];
        }
    }
    
    return $changes;
}

/**
 * Apply behavior change to cat
 */
function applyBehaviorChange($catId, $change) {
    $pdo = get_db();
    
    // Get cat's current stats
    $cat = getCatById($catId);
    
    // Apply changes based on change type
    switch ($change['type']) {
        case 'positive_reinforcement':
            // Increase positive behaviors
            $statChanges = calculatePositiveStatChanges($change);
            break;
            
        case 'negative_reinforcement':
            // Decrease negative behaviors
            $statChanges = calculateNegativeStatChanges($change);
            break;
            
        default:
            $statChanges = [];
    }
    
    // Apply stat changes
    if (!empty($statChanges)) {
        updateCatStats($catId, $statChanges);
    }
    
    // Log behavior change
    logBehaviorChange($catId, $change);
    
    return $statChanges;
}

/**
 * Calculate positive stat changes
 */
function calculatePositiveStatChanges($change) {
    $changes = [];
    
    // Increase happiness and mood for positive interactions
    if ($change['significance'] > 0.5) {
        $changes['happiness'] = round(5 * $change['significance']);
        $changes['mood'] = round(3 * $change['significance']);
    }
    
    // Increase training for successful training interactions
    if ($change['interaction_type'] === 'training' && $change['significance'] > 0.7) {
        $changes['training'] = round(2 * $change['significance']);
    }
    
    return $changes;
}

/**
 * Calculate negative stat changes
 */
function calculateNegativeStatChanges($change) {
    $changes = [];
    
    // Decrease mood for negative interactions
    if ($change['significance'] > 0.5) {
        $changes['mood'] = round(-2 * $change['significance']);
    }
    
    return $changes;
}

/**
 * Get AI cat profile
 */
function getAICatProfile($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM cat_ai_profiles 
        WHERE cat_id = ?
    ");
    
    $stmt->execute([$catId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Create default AI cat profile
 */
function createDefaultAICatProfile($catId) {
    $pdo = get_db();
    
    $defaultProfile = [
        'cat_id' => $catId,
        'learning_enabled' => true,
        'learning_rate' => AI_LEARNING_RATE,
        'memory_decay' => AI_MEMORY_DECAY,
        'behavior_update_threshold' => AI_BEHAVIOR_UPDATE_THRESHOLD,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO cat_ai_profiles 
        (cat_id, learning_enabled, learning_rate, memory_decay, behavior_update_threshold, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $defaultProfile['cat_id'],
        $defaultProfile['learning_enabled'],
        $defaultProfile['learning_rate'],
        $defaultProfile['memory_decay'],
        $defaultProfile['behavior_update_threshold'],
        $defaultProfile['created_at'],
        $defaultProfile['updated_at']
    ]);
    
    $defaultProfile['id'] = $pdo->lastInsertId();
    return $defaultProfile;
}

/**
 * Initialize cat behavior patterns
 */
function initializeCatBehaviorPatterns($catId) {
    $pdo = get_db();
    
    $defaultPatterns = [
        'interaction_preferences' => [
            'petting' => ['head' => 0.8, 'back' => 0.6, 'belly' => 0.3],
            'playing' => ['laser_pointer' => 0.9, 'feather_toy' => 0.7, 'ball' => 0.6],
            'grooming' => ['brushing' => 0.6, 'combing' => 0.5, 'massage' => 0.8],
            'training' => ['sit' => 0.7, 'stay' => 0.5, 'come' => 0.8]
        ],
        'environmental_responses' => [
            'noise' => ['loud' => 0.3, 'quiet' => 0.8, 'sudden' => 0.2],
            'light' => ['bright' => 0.6, 'dim' => 0.9, 'changing' => 0.7],
            'temperature' => ['warm' => 0.9, 'cool' => 0.7, 'hot' => 0.3],
            'crowds' => ['many_people' => 0.4, 'few_people' => 0.8, 'alone' => 0.9]
        ],
        'social_behaviors' => [
            'friendliness' => 0.7,
            'territorial' => 0.4,
            'playful' => 0.8,
            'shy' => 0.3
        ],
        'activity_patterns' => [
            'sleep_cycle' => ['morning' => 0.3, 'afternoon' => 0.6, 'evening' => 0.8, 'night' => 0.9],
            'feeding_times' => ['breakfast' => 0.9, 'lunch' => 0.7, 'dinner' => 0.9, 'snacks' => 0.8],
            'play_schedule' => ['morning' => 0.6, 'afternoon' => 0.8, 'evening' => 0.7, 'night' => 0.4],
            'rest_periods' => ['morning' => 0.8, 'afternoon' => 0.6, 'evening' => 0.4, 'night' => 0.9]
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO cat_ai_behavior_patterns 
        (cat_id, patterns, created_at, updated_at)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $catId,
        json_encode($defaultPatterns),
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ]);
    
    return $defaultPatterns;
}

/**
 * Get cat behavior patterns
 */
function getCatBehaviorPatterns($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT patterns FROM cat_ai_behavior_patterns 
        WHERE cat_id = ?
    ");
    
    $stmt->execute([$catId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? json_decode($result['patterns'], true) : [];
}

/**
 * Initialize cat learning memory
 */
function initializeCatLearningMemory($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO cat_ai_learning_memory 
        (cat_id, memory_data, created_at, updated_at)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $catId,
        json_encode([]),
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ]);
    
    return [];
}

/**
 * Get cat learning memory
 */
function getCatLearningMemory($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT memory_data FROM cat_ai_learning_memory 
        WHERE cat_id = ?
    ");
    
    $stmt->execute([$catId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? json_decode($result['memory_data'], true) : [];
}

/**
 * Log AI learning event
 */
function logAILearningEvent($catId, $interactionType, $interactionOutcome) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO cat_ai_learning_events 
        (cat_id, interaction_type, outcome_data, success_score, created_at)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $catId,
        $interactionType,
        json_encode($interactionOutcome),
        $interactionOutcome['success_score'],
        date('Y-m-d H:i:s')
    ]);
}

/**
 * Log behavior change
 */
function logBehaviorChange($catId, $change) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO cat_ai_behavior_changes 
        (cat_id, change_type, change_data, significance, created_at)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $catId,
        $change['type'],
        json_encode($change['data']),
        $change['significance'],
        date('Y-m-d H:i:s')
    ]);
}

/**
 * Get AI learning insights for a cat
 */
function getAICatLearningInsights($catId) {
    $aiProfile = getAICatProfile($catId);
    $patterns = getCatBehaviorPatterns($catId);
    $memory = getCatLearningMemory($catId);
    
    $insights = [
        'learning_progress' => calculateLearningProgress($memory),
        'behavior_evolution' => analyzeBehaviorEvolution($patterns),
        'preference_changes' => detectPreferenceChanges($patterns),
        'learning_recommendations' => generateLearningRecommendations($patterns, $memory)
    ];
    
    return $insights;
}

/**
 * Calculate learning progress
 */
function calculateLearningProgress($memory) {
    if (empty($memory)) {
        return ['total_interactions' => 0, 'average_success' => 0, 'learning_trend' => 'stable'];
    }
    
    $totalInteractions = count($memory);
    $averageSuccess = array_sum(array_column($memory, 'success_score')) / $totalInteractions;
    
    // Calculate learning trend
    $recentMemory = array_slice($memory, -10);
    $recentAverage = array_sum(array_column($recentMemory, 'success_score')) / count($recentMemory);
    
    if ($recentAverage > $averageSuccess + 1) {
        $trend = 'improving';
    } elseif ($recentAverage < $averageSuccess - 1) {
        $trend = 'declining';
    } else {
        $trend = 'stable';
    }
    
    return [
        'total_interactions' => $totalInteractions,
        'average_success' => round($averageSuccess, 2),
        'learning_trend' => $trend,
        'recent_performance' => round($recentAverage, 2)
    ];
}
