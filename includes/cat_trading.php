<?php
/**
 * 🔄 Purrr.love Cross-Platform Cat Trading System
 * Trade cats between different users across platforms
 */

// Trading configuration
define('TRADING_MIN_CAT_LEVEL', 5);
define('TRADING_COOLDOWN_HOURS', 24);
define('TRADING_MAX_ACTIVE_OFFERS', 10);
define('TRADING_VERIFICATION_REQUIRED', true);
define('TRADING_ESCROW_ENABLED', true);

// Trading status constants
define('TRADE_STATUS_PENDING', 'pending');
define('TRADE_STATUS_ACCEPTED', 'accepted');
define('TRADE_STATUS_REJECTED', 'rejected');
define('TRADE_STATUS_CANCELLED', 'cancelled');
define('TRADE_STATUS_COMPLETED', 'completed');
define('TRADE_STATUS_DISPUTED', 'disputed');

// Platform identifiers
define('TRADING_PLATFORMS', [
    'purrr_love' => 'Purrr.love',
    'mobile_app' => 'Mobile App',
    'web_platform' => 'Web Platform',
    'third_party' => 'Third Party Integration'
]);

/**
 * Create a cat trading offer
 */
function createCatTradingOffer($sellerId, $catId, $offerData) {
    // Validate cat ownership
    if (!canTradeCat($sellerId, $catId)) {
        throw new Exception('Cannot trade this cat', 403);
    }
    
    // Validate cat eligibility for trading
    $cat = getCatById($catId);
    if (!$cat || $cat['level'] < TRADING_MIN_CAT_LEVEL) {
        throw new Exception('Cat does not meet trading requirements', 400);
    }
    
    // Check trading cooldown
    if (isCatOnTradingCooldown($catId)) {
        throw new Exception('Cat is on trading cooldown', 400);
    }
    
    // Check active offers limit
    if (getUserActiveOffersCount($sellerId) >= TRADING_MAX_ACTIVE_OFFERS) {
        throw new Exception('Maximum active offers reached', 400);
    }
    
    // Validate offer data
    $validatedOffer = validateTradingOffer($offerData);
    
    // Create trading offer
    $offerId = createTradingOfferRecord($sellerId, $catId, $validatedOffer);
    
    // Lock cat for trading
    lockCatForTrading($catId, $offerId);
    
    // Notify potential buyers
    notifyPotentialBuyers($offerId, $cat, $validatedOffer);
    
    // Log trading event
    logTradingEvent('offer_created', $offerId, $sellerId, $catId);
    
    return [
        'offer_id' => $offerId,
        'status' => TRADE_STATUS_PENDING,
        'cat' => $cat,
        'offer_details' => $validatedOffer,
        'created_at' => date('c')
    ];
}

/**
 * Accept a cat trading offer
 */
function acceptCatTradingOffer($buyerId, $offerId) {
    // Get offer details
    $offer = getTradingOffer($offerId);
    if (!$offer || $offer['status'] !== TRADE_STATUS_PENDING) {
        throw new Exception('Invalid or unavailable offer', 400);
    }
    
    // Check if buyer can afford the trade
    if (!canUserAffordTrade($buyerId, $offer)) {
        throw new Exception('Insufficient resources for trade', 400);
    }
    
    // Validate buyer eligibility
    if (!canUserParticipateInTrade($buyerId)) {
        throw new Exception('User cannot participate in trading', 400);
    }
    
    // Check if buyer already has max cats
    if (getUserCatCount($buyerId) >= getUserMaxCatLimit($buyerId)) {
        throw new Exception('Maximum cat limit reached', 400);
    }
    
    // Process trade acceptance
    $tradeId = processTradeAcceptance($offerId, $buyerId);
    
    // Transfer cat ownership
    transferCatOwnership($offer['cat_id'], $offer['seller_id'], $buyerId);
    
    // Process payment
    processTradePayment($offer, $buyerId, $offer['seller_id']);
    
    // Update offer status
    updateTradingOfferStatus($offerId, TRADE_STATUS_ACCEPTED);
    
    // Notify both parties
    notifyTradeCompletion($tradeId, $offer);
    
    // Log trading event
    logTradingEvent('offer_accepted', $offerId, $buyerId, $offer['cat_id']);
    
    return [
        'trade_id' => $tradeId,
        'status' => TRADE_STATUS_ACCEPTED,
        'cat_transferred' => true,
        'payment_processed' => true,
        'completed_at' => date('c')
    ];
}

