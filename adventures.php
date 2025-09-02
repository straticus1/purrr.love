<?php
/**
 * Purrr.love - Enhanced Cat Adventures
 * Developed and Designed by Ryan Coleman. <coleman.ryan@gmail.com>
 * Enhanced with feline-specific quests, territory exploration, and personality-based adventures
 */

include 'includes/header.php';
include 'includes/cat_behavior.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = get_db();

// Fetch user's cats with enhanced stats
$stmt = $pdo->prepare("
    SELECT c.*, 
           COUNT(cti.id) as territory_items,
           SUM(cti.territory_bonus) as total_territory_bonus
    FROM cats c 
    LEFT JOIN cat_territory_items cti ON c.id = cti.cat_id
    WHERE c.user_id = ?
    GROUP BY c.id
    ORDER BY c.level DESC, c.experience DESC
");
$stmt->execute([$user_id]);
$pets = $stmt->fetchAll();

// Get current season and time
$current_season = getCurrentSeason();
$current_hour = (int)date('H');
$is_night = $current_hour < 6 || $current_hour > 20;
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">🐱 Cat Adventures</h1>
    <p class="text-center text-muted">Send your cats on exciting quests to earn experience, find rare items, and explore territories!</p>
    
    <!-- Adventure Status & Rewards Notification Area -->
    <div id="adventure-notifications" class="mb-4"></div>
    
    <!-- Season and Time Info -->
    <div class="environment-info mb-4">
        <div class="info-cards">
            <div class="info-card">
                <div class="info-icon"><?php echo getSeasonIcon($current_season); ?></div>
                <div class="info-content">
                    <h5>Current Season</h5>
                    <p><?php echo ucfirst($current_season); ?></p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon"><?php echo $is_night ? '🌙' : '☀️'; ?></div>
                <div class="info-content">
                    <h5>Time of Day</h5>
                    <p><?php echo $is_night ? 'Night' : 'Day'; ?></p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">🌡️</div>
                <div class="info-content">
                    <h5>Weather</h5>
                    <p id="current-weather">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Pet Selection -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Your Cats</h4>
                    <small class="text-muted">Select a cat to view available adventures</small>
                </div>
                <div class="list-group list-group-flush" id="pet-selection-list">
                    <?php if (empty($pets)): ?>
                        <a href="#" class="list-group-item list-group-item-action">You don't have any cats yet.</a>
                    <?php else: ?>
                        <?php foreach ($pets as $pet): ?>
                            <a href="#" class="list-group-item list-group-item-action" data-pet-id="<?php echo $pet['id']; ?>">
                                <div class="pet-selection-item">
                                    <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($pet['name']); ?>" 
                                         class="pet-avatar">
                                    <div class="pet-info">
                                        <strong><?php echo htmlspecialchars($pet['name']); ?></strong>
                                        <div class="pet-stats">
                                            <span class="stat">🏆 Level <?php echo $pet['level']; ?></span>
                                            <span class="stat">⚡ <?php echo $pet['energy']; ?>/100</span>
                                            <span class="stat">❤️ <?php echo $pet['happiness']; ?>/100</span>
                                        </div>
                                        <div class="pet-personality">
                                            <?php echo getCatPersonalityName($pet['personality_type']); ?>
                                        </div>
                                        <div class="territory-info">
                                            <span class="territory-bonus">🏠 +<?php echo number_format($pet['total_territory_bonus'] ?? 0, 1); ?> Territory</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="adventure-status-badge" id="status-pet-<?php echo $pet['id']; ?>"></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Quests & Adventure Details -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 id="quest-list-header">Select a Cat to View Adventures</h4>
                </div>
                <div class="card-body" id="quest-list-container">
                    <p class="text-muted">Please select a cat from the list on the left to view available adventures.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adventure Types Template -->
<template id="adventure-types-template">
    <div class="adventure-categories">
        <!-- Territory Exploration -->
        <div class="adventure-category">
            <h5>🏠 Territory Exploration</h5>
            <div class="adventure-list" id="territory-adventures"></div>
        </div>
        
        <!-- Hunting Adventures -->
        <div class="adventure-category">
            <h5>🐭 Hunting Adventures</h5>
            <div class="adventure-list" id="hunting-adventures"></div>
        </div>
        
        <!-- Social Adventures -->
        <div class="adventure-category">
            <h5>👥 Social Adventures</h5>
            <div class="adventure-list" id="social-adventures"></div>
        </div>
        
        <!-- Seasonal Adventures -->
        <div class="adventure-category">
            <h5>🌙 Seasonal Adventures</h5>
            <div class="adventure-list" id="seasonal-adventures"></div>
        </div>
        
        <!-- Special Events -->
        <div class="adventure-category">
            <h5>🎉 Special Events</h5>
            <div class="adventure-list" id="special-adventures"></div>
        </div>
    </div>
</template>

<!-- Adventure Card Template -->
<template id="adventure-card-template">
    <div class="adventure-card" data-adventure-id="">
        <div class="adventure-header">
            <div class="adventure-icon"></div>
            <div class="adventure-info">
                <h6 class="adventure-title"></h6>
                <p class="adventure-description"></p>
            </div>
            <div class="adventure-difficulty"></div>
        </div>
        
        <div class="adventure-details">
            <div class="adventure-requirements">
                <strong>Requirements:</strong>
                <ul class="requirements-list"></ul>
            </div>
            
            <div class="adventure-rewards">
                <strong>Rewards:</strong>
                <div class="rewards-list"></div>
            </div>
            
            <div class="adventure-stats">
                <div class="stat-item">
                    <span class="stat-label">Duration:</span>
                    <span class="stat-value duration"></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Success Rate:</span>
                    <span class="stat-value success-rate"></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Energy Cost:</span>
                    <span class="stat-value energy-cost"></span>
                </div>
            </div>
        </div>
        
        <div class="adventure-actions">
            <button class="btn btn-primary start-adventure-btn" disabled>
                🚀 Start Adventure
            </button>
            <button class="btn btn-secondary view-details-btn">
                📋 View Details
            </button>
        </div>
    </div>
</template>

<!-- Quest Details Modal -->
<div class="modal fade" id="questModal" tabindex="-1" aria-labelledby="questModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questModalLabel">Adventure Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="questModalBody">
                <!-- Adventure details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="start-adventure-btn">Start Adventure</button>
            </div>
        </div>
    </div>
</div>

<!-- Adventure Progress Modal -->
<div class="modal fade" id="adventureProgressModal" tabindex="-1" aria-labelledby="adventureProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adventureProgressModalLabel">Adventure in Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="adventureProgressBody">
                <!-- Adventure progress will be shown here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="assets/js/cat-adventures.js"></script>

<style>
.environment-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px;
    color: white;
}

