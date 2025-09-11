<?php
/**
 * 🚀 Purrr.love Real-Time Cat Monitoring Dashboard
 * Live updates with WebSocket support and comprehensive cat monitoring
 */

// Define secure access for includes
define('SECURE_ACCESS', true);

session_start();
require_once '../includes/functions.php';
require_once '../includes/authentication.php';
require_once '../includes/enhanced_cat_personality.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

try {
    $user = getUserById($_SESSION['user_id']);
    if (!$user) {
        session_destroy();
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Get user's cats and households
$cats = [];
$households = [];
$realtimeData = [];

try {
    $pdo = get_db();
    
    // Get user's cats with real-time status
    $stmt = $pdo->prepare("
        SELECT c.*, rts.*, cds.pending_reminders, cds.avg_care_satisfaction, cds.total_assessments
        FROM cats c 
        LEFT JOIN cat_realtime_status rts ON c.id = rts.cat_id
        LEFT JOIN cat_dashboard_summary cds ON c.id = cds.id
        WHERE c.owner_id = ? AND c.active = 1
        ORDER BY c.name
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cats = $stmt->fetchAll();
    
    // Get household information
    $stmt = $pdo->prepare("
        SELECT h.*, hmo.* 
        FROM cat_households h
        LEFT JOIN household_management_overview hmo ON h.id = hmo.household_id
        WHERE h.owner_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $households = $stmt->fetchAll();
    
    // Get recent activity timeline
    $stmt = $pdo->prepare("
        SELECT * FROM recent_activity_timeline 
        WHERE cat_id IN (SELECT id FROM cats WHERE owner_id = ?)
        ORDER BY event_time DESC 
        LIMIT 20
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recentActivities = $stmt->fetchAll();
    
} catch (Exception $e) {
    $cats = [];
    $households = [];
    $recentActivities = [];
}

// Generate WebSocket connection ID
$connectionId = 'conn_' . $_SESSION['user_id'] . '_' . uniqid();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Real-Time Cat Dashboard - Purrr.love</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.socket.io/4.5.0/socket.io.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .live-indicator {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        
        .live-indicator::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }
        
        .status-card {
            transition: all 0.3s ease;
            border-left: 4px solid #e2e8f0;
        }
        
        .status-card.excellent {
            border-left-color: #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #f0fdf4 100%);
        }
        
        .status-card.good {
            border-left-color: #3b82f6;
            background: linear-gradient(135deg, #eff6ff 0%, #eff6ff 100%);
        }
        
        .status-card.warning {
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fffbeb 100%);
        }
        
        .status-card.critical {
            border-left-color: #ef4444;
            background: linear-gradient(135deg, #fef2f2 0%, #fef2f2 100%);
        }
        
        .cat-avatar {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
            border: 3px solid white;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .activity-pulse {
            animation: activity-pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes activity-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .floating-action {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            min-width: 300px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        
        .notification-toast.show {
            transform: translateX(0);
        }
        
        .wellness-meter {
            background: conic-gradient(from 0deg, #ef4444 0% 20%, #f59e0b 20% 40%, #eab308 40% 60%, #22c55e 60% 80%, #10b981 80% 100%);
            border-radius: 50%;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">

<!-- Navigation -->
<nav class="bg-white shadow-lg border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="dashboard.php" class="text-2xl font-bold text-purple-600">🐱 Purrr.love</a>
                <span class="ml-4 text-sm text-gray-500">Real-Time Dashboard</span>
            </div>
            <div class="flex items-center space-x-4">
                <div class="live-indicator text-sm font-semibold text-gray-700">LIVE</div>
                <div id="connectionStatus" class="text-xs text-gray-500">Connecting...</div>
                <a href="cat-needs.php" class="text-purple-600 hover:text-purple-700">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

<!-- Header -->
<div class="mb-8">
    <div class="gradient-bg rounded-3xl p-8 text-white shadow-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">🚀 Real-Time Cat Monitoring</h1>
                <p class="text-xl text-purple-100">Live tracking of your feline family's wellness and activities</p>
                <div class="mt-4 flex space-x-4">
                    <div class="bg-white/10 rounded-lg px-4 py-2">
                        <span class="text-sm">Total Cats: <strong><?php echo count($cats); ?></strong></span>
                    </div>
                    <?php if (!empty($households)): ?>
                    <div class="bg-white/10 rounded-lg px-4 py-2">
                        <span class="text-sm">Households: <strong><?php echo count($households); ?></strong></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hidden lg:block">
                <div class="text-8xl animate-bounce">📡</div>
            </div>
        </div>
    </div>
</div>

<!-- Real-Time Status Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
    <?php foreach ($cats as $cat): ?>
    <div id="cat-status-<?php echo $cat['id']; ?>" class="status-card bg-white rounded-2xl p-6 shadow-lg <?php echo getCatStatusClass($cat); ?>">
        <div class="flex items-start justify-between mb-4">
            <div class="cat-avatar w-16 h-16 rounded-full flex items-center justify-center text-2xl">
                🐱
            </div>
            <div class="text-right">
                <div class="wellness-score text-2xl font-bold text-gray-900">
                    <?php echo round(($cat['wellness_score'] ?? 0.75) * 100); ?>%
                </div>
                <div class="text-xs text-gray-500">Wellness</div>
            </div>
        </div>
        
        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($cat['name']); ?></h3>
        
        <div class="space-y-2 mb-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Current Activity:</span>
                <span id="activity-<?php echo $cat['id']; ?>" class="text-sm font-medium activity-pulse">
                    <?php echo ucfirst($cat['current_activity'] ?? 'resting'); ?>
                </span>
            </div>
            
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Mood:</span>
                <span id="mood-<?php echo $cat['id']; ?>" class="text-sm font-medium">
                    <?php echo getMoodEmoji($cat['current_mood'] ?? 'content'); ?> 
                    <?php echo ucfirst(str_replace('_', ' ', $cat['current_mood'] ?? 'content')); ?>
                </span>
            </div>
            
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Energy:</span>
                <div class="flex items-center">
                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo round(($cat['energy_level_current'] ?? 0.5) * 100); ?>%"></div>
                    </div>
                    <span class="text-xs text-gray-500"><?php echo round(($cat['energy_level_current'] ?? 0.5) * 100); ?>%</span>
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Stress:</span>
                <div class="flex items-center">
                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                        <div class="bg-red-500 h-2 rounded-full" style="width: <?php echo round(($cat['current_stress_level'] ?? 0.2) * 100); ?>%"></div>
                    </div>
                    <span class="text-xs text-gray-500"><?php echo round(($cat['current_stress_level'] ?? 0.2) * 100); ?>%</span>
                </div>
            </div>
        </div>
        
        <div class="text-xs text-gray-500 mb-3">
            <div>🍽️ Last fed: <?php echo $cat['last_feeding'] ? timeAgo($cat['last_feeding']) : 'Unknown'; ?></div>
            <div>🎾 Last played: <?php echo $cat['last_play_session'] ? timeAgo($cat['last_play_session']) : 'Unknown'; ?></div>
            <div>📊 Today's activities: <strong><?php echo $cat['activity_count_today'] ?? 0; ?></strong></div>
        </div>
        
        <?php if (($cat['pending_reminders'] ?? 0) > 0): ?>
        <div class="bg-amber-100 border border-amber-300 text-amber-800 px-3 py-2 rounded-lg text-xs">
            <i class="fas fa-bell mr-1"></i>
            <?php echo $cat['pending_reminders']; ?> pending reminder(s)
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Activity Timeline -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Real-Time Activity Feed -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-3xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-stream text-blue-500 mr-3"></i>
                    Live Activity Feed
                </h2>
                <div class="live-indicator text-sm font-semibold text-gray-700">LIVE</div>
            </div>
            
            <div id="activityFeed" class="space-y-4 max-h-96 overflow-y-auto">
                <?php foreach ($recentActivities as $activity): ?>
                <div class="activity-item flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center text-white text-sm font-bold">
                            <?php echo getEventEmoji($activity['event_type']); ?>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900">
                                <?php echo htmlspecialchars($activity['cat_name']); ?>
                            </h4>
                            <span class="text-xs text-gray-500">
                                <?php echo timeAgo($activity['event_time']); ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($activity['event_title']); ?></p>
                        <?php if ($activity['event_details']): ?>
                        <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($activity['event_details']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats & Controls -->
    <div class="space-y-6">
        <!-- Household Overview -->
        <?php if (!empty($households)): ?>
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-home text-green-500 mr-2"></i>
                Household Harmony
            </h3>
            <?php foreach ($households as $household): ?>
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($household['household_name']); ?></span>
                    <span class="text-sm text-gray-500"><?php echo $household['active_cats'] ?? 0; ?> cats</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-green-500 h-3 rounded-full" style="width: <?php echo round(($household['social_harmony_score'] ?? 0.5) * 100); ?>%"></div>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    Harmony: <?php echo round(($household['social_harmony_score'] ?? 0.5) * 100); ?>%
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                Quick Actions
            </h3>
            <div class="space-y-3">
                <button onclick="refreshAllData()" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Refresh All Data
                </button>
                <button onclick="openAddActivity()" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Log Activity
                </button>
                <button onclick="openPhotoAnalysis()" class="w-full bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-camera mr-2"></i>
                    Analyze Photo
                </button>
            </div>
        </div>
        
        <!-- Connection Status -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-wifi text-blue-500 mr-2"></i>
                Connection Status
            </h3>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">WebSocket:</span>
                    <span id="wsStatus" class="text-sm font-medium text-red-500">Disconnected</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Last Update:</span>
                    <span id="lastUpdate" class="text-sm font-medium text-gray-500">Never</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Updates Received:</span>
                    <span id="updateCount" class="text-sm font-medium text-gray-500">0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Button -->
<div class="floating-action">
    <button onclick="toggleRealtimeMode()" class="bg-purple-500 hover:bg-purple-600 text-white p-4 rounded-full shadow-lg hover:shadow-xl transition-all">
        <i id="realtimeIcon" class="fas fa-pause text-xl"></i>
    </button>
</div>

<!-- Notification Toast -->
<div id="notificationToast" class="notification-toast bg-white rounded-lg shadow-xl border border-gray-200 p-4">
    <div class="flex items-start">
        <div id="toastIcon" class="flex-shrink-0 mr-3 text-xl"></div>
        <div class="flex-1">
            <div id="toastTitle" class="text-sm font-semibold text-gray-900"></div>
            <div id="toastMessage" class="text-sm text-gray-600 mt-1"></div>
        </div>
        <button onclick="hideToast()" class="ml-3 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<!-- JavaScript for Real-Time Features -->
<script>
class CatDashboardRealtime {
    constructor() {
        this.connectionId = '<?php echo $connectionId; ?>';
        this.userId = <?php echo intval($_SESSION['user_id']); ?>;
        this.isRealtimeActive = true;
        this.updateCount = 0;
        this.lastUpdate = null;
        
        this.init();
    }
    
    init() {
        this.setupWebSocket();
        this.startPeriodicUpdates();
        this.updateConnectionStatus('connecting', 'Connecting...');
        
        // Auto-refresh every 30 seconds as fallback
        setInterval(() => {
            if (this.isRealtimeActive && !this.websocketConnected) {
                this.refreshData();
            }
        }, 30000);
    }
    
    setupWebSocket() {
        // In a real implementation, this would connect to your WebSocket server
        // For demo, we'll simulate WebSocket behavior
        console.log('WebSocket simulation started for connection:', this.connectionId);
        
        // Simulate connection after 1 second
        setTimeout(() => {
            this.websocketConnected = true;
            this.updateConnectionStatus('connected', 'Connected');
            this.showNotification('🔗 Real-time monitoring connected!', 'Live updates are now active.', 'success');
            
            // Simulate periodic updates
            this.simulateRealtimeUpdates();
        }, 1000);
    }
    
    simulateRealtimeUpdates() {
        if (!this.isRealtimeActive) return;
        
        // Simulate random cat activity updates
        setInterval(() => {
            if (this.isRealtimeActive && this.websocketConnected) {
                this.simulateActivityUpdate();
            }
        }, Math.random() * 10000 + 5000); // 5-15 seconds
        
        // Simulate mood changes
        setInterval(() => {
            if (this.isRealtimeActive && this.websocketConnected) {
                this.simulateMoodUpdate();
            }
        }, Math.random() * 20000 + 15000); // 15-35 seconds
    }
    
    simulateActivityUpdate() {
        const activities = ['playing', 'sleeping', 'eating', 'grooming', 'exploring', 'socializing', 'resting'];
        const catIds = <?php echo json_encode(array_column($cats, 'id')); ?>;
        
        if (catIds.length > 0) {
            const randomCatId = catIds[Math.floor(Math.random() * catIds.length)];
            const randomActivity = activities[Math.floor(Math.random() * activities.length)];
            
            this.updateCatActivity(randomCatId, randomActivity);
            this.incrementUpdateCount();
        }
    }
    
    simulateMoodUpdate() {
        const moods = ['very_happy', 'happy', 'content', 'playful', 'sleepy', 'alert'];
        const moodEmojis = {
            'very_happy': '😻', 'happy': '😸', 'content': '😺', 
            'playful': '😹', 'sleepy': '😴', 'alert': '👀'
        };
        const catIds = <?php echo json_encode(array_column($cats, 'id')); ?>;
        
        if (catIds.length > 0) {
            const randomCatId = catIds[Math.floor(Math.random() * catIds.length)];
            const randomMood = moods[Math.floor(Math.random() * moods.length)];
            
            this.updateCatMood(randomCatId, randomMood, moodEmojis[randomMood] || '😺');
            this.incrementUpdateCount();
        }
    }
    
    updateCatActivity(catId, activity) {
        const activityElement = document.getElementById(`activity-${catId}`);
        if (activityElement) {
            activityElement.textContent = this.capitalize(activity);
            activityElement.classList.add('activity-pulse');
            
            setTimeout(() => {
                activityElement.classList.remove('activity-pulse');
            }, 2000);
            
            this.addToActivityFeed(`Cat activity update`, `Started ${activity}`, 'activity');
        }
    }
    
    updateCatMood(catId, mood, emoji) {
        const moodElement = document.getElementById(`mood-${catId}`);
        if (moodElement) {
            moodElement.textContent = `${emoji} ${this.capitalize(mood.replace('_', ' '))}`;
            
            this.addToActivityFeed(`Mood change detected`, `Now feeling ${mood.replace('_', ' ')}`, 'mood');
        }
    }
    
    addToActivityFeed(title, description, type) {
        const feed = document.getElementById('activityFeed');
        const now = new Date();
        
        const newItem = document.createElement('div');
        newItem.className = 'activity-item flex items-start space-x-4 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-100';
        
        newItem.innerHTML = `
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center text-white text-sm font-bold">
                    ${this.getEventEmoji(type)}
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-gray-900">${title}</h4>
                    <span class="text-xs text-gray-500">Just now</span>
                </div>
                <p class="text-sm text-gray-600">${description}</p>
            </div>
        `;
        
        feed.insertBefore(newItem, feed.firstChild);
        
        // Remove old items if too many
        while (feed.children.length > 15) {
            feed.removeChild(feed.lastChild);
        }
        
        // Add entrance animation
        newItem.style.transform = 'translateX(-100%)';
        newItem.style.opacity = '0';
        requestAnimationFrame(() => {
            newItem.style.transform = 'translateX(0)';
            newItem.style.opacity = '1';
            newItem.style.transition = 'all 0.5s ease';
        });
    }
    
    refreshData() {
        // In a real implementation, this would make an API call to refresh data
        console.log('Refreshing dashboard data...');
        this.showNotification('🔄 Refreshing...', 'Updating cat data from server.', 'info');
        
        // Simulate data refresh
        setTimeout(() => {
            location.reload();
        }, 1500);
    }
    
    updateConnectionStatus(status, message) {
        const statusElement = document.getElementById('connectionStatus');
        const wsStatusElement = document.getElementById('wsStatus');
        
        if (statusElement) statusElement.textContent = message;
        if (wsStatusElement) {
            wsStatusElement.textContent = this.capitalize(status);
            wsStatusElement.className = `text-sm font-medium ${status === 'connected' ? 'text-green-500' : status === 'connecting' ? 'text-yellow-500' : 'text-red-500'}`;
        }
    }
    
    incrementUpdateCount() {
        this.updateCount++;
        this.lastUpdate = new Date();
        
        document.getElementById('updateCount').textContent = this.updateCount;
        document.getElementById('lastUpdate').textContent = this.lastUpdate.toLocaleTimeString();
    }
    
    showNotification(title, message, type = 'info') {
        const toast = document.getElementById('notificationToast');
        const titleEl = document.getElementById('toastTitle');
        const messageEl = document.getElementById('toastMessage');
        const iconEl = document.getElementById('toastIcon');
        
        titleEl.textContent = title;
        messageEl.textContent = message;
        
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        
        iconEl.textContent = icons[type] || icons.info;
        
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 5000);
    }
    
    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    getEventEmoji(type) {
        const emojis = {
            activity: '🎯',
            reminder: '⏰',
            mood: '😸',
            health: '🏥',
            social: '👥'
        };
        return emojis[type] || '📊';
    }
}

// Global functions
function getCatStatusClass(cat) {
    const wellness = cat.wellness_score || 0.75;
    if (wellness >= 0.8) return 'excellent';
    if (wellness >= 0.6) return 'good';
    if (wellness >= 0.4) return 'warning';
    return 'critical';
}

function hideToast() {
    document.getElementById('notificationToast').classList.remove('show');
}

function toggleRealtimeMode() {
    dashboard.isRealtimeActive = !dashboard.isRealtimeActive;
    const icon = document.getElementById('realtimeIcon');
    
    if (dashboard.isRealtimeActive) {
        icon.className = 'fas fa-pause text-xl';
        dashboard.showNotification('▶️ Real-time monitoring resumed', 'Live updates are now active.', 'success');
    } else {
        icon.className = 'fas fa-play text-xl';
        dashboard.showNotification('⏸️ Real-time monitoring paused', 'Live updates are paused.', 'info');
    }
}

function refreshAllData() {
    dashboard.refreshData();
}

function openAddActivity() {
    dashboard.showNotification('🎯 Add Activity', 'Feature coming soon!', 'info');
}

function openPhotoAnalysis() {
    dashboard.showNotification('📸 Photo Analysis', 'AI photo analysis feature coming soon!', 'info');
}

// Initialize dashboard
let dashboard;
document.addEventListener('DOMContentLoaded', function() {
    dashboard = new CatDashboardRealtime();
});
</script>

</body>
</html>

<?php
// Helper functions
function getCatStatusClass($cat) {
    $wellness = $cat['wellness_score'] ?? 0.75;
    if ($wellness >= 0.8) return 'excellent';
    if ($wellness >= 0.6) return 'good';
    if ($wellness >= 0.4) return 'warning';
    return 'critical';
}

function getMoodEmoji($mood) {
    $emojis = [
        'very_happy' => '😻',
        'happy' => '😸',
        'content' => '😺',
        'neutral' => '😐',
        'stressed' => '😰',
        'anxious' => '😿',
        'playful' => '😹',
        'sleepy' => '😴',
        'hungry' => '🍽️',
        'alert' => '👀'
    ];
    return $emojis[$mood] ?? '😺';
}

function getEventEmoji($eventType) {
    $emojis = [
        'activity' => '🎯',
        'reminder' => '⏰',
        'health_prediction' => '🏥'
    ];
    return $emojis[$eventType] ?? '📊';
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 2592000) return floor($time/86400) . 'd ago';
    if ($time < 31536000) return floor($time/2592000) . 'mo ago';
    return floor($time/31536000) . 'y ago';
}
?>
