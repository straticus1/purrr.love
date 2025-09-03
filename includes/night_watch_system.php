<?php
/**
 * 🌙 Purrr.love Night Watch: Save the Strays System
 * A nighttime protection system where cats patrol neighborhoods and protect strays from bobcat attacks
 * Inspired by BanditCat's story of being saved from euthanasia - now he saves others!
 */

define('NIGHT_WATCH_HOURS', [
    'start' => 21, // 9 PM
    'end' => 6     // 6 AM
]);

define('BOBCAT_ACTIVITY_LEVELS', [
    'low' => 0.1,      // Rare sightings
    'medium' => 0.3,   // Occasional encounters
    'high' => 0.6,     // Frequent activity
    'critical' => 0.9  // Bobcat attack in progress
]);

define('GUARDIAN_CAT_ROLES', [
    'scout' => [
        'description' => 'Detect bobcat presence early',
        'personality_bonus' => ['curious', 'independent'],
        'abilities' => ['early_detection', 'stealth_patrol', 'danger_sense']
    ],
    'guardian' => [
        'description' => 'Confront and deter bobcats',
        'personality_bonus' => ['aggressive', 'playful'],
        'abilities' => ['bobcat_deterrence', 'emergency_response', 'protection_aura']
    ],
    'healer' => [
        'description' => 'Tend to injured strays',
        'personality_bonus' => ['calm', 'social'],
        'abilities' => ['first_aid', 'stray_rehabilitation', 'comfort_aura']
    ],
    'alarm' => [
        'description' => 'Alert to danger and coordinate response',
        'personality_bonus' => ['playful', 'curious'],
        'abilities' => ['emergency_alert', 'coordination', 'communication']
    ]
]);

define('PROTECTION_ZONE_TYPES', [
    'cat_condo' => [
        'name' => 'Cat Condo',
        'description' => 'Elevated shelter that bobcats can\'t reach',
        'cost' => 500,
        'protection_level' => 0.8,
        'capacity' => 5
    ],
    'motion_sensor' => [
        'name' => 'Motion Sensor',
        'description' => 'Detect bobcat movement in the area',
        'cost' => 200,
        'detection_range' => 50,
        'alert_speed' => 0.9
    ],
    'safe_haven' => [
        'name' => 'Safe Haven',
        'description' => 'Emergency shelter for strays under attack',
        'cost' => 300,
        'emergency_capacity' => 10,
        'healing_rate' => 0.3
    ],
    'community_alert' => [
        'name' => 'Community Alert System',
        'description' => 'Notify other players of bobcat activity',
        'cost' => 150,
        'alert_radius' => 1000,
        'response_time' => 0.8
    ]
]);

/**
 * Initialize night watch system for a user
 */
