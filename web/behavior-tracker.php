<?php
/**
 * 🐾 Purrr.love - Behavioral Tracking Interface
 * Real-time behavior monitoring and personality insights
 */

// Define secure access for includes
define('SECURE_ACCESS', true);

session_start();
require_once '../includes/functions.php';
require_once '../includes/authentication.php';
require_once '../includes/behavioral_tracking_system.php';

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

// Handle behavior recording
$message = '';
$messageType = '';

if ($_POST && isset($_POST['action']) && $_POST['action'] === 'record_behavior') {
    try {
        $catId = (int)$_POST['cat_id'];
        $behaviorType = $_POST['behavior_type'];
        $intensity = $_POST['intensity'];
        $duration = (int)$_POST['duration'];
        $context = [
            'environmental' => [
                'location' => $_POST['location'] ?? '',
                'time_of_day' => date('H:i'),
                'weather' => $_POST['weather'] ?? ''
            ],
            'social' => [
                'other_cats_present' => $_POST['other_cats'] ?? false,
                'humans_present' => $_POST['humans_present'] ?? true,
                'activity_level' => $_POST['activity_level'] ?? 'normal'
            ]
        ];
        
        if (recordCatBehavior($catId, $behaviorType, $intensity, $duration, $context)) {
            $message = "Behavior recorded successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to record behavior. Please try again.";
            $messageType = "error";
        }
    } catch (Exception $e) {
        $message = "Error recording behavior: " . $e->getMessage();
        $messageType = "error";
    }
}

// Get user's cats
$cats = [];
$selectedCatId = $_GET['cat_id'] ?? null;
$behavioralInsights = null;
$behavioralPatterns = null;

