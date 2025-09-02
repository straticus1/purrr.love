<?php
/**
 * Purrr.love - Enhanced Cat Behavior System
 * Developed and Designed by Ryan Coleman. <coleman.ryan@gmail.com>
 * Enhanced with feline-specific behaviors, personality types, and territory system
 */

// Cat Personality Types
define('CAT_PERSONALITY_PLAYFUL', 1);
define('CAT_PERSONALITY_ALOOF', 2);
define('CAT_PERSONALITY_CURIOUS', 3);
define('CAT_PERSONALITY_LAZY', 4);
define('CAT_PERSONALITY_TERRITORIAL', 5);
define('CAT_PERSONALITY_SOCIAL_BUTTERFLY', 6);

// Cat Mood Types
define('CAT_MOOD_HAPPY', 1);
define('CAT_MOOD_CONTENT', 2);
define('CAT_MOOD_NEUTRAL', 3);
define('CAT_MOOD_ANNOYED', 4);
define('CAT_MOOD_STRESSED', 5);

// Territory Types
define('TERRITORY_HOME', 1);
define('TERRITORY_EXTENDED', 2);
define('TERRITORY_NEUTRAL', 3);

/**
 * Get cat personality name by type
 */
function getCatPersonalityName($personalityType) {
    $personalities = [
        CAT_PERSONALITY_PLAYFUL => 'Playful',
        CAT_PERSONALITY_ALOOF => 'Aloof',
        CAT_PERSONALITY_CURIOUS => 'Curious',
        CAT_PERSONALITY_LAZY => 'Lazy',
        CAT_PERSONALITY_TERRITORIAL => 'Territorial',
        CAT_PERSONALITY_SOCIAL_BUTTERFLY => 'Social Butterfly'
    ];
    
    return $personalities[$personalityType] ?? 'Unknown';
}

/**
 * Get cat personality description
 */
function getCatPersonalityDescription($personalityType) {
    $descriptions = [
        CAT_PERSONALITY_PLAYFUL => 'Energetic and loves to play games. Excels at interactive activities and gets bonus rewards from play sessions.',
        CAT_PERSONALITY_ALOOF => 'Independent and prefers solitude. Gets bonuses from quiet activities and alone time.',
        CAT_PERSONALITY_CURIOUS => 'Always exploring and investigating. Gets bonuses from exploration activities and discovery quests.',
        CAT_PERSONALITY_LAZY => 'Loves to relax and observe. Gets bonuses from passive activities and observation games.',
        CAT_PERSONALITY_TERRITORIAL => 'Protective of their space. Gets bonuses from territory management and climbing activities.',
        CAT_PERSONALITY_SOCIAL_BUTTERFLY => 'Loves interacting with other cats and humans. Gets bonuses from social activities and group events.'
    ];
    
    return $descriptions[$personalityType] ?? 'A mysterious cat with unknown traits.';
}

/**
 * Get cat mood name by type
 */
function getCatMoodName($moodType) {
    $moods = [
        CAT_MOOD_HAPPY => 'Happy',
        CAT_MOOD_CONTENT => 'Content',
        CAT_MOOD_NEUTRAL => 'Neutral',
        CAT_MOOD_ANNOYED => 'Annoyed',
        CAT_MOOD_STRESSED => 'Stressed'
    ];
    
    return $moods[$moodType] ?? 'Unknown';
}

/**
 * Calculate cat personality bonus for specific activities
 */