/**
 * Reject a cat trading offer
 */
function rejectCatTradingOffer($buyerId, $offerId, $reason = '') {
    // Get offer details
    $offer = getTradingOffer($offerId);
    if (!$offer || $offer['status'] !== TRADE_STATUS_PENDING) {
        throw new Exception('Invalid or unavailable offer', 400);
    }
    
    // Update offer status
    updateTradingOfferStatus($offerId, TRADE_STATUS_REJECTED);
    
    // Unlock cat for trading
    unlockCatFromTrading($offer['cat_id']);
    
    // Notify seller
    notifyTradeRejection($offerId, $buyerId, $reason);
    
    // Log trading event
    logTradingEvent('offer_rejected', $offerId, $buyerId, $offer['cat_id']);
    
    return [
        'status' => TRADE_STATUS_REJECTED,
        'offer_id' => $offerId,
        'rejected_at' => date('c')
    ];
}

/**
 * Cancel a cat trading offer
 */
function cancelCatTradingOffer($sellerId, $offerId) {
    // Get offer details
    $offer = getTradingOffer($offerId);
    if (!$offer || $offer['seller_id'] !== $sellerId) {
        throw new Exception('Cannot cancel this offer', 403);
    }
    
    if ($offer['status'] !== TRADE_STATUS_PENDING) {
        throw new Exception('Cannot cancel non-pending offer', 400);
    }
    
    // Update offer status
    updateTradingOfferStatus($offerId, TRADE_STATUS_CANCELLED);
    
    // Unlock cat for trading
    unlockCatFromTrading($offer['cat_id']);
    
    // Notify interested buyers
    notifyOfferCancellation($offerId);
    
    // Log trading event
    logTradingEvent('offer_cancelled', $offerId, $sellerId, $offer['cat_id']);
    
    return [
        'status' => TRADE_STATUS_CANCELLED,
        'offer_id' => $offerId,
        'cancelled_at' => date('c')
    ];
}

/**
 * Get available trading offers
 */
