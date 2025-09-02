<?php
/**
 * Purrr.love - Enhanced Cat Care System
 * Developed and Designed by Ryan Coleman. <coleman.ryan@gmail.com>
 * Enhanced with feline-specific care activities and personality-based bonuses
 */

/**
 * Feed a cat with food items
 */
function feedCat($catId, $foodItemId, $userId) {
    global $pdo;
    
    // Verify ownership or permission
    if (!canCareForCat($catId, $userId)) {
        return ['success' => false, 'message' => 'You cannot care for this cat.'];
    }
    
    // Get food item details
    $stmt = $pdo->prepare("SELECT * FROM store_items WHERE id = ? AND item_type = 'food'");
    $stmt->execute([$foodItemId]);
    $foodItem = $stmt->fetch();
    
    if (!$foodItem) {
        return ['success' => false, 'message' => 'Invalid food item.'];
    }
    
    // Check if user has the food item
    $stmt = $pdo->prepare("SELECT quantity FROM user_inventory WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$userId, $foodItemId]);
    $inventory = $stmt->fetch();
    
    if (!$inventory || $inventory['quantity'] < 1) {
        return ['success' => false, 'message' => 'You don\'t have this food item.'];
    }
    
    // Get cat's current stats and personality
    $stmt = $pdo->prepare("SELECT * FROM cats WHERE id = ?");
    $stmt->execute([$catId]);
    $cat = $stmt->fetch();
    
    if (!$cat) {
        return ['success' => false, 'message' => 'Cat not found.'];
    }
    
    // Calculate feeding effects with personality bonuses
    $personalityBonus = getCatPersonalityBonus($cat['personality_type'], 'feeding');
    $hungerRestore = $foodItem['hunger_restore'] * (1 + $personalityBonus);
    $happinessBoost = $foodItem['happiness_boost'] * (1 + $personalityBonus);
    $energyBoost = $foodItem['energy_boost'] * (1 + $personalityBonus);
    
    // Apply feeding effects
    $newHunger = min(100, $cat['hunger'] + $hungerRestore);
    $newHappiness = min(100, $cat['happiness'] + $happinessBoost);
    $newEnergy = min(100, $cat['energy'] + $energyBoost);
    
    // Update cat stats
    $stmt = $pdo->prepare("
        UPDATE cats 
        SET hunger = ?, happiness = ?, energy = ?, last_fed = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$newHunger, $newHappiness, $newEnergy, $catId]);
    
    // Consume food item
    $stmt = $pdo->prepare("
        UPDATE user_inventory 
        SET quantity = quantity - 1 
        WHERE user_id = ? AND item_id = ?
    ");
    $stmt->execute([$userId, $foodItemId]);
    
    // Remove item if quantity becomes 0
    $stmt = $pdo->prepare("DELETE FROM user_inventory WHERE user_id = ? AND item_id = ? AND quantity <= 0");
    $stmt->execute([$userId, $foodItemId]);
    
    // Record feeding activity
    $stmt = $pdo->prepare("
        INSERT INTO cat_activities (cat_id, user_id, activity_type, item_id, effects, created_at)
        VALUES (?, ?, 'feeding', ?, ?, NOW())
    ");
    $effects = json_encode([
        'hunger_restore' => $hungerRestore,
        'happiness_boost' => $happinessBoost,
        'energy_boost' => $energyBoost,
        'personality_bonus' => $personalityBonus
    ]);
    $stmt->execute([$catId, $userId, $foodItemId, $effects]);
    
    // Check for level up
    checkCatLevelUp($catId);
    
    // Track quest progress
    update_quest_progress($userId, 'feed_cats');
    
    return [
        'success' => true,
        'message' => 'Cat fed successfully!',
        'effects' => [
            'hunger_restore' => $hungerRestore,
            'happiness_boost' => $happinessBoost,
            'energy_boost' => $energyBoost,
            'personality_bonus' => $personalityBonus
        ]
    ];
}

/**
 * Give a cat treats
 */
function giveCatTreats($catId, $treatItemId, $userId) {
    global $pdo;
    
    // Verify ownership or permission
    if (!canCareForCat($catId, $userId)) {
        return ['success' => false, 'message' => 'You cannot care for this cat.'];
    }
    
    // Get treat item details
    $stmt = $pdo->prepare("SELECT * FROM store_items WHERE id = ? AND item_type = 'treats'");
    $stmt->execute([$treatItemId]);
    $treatItem = $stmt->fetch();
    
    if (!$treatItem) {
        return ['success' => false, 'message' => 'Invalid treat item.'];
    }
    
    // Check if user has the treat item
    $stmt = $pdo->prepare("SELECT quantity FROM user_inventory WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$userId, $treatItemId]);
    $inventory = $stmt->fetch();
    
    if (!$inventory || $inventory['quantity'] < 1) {
        return ['success' => false, 'message' => 'You don\'t have this treat item.'];
    }
    
    // Get cat's current stats and personality
    $stmt = $pdo->prepare("SELECT * FROM cats WHERE id = ?");
    $stmt->execute([$catId]);
    $cat = $stmt->fetch();
    
    if (!$cat) {
        return ['success' => false, 'message' => 'Cat not found.'];
    }
    
    // Calculate treat effects with personality bonuses
    $personalityBonus = getCatPersonalityBonus($cat['personality_type'], 'treats');
    $happinessBoost = $treatItem['happiness_boost'] * (1 + $personalityBonus);
    $moodBoost = $treatItem['mood_boost'] * (1 + $personalityBonus);
    $trainingBonus = $treatItem['training_bonus'] * (1 + $personalityBonus);
    
    // Apply treat effects
    $newHappiness = min(100, $cat['happiness'] + $happinessBoost);
    $newMood = min(100, $cat['mood'] + $moodBoost);
    
    // Update cat stats
    $stmt = $pdo->prepare("
        UPDATE cats 
        SET happiness = ?, mood = ?, last_treated = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$newHappiness, $newMood, $catId]);
    
    // Consume treat item
    $stmt = $pdo->prepare("
        UPDATE user_inventory 
        SET quantity = quantity - 1 
        WHERE user_id = ? AND item_id = ?
    ");
    $stmt->execute([$userId, $treatItemId]);
    
    // Remove item if quantity becomes 0
    $stmt = $pdo->prepare("DELETE FROM user_inventory WHERE user_id = ? AND item_id = ? AND quantity <= 0");
    $stmt->execute([$userId, $treatItemId]);
    
    // Record treat activity
    $stmt = $pdo->prepare("
        INSERT INTO cat_activities (cat_id, user_id, activity_type, item_id, effects, created_at)
        VALUES (?, ?, 'treats', ?, ?, NOW())
    ");
    $effects = json_encode([
        'happiness_boost' => $happinessBoost,
        'mood_boost' => $moodBoost,
        'training_bonus' => $trainingBonus,
        'personality_bonus' => $personalityBonus
    ]);
    $stmt->execute([$catId, $userId, $treatItemId, $effects]);
    
    // Track quest progress
    update_quest_progress($userId, 'give_treats');
    
    return [
        'success' => true,
        'message' => 'Cat treated successfully!',
        'effects' => [
            'happiness_boost' => $happinessBoost,
            'mood_boost' => $moodBoost,
            'training_bonus' => $trainingBonus,
            'personality_bonus' => $personalityBonus
        ]
    ];
}

/**
 * Play with a cat using toys
 */
function playWithCat($catId, $toyItemId, $userId) {
    global $pdo;
    
    // Verify ownership or permission
    if (!canCareForCat($catId, $userId)) {
        return ['success' => false, 'message' => 'You cannot care for this cat.'];
    }
    
    // Get toy item details
    $stmt = $pdo->prepare("SELECT * FROM store_items WHERE id = ? AND item_type = 'toys'");
    $stmt->execute([$toyItemId]);
    $toyItem = $stmt->fetch();
    
    if (!$toyItem) {
        return ['success' => false, 'message' => 'Invalid toy item.'];
    }
    
    // Check if user has the toy item
    $stmt = $pdo->prepare("SELECT quantity FROM user_inventory WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$userId, $toyItemId]);
    $inventory = $stmt->fetch();
    
    if (!$inventory || $inventory['quantity'] < 1) {
        return ['success' => false, 'message' => 'You don\'t have this toy item.'];
    }
    
    // Get cat's current stats and personality
    $stmt = $pdo->prepare("SELECT * FROM cats WHERE id = ?");
    $stmt->execute([$catId]);
    $cat = $stmt->fetch();
    
    if (!$cat) {
        return ['success' => false, 'message' => 'Cat not found.'];
    }
    
    // Calculate play effects with personality bonuses
    $personalityBonus = getCatPersonalityBonus($cat['personality_type'], 'play_session');
    $energyBoost = $toyItem['energy_boost'] * (1 + $personalityBonus);
    $playBonus = $toyItem['play_bonus'] * (1 + $personalityBonus);
    $trainingBonus = $toyItem['training_bonus'] * (1 + $personalityBonus);
    
    // Apply play effects
    $newEnergy = min(100, $cat['energy'] + $energyBoost);
    $newHappiness = min(100, $cat['happiness'] + $playBonus);
    
    // Update cat stats
    $stmt = $pdo->prepare("
        UPDATE cats 
        SET energy = ?, happiness = ?, last_played = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$newEnergy, $newHappiness, $catId]);
    
    // Consume toy item (toys can be used multiple times but wear out)
    $stmt = $pdo->prepare("
        UPDATE user_inventory 
        SET quantity = quantity - 1 
        WHERE user_id = ? AND item_id = ?
    ");
    $stmt->execute([$userId, $toyItemId]);
    
    // Remove item if quantity becomes 0
    $stmt = $pdo->prepare("DELETE FROM user_inventory WHERE user_id = ? AND item_id = ? AND quantity <= 0");
    $stmt->execute([$userId, $toyItemId]);
    
    // Record play activity
    $stmt = $pdo->prepare("
        INSERT INTO cat_activities (cat_id, user_id, activity_type, item_id, effects, created_at)
        VALUES (?, ?, 'play', ?, ?, NOW())
    ");
    $effects = json_encode([
        'energy_boost' => $energyBoost,
        'play_bonus' => $playBonus,
        'training_bonus' => $trainingBonus,
        'personality_bonus' => $personalityBonus
    ]);
    $stmt->execute([$catId, $userId, $toyItemId, $effects]);
    
    // Check for level up
    checkCatLevelUp($catId);
    
    // Track quest progress
    update_quest_progress($userId, 'play_with_cats');
    
    return [
        'success' => true,
        'message' => 'Play session completed successfully!',
        'effects' => [
            'energy_boost' => $energyBoost,
            'play_bonus' => $playBonus,
            'training_bonus' => $trainingBonus,
            'personality_bonus' => $personalityBonus
        ]
    ];
}

/**
 * Groom a cat using grooming tools
 */
function groomCat($catId, $groomingItemId, $userId) {
    global $pdo;
    
    // Verify ownership or permission
    if (!canCareForCat($catId, $userId)) {
        return ['success' => false, 'message' => 'You cannot care for this cat.'];
    }
    
    // Get grooming item details
    $stmt = $pdo->prepare("SELECT * FROM store_items WHERE id = ? AND item_type = 'grooming'");
    $stmt->execute([$groomingItemId]);
    $groomingItem = $stmt->fetch();
    
    if (!$groomingItem) {
        return ['success' => false, 'message' => 'Invalid grooming item.'];
    }
    
    // Check if user has the grooming item
    $stmt = $pdo->prepare("SELECT quantity FROM user_inventory WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$userId, $groomingItemId]);
    $inventory = $stmt->fetch();
    
    if (!$inventory || $inventory['quantity'] < 1) {
        return ['success' => false, 'message' => 'You don\'t have this grooming item.'];
    }
    
    // Get cat's current stats and personality
    $stmt = $pdo->prepare("SELECT * FROM cats WHERE id = ?");
    $stmt->execute([$catId]);
    $cat = $stmt->fetch();
    
    if (!$cat) {
        return ['success' => false, 'message' => 'Cat not found.'];
    }
    
    // Calculate grooming effects with personality bonuses
    $personalityBonus = getCatPersonalityBonus($cat['personality_type'], 'grooming');
    $groomingBonus = $groomingItem['grooming_bonus'] * (1 + $personalityBonus);
    $healthBonus = $groomingItem['health_bonus'] * (1 + $personalityBonus);
    $moodBonus = $groomingItem['mood_bonus'] * (1 + $personalityBonus);
    
    // Apply grooming effects
    $newHealth = min(100, $cat['health'] + $healthBonus);
    $newMood = min(100, $cat['mood'] + $moodBonus);
    $newHappiness = min(100, $cat['happiness'] + $groomingBonus);
    
    // Update cat stats
    $stmt = $pdo->prepare("
        UPDATE cats 
        SET health = ?, mood = ?, happiness = ?, last_groomed = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$newHealth, $newMood, $newHappiness, $catId]);
    
    // Consume grooming item
    $stmt = $pdo->prepare("
        UPDATE user_inventory 
        SET quantity = quantity - 1 
        WHERE user_id = ? AND item_id = ?
    ");
    $stmt->execute([$userId, $groomingItemId]);
    
    // Remove item if quantity becomes 0
    $stmt = $pdo->prepare("DELETE FROM user_inventory WHERE user_id = ? AND item_id = ? AND quantity <= 0");
    $stmt->execute([$userId, $groomingItemId]);
    
    // Record grooming activity
    $stmt = $pdo->prepare("
        INSERT INTO cat_activities (cat_id, user_id, activity_type, item_id, effects, created_at)
        VALUES (?, ?, 'grooming', ?, ?, NOW())
    ");
    $effects = json_encode([
        'grooming_bonus' => $groomingBonus,
        'health_bonus' => $healthBonus,
        'mood_bonus' => $moodBonus,
        'personality_bonus' => $personalityBonus
    ]);
    $stmt->execute([$catId, $userId, $groomingItemId, $effects]);
    
    // Track quest progress
    update_quest_progress($userId, 'groom_cats');
    
    return [
        'success' => true,
        'message' => 'Cat groomed successfully!',
        'effects' => [
            'grooming_bonus' => $groomingBonus,
            'health_bonus' => $healthBonus,
            'mood_bonus' => $moodBonus,
            'personality_bonus' => $personalityBonus
        ]
    ];
}

/**
 * Install cat furniture to enhance territory
 */
function installCatFurniture($catId, $furnitureItemId, $userId) {
    global $pdo;
    
    // Verify ownership
    $stmt = $pdo->prepare("SELECT user_id FROM cats WHERE id = ?");
    $stmt->execute([$catId]);
    $cat = $stmt->fetch();
    
    if (!$cat || $cat['user_id'] != $userId) {
        return ['success' => false, 'message' => 'You can only install furniture for your own cats.'];
    }
    
    // Get furniture item details
    $stmt = $pdo->prepare("SELECT * FROM store_items WHERE id = ? AND item_type = 'furniture'");
    $stmt->execute([$furnitureItemId]);
    $furnitureItem = $stmt->fetch();
    
    if (!$furnitureItem) {
        return ['success' => false, 'message' => 'Invalid furniture item.'];
    }
    
    // Check if user has the furniture item
    $stmt = $pdo->prepare("SELECT quantity FROM user_inventory WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$userId, $furnitureItemId]);
    $inventory = $stmt->fetch();
    
    if (!$inventory || $inventory['quantity'] < 1) {
        return ['success' => false, 'message' => 'You don\'t have this furniture item.'];
    }
    
    // Install furniture and enhance cat's territory
    $territoryBonus = $furnitureItem['territory_bonus'];
    $restBonus = $furnitureItem['rest_bonus'];
    $climbingBonus = $furnitureItem['climbing_bonus'];
    
    // Add furniture to cat's territory
    $stmt = $pdo->prepare("
        INSERT INTO cat_territory_items (cat_id, item_id, territory_bonus, rest_bonus, climbing_bonus, installed_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$catId, $furnitureItemId, $territoryBonus, $restBonus, $climbingBonus]);
    
    // Consume furniture item
    $stmt = $pdo->prepare("
        UPDATE user_inventory 
        SET quantity = quantity - 1 
        WHERE user_id = ? AND item_id = ?
    ");
    $stmt->execute([$userId, $furnitureItemId]);
    
    // Remove item if quantity becomes 0
    $stmt = $pdo->prepare("DELETE FROM user_inventory WHERE user_id = ? AND item_id = ? AND quantity <= 0");
    $stmt->execute([$userId, $furnitureItemId]);
    
    // Record furniture installation
    $stmt = $pdo->prepare("
        INSERT INTO cat_activities (cat_id, user_id, activity_type, item_id, effects, created_at)
        VALUES (?, ?, 'furniture_install', ?, ?, NOW())
    ");
    $effects = json_encode([
        'territory_bonus' => $territoryBonus,
        'rest_bonus' => $restBonus,
        'climbing_bonus' => $climbingBonus
    ]);
    $stmt->execute([$catId, $userId, $furnitureItemId, $effects]);
    
    // Track quest progress
    update_quest_progress($userId, 'install_furniture');
    
    return [
        'success' => true,
        'message' => 'Furniture installed successfully!',
        'effects' => [
            'territory_bonus' => $territoryBonus,
            'rest_bonus' => $restBonus,
            'climbing_bonus' => $climbingBonus
        ]
    ];
}

/**
 * Check if user can care for a cat
 */
function canCareForCat($catId, $userId) {
    global $pdo;
    
    // Owner can always care for their cat
    $stmt = $pdo->prepare("SELECT user_id FROM cats WHERE id = ?");
    $stmt->execute([$catId]);
    $cat = $stmt->fetch();
    
    if ($cat && $cat['user_id'] == $userId) {
        return true;
    }
    
    // Check if user has permission through vacation mode or other arrangements
    $stmt = $pdo->prepare("
        SELECT * FROM cat_care_permissions 
        WHERE cat_id = ? AND user_id = ? AND is_active = 1 
        AND (expires_at IS NULL OR expires_at > NOW())
    ");
    $stmt->execute([$catId, $userId]);
    $permission = $stmt->fetch();
    
    return $permission !== false;
}

/**
 * Get cat's current care status
 */
function getCatCareStatus($catId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT c.*, 
               TIMESTAMPDIFF(HOUR, c.last_fed, NOW()) as hours_since_fed,
               TIMESTAMPDIFF(HOUR, c.last_played, NOW()) as hours_since_played,
               TIMESTAMPDIFF(HOUR, c.last_groomed, NOW()) as hours_since_groomed,
               TIMESTAMPDIFF(HOUR, c.last_treated, NOW()) as hours_since_treated
        FROM cats c 
        WHERE c.id = ?
    ");
    $stmt->execute([$catId]);
    $cat = $stmt->fetch();
    
    if (!$cat) {
        return null;
    }
    
    // Calculate care needs
    $careNeeds = [];
    
    if ($cat['hours_since_fed'] > 12) {
        $careNeeds[] = 'hungry';
    }
    
    if ($cat['hours_since_played'] > 8) {
        $careNeeds[] = 'needs_play';
    }
    
    if ($cat['hours_since_groomed'] > 24) {
        $careNeeds[] = 'needs_grooming';
    }
    
    if ($cat['hours_since_treated'] > 6) {
        $careNeeds[] = 'needs_treats';
    }
    
    // Calculate overall care score
    $careScore = 100;
    $careScore -= max(0, $cat['hours_since_fed'] - 12) * 2;
    $careScore -= max(0, $cat['hours_since_played'] - 8) * 3;
    $careScore -= max(0, $cat['hours_since_groomed'] - 24) * 1;
    $careScore -= max(0, $cat['hours_since_treated'] - 6) * 2;
    
    $careScore = max(0, min(100, $careScore));
    
    return [
        'cat' => $cat,
        'care_needs' => $careNeeds,
        'care_score' => $careScore,
        'care_level' => getCareLevel($careScore)
    ];
}

/**
 * Get care level based on score
 */
function getCareLevel($careScore) {
    if ($careScore >= 90) return 'excellent';
    if ($careScore >= 75) return 'good';
    if ($careScore >= 60) return 'fair';
    if ($careScore >= 40) return 'poor';
    return 'critical';
}

/**
 * Get cat store items
 */
function getCatStoreItems() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM store_items 
        WHERE is_active = 1 
        ORDER BY item_type, price_usd ASC
    ");
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Get user inventory
 */
function getUserInventory($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT ui.*, si.name, si.image_url, si.item_type, si.description
        FROM user_inventory ui
        JOIN store_items si ON ui.item_id = si.id
        WHERE ui.user_id = ? AND ui.quantity > 0
        ORDER BY si.item_type, si.name
    ");
    $stmt->execute([$userId]);
    
    return $stmt->fetchAll();
}

/**
 * Purchase store item
 */
function purchaseStoreItem($userId, $itemId, $quantity, $cryptoType) {
    global $pdo;
    
    // Get item details
    $stmt = $pdo->prepare("SELECT * FROM store_items WHERE id = ? AND is_active = 1");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();
    
    if (!$item) {
        return ['success' => false, 'message' => 'Item not found or not available.'];
    }
    
    // Calculate total cost
    $totalCostUSD = $item['price_usd'] * $quantity;
    $totalCostCrypto = convertUSDToCrypto($totalCostUSD, $cryptoType);
    
    if ($totalCostCrypto === null) {
        return ['success' => false, 'message' => 'Unable to get crypto price. Please try again.'];
    }
    
    // Check user balance
    $userBalance = getUserCryptoBalance($userId, $cryptoType);
    if ($userBalance < $totalCostCrypto) {
        return ['success' => false, 'message' => 'Insufficient balance.'];
    }
    
    // In developer mode, skip actual crypto deduction
    if (!(defined('DEVELOPER_MODE') && DEVELOPER_MODE)) {
        // Deduct crypto from user balance
        updateUserBalance($userId, $cryptoType, $totalCostCrypto, 'subtract');
    }
    
    // Add item to user inventory
    $stmt = $pdo->prepare("
        INSERT INTO user_inventory (user_id, item_id, quantity, acquired_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
        quantity = quantity + VALUES(quantity),
        updated_at = NOW()
    ");
    $stmt->execute([$userId, $itemId, $quantity]);
    
    // Record purchase
    $stmt = $pdo->prepare("
        INSERT INTO store_purchases (user_id, item_id, quantity, crypto_type, crypto_amount, usd_amount, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $itemId, $quantity, $cryptoType, $totalCostCrypto, $totalCostUSD]);
    
    return [
        'success' => true,
        'message' => 'Purchase successful!',
        'item' => $item,
        'quantity' => $quantity,
        'crypto_amount' => $totalCostCrypto,
        'usd_amount' => $totalCostUSD
    ];
}
?>
