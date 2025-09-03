<?php
/**
 * 👑 Purrr.love Cat Show Competitions System
 * Competitive cat beauty and talent shows with judging and prizes
 */

// Show configuration
define('SHOW_MIN_CAT_LEVEL', 10);
define('SHOW_REGISTRATION_DEADLINE_HOURS', 24);
define('SHOW_MAX_PARTICIPANTS', 100);
define('SHOW_JUDGING_DURATION_MINUTES', 30);
define('SHOW_RESULTS_ANNOUNCEMENT_DELAY_MINUTES', 15);

// Show categories
define('SHOW_CATEGORIES', [
    'beauty' => [
        'name' => 'Beauty Contest',
        'description' => 'Judged on appearance, grooming, and breed standards',
        'judging_criteria' => ['appearance', 'grooming', 'breed_standards', 'overall_presentation'],
        'max_score' => 100
    ],
    'talent' => [
        'name' => 'Talent Show',
        'description' => 'Judged on special abilities and tricks',
        'judging_criteria' => ['skill_execution', 'creativity', 'difficulty', 'entertainment_value'],
        'max_score' => 100
    ],
    'agility' => [
        'name' => 'Agility Competition',
        'description' => 'Obstacle course completion and speed',
        'judging_criteria' => ['speed', 'accuracy', 'obstacle_handling', 'completion_time'],
        'max_score' => 100
    ],
    'personality' => [
        'name' => 'Personality Contest',
        'description' => 'Judged on charm, friendliness, and unique traits',
        'judging_criteria' => ['friendliness', 'charm', 'uniqueness', 'audience_appeal'],
        'max_score' => 100
    ],
    'costume' => [
        'name' => 'Costume Contest',
        'description' => 'Creative costume design and presentation',
        'judging_criteria' => ['creativity', 'execution', 'fit', 'overall_impact'],
        'max_score' => 100
    ]
]);

// Show status constants
define('SHOW_STATUS_ANNOUNCED', 'announced');
define('SHOW_STATUS_REGISTRATION_OPEN', 'registration_open');
define('SHOW_STATUS_REGISTRATION_CLOSED', 'registration_closed');
define('SHOW_STATUS_IN_PROGRESS', 'in_progress');
define('SHOW_STATUS_JUDGING', 'judging');
define('SHOW_STATUS_COMPLETED', 'completed');
define('SHOW_STATUS_CANCELLED', 'cancelled');

/**
 * Create a new cat show
 */
function createCatShow($organizerId, $showData) {
    // Validate organizer permissions
    if (!canOrganizeShows($organizerId)) {
        throw new Exception('Cannot organize shows', 403);
    }
    
    // Validate show data
    $validatedShow = validateShowData($showData);
    
    // Check for conflicting shows
    if (hasConflictingShows($validatedShow['start_time'], $validatedShow['end_time'])) {
        throw new Exception('Show time conflicts with existing shows', 400);
    }
    
    // Create show record
    $showId = createShowRecord($organizerId, $validatedShow);
    
    // Create show categories
    createShowCategories($showId, $validatedShow['categories']);
    
    // Create prize pool
    createShowPrizePool($showId, $validatedShow['prizes']);
    
    // Announce show
    announceShow($showId);
    
    // Log show creation
    logShowEvent('show_created', $showId, $organizerId);
    
    return [
        'show_id' => $showId,
        'status' => SHOW_STATUS_ANNOUNCED,
        'show_details' => $validatedShow,
        'created_at' => date('c')
    ];
}

/**
 * Register a cat for a show
 */