function getAvailableTradingOffers($filters = []) {
    $pdo = get_db();
    
    $whereConditions = ['status = ?'];
    $params = [TRADE_STATUS_PENDING];
    
    // Apply filters
    if (isset($filters['cat_species'])) {
        $whereConditions[] = 'c.species = ?';
        $params[] = $filters['cat_species'];
    }
    
    if (isset($filters['min_level'])) {
        $whereConditions[] = 'c.level >= ?';
        $params[] = $filters['min_level'];
    }
    
    if (isset($filters['max_level'])) {
        $whereConditions[] = 'c.level <= ?';
        $params[] = $filters['max_level'];
    }
    
    if (isset($filters['personality_type'])) {
        $whereConditions[] = 'c.personality_type = ?';
        $params[] = $filters['personality_type'];
    }
    
    if (isset($filters['price_min'])) {
        $whereConditions[] = 'to.price >= ?';
        $params[] = $filters['price_min'];
    }
    
    if (isset($filters['price_max'])) {
        $whereConditions[] = 'to.price <= ?';
        $params[] = $filters['price_max'];
    }
    
    if (isset($filters['platform'])) {
        $whereConditions[] = 'to.platform = ?';
        $params[] = $filters['platform'];
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $stmt = $pdo->prepare("
        SELECT to.*, c.*, u.username as seller_username, u.rating as seller_rating
        FROM trading_offers to
        JOIN cats c ON to.cat_id = c.id
        JOIN users u ON to.seller_id = u.id
        WHERE $whereClause
        ORDER BY to.created_at DESC
        LIMIT 100
    ");
    
    $stmt->execute($params);
    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format offers for display
    return formatTradingOffers($offers);
}

/**
 * Get user's trading history
 */
function getUserTradingHistory($userId, $limit = 50) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT to.*, c.name as cat_name, c.species, c.level,
               CASE 
                   WHEN to.seller_id = ? THEN 'sold'
                   WHEN to.buyer_id = ? THEN 'bought'
                   ELSE 'unknown'
               END as trade_type,
               CASE 
                   WHEN to.seller_id = ? THEN u2.username
                   WHEN to.buyer_id = ? THEN u1.username
                   ELSE 'unknown'
               END as other_party
        FROM trading_offers to
        JOIN cats c ON to.cat_id = c.id
        JOIN users u1 ON to.seller_id = u1.id
        JOIN users u2 ON to.buyer_id = u2.id
        WHERE (to.seller_id = ? OR to.buyer_id = ?)
        AND to.status IN (?, ?)
        ORDER BY to.updated_at DESC
        LIMIT ?
    ");
    
    $stmt->execute([
        $userId, $userId, $userId, $userId, $userId, $userId,
        TRADE_STATUS_ACCEPTED, TRADE_STATUS_COMPLETED, $limit
    ]);
    
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return formatTradingHistory($history);
}

/**
 * Get user's active trading offers
 */
function getUserActiveOffers($userId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT to.*, c.name as cat_name, c.species, c.level, c.image_url,
               COUNT(tv.id) as view_count,
               COUNT(ti.id) as interest_count
        FROM trading_offers to
        JOIN cats c ON to.cat_id = c.id
        LEFT JOIN trading_views tv ON to.id = tv.offer_id
        LEFT JOIN trading_interest ti ON to.id = ti.offer_id
        WHERE to.seller_id = ? AND to.status = ?
        GROUP BY to.id
        ORDER BY to.created_at DESC
    ");
    
    $stmt->execute([$userId, TRADE_STATUS_PENDING]);
    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return formatUserOffers($offers);
}

/**
 * Search for specific cats in trading
 */
function searchTradingCats($searchCriteria) {
    $pdo = get_db();
    
    $whereConditions = ['to.status = ?'];
    $params = [TRADE_STATUS_PENDING];
    
    // Name search
    if (isset($searchCriteria['name']) && !empty($searchCriteria['name'])) {
        $whereConditions[] = 'c.name ILIKE ?';
        $params[] = '%' . $searchCriteria['name'] . '%';
    }
    
    // Breed search
    if (isset($searchCriteria['breed']) && !empty($searchCriteria['breed'])) {
        $whereConditions[] = 'c.breed ILIKE ?';
        $params[] = '%' . $searchCriteria['breed'] . '%';
    }
    
    // Personality search
    if (isset($searchCriteria['personality']) && !empty($searchCriteria['personality'])) {
        $whereConditions[] = 'c.personality_type = ?';
        $params[] = $searchCriteria['personality'];
    }
    
    // Price range
    if (isset($searchCriteria['price_min'])) {
        $whereConditions[] = 'to.price >= ?';
        $params[] = $searchCriteria['price_min'];
    }
    
    if (isset($searchCriteria['price_max'])) {
        $whereConditions[] = 'to.price <= ?';
        $params[] = $searchCriteria['price_max'];
    }
    
    // Level range
    if (isset($searchCriteria['level_min'])) {
        $whereConditions[] = 'c.level >= ?';
        $params[] = $searchCriteria['level_min'];
    }
    
    if (isset($searchCriteria['level_max'])) {
        $whereConditions[] = 'c.level <= ?';
        $params[] = $searchCriteria['level_max'];
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $stmt = $pdo->prepare("
        SELECT to.*, c.*, u.username as seller_username, u.rating as seller_rating,
               u.verified as seller_verified
        FROM trading_offers to
        JOIN cats c ON to.cat_id = c.id
        JOIN users u ON to.seller_id = u.id
        WHERE $whereClause
        ORDER BY 
            CASE WHEN u.verified = 1 THEN 1 ELSE 2 END,
            c.level DESC,
            to.created_at DESC
        LIMIT 100
    ");
    
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return formatSearchResults($results);
}

/**
 * Express interest in a trading offer
 */
function expressTradingInterest($userId, $offerId, $message = '') {
    // Check if user already expressed interest
    if (hasUserExpressedInterest($userId, $offerId)) {
        throw new Exception('Already expressed interest in this offer', 400);
    }
    
    // Get offer details
    $offer = getTradingOffer($offerId);
    if (!$offer || $offer['status'] !== TRADE_STATUS_PENDING) {
        throw new Exception('Invalid or unavailable offer', 400);
    }
    
    // Cannot express interest in own offer
    if ($offer['seller_id'] === $userId) {
        throw new Exception('Cannot express interest in own offer', 400);
    }
    
    // Record interest
    $interestId = recordTradingInterest($userId, $offerId, $message);
    
    // Notify seller
    notifyTradingInterest($offerId, $userId, $message);
    
    // Log event
    logTradingEvent('interest_expressed', $offerId, $userId, $offer['cat_id']);
    
    return [
        'interest_id' => $interestId,
        'status' => 'recorded',
        'message' => $message,
        'expressed_at' => date('c')
    ];
}

/**
 * View a trading offer (for analytics)
 */
function viewTradingOffer($userId, $offerId) {
    // Record view
    recordTradingView($userId, $offerId);
    
    // Get offer details
    $offer = getTradingOffer($offerId);
    if (!$offer) {
        throw new Exception('Offer not found', 404);
    }
    
    // Get cat details
    $cat = getCatById($offer['cat_id']);
    
    // Get seller information
    $seller = getUserById($offer['seller_id']);
    
    // Get offer statistics
    $stats = getOfferStatistics($offerId);
    
    return [
        'offer' => $offer,
        'cat' => $cat,
        'seller' => $seller,
        'statistics' => $stats,
        'viewed_at' => date('c')
    ];
}

/**
 * Validate trading offer data
 */
function validateTradingOffer($offerData) {
    $validated = [];
    
    // Required fields
    if (empty($offerData['price']) || !is_numeric($offerData['price'])) {
        throw new Exception('Valid price is required', 400);
    }
    
    if ($offerData['price'] < 0) {
        throw new Exception('Price cannot be negative', 400);
    }
    
    // Optional fields with defaults
    $validated['price'] = (float)$offerData['price'];
    $validated['currency'] = $offerData['currency'] ?? 'USD';
    $validated['description'] = $offerData['description'] ?? '';
    $validated['platform'] = $offerData['platform'] ?? 'purrr_love';
    $validated['accepts_counter_offers'] = $offerData['accepts_counter_offers'] ?? false;
    $validated['trade_deadline'] = $offerData['trade_deadline'] ?? null;
    $validated['special_requirements'] = $offerData['special_requirements'] ?? [];
    
    // Validate platform
    if (!array_key_exists($validated['platform'], TRADING_PLATFORMS)) {
        throw new Exception('Invalid platform specified', 400);
    }
    
    // Validate trade deadline
    if ($validated['trade_deadline']) {
        $deadline = strtotime($validated['trade_deadline']);
        if ($deadline === false || $deadline < time()) {
            throw new Exception('Invalid trade deadline', 400);
        }
    }
    
    return $validated;
}

/**
 * Check if user can trade a cat
 */
function canTradeCat($userId, $catId) {
    // Check cat ownership
    $cat = getCatById($catId);
    if (!$cat || $cat['owner_id'] !== $userId) {
        return false;
    }
    
    // Check if cat is locked in another trade
    if (isCatLockedForTrading($catId)) {
        return false;
    }
    
    // Check if cat is on cooldown
    if (isCatOnTradingCooldown($catId)) {
        return false;
    }
    
    // Check if cat meets trading requirements
    if ($cat['level'] < TRADING_MIN_CAT_LEVEL) {
        return false;
    }
    
    return true;
}

/**
 * Check if cat is on trading cooldown
 */
function isCatOnTradingCooldown($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT updated_at FROM trading_offers 
        WHERE cat_id = ? AND status IN (?, ?)
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    
    $stmt->execute([$catId, TRADE_STATUS_ACCEPTED, TRADE_STATUS_COMPLETED]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        return false;
    }
    
    $lastTradeTime = strtotime($result['updated_at']);
    $cooldownEnd = $lastTradeTime + (TRADING_COOLDOWN_HOURS * 3600);
    
    return time() < $cooldownEnd;
}

/**
 * Check if cat is locked for trading
 */
function isCatLockedForTrading($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as locked FROM trading_offers 
        WHERE cat_id = ? AND status = ?
    ");
    
    $stmt->execute([$catId, TRADE_STATUS_PENDING]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['locked'] > 0;
}

/**
 * Lock cat for trading
 */
function lockCatForTrading($catId, $offerId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        UPDATE cats SET trading_locked = ?, trading_offer_id = ? 
        WHERE id = ?
    ");
    
    $stmt->execute([true, $offerId, $catId]);
}

/**
 * Unlock cat from trading
 */
function unlockCatFromTrading($catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        UPDATE cats SET trading_locked = false, trading_offer_id = NULL 
        WHERE id = ?
    ");
    
    $stmt->execute([$catId]);
}

/**
 * Create trading offer record
 */
function createTradingOfferRecord($sellerId, $catId, $offerData) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO trading_offers 
        (seller_id, cat_id, price, currency, description, platform, 
         accepts_counter_offers, trade_deadline, special_requirements, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $sellerId,
        $catId,
        $offerData['price'],
        $offerData['currency'],
        $offerData['description'],
        $offerData['platform'],
        $offerData['accepts_counter_offers'],
        $offerData['trade_deadline'],
        json_encode($offerData['special_requirements']),
        TRADE_STATUS_PENDING,
        date('Y-m-d H:i:s')
    ]);
    
    return $pdo->lastInsertId();
}

/**
 * Get trading offer
 */
function getTradingOffer($offerId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM trading_offers WHERE id = ?
    ");
    
    $stmt->execute([$offerId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update trading offer status
 */
function updateTradingOfferStatus($offerId, $status) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        UPDATE trading_offers 
        SET status = ?, updated_at = ? 
        WHERE id = ?
    ");
    
    $stmt->execute([$status, date('Y-m-d H:i:s'), $offerId]);
}

/**
 * Get user active offers count
 */
function getUserActiveOffersCount($userId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM trading_offers 
        WHERE seller_id = ? AND status = ?
    ");
    
    $stmt->execute([$userId, TRADE_STATUS_PENDING]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'];
}

/**
 * Process trade acceptance
 */
function processTradeAcceptance($offerId, $buyerId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        UPDATE trading_offers 
        SET buyer_id = ?, status = ?, updated_at = ? 
        WHERE id = ?
    ");
    
    $stmt->execute([$buyerId, TRADE_STATUS_ACCEPTED, date('Y-m-d H:i:s'), $offerId]);
    
    // Create trade record
    $stmt = $pdo->prepare("
        INSERT INTO trades 
        (offer_id, buyer_id, seller_id, cat_id, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $offer = getTradingOffer($offerId);
    $stmt->execute([
        $offerId,
        $buyerId,
        $offer['seller_id'],
        $offer['cat_id'],
        TRADE_STATUS_ACCEPTED,
        date('Y-m-d H:i:s')
    ]);
    
    return $pdo->lastInsertId();
}

/**
 * Transfer cat ownership
 */
function transferCatOwnership($catId, $oldOwnerId, $newOwnerId) {
    $pdo = get_db();
    
    // Update cat ownership
    $stmt = $pdo->prepare("
        UPDATE cats 
        SET owner_id = ?, trading_locked = false, trading_offer_id = NULL,
            last_traded_at = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$newOwnerId, date('Y-m-d H:i:s'), $catId]);
    
    // Log ownership transfer
    $stmt = $pdo->prepare("
        INSERT INTO cat_ownership_history 
        (cat_id, previous_owner_id, new_owner_id, transfer_reason, created_at)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$catId, $oldOwnerId, $newOwnerId, 'trading', date('Y-m-d H:i:s')]);
}

/**
 * Process trade payment
 */
function processTradePayment($offer, $buyerId, $sellerId) {
    // This would integrate with your payment system
    // For now, we'll just log the payment
    
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO trade_payments 
        (offer_id, buyer_id, seller_id, amount, currency, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $offer['id'],
        $buyerId,
        $sellerId,
        $offer['price'],
        $offer['currency'],
        'completed',
        date('Y-m-d H:i:s')
    ]);
}

/**
 * Format trading offers for display
 */
function formatTradingOffers($offers) {
    $formatted = [];
    
    foreach ($offers as $offer) {
        $formatted[] = [
            'id' => $offer['id'],
            'cat' => [
                'id' => $offer['cat_id'],
                'name' => $offer['name'],
                'species' => $offer['species'],
                'breed' => $offer['breed'],
                'level' => $offer['level'],
                'personality_type' => $offer['personality_type'],
                'image_url' => $offer['image_url']
            ],
            'seller' => [
                'id' => $offer['seller_id'],
                'username' => $offer['seller_username'],
                'rating' => $offer['seller_rating'],
                'verified' => $offer['seller_verified'] ?? false
            ],
            'offer' => [
                'price' => $offer['price'],
                'currency' => $offer['currency'],
                'description' => $offer['description'],
                'platform' => $offer['platform'],
                'accepts_counter_offers' => $offer['accepts_counter_offers'],
                'created_at' => $offer['created_at']
            ]
        ];
    }
    
    return $formatted;
}

/**
 * Log trading event
 */
function logTradingEvent($eventType, $offerId, $userId, $catId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO trading_events 
        (event_type, offer_id, user_id, cat_id, created_at)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$eventType, $offerId, $userId, $catId, date('Y-m-d H:i:s')]);
}
