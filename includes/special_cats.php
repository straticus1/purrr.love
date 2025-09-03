<?php
/**
 * 🐱 Purrr.love Special Cats System
 * Hidden cats that can be earned through special achievements
 */

define('SPECIAL_CATS', [
    'bandit' => [
        'id' => 'bandit',
        'name' => 'Bandit',
        'full_name' => 'BanditCat',
        'description' => 'A blind tuxedo cat who was super playful and loved to catch mice. He lived on top of a pizzeria with his blind nerd owner named RyCat.',
        'breed' => 'tuxedo',
        'personality_type' => 'playful',
        'special_traits' => [
            'blind' => true,
            'mouse_hunter' => true,
            'pizzeria_resident' => true,
            'rycat_companion' => true
        ],
        'unlock_conditions' => [
            'type' => 'achievement',
            'achievement' => 'mouse_master',
            'description' => 'Catch 100 mice in Mouse Hunt game'
        ],
        'stats' => [
            'level' => 15,
            'experience' => 1500,
            'health' => 100,
            'hunger' => 100,
            'happiness' => 100,
            'energy' => 100,
            'age_days' => 365
        ],
        'special_abilities' => [
            'enhanced_mouse_hunting' => 'Gets +50% bonus in Mouse Hunt game',
            'pizzeria_nostalgia' => 'Loves pizza-themed toys and treats',
            'rycat_bond' => 'Special interactions with RyCat character'
        ],
        'rarity' => 'legendary',
        'unlock_message' => '🎉 You\'ve unlocked BanditCat! This blind tuxedo cat was a legendary mouse hunter who lived on top of a pizzeria with his owner RyCat!'
    ],
    
    'luna' => [
        'id' => 'luna',
        'name' => 'Luna',
        'full_name' => 'LunaCat',
        'description' => 'Also known as Lunatic, Luna was stolen and she also used to live on top of the Pizzeria. She has a mysterious past and loves to explore.',
        'breed' => 'mystery',
        'personality_type' => 'curious',
        'special_traits' => [
            'stolen_past' => true,
            'pizzeria_resident' => true,
            'explorer' => true,
            'mysterious' => true
        ],
        'unlock_conditions' => [
            'type' => 'achievement',
            'achievement' => 'explorer_master',
            'description' => 'Complete 50 exploration adventures'
        ],
        'stats' => [
            'level' => 12,
            'experience' => 1200,
            'health' => 100,
            'hunger' => 100,
            'happiness' => 100,
            'energy' => 100,
            'age_days' => 300
        ],
        'special_abilities' => [
            'enhanced_exploration' => 'Gets +40% bonus in exploration adventures',
            'mystery_sense' => 'Can find hidden items and secret areas',
            'pizzeria_memory' => 'Special knowledge of pizzeria locations'
        ],
        'rarity' => 'epic',
        'unlock_message' => '🌙 You\'ve unlocked LunaCat! This mysterious cat has a stolen past and loves to explore, especially places that remind her of the pizzeria!'
    ],
    
    'rycat' => [
        'id' => 'rycat',
        'name' => 'RyCat',
        'full_name' => 'RyCat the Nerd',
        'description' => 'A blind nerd cat who was Bandit\'s owner and lived on top of the pizzeria. He loves technology and has a special bond with Bandit.',
        'breed' => 'nerd_cat',
        'personality_type' => 'curious',
        'special_traits' => [
            'blind' => true,
            'nerd' => true,
            'pizzeria_resident' => true,
            'bandit_owner' => true,
            'tech_savvy' => true
        ],
        'unlock_conditions' => [
            'type' => 'achievement',
            'achievement' => 'tech_master',
            'description' => 'Complete 25 puzzle games and tech challenges'
        ],
        'stats' => [
            'level' => 10,
            'experience' => 1000,
            'health' => 100,
            'hunger' => 100,
            'happiness' => 100,
            'energy' => 100,
            'age_days' => 250
        ],
        'special_abilities' => [
            'enhanced_puzzles' => 'Gets +60% bonus in puzzle games',
            'tech_insight' => 'Can hack special game modes',
            'bandit_bond' => 'Special team bonuses when playing with Bandit'
        ],
        'rarity' => 'rare',
        'unlock_message' => '👓 You\'ve unlocked RyCat! This blind nerd cat was Bandit\'s owner and loves technology. He has a special bond with Bandit!'
    ]
]);

/**
 * Check if a user can unlock a special cat
 */
function canUnlockSpecialCat($userId, $catId) {
    $specialCat = SPECIAL_CATS[$catId] ?? null;
    if (!$specialCat) {
        return false;
    }
    
    $conditions = $specialCat['unlock_conditions'];
    
    switch ($conditions['type']) {
        case 'achievement':
            return hasAchievement($userId, $conditions['achievement']);
        case 'quest':
            return hasCompletedQuest($userId, $conditions['quest']);
        case 'level':
            return getUserLevel($userId) >= $conditions['level'];
        case 'special':
            return checkSpecialUnlockCondition($userId, $conditions);
        default:
            return false;
    }
}

/**
 * Unlock a special cat for a user
 */