function registerCatForShow($userId, $catId, $showId, $categories) {
    // Validate cat ownership
    if (!canRegisterCat($userId, $catId)) {
        throw new Exception('Cannot register this cat', 403);
    }
    
    // Get show details
    $show = getShowById($showId);
    if (!$show || $show['status'] !== SHOW_STATUS_REGISTRATION_OPEN) {
        throw new Exception('Show not available for registration', 400);
    }
    
    // Check registration deadline
    if (isRegistrationClosed($show)) {
        throw new Exception('Registration deadline has passed', 400);
    }
    
    // Check participant limit
    if (isShowFull($showId)) {
        throw new Exception('Show is full', 400);
    }
    
    // Validate categories
    $validatedCategories = validateShowCategories($showId, $categories);
    
    // Check if cat meets category requirements
    foreach ($validatedCategories as $category) {
        if (!doesCatMeetCategoryRequirements($catId, $category)) {
            throw new Exception("Cat does not meet requirements for category: $category", 400);
        }
    }
    
    // Check if cat is already registered
    if (isCatAlreadyRegistered($catId, $showId)) {
        throw new Exception('Cat is already registered for this show', 400);
    }
    
    // Register cat
    $registrationId = createShowRegistration($userId, $catId, $showId, $validatedCategories);
    
    // Update show participant count
    updateShowParticipantCount($showId);
    
    // Notify organizers
    notifyCatRegistration($showId, $catId, $userId);
    
    // Log registration
    logShowEvent('cat_registered', $showId, $userId, $catId);
    
    return [
        'registration_id' => $registrationId,
        'status' => 'registered',
        'categories' => $validatedCategories,
        'registered_at' => date('c')
    ];
}

/**
 * Start a cat show
 */
function startCatShow($showId, $organizerId) {
    // Validate organizer permissions
    $show = getShowById($showId);
    if (!$show || $show['organizer_id'] !== $organizerId) {
        throw new Exception('Cannot start this show', 403);
    }
    
    if ($show['status'] !== SHOW_STATUS_REGISTRATION_CLOSED) {
        throw new Exception('Show cannot be started at this time', 400);
    }
    
    // Check minimum participants
    if (getShowParticipantCount($showId) < 3) {
        throw new Exception('Insufficient participants to start show', 400);
    }
    
    // Update show status
    updateShowStatus($showId, SHOW_STATUS_IN_PROGRESS);
    
    // Generate show schedule
    $schedule = generateShowSchedule($showId);
    
    // Notify participants
    notifyShowStart($showId);
    
    // Log show start
    logShowEvent('show_started', $showId, $organizerId);
    
    return [
        'status' => SHOW_STATUS_IN_PROGRESS,
        'schedule' => $schedule,
        'started_at' => date('c')
    ];
}

/**
 * Submit cat show entry
 */
function submitCatShowEntry($userId, $catId, $showId, $category, $entryData) {
    // Validate registration
    if (!isCatRegisteredForCategory($catId, $showId, $category)) {
        throw new Exception('Cat not registered for this category', 400);
    }
    
    // Get show details
    $show = getShowById($showId);
    if ($show['status'] !== SHOW_STATUS_IN_PROGRESS) {
        throw new Exception('Show not in progress', 400);
    }
    
    // Validate entry data
    $validatedEntry = validateShowEntry($category, $entryData);
    
    // Submit entry
    $entryId = createShowEntry($userId, $catId, $showId, $category, $validatedEntry);
    
    // Log entry submission
    logShowEvent('entry_submitted', $showId, $userId, $catId, $category);
    
    return [
        'entry_id' => $entryId,
        'status' => 'submitted',
        'category' => $category,
        'submitted_at' => date('c')
    ];
}

/**
 * Judge a cat show category
 */
function judgeCatShowCategory($judgeId, $showId, $category, $judgingData) {
    // Validate judge permissions
    if (!canJudgeShow($judgeId, $showId)) {
        throw new Exception('Cannot judge this show', 403);
    }
    
    // Get show details
    $show = getShowById($showId);
    if ($show['status'] !== SHOW_STATUS_JUDGING) {
        throw new Exception('Show not in judging phase', 400);
    }
    
    // Validate judging data
    $validatedJudging = validateJudgingData($category, $judgingData);
    
    // Process judging results
    $results = processJudgingResults($showId, $category, $validatedJudging);
    
    // Update category results
    updateCategoryResults($showId, $category, $results);
    
    // Log judging
    logShowEvent('category_judged', $showId, $judgeId, null, $category);
    
    return [
        'status' => 'judged',
        'category' => $category,
        'results' => $results,
        'judged_at' => date('c')
    ];
}

