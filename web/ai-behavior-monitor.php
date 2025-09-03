<?php
/**
 * 🧠 Purrr.love - AI Behavior Monitor
 * ✨ Real-time cat behavior analysis and monitoring
 */

session_start();
require_once '../includes/functions.php';
require_once '../includes/authentication.php';
require_once '../includes/ai_behavior_monitor.php';

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

// Initialize AI Behavior Monitor
$aiMonitor = new AIBehaviorMonitor($pdo);

// Get user's cats
$cats = [];
try {
    $stmt = $pdo->prepare("SELECT id, name, breed, health, happiness, energy, hunger FROM cats WHERE owner_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cats = $stmt->fetchAll();
} catch (Exception $e) {
    $cats = [];
}

// Handle behavior data submission
$analysisResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['analyze_behavior'])) {
    $catId = $_POST['cat_id'] ?? null;
    $behaviorData = [
        'activity_level' => (int)($_POST['activity_level'] ?? 50),
        'social_behavior' => (int)($_POST['social_behavior'] ?? 50),
        'eating_behavior' => [
            'frequency' => (int)($_POST['eating_frequency'] ?? 50),
            'enthusiasm' => (int)($_POST['eating_enthusiasm'] ?? 50),
            'amount' => (int)($_POST['eating_amount'] ?? 50)
        ],
        'sleep_patterns' => [
            'duration' => (int)($_POST['sleep_duration'] ?? 50),
            'quality' => (int)($_POST['sleep_quality'] ?? 50),
            'consistency' => (int)($_POST['sleep_consistency'] ?? 50)
        ],
        'grooming_behavior' => [
            'frequency' => (int)($_POST['grooming_frequency'] ?? 50),
            'thoroughness' => (int)($_POST['grooming_thoroughness'] ?? 50),
            'areas_covered' => (int)($_POST['grooming_areas'] ?? 50)
        ]
    ];
    
    if ($catId) {
        $analysisResult = $aiMonitor->analyzeBehavior($catId, $behaviorData);
    }
}

