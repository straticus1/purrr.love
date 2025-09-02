<?php
/**
 * Purrr.love - Enhanced Cat Quests & Achievements
 * Developed and Designed by Ryan Coleman. <coleman.ryan@gmail.com>
 * Enhanced with feline-specific activities, seasonal events, and personality-based quests
 */
require_once 'includes/functions.php';
require_once 'includes/cat_behavior.php';
require_once 'includes/quests.php';

requireLogin();

$page_title = 'Cat Quests & Achievements';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$user_cats = getUserCats($user_id);
$current_season = getCurrentSeason();
?>

<div class="container mt-5">
    <div class="hero hero-padding">
        <h1><i class="fas fa-scroll"></i> Cat Quests & Achievements</h1>
        <p>Complete feline-focused quests to earn rewards and unlock special achievements!</p>
        <div class="season-banner">
            <span class="season-icon"><?php echo getSeasonIcon($current_season); ?></span>
            <span class="season-text"><?php echo ucfirst($current_season); ?> Season</span>
        </div>
    </div>

    <div class="tabs">
        <button class="tab-link active" onclick="openTab(event, 'daily-quests')">Daily Quests</button>
        <button class="tab-link" onclick="openTab(event, 'seasonal-quests')">Seasonal Quests</button>
        <button class="tab-link" onclick="openTab(event, 'personality-quests')">Personality Quests</button>
        <button class="tab-link" onclick="openTab(event, 'achievements')">Achievements</button>
        <button class="tab-link" onclick="openTab(event, 'quest-progress')">Progress</button>
    </div>

    <!-- Daily Quests Tab -->
    <div id="daily-quests" class="tab-content active">
        <h2>🐱 Your Daily Cat Quests</h2>
        <p class="text-muted">New quests appear every day. Complete them to earn rewards and experience!</p>
        
        <div class="quest-categories">
            <div class="quest-category">
                <h4>🍽️ Feeding Quests</h4>
                <div class="quest-list" id="feeding-quests">
                    <div class="loader"></div>
                </div>
            </div>
            
            <div class="quest-category">
                <h4>🎮 Gaming Quests</h4>
                <div class="quest-list" id="gaming-quests">
                    <div class="loader"></div>
                </div>
            </div>
            
            <div class="quest-category">
                <h4>🧴 Care Quests</h4>
                <div class="quest-list" id="care-quests">
                    <div class="loader"></div>
                </div>
            </div>
            
            <div class="quest-category">
                <h4>🏠 Social Quests</h4>
                <div class="quest-list" id="social-quests">
                    <div class="loader"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seasonal Quests Tab -->
    <div id="seasonal-quests" class="tab-content">
        <h2>🌙 Seasonal Cat Activities</h2>
        <p class="text-muted">Special quests that change with the seasons. Each season brings unique feline activities!</p>
        
        <div class="seasonal-quests-container">
            <?php foreach (getSeasonalQuests($current_season) as $quest): ?>
                <div class="seasonal-quest-card">
                    <div class="quest-header">
                        <div class="quest-icon"><?php echo $quest['icon']; ?></div>
                        <div class="quest-info">
                            <h4><?php echo htmlspecialchars($quest['title']); ?></h4>
                            <p><?php echo htmlspecialchars($quest['description']); ?></p>
                        </div>
                        <div class="quest-status">
                            <span class="status-badge <?php echo $quest['status']; ?>">
                                <?php echo ucfirst($quest['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="quest-details">
                        <div class="quest-requirements">
                            <strong>Requirements:</strong>
                            <ul>
                                <?php foreach ($quest['requirements'] as $req): ?>
                                    <li><?php echo htmlspecialchars($req); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="quest-rewards">
                            <strong>Rewards:</strong>
                            <div class="reward-items">
                                <?php foreach ($quest['rewards'] as $reward): ?>
                                    <span class="reward-item">
                                        <?php echo $reward['icon']; ?> <?php echo htmlspecialchars($reward['name']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="quest-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $quest['progress_percent']; ?>%"></div>
                            </div>
                            <span class="progress-text">
                                <?php echo $quest['current_progress']; ?> / <?php echo $quest['required_progress']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($quest['status'] === 'available'): ?>
                        <button class="btn btn-primary start-quest-btn" data-quest-id="<?php echo $quest['id']; ?>">
                            🚀 Start Quest
                        </button>
                    <?php elseif ($quest['status'] === 'in_progress'): ?>
                        <button class="btn btn-secondary" disabled>In Progress...</button>
                    <?php elseif ($quest['status'] === 'completed'): ?>
                        <button class="btn btn-success claim-reward-btn" data-quest-id="<?php echo $quest['id']; ?>">
                            🎁 Claim Reward
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Personality Quests Tab -->
    <div id="personality-quests" class="tab-content">
        <h2>😺 Personality-Based Quests</h2>
        <p class="text-muted">Special quests tailored to your cats' unique personalities!</p>
        
        <div class="personality-quests-container">
            <?php foreach ($user_cats as $cat): ?>
                <div class="cat-personality-quests">
                    <div class="cat-header">
                        <img src="<?php echo htmlspecialchars($cat['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($cat['name']); ?>" 
                             class="cat-avatar">
                        <div class="cat-info">
                            <h4><?php echo htmlspecialchars($cat['name']); ?></h4>
                            <p class="personality-type"><?php echo getCatPersonalityName($cat['personality_type']); ?></p>
                            <p class="personality-description"><?php echo getCatPersonalityDescription($cat['personality_type']); ?></p>
                        </div>
                    </div>
                    
                    <div class="personality-quests" id="personality-quests-<?php echo $cat['id']; ?>">
                        <div class="loader"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Achievements Tab -->
    <div id="achievements" class="tab-content">
        <h2>🏆 Your Cat Achievements</h2>
        <p class="text-muted">Unlock achievements by reaching milestones with your cats on Purrr.love.</p>
        
        <div class="achievement-categories">
            <div class="achievement-category">
                <h4>🐱 Cat Care Achievements</h4>
                <div class="achievement-list" id="care-achievements">
                    <div class="loader"></div>
                </div>
            </div>
            
            <div class="achievement-category">
                <h4>🎮 Gaming Achievements</h4>
                <div class="achievement-list" id="gaming-achievements">
                    <div class="loader"></div>
                </div>
            </div>
            
            <div class="achievement-category">
                <h4>🧬 Breeding Achievements</h4>
                <div class="achievement-list" id="breeding-achievements">
                    <div class="loader"></div>
                </div>
            </div>
            
            <div class="achievement-category">
                <h4>🏠 Social Achievements</h4>
                <div class="achievement-list" id="social-achievements">
                    <div class="loader"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quest Progress Tab -->
    <div id="quest-progress" class="tab-content">
        <h2>📊 Quest Progress Overview</h2>
        <p class="text-muted">Track your progress across all quest types and see your completion statistics.</p>
        
        <div class="progress-overview">
            <div class="progress-stats">
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <h4>Daily Quests</h4>
                        <p class="stat-number" id="daily-completed">-</p>
                        <p class="stat-label">Completed Today</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🌙</div>
                    <div class="stat-info">
                        <h4>Seasonal Quests</h4>
                        <p class="stat-number" id="seasonal-completed">-</p>
                        <p class="stat-label">Completed This Season</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-info">
                        <h4>Achievements</h4>
                        <p class="stat-number" id="achievements-unlocked">-</p>
                        <p class="stat-label">Total Unlocked</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-info">
                        <h4>Quest Score</h4>
                        <p class="stat-number" id="quest-score">-</p>
                        <p class="stat-label">Overall Rating</p>
                    </div>
                </div>
            </div>
            
            <div class="progress-charts">
                <div class="chart-container">
                    <h4>Quest Completion by Category</h4>
                    <canvas id="questCategoryChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h4>Weekly Progress</h4>
                    <canvas id="weeklyProgressChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quest Details Modal -->
<div class="modal fade" id="questDetailsModal" tabindex="-1" aria-labelledby="questDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questDetailsModalLabel">Quest Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="questDetailsBody">
                <!-- Quest details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="start-quest-btn">Start Quest</button>
            </div>
        </div>
    </div>
</div>

<!-- Quest Completion Modal -->
<div class="modal fade" id="questCompletionModal" tabindex="-1" aria-labelledby="questCompletionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questCompletionModalLabel">Quest Completed! 🎉</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="questCompletionBody">
                <!-- Quest completion details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Continue</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="assets/js/cat-quests.js"></script>

<style>
.season-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 25px;
    border-radius: 25px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    font-size: 1.1em;
    font-weight: 600;
}

.season-icon {
    font-size: 1.5em;
}

.tabs {
    display: flex;
    justify-content: center;
    margin: 30px 0;
    border-bottom: 2px solid #e9ecef;
}

.tab-link {
    background: none;
    border: none;
    padding: 15px 25px;
    margin: 0 5px;
    cursor: pointer;
    font-size: 1.1em;
    color: #6c757d;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.tab-link:hover {
    color: #007bff;
}

.tab-link.active {
    color: #007bff;
    border-bottom-color: #007bff;
}

.tab-content {
    display: none;
    animation: fadeIn 0.5s ease-in;
}

.tab-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.quest-categories {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.quest-category {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.quest-category h4 {
    color: #495057;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.seasonal-quest-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.quest-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.quest-icon {
    font-size: 2.5em;
    width: 60px;
    text-align: center;
}

.quest-info {
    flex: 1;
}

.quest-info h4 {
    margin: 0 0 5px 0;
    color: #495057;
}

.quest-info p {
    margin: 0;
    color: #6c757d;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.available {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.in_progress {
    background-color: #fff3cd;
    color: #856404;
}

.status-badge.completed {
    background-color: #cce5ff;
    color: #004085;
}

.quest-details {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.quest-requirements ul {
    margin: 10px 0;
    padding-left: 20px;
}

.quest-rewards {
    margin: 15px 0;
}

.reward-items {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.reward-item {
    background-color: #e9ecef;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.9em;
    color: #495057;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background-color: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    margin: 10px 0;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.9em;
    color: #6c757d;
    text-align: center;
    display: block;
}

.cat-personality-quests {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.cat-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
}

.cat-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e9ecef;
}

.cat-info h4 {
    margin: 0 0 5px 0;
    color: #495057;
}

.personality-type {
    font-weight: 600;
    color: #007bff;
    margin: 5px 0;
}

.personality-description {
    color: #6c757d;
    font-size: 0.9em;
    margin: 0;
}

.achievement-categories {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.achievement-list {
    min-height: 200px;
}

.progress-overview {
    margin-top: 30px;
}

.progress-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    font-size: 2.5em;
    color: #007bff;
}

.stat-info h4 {
    margin: 0 0 10px 0;
    color: #495057;
    font-size: 1.1em;
}

.stat-number {
    font-size: 2em;
    font-weight: bold;
    color: #007bff;
    margin: 0;
}

.stat-label {
    margin: 0;
    color: #6c757d;
    font-size: 0.9em;
}

.progress-charts {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
}

.chart-container {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.chart-container h4 {
    margin: 0 0 20px 0;
    color: #495057;
    text-align: center;
}

.loader {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

.btn {
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
</style>