function initializeNightWatch($userId) {
    $pdo = get_db();
    
    // Check if user already has night watch initialized
    $stmt = $pdo->prepare("SELECT id FROM night_watch_systems WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Night watch already initialized'];
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO night_watch_systems (user_id, protection_level, active_zones, 
                                           total_cats_saved, total_bobcat_encounters, 
                                           community_reputation, created_at, updated_at)
            VALUES (?, 1, 0, 0, 0, 0, ?, ?)
        ");
        
        $stmt->execute([$userId, time(), time()]);
        
        // Create default protection zone around user's home
        createProtectionZone($userId, 'safe_haven', [
            'name' => 'Home Base',
            'location' => 'user_home',
            'radius' => 100
        ]);
        
        return [
            'success' => true,
            'message' => '🌙 Night Watch system initialized! Your cats can now protect strays from bobcats!',
            'system_id' => $pdo->lastInsertId()
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to initialize night watch: ' . $e->getMessage()];
    }
}

/**
 * Deploy cats for night patrol
 */
function deployNightPatrol($userId, $catIds, $patrolArea = 'neighborhood') {
    $pdo = get_db();
    
    // Check if it's night time
    if (!isNightTime()) {
        return ['success' => false, 'message' => 'Night patrols can only be deployed between 9 PM and 6 AM'];
    }
    
    // Validate cats and assign roles
    $deployedCats = [];
    foreach ($catIds as $catId) {
        $cat = getCatById($catId);
        if (!$cat || $cat['user_id'] != $userId) {
            continue;
        }
        
        $role = determineGuardianRole($cat);
        $deployedCats[] = [
            'cat_id' => $catId,
            'role' => $role,
            'personality_bonus' => getGuardianPersonalityBonus($cat, $role),
            'special_abilities' => getGuardianAbilities($cat, $role)
        ];
    }
    
    if (empty($deployedCats)) {
        return ['success' => false, 'message' => 'No valid cats found for deployment'];
    }
    
    try {
        // Create patrol session
        $stmt = $pdo->prepare("
            INSERT INTO night_patrols (user_id, patrol_area, deployed_cats, 
                                     start_time, status, created_at)
            VALUES (?, ?, ?, ?, 'active', ?)
        ");
        
        $stmt->execute([
            $userId,
            $patrolArea,
            json_encode($deployedCats),
            time(),
            time()
        ]);
        
        $patrolId = $pdo->lastInsertId();
        
        // Log deployment
        logNightWatchEvent($userId, 'patrol_deployed', [
            'patrol_id' => $patrolId,
            'cats_deployed' => count($deployedCats),
            'patrol_area' => $patrolArea
        ]);
        
        return [
            'success' => true,
            'message' => '🌙 Night patrol deployed with ' . count($deployedCats) . ' guardian cats!',
            'patrol_id' => $patrolId,
            'deployed_cats' => $deployedCats
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to deploy patrol: ' . $e->getMessage()];
    }
}

/**
 * Determine the best guardian role for a cat based on personality
 */
function determineGuardianRole($cat) {
    $personality = $cat['personality_type'];
    $specialCatId = $cat['special_cat_id'] ?? null;
    
    // Special cats get enhanced roles
    if ($specialCatId === 'bandit') {
        return 'guardian'; // BanditCat is the ultimate protector
    }
    
    $rolePreferences = [
        'playful' => ['guardian', 'alarm'],
        'curious' => ['scout', 'alarm'],
        'aggressive' => ['guardian', 'scout'],
        'calm' => ['healer', 'scout'],
        'shy' => ['scout', 'healer'],
        'independent' => ['scout', 'guardian'],
        'social' => ['healer', 'alarm'],
        'lazy' => ['alarm'] // Can still alert others
    ];
    
    $preferredRoles = $rolePreferences[$personality] ?? ['scout'];
    return $preferredRoles[0];
}

/**
 * Get guardian personality bonus for a specific role
 */
function getGuardianPersonalityBonus($cat, $role) {
    $personality = $cat['personality_type'];
    $roleConfig = GUARDIAN_CAT_ROLES[$role];
    
    if (in_array($personality, $roleConfig['personality_bonus'])) {
        return 1.5; // 50% bonus for personality match
    }
    
    return 1.0; // Base effectiveness
}

/**
 * Get guardian abilities for a cat and role
 */
function getGuardianAbilities($cat, $role) {
    $abilities = GUARDIAN_CAT_ROLES[$role]['abilities'];
    $specialCatId = $cat['special_cat_id'] ?? null;
    
    // Special cats get enhanced abilities
    if ($specialCatId === 'bandit') {
        $abilities[] = 'guardian_instinct';      // +100% protection bonus
        $abilities[] = 'stray_savior';           // Can rescue cats from danger
        $abilities[] = 'bobcat_deterrence_max';  // Maximum bobcat scare factor
        $abilities[] = 'emergency_response_max'; // Fastest response time
    }
    
    if ($specialCatId === 'luna') {
        $abilities[] = 'mystery_sense';          // Can detect hidden dangers
        $abilities[] = 'explorer_protection';    // Protects in unknown areas
    }
    
    if ($specialCatId === 'rycat') {
        $abilities[] = 'tech_coordination';      // Coordinates multiple cats
        $abilities[] = 'strategic_planning';     // Better patrol routes
    }
    
    return $abilities;
}

/**
 * Process night patrol events (called periodically during night)
 */
function processNightPatrolEvents($patrolId) {
    $pdo = get_db();
    
    // Get patrol details
    $stmt = $pdo->prepare("SELECT * FROM night_patrols WHERE id = ?");
    $stmt->execute([$patrolId]);
    $patrol = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$patrol || $patrol['status'] !== 'active') {
        return;
    }
    
    // Check for bobcat activity
    $bobcatActivity = checkBobcatActivity($patrol['patrol_area']);
    
    if ($bobcatActivity['level'] > 0.3) {
        // Bobcat detected - initiate protection sequence
        $protectionResult = initiateProtectionSequence($patrolId, $bobcatActivity);
        
        // Update patrol with results
        updatePatrolResults($patrolId, $protectionResult);
        
        // Send emergency alerts if needed
        if ($bobcatActivity['level'] > 0.7) {
            sendEmergencyAlert($patrol['user_id'], $bobcatActivity);
        }
    }
    
    // Check for stray cats in need
    $strayCats = findStrayCatsInDanger($patrol['patrol_area']);
    
    if (!empty($strayCats)) {
        $rescueResult = initiateStrayRescue($patrolId, $strayCats);
        updatePatrolResults($patrolId, $rescueResult);
    }
}

/**
 * Check for bobcat activity in a patrol area
 */
function checkBobcatActivity($patrolArea) {
    // Base bobcat activity level
    $baseLevel = BOBCAT_ACTIVITY_LEVELS['low'];
    
    // Weather conditions affect bobcat activity
    $weather = getCurrentWeather();
    if ($weather['condition'] === 'rain' || $weather['condition'] === 'snow') {
        $baseLevel *= 0.7; // Bobcats less active in bad weather
    }
    
    // Seasonal patterns
    $month = date('n');
    if ($month >= 3 && $month <= 6) {
        $baseLevel *= 1.3; // Spring mating season - more activity
    }
    
    // Random bobcat encounter
    $encounterChance = mt_rand(1, 100) / 100;
    
    if ($encounterChance < $baseLevel) {
        $activityLevel = min(1.0, $baseLevel * (1 + mt_rand(1, 3)));
        
        return [
            'detected' => true,
            'level' => $activityLevel,
            'threat_level' => $activityLevel > 0.7 ? 'critical' : ($activityLevel > 0.4 ? 'high' : 'medium'),
            'location' => $patrolArea,
            'timestamp' => time()
        ];
    }
    
    return [
        'detected' => false,
        'level' => 0,
        'threat_level' => 'none'
    ];
}

/**
 * Initiate protection sequence when bobcats are detected
 */
function initiateProtectionSequence($patrolId, $bobcatActivity) {
    $pdo = get_db();
    
    // Get deployed cats
    $stmt = $pdo->prepare("SELECT deployed_cats FROM night_patrols WHERE id = ?");
    $stmt->execute([$patrolId]);
    $patrol = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $deployedCats = json_decode($patrol['deployed_cats'], true);
    $protectionResult = [
        'bobcat_deterred' => false,
        'cats_saved' => 0,
        'injuries_prevented' => 0,
        'experience_gained' => 0
    ];
    
    foreach ($deployedCats as $cat) {
        $catData = getCatById($cat['cat_id']);
        $protectionEffectiveness = calculateProtectionEffectiveness($catData, $cat, $bobcatActivity);
        
        if ($protectionEffectiveness > 0.5) {
            $protectionResult['bobcat_deterred'] = true;
            $protectionResult['cats_saved'] += $protectionEffectiveness * 2;
            $protectionResult['injuries_prevented'] += $protectionEffectiveness * 3;
            $protectionResult['experience_gained'] += $protectionEffectiveness * 100;
            
            // Award experience to the cat
            awardNightWatchExperience($cat['cat_id'], $protectionEffectiveness * 100);
        }
    }
    
    return $protectionResult;
}

/**
 * Calculate protection effectiveness for a cat
 */
function calculateProtectionEffectiveness($cat, $deployment, $bobcatActivity) {
    $baseEffectiveness = 0.3;
    
    // Role effectiveness
    $roleEffectiveness = [
        'guardian' => 0.8,
        'scout' => 0.6,
        'healer' => 0.4,
        'alarm' => 0.5
    ];
    
    $baseEffectiveness *= $roleEffectiveness[$deployment['role']] ?? 0.5;
    
    // Personality bonus
    $baseEffectiveness *= $deployment['personality_bonus'];
    
    // Special cat bonuses
    if ($cat['special_cat_id'] === 'bandit') {
        $baseEffectiveness *= 2.0; // BanditCat is the ultimate protector!
    }
    
    // Bobcat activity level affects difficulty
    $difficultyMultiplier = 1.0 - ($bobcatActivity['level'] * 0.3);
    $baseEffectiveness *= $difficultyMultiplier;
    
    // Random variation
    $randomFactor = 0.8 + (mt_rand(1, 40) / 100);
    $baseEffectiveness *= $randomFactor;
    
    return min(1.0, max(0.0, $baseEffectiveness));
}

/**
 * Find stray cats in danger
 */
function findStrayCatsInDanger($patrolArea) {
    // Simulate finding stray cats in the area
    $strayCount = mt_rand(0, 3);
    $strayCats = [];
    
    for ($i = 0; $i < $strayCount; $i++) {
        $strayCats[] = [
            'id' => 'stray_' . mt_rand(1000, 9999),
            'name' => generateStrayName(),
            'condition' => mt_rand(1, 100),
            'location' => $patrolArea,
            'needs_rescue' => mt_rand(1, 100) < 30 // 30% chance of needing rescue
        ];
    }
    
    return $strayCats;
}

/**
 * Initiate stray cat rescue
 */
function initiateStrayRescue($patrolId, $strayCats) {
    $rescueResult = [
        'strays_found' => count($strayCats),
        'strays_rescued' => 0,
        'strays_sheltered' => 0,
        'experience_gained' => 0
    ];
    
    foreach ($strayCats as $stray) {
        if ($stray['needs_rescue']) {
            $rescueSuccess = mt_rand(1, 100) < 70; // 70% rescue success rate
            
            if ($rescueSuccess) {
                $rescueResult['strays_rescued']++;
                $rescueResult['experience_gained'] += 50;
            }
        } else {
            $rescueResult['strays_sheltered']++;
            $rescueResult['experience_gained'] += 25;
        }
    }
    
    return $rescueResult;
}

/**
 * Award night watch experience to a cat
 */
function awardNightWatchExperience($catId, $experience) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        UPDATE cats 
        SET experience = experience + ?, updated_at = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$experience, time(), $catId]);
    
    // Check for level up
    checkCatLevelUp($catId);
}