/**
 * Complete a cat show
 */
function completeCatShow($showId, $organizerId) {
    // Validate organizer permissions
    $show = getShowById($showId);
    if (!$show || $show['organizer_id'] !== $organizerId) {
        throw new Exception('Cannot complete this show', 403);
    }
    
    if ($show['status'] !== SHOW_STATUS_JUDGING) {
        throw new Exception('Show not ready for completion', 400);
    }
    
    // Calculate final results
    $finalResults = calculateFinalShowResults($showId);
    
    // Distribute prizes
    distributeShowPrizes($showId, $finalResults);
    
    // Update show status
    updateShowStatus($showId, SHOW_STATUS_COMPLETED);
    
    // Announce results
    announceShowResults($showId, $finalResults);
    
    // Notify participants
    notifyShowCompletion($showId, $finalResults);
    
    // Log show completion
    logShowEvent('show_completed', $showId, $organizerId);
    
    return [
        'status' => SHOW_STATUS_COMPLETED,
        'final_results' => $finalResults,
        'completed_at' => date('c')
    ];
}

/**
 * Get available cat shows
 */
function getAvailableCatShows($filters = []) {
    $pdo = get_db();
    
    $whereConditions = ['status IN (?, ?)'];
    $params = [SHOW_STATUS_ANNOUNCED, SHOW_STATUS_REGISTRATION_OPEN];
    
    // Apply filters
    if (isset($filters['category'])) {
        $whereConditions[] = 'EXISTS (SELECT 1 FROM show_categories sc WHERE sc.show_id = s.id AND sc.category = ?)';
        $params[] = $filters['category'];
    }
    
    if (isset($filters['start_date'])) {
        $whereConditions[] = 'DATE(start_time) >= ?';
        $params[] = $filters['start_date'];
    }
    
    if (isset($filters['end_date'])) {
        $whereConditions[] = 'DATE(end_time) <= ?';
        $params[] = $filters['end_date'];
    }
    
    if (isset($filters['organizer'])) {
        $whereConditions[] = 'u.username ILIKE ?';
        $params[] = '%' . $filters['organizer'] . '%';
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $stmt = $pdo->prepare("
        SELECT s.*, u.username as organizer_username, u.rating as organizer_rating,
               COUNT(sr.id) as participant_count
        FROM cat_shows s
        JOIN users u ON s.organizer_id = u.id
        LEFT JOIN show_registrations sr ON s.id = sr.show_id
        WHERE $whereClause
        GROUP BY s.id, u.username, u.rating
        ORDER BY s.start_time ASC
        LIMIT 50
    ");
    
    $stmt->execute($params);
    $shows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return formatCatShows($shows);
}

/**
 * Get show details
 */
function getShowDetails($showId) {
    $pdo = get_db();
    
    // Get show information
    $stmt = $pdo->prepare("
        SELECT s.*, u.username as organizer_username, u.rating as organizer_rating
        FROM cat_shows s
        JOIN users u ON s.organizer_id = u.id
        WHERE s.id = ?
    ");
    
    $stmt->execute([$showId]);
    $show = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$show) {
        throw new Exception('Show not found', 404);
    }
    
    // Get show categories
    $categories = getShowCategories($showId);
    
    // Get prize information
    $prizes = getShowPrizes($showId);
    
    // Get participant count
    $participantCount = getShowParticipantCount($showId);
    
    // Get registration deadline
    $registrationDeadline = calculateRegistrationDeadline($show);
    
    return [
        'show' => $show,
        'categories' => $categories,
        'prizes' => $prizes,
        'participant_count' => $participantCount,
        'registration_deadline' => $registrationDeadline,
        'can_register' => canRegisterForShow($show)
    ];
}

/**
 * Get show results
 */
function getShowResults($showId) {
    $pdo = get_db();
    
    // Get show information
    $show = getShowById($showId);
    if (!$show) {
        throw new Exception('Show not found', 404);
    }
    
    // Get category results
    $categoryResults = getCategoryResults($showId);
    
    // Get overall winners
    $overallWinners = getOverallWinners($showId);
    
    // Get participant rankings
    $participantRankings = getParticipantRankings($showId);
    
    return [
        'show' => $show,
        'category_results' => $categoryResults,
        'overall_winners' => $overallWinners,
        'participant_rankings' => $participantRankings
    ];
}

/**
 * Validate show data
 */
function validateShowData($showData) {
    $validated = [];
    
    // Required fields
    if (empty($showData['title'])) {
        throw new Exception('Show title is required', 400);
    }
    
    if (empty($showData['start_time'])) {
        throw new Exception('Show start time is required', 400);
    }
    
    if (empty($showData['end_time'])) {
        throw new Exception('Show end time is required', 400);
    }
    
    if (empty($showData['categories']) || !is_array($showData['categories'])) {
        throw new Exception('Show categories are required', 400);
    }
    
    // Validate times
    $startTime = strtotime($showData['start_time']);
    $endTime = strtotime($showData['end_time']);
    
    if ($startTime === false || $endTime === false) {
        throw new Exception('Invalid time format', 400);
    }
    
    if ($startTime >= $endTime) {
        throw new Exception('End time must be after start time', 400);
    }
    
    if ($startTime < time()) {
        throw new Exception('Start time cannot be in the past', 400);
    }
    
    // Validate categories
    foreach ($showData['categories'] as $category) {
        if (!array_key_exists($category, SHOW_CATEGORIES)) {
            throw new Exception("Invalid category: $category", 400);
        }
    }
    
    // Optional fields with defaults
    $validated['title'] = $showData['title'];
    $validated['description'] = $showData['description'] ?? '';
    $validated['start_time'] = $showData['start_time'];
    $validated['end_time'] = $showData['end_time'];
    $validated['categories'] = $showData['categories'];
    $validated['location'] = $showData['location'] ?? 'Virtual';
    $validated['max_participants'] = $showData['max_participants'] ?? SHOW_MAX_PARTICIPANTS;
    $validated['entry_fee'] = $showData['entry_fee'] ?? 0;
    $validated['prizes'] = $showData['prizes'] ?? [];
    $validated['rules'] = $showData['rules'] ?? '';
    $validated['judging_criteria'] = $showData['judging_criteria'] ?? [];
    
    return $validated;
}

/**
 * Check if user can organize shows
 */
function canOrganizeShows($userId) {
    $user = getUserById($userId);
    
    // Check user level and reputation
    if ($user['level'] < 20) {
        return false;
    }
    
    if ($user['rating'] < 4.0) {
        return false;
    }
    
    // Check if user has organized shows before
    $organizedShows = getUserOrganizedShows($userId);
    if (count($organizedShows) > 0) {
        return true;
    }
    
    // First-time organizers need approval
    return false;
}

/**
 * Check for conflicting shows
 */
function hasConflictingShows($startTime, $endTime) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM cat_shows 
        WHERE status NOT IN (?, ?)
        AND (
            (start_time BETWEEN ? AND ?) OR
            (end_time BETWEEN ? AND ?) OR
            (start_time <= ? AND end_time >= ?)
        )
    ");
    
    $stmt->execute([
        SHOW_STATUS_CANCELLED, SHOW_STATUS_COMPLETED,
        $startTime, $endTime, $startTime, $endTime, $startTime, $endTime
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] > 0;
}

/**
 * Create show record
 */
function createShowRecord($organizerId, $showData) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO cat_shows 
        (organizer_id, title, description, start_time, end_time, location, 
         max_participants, entry_fee, rules, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $organizerId,
        $showData['title'],
        $showData['description'],
        $showData['start_time'],
        $showData['end_time'],
        $showData['location'],
        $showData['max_participants'],
        $showData['entry_fee'],
        $showData['rules'],
        SHOW_STATUS_ANNOUNCED,
        date('Y-m-d H:i:s')
    ]);
    
    return $pdo->lastInsertId();
}