.info-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.info-card {
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(255, 255, 255, 0.1);
    padding: 15px;
    border-radius: 10px;
    backdrop-filter: blur(10px);
}

.info-icon {
    font-size: 2em;
}

.info-content h5 {
    margin: 0 0 5px 0;
    font-size: 0.9em;
    opacity: 0.8;
}

.info-content p {
    margin: 0;
    font-size: 1.1em;
    font-weight: 600;
}

.pet-selection-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.pet-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e9ecef;
}

.pet-info {
    flex: 1;
}

.pet-stats {
    display: flex;
    gap: 10px;
    margin: 5px 0;
}

.stat {
    font-size: 0.8em;
    color: #6c757d;
}

.pet-personality {
    font-size: 0.9em;
    color: #007bff;
    font-weight: 600;
}

.territory-info {
    margin-top: 5px;
}

.territory-bonus {
    font-size: 0.8em;
    color: #28a745;
    background-color: #d4edda;
    padding: 2px 8px;
    border-radius: 10px;
}

.adventure-categories {
    display: grid;
    gap: 25px;
}

.adventure-category h5 {
    color: #495057;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.adventure-list {
    display: grid;
    gap: 15px;
}

.adventure-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.adventure-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.adventure-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.adventure-icon {
    font-size: 2em;
    width: 50px;
    text-align: center;
}

.adventure-info {
    flex: 1;
}

.adventure-title {
    margin: 0 0 5px 0;
    color: #495057;
    font-weight: 600;
}

.adventure-description {
    margin: 0;
    color: #6c757d;
    font-size: 0.9em;
}

.adventure-difficulty {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8em;
    font-weight: 600;
    text-transform: uppercase;
}

.adventure-difficulty.easy {
    background-color: #d4edda;
    color: #155724;
}

.adventure-difficulty.medium {
    background-color: #fff3cd;
    color: #856404;
}

.adventure-difficulty.hard {
    background-color: #f8d7da;
    color: #721c24;
}

.adventure-difficulty.epic {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
}

.adventure-details {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.adventure-requirements ul {
    margin: 10px 0;
    padding-left: 20px;
}

.adventure-rewards {
    margin: 15px 0;
}

.rewards-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.reward-item {
    background-color: #e9ecef;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8em;
    color: #495057;
}

.adventure-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 10px;
    margin-top: 15px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px;
    background-color: white;
    border-radius: 6px;
    font-size: 0.9em;
}

.stat-label {
    color: #6c757d;
}

.stat-value {
    font-weight: 600;
    color: #007bff;
}

.adventure-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn {
    border-radius: 20px;
    padding: 8px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.adventure-status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 8px;
    border-radius: 10px;
    font-size: 0.7em;
    font-weight: 600;
    text-transform: uppercase;
}

.adventure-status-badge.on-adventure {
    background-color: #fff3cd;
    color: #856404;
}

.adventure-status-badge.available {
    background-color: #d4edda;
    color: #155724;
}

.adventure-status-badge.exhausted {
    background-color: #f8d7da;
    color: #721c24;
}

.list-group-item {
    position: relative;
    transition: all 0.3s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.list-group-item.active .pet-personality,
.list-group-item.active .stat,
.list-group-item.active .territory-bonus {
    color: rgba(255, 255, 255, 0.8) !important;
}

.list-group-item.active .territory-bonus {
    background-color: rgba(255, 255, 255, 0.2) !important;
}
</style>