/**
 * Create a protection zone
 */
function createProtectionZone($userId, $zoneType, $zoneData) {
    $pdo = get_db();
    
    $zoneConfig = PROTECTION_ZONE_TYPES[$zoneType];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO protection_zones (user_id, zone_type, name, location, 
                                        radius, protection_level, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $zoneType,
            $zoneData['name'],
            $zoneData['location'],
            $zoneData['radius'] ?? 50,
            $zoneConfig['protection_level'] ?? 0.5,
            time()
        ]);
        
        return [
            'success' => true,
            'message' => "🛡️ Protection zone '{$zoneData['name']}' created!",
            'zone_id' => $pdo->lastInsertId()
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to create protection zone: ' . $e->getMessage()];
    }
}

/**
 * Check if it's currently night time
 */
function isNightTime() {
    $currentHour = (int)date('G');
    return $currentHour >= NIGHT_WATCH_HOURS['start'] || $currentHour < NIGHT_WATCH_HOURS['end'];
}

/**
 * Get current weather conditions
 */
function getCurrentWeather() {
    // Simulate weather - in production, integrate with weather API
    $conditions = ['clear', 'cloudy', 'rain', 'snow', 'fog'];
    $condition = $conditions[array_rand($conditions)];
    
    return [
        'condition' => $condition,
        'temperature' => mt_rand(20, 80),
        'humidity' => mt_rand(30, 90)
    ];
}