function getCatPersonalityBonus($personalityType, $activityType) {
    $bonuses = [
        CAT_PERSONALITY_PLAYFUL => [
            'paw_match' => 0.20,      // +20% win rate
            'yarn_chase' => 0.25,     // +25% speed bonus
            'laser_pointer' => 0.15,  // +15% interaction bonus
            'play_session' => 0.30    // +30% happiness gain
        ],
        CAT_PERSONALITY_ALOOF => [
            'bird_watching' => 0.25,  // +25% rare bird chance
            'sunbathing' => 0.20,     // +20% energy recovery
            'alone_time' => 0.30      // +30% mood improvement
        ],
        CAT_PERSONALITY_CURIOUS => [
            'mouse_hunt' => 0.25,     // +25% hunting success
            'exploration' => 0.30,    // +30% discovery chance
            'puzzle_solving' => 0.20  // +20% intelligence gain
        ],
        CAT_PERSONALITY_LAZY => [
            'bird_watching' => 0.40,  // +40% rare bird chance
            'napping' => 0.35,        // +35% energy recovery
            'relaxation' => 0.25      // +25% stress reduction
        ],
        CAT_PERSONALITY_TERRITORIAL => [
            'tower_climb' => 0.15,    // +15% climbing height
            'territory_marking' => 0.25, // +25% territory bonus
            'defense' => 0.20         // +20% protection bonus
        ],
        CAT_PERSONALITY_SOCIAL_BUTTERFLY => [
            'laser_pointer' => 0.25,  // +25% interaction bonus
            'social_events' => 0.30,  // +30% social bonus
            'group_activities' => 0.20 // +20% teamwork bonus
        ]
    ];
    
    return $bonuses[$personalityType][$activityType] ?? 0.0;
}

/**
 * Play a cat game with personality bonuses
 */
function playCatGame($gameType, $catId, $entryFee) {
    global $pdo;
    
    // Get cat information
    $stmt = $pdo->prepare("SELECT * FROM cats WHERE id = ?");
    $stmt->execute([$catId]);
    $cat = $stmt->fetch();
    
    if (!$cat) {
        return ['success' => false, 'message' => 'Cat not found'];
    }
    
    // Get cat's personality bonus for this game
    $personalityBonus = getCatPersonalityBonus($cat['personality_type'], $gameType);
    
    // Base win rates for different games
    $baseWinRates = [
        'paw_match' => 0.30,      // 30% base win rate
        'mouse_hunt' => 0.25,     // 25% base win rate
        'yarn_chase' => 0.35,     // 35% base win rate
        'tower_climb' => 0.40,    // 40% base win rate
        'bird_watching' => 0.45,  // 45% base win rate
        'laser_pointer' => 0.30   // 30% base win rate
    ];
    
    $baseWinRate = $baseWinRates[$gameType] ?? 0.30;
    $adjustedWinRate = $baseWinRate + $personalityBonus;
    
    // Ensure win rate doesn't exceed 80%
    $adjustedWinRate = min($adjustedWinRate, 0.80);
    
    // Determine if cat won
    $random = mt_rand() / mt_getrandmax();
    $won = $random <= $adjustedWinRate;
    
    if ($won) {
        // Calculate win amount based on game type and performance
        $performance = calculateCatPerformance($cat, $gameType);
        $winMultiplier = getGameWinMultiplier($gameType);
        $winAmount = $entryFee * $winMultiplier * (1 + $performance);
        
        return [
            'success' => true,
            'win_amount' => $winAmount,
            'performance' => $performance,
            'personality_bonus' => $personalityBonus,
            'adjusted_win_rate' => $adjustedWinRate
        ];
    } else {
        return [
            'success' => false,
            'performance' => 0,
            'personality_bonus' => $personalityBonus,
            'adjusted_win_rate' => $adjustedWinRate
        ];
    }
}

/**
 * Calculate cat performance based on stats and game type
 */
function calculateCatPerformance($cat, $gameType) {
    $performance = 0;
    
    // Base performance from cat level
    $performance += ($cat['level'] - 1) * 0.05; // +5% per level
    
    // Performance based on relevant stats
    switch ($gameType) {
        case 'paw_match':
        case 'yarn_chase':
        case 'laser_pointer':
            $performance += ($cat['energy'] / 100) * 0.20; // Energy affects active games
            $performance += ($cat['happiness'] / 100) * 0.15; // Happiness affects all games
            break;
            
        case 'mouse_hunt':
        case 'tower_climb':
            $performance += ($cat['energy'] / 100) * 0.25; // Energy affects physical games
            $performance += ($cat['level'] / 10) * 0.10; // Level affects hunting/climbing
            break;
            
        case 'bird_watching':
            $performance += ($cat['happiness'] / 100) * 0.30; // Happiness affects passive games
            $performance += ($cat['level'] / 10) * 0.15; // Level affects observation
            break;
    }
    
    // Territory bonus
    $territoryBonus = getCatTerritoryBonus($cat['id']);
    $performance += $territoryBonus;
    
    return min($performance, 1.0); // Cap at 100%
}

