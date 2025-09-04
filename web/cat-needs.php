<?php
/**
 * 🐱 Purrr.love - Cat Needs & Personality Management
 * Comprehensive cat care needs tracking and personality-based recommendations
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

// Handle form submissions
$message = '';
$messageType = '';

if ($_POST) {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'assess_personality':
                    $catId = (int)$_POST['cat_id'];
                    $personalityType = determineCatPersonalityType($catId);
                    $message = "Personality assessment completed! Your cat is: " . ENHANCED_PERSONALITY_TYPES[$personalityType]['name'];
                    $messageType = 'success';
                    break;
                    
                case 'track_needs':
                    $catId = (int)$_POST['cat_id'];
                    $needCategory = $_POST['need_category'];
                    $needType = $_POST['need_type'];
                    $fulfillmentLevel = (float)$_POST['fulfillment_level'];
                    
                    if (trackCatNeedsFulfillment($catId, $needCategory, $needType, $fulfillmentLevel)) {
                        $message = "Needs fulfillment recorded successfully!";
                        $messageType = 'success';
                    } else {
                        $message = "Failed to record needs fulfillment.";
                        $messageType = 'error';
                    }
                    break;
                    
                case 'record_care_activity':
                    $catId = (int)$_POST['cat_id'];
                    $activityType = $_POST['activity_type'];
                    $activityName = $_POST['activity_name'];
                    $duration = (int)$_POST['duration'];
                    $satisfaction = (float)$_POST['satisfaction_rating'];
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO cat_care_activities 
                        (cat_id, activity_type, activity_name, duration_minutes, satisfaction_rating, completed_at, completed_by)
                        VALUES (?, ?, ?, ?, ?, NOW(), 'user')
                    ");
                    
                    if ($stmt->execute([$catId, $activityType, $activityName, $duration, $satisfaction])) {
                        $message = "Care activity recorded successfully!";
                        $messageType = 'success';
                    } else {
                        $message = "Failed to record care activity.";
                        $messageType = 'error';
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get user's cats
$cats = [];
$selectedCatId = $_GET['cat_id'] ?? null;
$personalityData = null;
$needsAssessment = null;
$careRecommendations = null;
$needsSatisfaction = null;

try {
    $stmt = $pdo->prepare("SELECT id, name, breed, age, gender, personality_type, needs_satisfaction_score FROM cats WHERE owner_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cats = $stmt->fetchAll();
} catch (Exception $e) {
    $cats = [];
}

// Get detailed data for selected cat
if ($selectedCatId && !empty($cats)) {
    try {
        // Check if this cat belongs to the user
        $catExists = false;
        foreach ($cats as $cat) {
            if ($cat['id'] == $selectedCatId) {
                $catExists = true;
                break;
            }
        }
        
        if ($catExists) {
            $personalityData = getCatCareRecommendations($selectedCatId);
            $needsAssessment = getCatNeedsAssessment($selectedCatId);
            $needsSatisfaction = getCatNeedsSatisfactionScore($selectedCatId);
        }
    } catch (Exception $e) {
        error_log("Cat Needs Analysis Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 Cat Needs & Personality - Purrr.love</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes pulse-glow {
            from { box-shadow: 0 0 20px rgba(139, 92, 246, 0.5); }
            to { box-shadow: 0 0 30px rgba(139, 92, 246, 0.8); }
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .cat-avatar {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
            border: 4px solid white;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .needs-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .needs-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: #8b5cf6;
        }
        
        .needs-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .needs-critical { background: #ef4444; }
        .needs-high { background: #f59e0b; }
        .needs-medium { background: #3b82f6; }
        .needs-good { background: #10b981; }
        
        .personality-badge {
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }
        
        .slide-up {
            animation: slide-up 0.8s ease-out;
        }
        
        @keyframes slide-up {
            0% { transform: translateY(30px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        
        .bounce-in {
            animation: bounce-in 0.6s ease-out;
        }
        
        @keyframes bounce-in {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background: #e2e8f0;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 1s ease;
            position: relative;
        }
        
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .critical-fill { background: linear-gradient(90deg, #ef4444, #dc2626); }
        .high-fill { background: linear-gradient(90deg, #f59e0b, #d97706); }
        .medium-fill { background: linear-gradient(90deg, #3b82f6, #2563eb); }
        .good-fill { background: linear-gradient(90deg, #10b981, #059669); }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-yellow-200 rounded-full mix-blend-multiply filter-blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute top-40 left-40 w-80 h-80 bg-pink-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Navigation -->
    <nav class="relative z-10 bg-white/80 backdrop-blur-md shadow-lg border-b border-purple-200 sticky top-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-3xl font-black gradient-text floating">
                            🐱 Purrr.love
                        </h1>
                    </div>
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-6">
                            <a href="dashboard.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                            <a href="cats.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-cat mr-2"></i>My Cats
                            </a>
                            <a href="ml-personality.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-brain mr-2"></i>AI Personality
                            </a>
                            <a href="behavior-tracker.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-chart-line mr-2"></i>Behavior Tracker
                            </a>
                            <a href="cat-needs.php" class="nav-link active-nav text-purple-600 px-4 py-2 rounded-lg text-sm font-semibold bg-purple-50">
                                <i class="fas fa-heart mr-2"></i>Cat Needs
                            </a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button class="p-2 text-gray-600 hover:text-purple-600 transition-colors duration-200">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
                        </button>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 cat-avatar rounded-full flex items-center justify-center text-white font-bold text-sm">
                            <?php echo strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div class="hidden md:block">
                            <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($user['username'] ?? 'User'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8 slide-up">
            <div class="bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600 rounded-3xl p-8 text-white shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-4xl font-bold mb-2">Cat Needs & Personality 🐱</h2>
                        <p class="text-xl text-purple-100">Comprehensive care needs tracking and personality-based recommendations</p>
                    </div>
                    <div class="hidden lg:block">
                        <div class="text-8xl floating">💝</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Display -->
        <?php if ($message): ?>
            <div class="mb-6 slide-up">
                <div class="bg-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-100 border border-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-400 text-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> mr-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Cat Selection -->
        <div class="mb-8 slide-up" style="animation-delay: 0.1s">
            <div class="bg-white rounded-3xl p-6 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-cat text-purple-500 mr-3"></i>
                    Select Cat for Needs Assessment
                </h3>
                <?php if (!empty($cats)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($cats as $cat): ?>
                            <a href="?cat_id=<?php echo $cat['id']; ?>" 
                               class="needs-card rounded-2xl p-4 text-left hover:bg-purple-50 transition-all duration-300 <?php echo ($selectedCatId == $cat['id']) ? 'ring-2 ring-purple-500 bg-purple-50' : ''; ?>">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 cat-avatar rounded-full flex items-center justify-center text-white font-bold">
                                        <?php echo strtoupper(substr($cat['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($cat['name']); ?></h4>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($cat['breed'] ?? 'Mixed Breed'); ?></p>
                                        <p class="text-xs text-gray-400">Age: <?php echo $cat['age'] ?? 'Unknown'; ?> | <?php echo ucfirst($cat['gender'] ?? 'Unknown'); ?></p>
                                    </div>
                                </div>
                                <?php if ($cat['personality_type']): ?>
                                    <div class="mt-2">
                                        <span class="personality-badge">
                                            <?php echo ENHANCED_PERSONALITY_TYPES[$cat['personality_type']]['name'] ?? 'Unknown Type'; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($selectedCatId == $cat['id']): ?>
                                    <div class="mt-2 flex items-center text-green-600 text-sm">
                                        <i class="fas fa-heart mr-1"></i>
                                        Currently Assessing
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-cat text-4xl mb-4 text-gray-300"></i>
                        <p>No cats available for assessment. Adopt a cat first!</p>
                        <a href="cats.php" class="mt-4 inline-block bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            Adopt a Cat
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($selectedCatId && $personalityData): ?>
        <!-- Personality Assessment -->
        <div class="mb-8 slide-up" style="animation-delay: 0.2s">
            <div class="bg-white rounded-3xl p-8 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-star text-yellow-500 mr-3"></i>
                    Personality Assessment
                </h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <div class="text-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-6 pulse-glow">
                                <i class="fas fa-cat text-white text-4xl"></i>
                            </div>
                            <h4 class="text-3xl font-bold text-gray-900 mb-2"><?php echo $personalityData['personality_name']; ?></h4>
                            <p class="text-gray-600 mb-6"><?php echo $personalityData['description']; ?></p>
                        </div>
                    </div>
                    <div>
                        <h5 class="text-lg font-semibold text-gray-900 mb-4">Key Characteristics</h5>
                        <div class="space-y-3">
                            <?php if (isset($personalityData['behavioral_tips'])): ?>
                                <?php foreach (array_slice($personalityData['behavioral_tips'], 0, 4) as $tip): ?>
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-check-circle text-green-500"></i>
                                        <span class="text-gray-700 capitalize"><?php echo htmlspecialchars($tip); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Needs Assessment -->
        <div class="mb-8 slide-up" style="animation-delay: 0.3s">
            <div class="bg-white rounded-3xl p-8 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-heart text-red-500 mr-3"></i>
                    Needs Assessment
                </h3>
                <?php if ($needsAssessment): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($needsAssessment as $category => $needs): ?>
                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-6 border border-purple-100">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 capitalize">
                                    <?php echo str_replace('_', ' ', $category); ?>
                                </h4>
                                <div class="space-y-4">
                                    <?php foreach ($needs as $needKey => $need): ?>
                                        <div>
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="font-medium text-gray-700"><?php echo $need['need_name']; ?></span>
                                                <span class="needs-indicator needs-<?php echo $need['status']; ?>"></span>
                                            </div>
                                            <div class="progress-bar">
                                                <div class="progress-fill <?php echo $need['status']; ?>-fill" style="width: <?php echo $need['fulfillment_level'] * 100; ?>%"></div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <?php echo round($need['fulfillment_level'] * 100); ?>% fulfilled
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Care Recommendations -->
        <div class="mb-8 slide-up" style="animation-delay: 0.4s">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-3xl p-8 text-white shadow-2xl">
                <h3 class="text-2xl font-bold mb-6 flex items-center">
                    <i class="fas fa-lightbulb text-yellow-300 mr-3"></i>
                    Personalized Care Recommendations
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php if (isset($personalityData['immediate_priorities']) && !empty($personalityData['immediate_priorities'])): ?>
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 border border-white/30">
                            <h4 class="text-lg font-semibold mb-4">Immediate Priorities</h4>
                            <ul class="space-y-2">
                                <?php foreach (array_slice($personalityData['immediate_priorities'], 0, 3) as $priority): ?>
                                    <li class="flex items-start space-x-2">
                                        <i class="fas fa-exclamation-triangle text-yellow-300 mt-1"></i>
                                        <span class="text-white/90"><?php echo htmlspecialchars($priority); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($personalityData['daily_care']) && !empty($personalityData['daily_care'])): ?>
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 border border-white/30">
                            <h4 class="text-lg font-semibold mb-4">Daily Care</h4>
                            <ul class="space-y-2">
                                <?php foreach (array_slice($personalityData['daily_care'], 0, 3) as $care): ?>
                                    <li class="flex items-start space-x-2">
                                        <i class="fas fa-check-circle text-green-300 mt-1"></i>
                                        <span class="text-white/90"><?php echo htmlspecialchars($care); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Needs Tracking Form -->
        <div class="mb-8 slide-up" style="animation-delay: 0.5s">
            <div class="bg-white rounded-3xl p-8 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-plus-circle text-green-500 mr-3"></i>
                    Track Needs Fulfillment
                </h3>
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="track_needs">
                    <input type="hidden" name="cat_id" value="<?php echo $selectedCatId; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Need Category</label>
                            <select name="need_category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Select category...</option>
                                <option value="physical_needs">Physical Needs</option>
                                <option value="mental_needs">Mental Needs</option>
                                <option value="social_needs">Social Needs</option>
                                <option value="emotional_needs">Emotional Needs</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Need Type</label>
                            <select name="need_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Select need type...</option>
                                <option value="nutrition">Nutrition</option>
                                <option value="exercise">Exercise</option>
                                <option value="grooming">Grooming</option>
                                <option value="stimulation">Mental Stimulation</option>
                                <option value="routine">Routine</option>
                                <option value="interaction">Social Interaction</option>
                                <option value="territory">Territory</option>
                                <option value="security">Security</option>
                                <option value="fulfillment">Fulfillment</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fulfillment Level</label>
                            <select name="fulfillment_level" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="0.2">Poor (20%)</option>
                                <option value="0.4">Fair (40%)</option>
                                <option value="0.6">Good (60%)</option>
                                <option value="0.8">Very Good (80%)</option>
                                <option value="1.0">Excellent (100%)</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-purple-700 hover:to-pink-700 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-save mr-2"></i>
                        Record Needs Fulfillment
                    </button>
                </form>
            </div>
        </div>

        <!-- Care Activity Form -->
        <div class="mb-8 slide-up" style="animation-delay: 0.6s">
            <div class="bg-white rounded-3xl p-8 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-heart text-pink-500 mr-3"></i>
                    Record Care Activity
                </h3>
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="record_care_activity">
                    <input type="hidden" name="cat_id" value="<?php echo $selectedCatId; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Activity Type</label>
                            <select name="activity_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Select activity...</option>
                                <option value="feeding">Feeding</option>
                                <option value="play">Play</option>
                                <option value="grooming">Grooming</option>
                                <option value="exercise">Exercise</option>
                                <option value="social_interaction">Social Interaction</option>
                                <option value="mental_stimulation">Mental Stimulation</option>
                                <option value="health_check">Health Check</option>
                                <option value="training">Training</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Activity Name</label>
                            <input type="text" name="activity_name" placeholder="e.g., Interactive play with feather toy" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                            <input type="number" name="duration" min="1" max="120" value="15" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Satisfaction Rating</label>
                            <select name="satisfaction_rating" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="0.2">Poor (20%)</option>
                                <option value="0.4">Fair (40%)</option>
                                <option value="0.6">Good (60%)</option>
                                <option value="0.8">Very Good (80%)</option>
                                <option value="1.0">Excellent (100%)</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-pink-600 to-purple-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-pink-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-heart mr-2"></i>
                        Record Care Activity
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Floating Action Button -->
    <div class="fixed bottom-8 right-8 z-50">
        <button class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-110 transition-all duration-300 pulse-glow floating">
            <i class="fas fa-heart text-white text-2xl"></i>
        </button>
    </div>

    <script>
        // Interactive JavaScript for enhanced UX
        document.addEventListener('DOMContentLoaded', function() {
            // Animate elements on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in');
                    }
                });
            }, observerOptions);

            // Observe all cards
            document.querySelectorAll('.card-hover, .slide-up').forEach(card => {
                observer.observe(card);
            });

            // Add floating animation to background elements
            const blobs = document.querySelectorAll('.animate-blob');
            blobs.forEach((blob, index) => {
                blob.style.animationDelay = `${index * 2}s`;
            });

            // Animate progress bars
            document.querySelectorAll('.progress-fill').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 1000);
            });

            // Form validation
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.classList.add('border-red-500');
                        } else {
                            field.classList.remove('border-red-500');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                        return false;
                    }
                });
            });
        });
    </script>
</body>
</html>