/**
 * Generate a random stray cat name
 */
function generateStrayName() {
    $names = [
        'Shadow', 'Mittens', 'Whiskers', 'Fluffy', 'Tiger', 'Smokey', 'Oreo',
        'Boots', 'Patches', 'Lucky', 'Milo', 'Bella', 'Simba', 'Luna',
        'Oliver', 'Lucy', 'Leo', 'Chloe', 'Max', 'Sophie', 'Charlie'
    ];
    
    return $names[array_rand($names)];
}

/**
 * Log night watch events
 */
function logNightWatchEvent($userId, $eventType, $eventData) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO night_watch_events (user_id, event_type, event_data, created_at)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $userId,
        $eventType,
        json_encode($eventData),
        time()
    ]);
}

/**
 * Update patrol results
 */
function updatePatrolResults($patrolId, $results) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        UPDATE night_patrols 
        SET results = ?, updated_at = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        json_encode($results),
        time(),
        $patrolId
    ]);
}

/**
 * Send emergency alert
 */
function sendEmergencyAlert($userId, $bobcatActivity) {
    // In production, this would send real notifications
    // For now, just log the alert
    
    logNightWatchEvent($userId, 'emergency_alert', [
        'bobcat_activity' => $bobcatActivity,
        'alert_level' => 'critical',
        'message' => '🚨 CRITICAL: Bobcat activity detected in your patrol area!'
    ]);
}

/**
 * Get night watch statistics for a user
 */
function getNightWatchStats($userId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM night_watch_systems WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stats) {
        return null;
    }
    
    // Get recent patrols
    $stmt = $pdo->prepare("
        SELECT * FROM night_patrols 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $recentPatrols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats['recent_patrols'] = $recentPatrols;
    
    return $stats;
}
?>