/**
 * Get game win multiplier
 */
function getGameWinMultiplier($gameType) {
    $multipliers = [
        'paw_match' => 2.5,      // 2.5x multiplier
        'mouse_hunt' => 3.0,     // 3.0x multiplier (higher risk/reward)
        'yarn_chase' => 2.8,     // 2.8x multiplier
        'tower_climb' => 2.2,    // 2.2x multiplier (easier to win)
        'bird_watching' => 2.0,  // 2.0x multiplier (easiest to win)
        'laser_pointer' => 2.6   // 2.6x multiplier
    ];
    
    return $multipliers[$gameType] ?? 2.5;
}

/**
 * Get game entry fee
 */
function getGameEntryFee($gameType) {
    $entryFees = [
        'paw_match' => 1.00,     // $1.00
        'mouse_hunt' => 1.50,    // $1.50 (higher risk/reward)
        'yarn_chase' => 1.25,    // $1.25
        'tower_climb' => 0.75,   // $0.75 (easier to win)
        'bird_watching' => 0.50, // $0.50 (easiest to win)
        'laser_pointer' => 1.25, // $1.25
        'cat_olympics' => 5.00,  // $5.00 (special event)
        'night_hunt' => 2.00     // $2.00 (special event)
    ];
    
    return $entryFees[$gameType] ?? 1.00;
}

/**
 * Get cat territory bonus
 */
function getCatTerritoryBonus($catId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT territory_type, territory_level 
        FROM cat_territories 
        WHERE cat_id = ? AND is_active = 1
        ORDER BY territory_level DESC 
        LIMIT 1
    ");
    $stmt->execute([$catId]);
    $territory = $stmt->fetch();
    
    if (!$territory) {
        return 0.0;
    }
    
    $baseBonuses = [
        TERRITORY_HOME => 0.15,      // +15% in home territory
        TERRITORY_EXTENDED => 0.08,  // +8% in extended territory
        TERRITORY_NEUTRAL => 0.03    // +3% in neutral territory
    ];
    
    $baseBonus = $baseBonuses[$territory['territory_type']] ?? 0.0;
    $levelBonus = ($territory['territory_level'] - 1) * 0.02; // +2% per level
    
    return $baseBonus + $levelBonus;
}

/**
 * Update cat stats after game performance
 */
function updateCatGameStats($catId, $gameType, $performance) {
    global $pdo;
    
    // Calculate stat gains based on performance
    $happinessGain = intval($performance * 10); // 0-10 happiness
    $energyGain = intval($performance * 8);     // 0-8 energy
    $experienceGain = intval($performance * 15); // 0-15 experience
    
    // Update cat stats
    $stmt = $pdo->prepare("
        UPDATE cats 
        SET happiness = LEAST(100, happiness + ?),
            energy = LEAST(100, energy + ?),
            experience = experience + ?,
            last_game_played = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$happinessGain, $energyGain, $experienceGain, $catId]);
    
    // Check for level up
    checkCatLevelUp($catId);
    
    // Update territory marking if cat performed well
    if ($performance > 0.7) {
        updateCatTerritoryMarking($catId, $gameType);
    }
}

/**
 * Check if cat should level up
 */
function checkCatLevelUp($catId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT level, experience FROM cats WHERE id = ?");
    $stmt->execute([$catId]);
    $cat = $stmt->fetch();
    
    $requiredExp = $cat['level'] * 100; // 100 exp per level
    
    if ($cat['experience'] >= $requiredExp) {
        $newLevel = $cat['level'] + 1;
        $remainingExp = $cat['experience'] - $requiredExp;
        
        $stmt = $pdo->prepare("
            UPDATE cats 
            SET level = ?, experience = ?, level_up_date = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$newLevel, $remainingExp, $catId]);
        
        // Trigger level up event
        triggerCatLevelUpEvent($catId, $newLevel);
    }
}