try {
    $stmt = $pdo->prepare("SELECT id, name, breed, age, gender FROM cats WHERE owner_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cats = $stmt->fetchAll();
} catch (Exception $e) {
    $cats = [];
}

// Get behavioral insights if cat is selected
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
            $behavioralInsights = getBehavioralInsights($selectedCatId);
            $behavioralPatterns = analyzeBehavioralPatterns($selectedCatId, 30);
        }
    } catch (Exception $e) {
        error_log("Behavioral Analysis Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐾 Behavioral Tracker - Purrr.love</title>
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
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .cat-avatar {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
            border: 4px solid white;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .behavior-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .behavior-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: #8b5cf6;
        }
        
        .behavior-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .behavior-play { background: #10b981; }
        .behavior-rest { background: #6b7280; }
        .behavior-explore { background: #3b82f6; }
        .behavior-socialize { background: #f59e0b; }
        .behavior-groom { background: #8b5cf6; }
        .behavior-hunt { background: #ef4444; }
        .behavior-eat { background: #f97316; }
        .behavior-sleep { background: #1f2937; }
        .behavior-vocalize { background: #ec4899; }
        .behavior-aggressive { background: #dc2626; }
        .behavior-submissive { background: #6b7280; }
        .behavior-anxious { background: #fbbf24; }
        
        .intensity-low { background: #10b981; }
        .intensity-medium { background: #f59e0b; }
        .intensity-high { background: #ef4444; }
        
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
                            <a href="behavior-tracker.php" class="nav-link active-nav text-purple-600 px-4 py-2 rounded-lg text-sm font-semibold bg-purple-50">
                                <i class="fas fa-chart-line mr-2"></i>Behavior Tracker
                            </a>
                            <a href="games.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-gamepad mr-2"></i>Games
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
                        <h2 class="text-4xl font-bold mb-2">Behavioral Tracking System 🐾</h2>
                        <p class="text-xl text-purple-100">Monitor and analyze your cat's behavior patterns in real-time</p>
                    </div>
                    <div class="hidden lg:block">
                        <div class="text-8xl floating">📊</div>
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
                    Select Cat for Behavior Tracking
                </h3>
                <?php if (!empty($cats)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($cats as $cat): ?>
                            <a href="?cat_id=<?php echo $cat['id']; ?>" 
                               class="behavior-card rounded-2xl p-4 text-left hover:bg-purple-50 transition-all duration-300 <?php echo ($selectedCatId == $cat['id']) ? 'ring-2 ring-purple-500 bg-purple-50' : ''; ?>">
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
                                <?php if ($selectedCatId == $cat['id']): ?>
                                    <div class="mt-2 flex items-center text-green-600 text-sm">
                                        <i class="fas fa-chart-line mr-1"></i>
                                        Currently Tracking
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-cat text-4xl mb-4 text-gray-300"></i>
                        <p>No cats available for tracking. Adopt a cat first!</p>
                        <a href="cats.php" class="mt-4 inline-block bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            Adopt a Cat
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($selectedCatId && $behavioralInsights): ?>
        <!-- Behavior Recording Form -->
        <div class="mb-8 slide-up" style="animation-delay: 0.2s">
            <div class="bg-white rounded-3xl p-8 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-plus-circle text-green-500 mr-3"></i>
                    Record New Behavior
                </h3>
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="record_behavior">
                    <input type="hidden" name="cat_id" value="<?php echo $selectedCatId; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Behavior Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Behavior Type</label>
                            <select name="behavior_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Select behavior...</option>
                                <option value="play">Play</option>
                                <option value="rest">Rest</option>
                                <option value="explore">Explore</option>
                                <option value="socialize">Socialize</option>
                                <option value="groom">Groom</option>
                                <option value="hunt">Hunt</option>
                                <option value="eat">Eat</option>
                                <option value="sleep">Sleep</option>
                                <option value="vocalize">Vocalize</option>
                                <option value="aggressive">Aggressive</option>
                                <option value="submissive">Submissive</option>
                                <option value="anxious">Anxious</option>
                            </select>
                        </div>
                        
                        <!-- Intensity -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Intensity</label>
                            <select name="intensity" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        
                        <!-- Duration -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                            <input type="number" name="duration" value="1" min="1" max="120" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        
                        <!-- Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" name="location" placeholder="e.g., living room, kitchen" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                    </div>
                    
                    <!-- Additional Context -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Other cats present?</label>
                            <select name="other_cats" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="false">No</option>
                                <option value="true">Yes</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Activity Level</label>
                            <select name="activity_level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="low">Low</option>
                                <option value="normal" selected>Normal</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-purple-700 hover:to-pink-700 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-save mr-2"></i>
                        Record Behavior
                    </button>
                </form>
            </div>
        </div>

        <!-- Behavioral Insights -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Dominant Behaviors -->
            <div class="slide-up" style="animation-delay: 0.3s">
                <div class="bg-white rounded-3xl p-8 shadow-xl card-hover">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-chart-pie text-blue-500 mr-3"></i>
                        Dominant Behaviors
                    </h3>
                    <?php if (isset($behavioralInsights['dominant_behaviors'])): ?>
                        <div class="space-y-4">
                            <?php foreach ($behavioralInsights['dominant_behaviors'] as $behavior): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <span class="behavior-indicator behavior-<?php echo $behavior['behavior']; ?>"></span>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 capitalize"><?php echo $behavior['behavior']; ?></h4>
                                            <p class="text-sm text-gray-600"><?php echo $behavior['description']; ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-purple-600"><?php echo $behavior['frequency']; ?>%</div>
                                        <div class="text-sm text-gray-500">frequency</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Activity Patterns -->
            <div class="slide-up" style="animation-delay: 0.4s">
                <div class="bg-white rounded-3xl p-8 shadow-xl card-hover">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-clock text-green-500 mr-3"></i>
                        Activity Patterns
                    </h3>
                    <?php if (isset($behavioralInsights['activity_patterns'])): ?>
                        <div class="space-y-4">
                            <div class="text-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                                <h4 class="text-lg font-semibold text-gray-900">Most Active Period</h4>
                                <p class="text-2xl font-bold text-green-600 capitalize"><?php echo $behavioralInsights['activity_patterns']['most_active_period']; ?></p>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">Peak Activity Hours</h4>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ($behavioralInsights['activity_patterns']['peak_hours'] as $hour): ?>
                                        <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
                                            <?php echo $hour; ?>:00
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Behavioral Predictions -->
        <div class="mb-8 slide-up" style="animation-delay: 0.5s">
            <div class="bg-white rounded-3xl p-8 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-crystal-ball text-purple-500 mr-3"></i>
                    AI Behavioral Predictions
                </h3>
                <?php if (isset($behavioralInsights['predictions'])): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($behavioralInsights['predictions'] as $behavior => $probability): ?>
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="behavior-indicator behavior-<?php echo $behavior; ?>"></span>
                                    <span class="text-sm font-medium text-blue-600"><?php echo $probability; ?>%</span>
                                </div>
                                <h4 class="font-semibold text-gray-900 capitalize"><?php echo $behavior; ?></h4>
                                <p class="text-sm text-gray-600">Predicted likelihood</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="mb-8 slide-up" style="animation-delay: 0.6s">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-3xl p-8 text-white shadow-2xl">
                <h3 class="text-2xl font-bold mb-6 flex items-center">
                    <i class="fas fa-lightbulb text-yellow-300 mr-3"></i>
                    Behavioral Recommendations
                </h3>
                <?php if (isset($behavioralInsights['recommendations']) && !empty($behavioralInsights['recommendations'])): ?>
                    <div class="space-y-4">
                        <?php foreach ($behavioralInsights['recommendations'] as $recommendation): ?>
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 border border-white/30">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-check-circle text-green-300 mt-1"></i>
                                    <p class="text-white/90"><?php echo htmlspecialchars($recommendation); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-heart text-4xl mb-4 text-white/60"></i>
                        <p class="text-white/80">Your cat's behavior patterns look healthy! Keep up the great care.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Floating Action Button -->
    <div class="fixed bottom-8 right-8 z-50">
        <button class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-110 transition-all duration-300 pulse-glow floating">
            <i class="fas fa-chart-line text-white text-2xl"></i>
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

            // Form validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const behaviorType = form.querySelector('select[name="behavior_type"]').value;
                    if (!behaviorType) {
                        e.preventDefault();
                        alert('Please select a behavior type.');
                        return false;
                    }
                });
            }

            // Auto-refresh predictions every 5 minutes
            setInterval(function() {
                if (window.location.search.includes('cat_id=')) {
                    // Could implement auto-refresh of predictions here
                }
            }, 300000); // 5 minutes
        });
    </script>
</body>
</html>