// Get AI model information
$modelInfo = $aiMonitor->getModelInfo();
$behaviorPatterns = $aiMonitor->getBehaviorPatterns();
$healthIndicators = $aiMonitor->getHealthIndicators();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧠 AI Behavior Monitor - Purrr.love</title>
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
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
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
        
        .stats-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .active-nav::after {
            width: 100%;
        }
        
        .floating-ai {
            animation: floating-ai 4s ease-in-out infinite;
        }
        
        @keyframes floating-ai {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            25% { transform: translateY(-15px) rotate(2deg); }
            50% { transform: translateY(-20px) rotate(0deg); }
            75% { transform: translateY(-15px) rotate(-2deg); }
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
        
        .slide-up {
            animation: slide-up 0.8s ease-out;
        }
        
        @keyframes slide-up {
            0% { transform: translateY(30px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        
        .behavior-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .behavior-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: #8b5cf6;
        }
        
        .metric-slider {
            -webkit-appearance: none;
            width: 100%;
            height: 8px;
            border-radius: 4px;
            background: #e2e8f0;
            outline: none;
            transition: all 0.3s ease;
        }
        
        .metric-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .metric-slider::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .ai-glow {
            box-shadow: 0 0 30px rgba(139, 92, 246, 0.3);
            animation: ai-glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes ai-glow {
            from { box-shadow: 0 0 30px rgba(139, 92, 246, 0.3); }
            to { box-shadow: 0 0 50px rgba(139, 92, 246, 0.6); }
        }
        
        .floating-action {
            animation: floating-action 2s ease-in-out infinite;
        }
        
        @keyframes floating-action {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .insight-card {
            transition: all 0.3s ease;
        }
        
        .insight-card.positive {
            border-left: 4px solid #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        }
        
        .insight-card.warning {
            border-left: 4px solid #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        }
        
        .insight-card.alert {
            border-left: 4px solid #ef4444;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        }
        
        .insight-card.caution {
            border-left: 4px solid #8b5cf6;
            background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
        }
        
        .pattern-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .pattern-positive { background: #10b981; }
        .pattern-negative { background: #ef4444; }
        .pattern-neutral { background: #6b7280; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-yellow-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
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
                            <a href="games.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-gamepad mr-2"></i>Games
                            </a>
                            <a href="store.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-store mr-2"></i>Store
                            </a>
                            <a href="lost_pet_finder.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-search mr-2"></i>Lost Pet Finder
                            </a>
                            <a href="ml-personality.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-brain mr-2"></i>AI Personality
                            </a>
                            <a href="ai-behavior-monitor.php" class="nav-link active-nav text-purple-600 px-4 py-2 rounded-lg text-sm font-semibold bg-purple-50">
                                <i class="fas fa-chart-line mr-2"></i>AI Monitor
                            </a>
                            <a href="metaverse-vr.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-vr-cardboard mr-2"></i>Metaverse
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
                        <h2 class="text-4xl font-bold mb-2">AI Behavior Monitor 🧠</h2>
                        <p class="text-xl text-purple-100">Real-time cat behavior analysis using advanced machine learning</p>
                    </div>
                    <div class="hidden lg:block">
                        <div class="text-8xl floating-ai">🤖</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Model Information -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">AI Model Version</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $modelInfo['version']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-pink-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-robot text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Accuracy</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $modelInfo['accuracy']; ?>%</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-teal-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-bullseye text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Training Samples</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo number_format($modelInfo['training_samples']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-database text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.4s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Model Type</p>
                        <p class="text-lg font-bold text-gray-900">LSTM + CNN</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-brain text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Behavior Analysis Form -->
        <div class="mb-8 slide-up" style="animation-delay: 0.5s">
            <div class="bg-white rounded-3xl p-8 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-chart-line text-purple-500 mr-3"></i>
                    Analyze Cat Behavior
                </h3>
                
                <form method="POST" class="space-y-8">
                    <!-- Cat Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Select Cat</label>
                        <select name="cat_id" required class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Choose a cat to analyze...</option>
                            <?php foreach ($cats as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?> (<?php echo htmlspecialchars($cat['breed'] ?? 'Mixed Breed'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Behavior Metrics -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Activity Level -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Activity Level: <span id="activity_value">50</span>%
                            </label>
                            <input type="range" name="activity_level" id="activity_level" min="0" max="100" value="50" 
                                   class="metric-slider" oninput="updateValue('activity_value', this.value)">
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>Very Low</span>
                                <span>Very High</span>
                            </div>
                        </div>

                        <!-- Social Behavior -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Social Behavior: <span id="social_value">50</span>%
                            </label>
                            <input type="range" name="social_behavior" id="social_behavior" min="0" max="100" value="50" 
                                   class="metric-slider" oninput="updateValue('social_value', this.value)">
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>Very Shy</span>
                                <span>Very Social</span>
                            </div>
                        </div>

                        <!-- Eating Behavior -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Eating Frequency: <span id="eating_frequency_value">50</span>%
                            </label>
                            <input type="range" name="eating_frequency" id="eating_frequency" min="0" max="100" value="50" 
                                   class="metric-slider" oninput="updateValue('eating_frequency_value', this.value)">
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>Rarely</span>
                                <span>Very Often</span>
                            </div>
                        </div>

                        <!-- Sleep Patterns -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Sleep Quality: <span id="sleep_quality_value">50</span>%
                            </label>
                            <input type="range" name="sleep_quality" id="sleep_quality" min="0" max="100" value="50" 
                                   class="metric-slider" oninput="updateValue('sleep_quality_value', this.value)">
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>Poor</span>
                                <span>Excellent</span>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Metrics -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Eating Enthusiasm: <span id="eating_enthusiasm_value">50</span>%
                            </label>
                            <input type="range" name="eating_enthusiasm" id="eating_enthusiasm" min="0" max="100" value="50" 
                                   class="metric-slider" oninput="updateValue('eating_enthusiasm_value', this.value)">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Sleep Duration: <span id="sleep_duration_value">50</span>%
                            </label>
                            <input type="range" name="sleep_duration" id="sleep_duration" min="0" max="100" value="50" 
                                   class="metric-slider" oninput="updateValue('sleep_duration_value', this.value)">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Grooming Frequency: <span id="grooming_frequency_value">50</span>%
                            </label>
                            <input type="range" name="grooming_frequency" id="grooming_frequency" min="0" max="100" value="50" 
                                   class="metric-slider" oninput="updateValue('grooming_frequency_value', this.value)">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" name="analyze_behavior" 
                                class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-8 py-4 rounded-2xl text-lg font-semibold hover:from-purple-600 hover:to-pink-600 transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-brain mr-2"></i>
                            Analyze Behavior with AI
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Analysis Results -->
        <?php if ($analysisResult && $analysisResult['success']): ?>
            <div class="space-y-8 slide-up" style="animation-delay: 0.6s">
                <!-- Overall Wellness Score -->
                <div class="bg-white rounded-3xl p-8 shadow-xl">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-heart text-red-500 mr-3"></i>
                        Overall Wellness Analysis
                    </h3>
                    <div class="text-center">
                        <div class="relative w-48 h-48 mx-auto mb-6">
                            <svg class="w-48 h-48" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#8b5cf6" stroke-width="8" 
                                        stroke-dasharray="<?php echo $analysisResult['analysis']['overall_wellness'] * 339.292; ?> 339.292"
                                        stroke-dashoffset="84.823" transform="rotate(-90 60 60)"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-purple-600">
                                        <?php echo round($analysisResult['analysis']['overall_wellness'] * 100); ?>%
                                    </div>
                                    <div class="text-sm text-gray-500">Wellness Score</div>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600">AI Confidence: <?php echo round($analysisResult['confidence_score'] * 100); ?>%</p>
                    </div>
                </div>

                <!-- Behavior Patterns -->
                <div class="bg-white rounded-3xl p-8 shadow-xl">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-puzzle-piece text-blue-500 mr-3"></i>
                        Identified Behavior Patterns
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($analysisResult['patterns'] as $pattern => $data): ?>
                            <div class="behavior-card rounded-2xl p-6">
                                <div class="flex items-center mb-4">
                                    <div class="pattern-indicator pattern-<?php echo $data['health_impact']; ?>"></div>
                                    <h4 class="text-lg font-semibold text-gray-900"><?php echo ucfirst($pattern); ?></h4>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">
                                    Confidence: <?php echo round($data['confidence'] * 100); ?>%
                                </p>
                                <p class="text-xs text-gray-500 capitalize">
                                    Health Impact: <?php echo $data['health_impact']; ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Health Insights -->
                <div class="bg-white rounded-3xl p-8 shadow-xl">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-lightbulb text-yellow-500 mr-3"></i>
                        AI Health Insights
                    </h3>
                    <div class="space-y-4">
                        <?php foreach ($analysisResult['health_insights'] as $insight): ?>
                            <div class="insight-card <?php echo $insight['type']; ?> rounded-2xl p-6">
                                <div class="flex items-start space-x-4">
                                    <div class="w-8 h-8 bg-white/50 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-<?php echo $insight['type'] === 'positive' ? 'check' : ($insight['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle'); ?> text-<?php echo $insight['type'] === 'positive' ? 'green' : ($insight['type'] === 'warning' ? 'yellow' : 'red'); ?>-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2"><?php echo $insight['title']; ?></h4>
                                        <p class="text-gray-600 text-sm"><?php echo $insight['description']; ?></p>
                                        <span class="inline-block mt-2 px-3 py-1 text-xs font-medium bg-<?php echo $insight['priority'] === 'high' ? 'red' : ($insight['priority'] === 'medium' ? 'yellow' : 'green'); ?>-100 text-<?php echo $insight['priority'] === 'high' ? 'red' : ($insight['priority'] === 'medium' ? 'yellow' : 'green'); ?>-800 rounded-full">
                                            <?php echo ucfirst($insight['priority']); ?> Priority
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- AI Recommendations -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-3xl p-8 text-white shadow-2xl">
                    <h3 class="text-2xl font-bold mb-6 flex items-center">
                        <i class="fas fa-robot text-yellow-300 mr-3"></i>
                        AI-Powered Recommendations
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($analysisResult['recommendations'] as $index => $recommendation): ?>
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 border border-white/30">
                                <div class="flex items-start space-x-3">
                                    <div class="w-8 h-8 bg-white/30 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-sm"><?php echo $index + 1; ?></span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold mb-2"><?php echo $recommendation['title']; ?></h4>
                                        <p class="text-white/90 text-sm"><?php echo $recommendation['description']; ?></p>
                                        <span class="inline-block mt-2 px-3 py-1 text-xs font-medium bg-white/20 rounded-full">
                                            <?php echo ucfirst($recommendation['category']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Behavior Pattern Information -->
        <div class="mt-16 slide-up" style="animation-delay: 0.7s">
            <div class="bg-white rounded-3xl p-8 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                    <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                    Understanding Behavior Patterns
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($behaviorPatterns as $pattern => $data): ?>
                        <div class="behavior-card rounded-2xl p-6">
                            <div class="flex items-center mb-4">
                                <div class="pattern-indicator pattern-<?php echo $data['health_impact']; ?>"></div>
                                <h4 class="text-lg font-semibold text-gray-900"><?php echo ucfirst($pattern); ?></h4>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">
                                <?php echo ucfirst($data['health_impact']); ?> health impact
                            </p>
                            <div class="space-y-2">
                                <p class="text-xs font-medium text-gray-700">Key Indicators:</p>
                                <ul class="text-xs text-gray-600 space-y-1">
                                    <?php foreach ($data['indicators'] as $indicator): ?>
                                        <li>• <?php echo ucwords(str_replace('_', ' ', $indicator)); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Floating Action Button -->
    <div class="fixed bottom-8 right-8 z-50">
        <button class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-110 transition-all duration-300 pulse-glow floating-action">
            <i class="fas fa-brain text-white text-2xl"></i>
        </button>
    </div>

    <script>
        // Update slider value displays
        function updateValue(elementId, value) {
            document.getElementById(elementId).textContent = value;
        }
        
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

            // Interactive slider effects
            document.querySelectorAll('.metric-slider').forEach(slider => {
                slider.addEventListener('input', function() {
                    this.style.background = `linear-gradient(90deg, #8b5cf6 0%, #8b5cf6 ${this.value}%, #e2e8f0 ${this.value}%, #e2e8f0 100%)`;
                });
                
                // Initialize slider backgrounds
                slider.dispatchEvent(new Event('input'));
            });

            // Add hover effects to behavior cards
            document.querySelectorAll('.behavior-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Add hover effects to insight cards
            document.querySelectorAll('.insight-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>