/**
 * Update cat territory marking
 */
function updateCatTerritoryMarking($catId, $gameType) {
    global $pdo;
    
    // Cats mark territory after successful activities
    $markingStrength = 1;
    
    // Different games provide different marking strength
    switch ($gameType) {
        case 'tower_climb':
            $markingStrength = 2; // Climbing provides strong marking
            break;
        case 'mouse_hunt':
            $markingStrength = 1.5; // Hunting provides medium marking
            break;
        case 'territory_defense':
            $markingStrength = 3; // Defense provides strongest marking
            break;
    }
    
    // Add territory marking
    $stmt = $pdo->prepare("
        INSERT INTO cat_territory_markings (cat_id, marking_strength, game_type, created_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
        marking_strength = marking_strength + VALUES(marking_strength),
        updated_at = NOW()
    ");
    $stmt->execute([$catId, $markingStrength, $gameType]);
}

/**
 * Trigger cat level up event
 */
function triggerCatLevelUpEvent($catId, $newLevel) {
    global $pdo;
    
    // Record level up event
    $stmt = $pdo->prepare("
        INSERT INTO cat_events (cat_id, event_type, event_data, created_at)
        VALUES (?, 'level_up', ?, NOW())
    ");
    $stmt->execute([$catId, json_encode(['new_level' => $newLevel])]);
    
    // Check for special rewards at milestone levels
    if ($newLevel % 5 == 0) {
        // Every 5 levels, give special reward
        giveCatMilestoneReward($catId, $newLevel);
    }
}

/**
 * Give cat milestone reward
 */
function giveCatMilestoneReward($catId, $level) {
    global $pdo;
    
    $rewards = [
        5 => ['type' => 'item', 'item_id' => 'rare_toy'],
        10 => ['type' => 'item', 'item_id' => 'premium_food'],
        15 => ['type' => 'item', 'item_id' => 'designer_collar'],
        20 => ['type' => 'crypto', 'amount' => 0.001, 'crypto_type' => 'BTC'],
        25 => ['type' => 'item', 'item_id' => 'luxury_bed'],
        30 => ['type' => 'crypto', 'amount' => 0.005, 'crypto_type' => 'ETH']
    ];
    
    if (isset($rewards[$level])) {
        $reward = $rewards[$level];
        
        // Record reward
        $stmt = $pdo->prepare("
            INSERT INTO cat_rewards (cat_id, reward_type, reward_data, level_earned, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$catId, $reward['type'], json_encode($reward), $level]);
        
        // Apply reward
        applyCatReward($catId, $reward);
    }
}

/**
 * Apply cat reward
 */
function applyCatReward($catId, $reward) {
    global $pdo;
    
    switch ($reward['type']) {
        case 'item':
            // Add item to cat's inventory
            $stmt = $pdo->prepare("
                INSERT INTO cat_inventory (cat_id, item_id, quantity, acquired_from, created_at)
                VALUES (?, ?, 1, 'level_up', NOW())
            ");
            $stmt->execute([$catId, $reward['item_id']]);
            break;
            
        case 'crypto':
            // Add crypto to owner's balance
            $stmt = $pdo->prepare("
                SELECT user_id FROM cats WHERE id = ?
            ");
            $stmt->execute([$catId]);
            $cat = $stmt->fetch();
            
            if ($cat) {
                updateUserBalance($cat['user_id'], $reward['crypto_type'], $reward['amount'], 'add');
            }
            break;
    }
}

/**
 * Get cat's current mood based on stats and recent activities
 */
function getCatCurrentMood($catId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT happiness, energy, hunger, last_fed, last_played, last_groomed
        FROM cats WHERE id = ?
    ");
    $stmt->execute([$catId]);
    $cat = $stmt->fetch();
    
    if (!$cat) {
        return CAT_MOOD_NEUTRAL;
    }
    
    $moodScore = 0;
    
    // Happiness contributes most to mood
    $moodScore += ($cat['happiness'] / 100) * 40;
    
    // Energy level affects mood
    $moodScore += ($cat['energy'] / 100) * 20;
    
    // Hunger affects mood negatively
    $hungerPenalty = max(0, (100 - $cat['hunger']) / 100) * 15;
    $moodScore -= $hungerPenalty;
    
    // Recent activities affect mood
    $timeSinceFed = time() - strtotime($cat['last_fed']);
    $timeSincePlayed = time() - strtotime($cat['last_played']);
    $timeSinceGroomed = time() - strtotime($cat['last_groomed']);
    
    if ($timeSinceFed < 3600) $moodScore += 10; // Fed within last hour
    if ($timeSincePlayed < 7200) $moodScore += 8; // Played within last 2 hours
    if ($timeSinceGroomed < 86400) $moodScore += 5; // Groomed within last day
    
    // Determine mood based on score
    if ($moodScore >= 80) return CAT_MOOD_HAPPY;
    if ($moodScore >= 60) return CAT_MOOD_CONTENT;
    if ($moodScore >= 40) return CAT_MOOD_NEUTRAL;
    if ($moodScore >= 20) return CAT_MOOD_ANNOYED;
    return CAT_MOOD_STRESSED;
}

/**
 * Simulate cat's natural behavior changes over time
 */
function simulateCatBehavior($catId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM cats WHERE id = ?
    ");
    $stmt->execute([$catId]);
    $cat = $stmt->fetch();
    
    if (!$cat) return;
    
    $currentTime = time();
    $lastUpdate = strtotime($cat['last_behavior_update'] ?? 'now');
    $hoursSinceUpdate = ($currentTime - $lastUpdate) / 3600;
    
    if ($hoursSinceUpdate < 1) return; // Update at most once per hour
    
    // Natural stat changes
    $hungerDecrease = $hoursSinceUpdate * 2; // Hunger decreases by 2 per hour
    $energyIncrease = $hoursSinceUpdate * 1.5; // Energy increases by 1.5 per hour
    $happinessDecrease = $hoursSinceUpdate * 0.5; // Happiness decreases slowly
    
    // Apply changes
    $newHunger = max(0, $cat['hunger'] - $hungerDecrease);
    $newEnergy = min(100, $cat['energy'] + $energyIncrease);
    $newHappiness = max(0, $cat['happiness'] - $happinessDecrease);
    
    // Update cat
    $stmt = $pdo->prepare("
        UPDATE cats 
        SET hunger = ?, energy = ?, happiness = ?, last_behavior_update = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$newHunger, $newEnergy, $newHappiness, $catId]);
    
    // Trigger events based on changes
    if ($newHunger < 20) {
        triggerCatEvent($catId, 'hungry', ['hunger_level' => $newHunger]);
    }
    
    if ($newHappiness < 30) {
        triggerCatEvent($catId, 'unhappy', ['happiness_level' => $newHappiness]);
    }
}

/**
 * Trigger cat event
 */
function triggerCatEvent($catId, $eventType, $eventData = []) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO cat_events (cat_id, event_type, event_data, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$catId, $eventType, json_encode($eventData)]);
    
    // Notify owner if it's an important event
    $importantEvents = ['hungry', 'unhappy', 'sick', 'level_up'];
    if (in_array($eventType, $importantEvents)) {
        notifyCatOwner($catId, $eventType, $eventData);
    }
}

/**
 * Notify cat owner
 */
function notifyCatOwner($catId, $eventType, $eventData) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT user_id FROM cats WHERE id = ?
    ");
    $stmt->execute([$catId]);
    $cat = $stmt->fetch();
    
    if ($cat) {
        $notificationMessages = [
            'hungry' => 'Your cat is getting hungry! Consider feeding them soon.',
            'unhappy' => 'Your cat seems unhappy. They might need some attention or playtime.',
            'sick' => 'Your cat appears to be unwell. Consider visiting the vet clinic.',
            'level_up' => 'Congratulations! Your cat has leveled up!'
        ];
        
        $message = $notificationMessages[$eventType] ?? 'Something happened with your cat!';
        
        // Add notification to user's notification system
        $stmt = $pdo->prepare("
            INSERT INTO user_notifications (user_id, type, message, data, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$cat['user_id'], 'cat_event', $message, json_encode($eventData)]);
    }
}
?>