/**
 * Create show categories
 */
function createShowCategories($showId, $categories) {
    $pdo = get_db();
    
    foreach ($categories as $category) {
        $stmt = $pdo->prepare("
            INSERT INTO show_categories 
            (show_id, category, name, description, judging_criteria, max_score, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $categoryInfo = SHOW_CATEGORIES[$category];
        $stmt->execute([
            $showId,
            $category,
            $categoryInfo['name'],
            $categoryInfo['description'],
            json_encode($categoryInfo['judging_criteria']),
            $categoryInfo['max_score'],
            date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Create show prize pool
 */
function createShowPrizePool($showId, $prizes) {
    if (empty($prizes)) {
        return;
    }
    
    $pdo = get_db();
    
    foreach ($prizes as $prize) {
        $stmt = $pdo->prepare("
            INSERT INTO show_prizes 
            (show_id, rank, prize_type, prize_value, description, created_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $showId,
            $prize['rank'],
            $prize['type'],
            $prize['value'],
            $prize['description'],
            date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Get show by ID
 */
function getShowById($showId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM cat_shows WHERE id = ?
    ");
    
    $stmt->execute([$showId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update show status
 */
function updateShowStatus($showId, $status) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        UPDATE cat_shows 
        SET status = ?, updated_at = ? 
        WHERE id = ?
    ");
    
    $stmt->execute([$status, date('Y-m-d H:i:s'), $showId]);
}

/**
 * Get show categories
 */
function getShowCategories($showId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM show_categories WHERE show_id = ?
    ");
    
    $stmt->execute([$showId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get show prizes
 */
function getShowPrizes($showId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM show_prizes WHERE show_id = ? ORDER BY rank ASC
    ");
    
    $stmt->execute([$showId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get show participant count
 */
function getShowParticipantCount($showId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT cat_id) as count FROM show_registrations WHERE show_id = ?
    ");
    
    $stmt->execute([$showId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'];
}

/**
 * Update show participant count
 */
function updateShowParticipantCount($showId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        UPDATE cat_shows 
        SET participant_count = (SELECT COUNT(DISTINCT cat_id) FROM show_registrations WHERE show_id = ?)
        WHERE id = ?
    ");
    
    $stmt->execute([$showId, $showId]);
}

/**
 * Check if show is full
 */
function isShowFull($showId) {
    $show = getShowById($showId);
    $participantCount = getShowParticipantCount($showId);
    
    return $participantCount >= $show['max_participants'];
}

/**
 * Check if registration is closed
 */
function isRegistrationClosed($show) {
    $startTime = strtotime($show['start_time']);
    $deadline = $startTime - (SHOW_REGISTRATION_DEADLINE_HOURS * 3600);
    
    return time() > $deadline;
}

/**
 * Calculate registration deadline
 */
function calculateRegistrationDeadline($show) {
    $startTime = strtotime($show['start_time']);
    return $startTime - (SHOW_REGISTRATION_DEADLINE_HOURS * 3600);
}

/**
 * Check if can register for show
 */
function canRegisterForShow($show) {
    if ($show['status'] !== SHOW_STATUS_REGISTRATION_OPEN) {
        return false;
    }
    
    if (isRegistrationClosed($show)) {
        return false;
    }
    
    if (isShowFull($show['id'])) {
        return false;
    }
    
    return true;
}

/**
 * Log show event
 */
function logShowEvent($eventType, $showId, $userId, $catId = null, $category = null) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO show_events 
        (event_type, show_id, user_id, cat_id, category, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$eventType, $showId, $userId, $catId, $category, date('Y-m-d H:i:s')]);
}

/**
 * Format cat shows for display
 */
function formatCatShows($shows) {
    $formatted = [];
    
    foreach ($shows as $show) {
        $formatted[] = [
            'id' => $show['id'],
            'title' => $show['title'],
            'description' => $show['description'],
            'start_time' => $show['start_time'],
            'end_time' => $show['end_time'],
            'location' => $show['location'],
            'status' => $show['status'],
            'organizer' => [
                'username' => $show['organizer_username'],
                'rating' => $show['organizer_rating']
            ],
            'participant_count' => $show['participant_count'],
            'max_participants' => $show['max_participants'],
            'entry_fee' => $show['entry_fee'],
            'created_at' => $show['created_at']
        ];
    }
    
    return $formatted;
}