function unlockSpecialCat($userId, $catId) {
    $specialCat = SPECIAL_CATS[$catId] ?? null;
    if (!$specialCat) {
        return ['success' => false, 'message' => 'Special cat not found'];
    }
    
    if (!canUnlockSpecialCat($userId, $catId)) {
        return ['success' => false, 'message' => 'You haven\'t met the unlock conditions yet'];
    }
    
    // Check if user already has this cat
    if (userHasSpecialCat($userId, $catId)) {
        return ['success' => false, 'message' => 'You already have this special cat'];
    }
    
    $pdo = get_db();
    
    try {
        // Create the special cat
        $stmt = $pdo->prepare("
            INSERT INTO cats (user_id, name, full_name, breed, personality_type, response_type, 
                            special_cat_id, level, experience, health, hunger, happiness, energy, 
                            age_days, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $specialCat['name'],
            $specialCat['full_name'],
            $specialCat['breed'],
            $specialCat['personality_type'],
            'dual_responder', // Special cats respond to both catnip and honeysuckle
            $catId,
            $specialCat['stats']['level'],
            $specialCat['stats']['experience'],
            $specialCat['stats']['health'],
            $specialCat['stats']['hunger'],
            $specialCat['stats']['happiness'],
            $specialCat['stats']['energy'],
            $specialCat['stats']['age_days'],
            time(),
            time()
        ]);
        
        $catId = $pdo->lastInsertId();
        
        // Add special traits
        foreach ($specialCat['special_traits'] as $trait => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO cat_traits (cat_id, trait_name, trait_value, created_at)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$catId, $trait, $value, time()]);
        }
        
        // Log the unlock
        logSpecialCatUnlock($userId, $catId, $specialCat);
        
        return [
            'success' => true,
            'message' => $specialCat['unlock_message'],
            'cat_id' => $catId,
            'cat_name' => $specialCat['name']
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to unlock special cat: ' . $e->getMessage()];
    }
}

/**
 * Check if user has a specific special cat
 */
function userHasSpecialCat($userId, $specialCatId) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT id FROM cats 
        WHERE user_id = ? AND special_cat_id = ?
    ");
    $stmt->execute([$userId, $specialCatId]);
    return $stmt->fetch() !== false;
}

/**
 * Get all special cats a user has unlocked
 */
function getUserSpecialCats($userId) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT c.*, sc.name as special_name, sc.description, sc.rarity
        FROM cats c
        JOIN special_cats sc ON c.special_cat_id = sc.id
        WHERE c.user_id = ? AND c.special_cat_id IS NOT NULL
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get special cat unlock progress
 */
function getSpecialCatUnlockProgress($userId) {
    $progress = [];
    
    foreach (SPECIAL_CATS as $catId => $specialCat) {
        $conditions = $specialCat['unlock_conditions'];
        $unlocked = userHasSpecialCat($userId, $catId);
        
        $progress[$catId] = [
            'name' => $specialCat['name'],
            'description' => $specialCat['description'],
            'rarity' => $specialCat['rarity'],
            'unlocked' => $unlocked,
            'conditions' => $conditions,
            'progress' => getUnlockProgress($userId, $conditions)
        ];
    }
    
    return $progress;
}

/**
 * Get unlock progress for specific conditions
 */
function getUnlockProgress($userId, $conditions) {
    switch ($conditions['type']) {
        case 'achievement':
            return getAchievementProgress($userId, $conditions['achievement']);
        case 'quest':
            return getQuestProgress($userId, $conditions['quest']);
        case 'level':
            $userLevel = getUserLevel($userId);
            return [
                'current' => $userLevel,
                'required' => $conditions['level'],
                'percentage' => min(100, ($userLevel / $conditions['level']) * 100)
            ];
        default:
            return ['current' => 0, 'required' => 1, 'percentage' => 0];
    }
}

/**
 * Log special cat unlock
 */
function logSpecialCatUnlock($userId, $catId, $specialCat) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO special_cat_unlocks (user_id, special_cat_id, cat_id, unlocked_at)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $specialCat['id'], $catId, time()]);
    
    // Also log as achievement
    logAchievement($userId, 'unlock_special_cat', [
        'special_cat' => $specialCat['id'],
        'cat_name' => $specialCat['name'],
        'rarity' => $specialCat['rarity']
    ]);
}

/**
 * Get special cat by ID
 */
function getSpecialCat($catId) {
    return SPECIAL_CATS[$catId] ?? null;
}

/**
 * Get all available special cats
 */
function getAllSpecialCats() {
    return SPECIAL_CATS;
}

/**
 * Check if a cat is a special cat
 */
function isSpecialCat($cat) {
    return isset($cat['special_cat_id']) && !empty($cat['special_cat_id']);
}

/**
 * Get special cat abilities
 */
function getSpecialCatAbilities($catId) {
    $specialCat = getSpecialCat($catId);
    return $specialCat['special_abilities'] ?? [];
}

/**
 * Apply special cat bonuses
 */
function applySpecialCatBonuses($cat, $gameType, $baseBonus) {
    if (!isSpecialCat($cat)) {
        return $baseBonus;
    }
    
    $specialCat = getSpecialCat($cat['special_cat_id']);
    $abilities = $specialCat['special_abilities'] ?? [];
    
    foreach ($abilities as $ability => $description) {
        if (strpos($ability, 'enhanced_') === 0) {
            $gameTypeMatch = str_replace('enhanced_', '', $ability);
            if (strpos($gameType, $gameTypeMatch) !== false) {
                // Extract bonus percentage from description
                if (preg_match('/(\d+)%/', $description, $matches)) {
                    $bonus = $matches[1] / 100;
                    $baseBonus *= (1 + $bonus);
                }
            }
        }
    }
    
    return $baseBonus;
}
?>
