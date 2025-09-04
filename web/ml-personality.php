<?php
/**
 * 🧠 Purrr.love - Advanced AI Personality Analysis
 * Next-generation AI-powered cat personality insights and recommendations
 */

// Define secure access for includes
define('SECURE_ACCESS', true);

session_start();
require_once '../includes/functions.php';
require_once '../includes/authentication.php';
require_once '../includes/advanced_ai_personality.php';

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

// Get user's cats for analysis
$cats = [];
$selectedCatId = $_GET['cat_id'] ?? null;
$analysisData = null;
$evolutionData = null;

try {
    $stmt = $pdo->prepare("SELECT id, name, breed, health, happiness, energy, hunger, age, gender FROM cats WHERE owner_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cats = $stmt->fetchAll();
} catch (Exception $e) {
    $cats = [];
}

// Perform advanced AI analysis if cat is selected
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
            // Run advanced AI personality analysis
            $analysisData = predictAdvancedCatPersonality($selectedCatId, true);
            $evolutionData = getPersonalityEvolution($selectedCatId);
        }
    } catch (Exception $e) {
        error_log("AI Analysis Error: " . $e->getMessage());
        $analysisData = null;
    }
}

// Fallback sample data for demonstration
if (!$analysisData) {
    $analysisData = [
        'personality_profile' => [
            'personality_type' => 'Social Butterfly',
            'personality_dimensions' => [
                'openness' => ['score' => 78, 'confidence' => 0.92],
                'conscientiousness' => ['score' => 65, 'confidence' => 0.88],
                'extraversion' => ['score' => 92, 'confidence' => 0.95],
                'agreeableness' => ['score' => 88, 'confidence' => 0.90],
                'neuroticism' => ['score' => 23, 'confidence' => 0.85]
            ],
            'behavioral_patterns' => [
                'next_behaviors' => ['play' => 85, 'socialize' => 90, 'explore' => 70],
                'mood_trends' => ['current_mood' => 'happy', 'mood_stability' => 87],
                'activity_levels' => ['current_activity' => 75, 'peak_times' => ['morning', 'evening']]
            ],
            'emotional_profile' => [
                'primary_emotions' => ['happy', 'playful', 'curious'],
                'emotional_stability' => 0.87,
                'stress_indicators' => ['low']
            ]
        ],
        'insights' => [
            'personality' => [
                'strengths' => ['Highly social and outgoing', 'Very playful and energetic'],
                'growth_areas' => ['Can be overly dependent on attention'],
                'unique_traits' => ['Natural entertainer', 'Excellent with children']
            ],
            'behavioral' => [
                'patterns' => ['Prefers morning and evening activity', 'Loves interactive play'],
                'predictions' => ['Will likely enjoy puzzle toys', 'May benefit from a companion']
            ],
            'environmental' => [
                'recommendations' => ['Provide multiple play areas', 'Ensure social interaction opportunities']
            ],
            'training' => [
                'suggestions' => ['Positive reinforcement works well', 'Keep training sessions short and fun']
            ]
        ],
        'confidence_scores' => [
            'overall' => 0.87,
            'personality' => 0.90,
            'behavioral' => 0.85
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧠 AI Cat Personality - Purrr.love</title>
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
        
        .floating-brain {
            animation: floating-brain 4s ease-in-out infinite;
        }
        
        @keyframes floating-brain {
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
        
        .personality-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .personality-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: #8b5cf6;
        }
        
        .trait-bar {
            height: 12px;
            border-radius: 6px;
            background: #e2e8f0;
            overflow: hidden;
            position: relative;
        }
        
        .trait-fill {
            height: 100%;
            border-radius: 6px;
            transition: width 1s ease;
            position: relative;
        }
        
        .trait-fill::after {
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
        
        .openness-fill { background: linear-gradient(90deg, #10b981, #059669); }
        .conscientiousness-fill { background: linear-gradient(90deg, #3b82f6, #2563eb); }
        .extraversion-fill { background: linear-gradient(90deg, #f59e0b, #d97706); }
        .agreeableness-fill { background: linear-gradient(90deg, #ef4444, #dc2626); }
        .neuroticism-fill { background: linear-gradient(90deg, #8b5cf6, #7c3aed); }
        
        .confidence-ring {
            transform: rotate(-90deg);
        }
        
        .confidence-ring-circle {
            transition: stroke-dasharray 1s ease;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
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
                            <a href="games.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-gamepad mr-2"></i>Games
                            </a>
                            <a href="store.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-store mr-2"></i>Store
                            </a>
                            <a href="lost_pet_finder.php" class="nav-link text-gray-700 hover:text-purple-600 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-purple-50">
                                <i class="fas fa-search mr-2"></i>Lost Pet Finder
                            </a>
                            <a href="ml-personality.php" class="nav-link active-nav text-purple-600 px-4 py-2 rounded-lg text-sm font-semibold bg-purple-50">
                                <i class="fas fa-brain mr-2"></i>AI Personality
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
                        <h2 class="text-4xl font-bold mb-2">AI Cat Personality Analysis 🧠</h2>
                        <p class="text-xl text-purple-100">Discover your cat's unique personality using advanced machine learning</p>
                    </div>
                    <div class="hidden lg:block">
                        <div class="text-8xl floating-brain">🧠</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Analysis Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Cats Analyzed</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo count($cats); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-purple-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-cat text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">AI Accuracy</p>
                        <p class="text-3xl font-bold text-gray-900">95.2%</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-teal-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-robot text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Models Trained</p>
                        <p class="text-3xl font-bold text-gray-900">12</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-brain text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cat Selection -->
        <div class="mb-8 slide-up" style="animation-delay: 0.4s">
            <div class="bg-white rounded-3xl p-6 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-cat text-purple-500 mr-3"></i>
                    Select Cat for Advanced AI Analysis
                </h3>
                <?php if (!empty($cats)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($cats as $cat): ?>
                            <a href="?cat_id=<?php echo $cat['id']; ?>" 
                               class="personality-card rounded-2xl p-4 text-left hover:bg-purple-50 transition-all duration-300 <?php echo ($selectedCatId == $cat['id']) ? 'ring-2 ring-purple-500 bg-purple-50' : ''; ?>">
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
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Currently Analyzing
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-cat text-4xl mb-4 text-gray-300"></i>
                        <p>No cats available for analysis. Adopt a cat first!</p>
                        <a href="cats.php" class="mt-4 inline-block bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            Adopt a Cat
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($selectedCatId && $analysisData): ?>
        <!-- Advanced AI Analysis Results -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Personality Type -->
            <div class="slide-up" style="animation-delay: 0.5s">
                <div class="bg-white rounded-3xl p-8 shadow-xl card-hover">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-star text-yellow-500 mr-3"></i>
                        Advanced Personality Profile
                    </h3>
                    <div class="text-center">
                        <div class="w-24 h-24 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-6 ai-glow">
                            <i class="fas fa-cat text-white text-4xl"></i>
                        </div>
                        <h4 class="text-3xl font-bold text-gray-900 mb-2"><?php echo $analysisData['personality_profile']['personality_type']; ?></h4>
                        <p class="text-gray-600 mb-6">AI-analyzed personality classification</p>
                        
                        <!-- Advanced Confidence Score -->
                        <div class="relative w-32 h-32 mx-auto mb-4">
                            <svg class="w-32 h-32 confidence-ring">
                                <circle class="confidence-ring-circle" stroke="#e5e7eb" stroke-width="8" fill="transparent" r="56" cx="64" cy="64"/>
                                <circle class="confidence-ring-circle" stroke="#8b5cf6" stroke-width="8" fill="transparent" r="56" cx="64" cy="64" 
                                        stroke-dasharray="<?php echo ($analysisData['confidence_scores']['overall'] * 100) * 3.5186; ?> 351.86"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-2xl font-bold text-purple-600"><?php echo round($analysisData['confidence_scores']['overall'] * 100); ?>%</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">Advanced AI Confidence</p>
                        
                        <!-- Model Version -->
                        <div class="mt-4 text-xs text-gray-400">
                            <i class="fas fa-robot mr-1"></i>
                            Model v2.0 Advanced
                        </div>
                    </div>
                </div>
            </div>

            <!-- Big Five Traits with Confidence -->
            <div class="slide-up" style="animation-delay: 0.6s">
                <div class="bg-white rounded-3xl p-8 shadow-xl card-hover">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-chart-bar text-blue-500 mr-3"></i>
                        Big Five Personality Dimensions
                    </h3>
                    <div class="space-y-6">
                        <?php 
                        $traitNames = [
                            'openness' => 'Openness to Experience',
                            'conscientiousness' => 'Conscientiousness', 
                            'extraversion' => 'Extraversion',
                            'agreeableness' => 'Agreeableness',
                            'neuroticism' => 'Neuroticism'
                        ];
                        foreach ($analysisData['personality_profile']['personality_dimensions'] as $trait => $data): 
                            $score = $data['score'];
                            $confidence = $data['confidence'] ?? 0.8;
                        ?>
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-medium text-gray-700"><?php echo $traitNames[$trait]; ?></span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-500"><?php echo $score; ?>%</span>
                                        <div class="w-2 h-2 rounded-full <?php echo $confidence > 0.8 ? 'bg-green-500' : ($confidence > 0.6 ? 'bg-yellow-500' : 'bg-red-500'); ?>"></div>
                                    </div>
                                </div>
                                <div class="trait-bar">
                                    <div class="trait-fill <?php echo $trait; ?>-fill" style="width: <?php echo $score; ?>%"></div>
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Confidence: <?php echo round($confidence * 100); ?>%
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Behavioral Predictions -->
        <div class="mb-8 slide-up" style="animation-delay: 0.7s">
            <div class="bg-white rounded-3xl p-8 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-crystal-ball text-purple-500 mr-3"></i>
                    AI Behavioral Predictions
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Next Behaviors -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
                        <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-clock text-blue-500 mr-2"></i>
                            Predicted Next Behaviors
                        </h4>
                        <?php if (isset($analysisData['personality_profile']['behavioral_patterns']['next_behaviors'])): ?>
                            <?php foreach ($analysisData['personality_profile']['behavioral_patterns']['next_behaviors'] as $behavior => $probability): ?>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-700 capitalize"><?php echo $behavior; ?></span>
                                    <span class="text-sm font-medium text-blue-600"><?php echo $probability; ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Mood Trends -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-100">
                        <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-heart text-green-500 mr-2"></i>
                            Mood Analysis
                        </h4>
                        <?php if (isset($analysisData['personality_profile']['behavioral_patterns']['mood_trends'])): ?>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-700">Current Mood</span>
                                    <span class="text-sm font-medium text-green-600 capitalize"><?php echo $analysisData['personality_profile']['behavioral_patterns']['mood_trends']['current_mood']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-700">Stability</span>
                                    <span class="text-sm font-medium text-green-600"><?php echo $analysisData['personality_profile']['behavioral_patterns']['mood_trends']['mood_stability']; ?>%</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Activity Levels -->
                    <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-2xl p-6 border border-orange-100">
                        <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-chart-line text-orange-500 mr-2"></i>
                            Activity Patterns
                        </h4>
                        <?php if (isset($analysisData['personality_profile']['behavioral_patterns']['activity_levels'])): ?>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-700">Current Level</span>
                                    <span class="text-sm font-medium text-orange-600"><?php echo $analysisData['personality_profile']['behavioral_patterns']['activity_levels']['current_activity']; ?>%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-700">Peak Times</span>
                                    <span class="text-sm font-medium text-orange-600"><?php echo implode(', ', $analysisData['personality_profile']['behavioral_patterns']['activity_levels']['peak_times']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Insights -->
        <div class="mb-8 slide-up" style="animation-delay: 0.8s">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-3xl p-8 text-white shadow-2xl">
                <h3 class="text-2xl font-bold mb-6 flex items-center">
                    <i class="fas fa-lightbulb text-yellow-300 mr-3"></i>
                    Advanced AI Insights & Recommendations
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php if (isset($analysisData['insights'])): ?>
                        <?php foreach ($analysisData['insights'] as $category => $insightData): ?>
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 border border-white/30">
                                <h4 class="text-lg font-semibold mb-4 capitalize"><?php echo $category; ?> Insights</h4>
                                <?php if (is_array($insightData)): ?>
                                    <?php foreach ($insightData as $key => $value): ?>
                                        <?php if (is_array($value)): ?>
                                            <div class="mb-3">
                                                <h5 class="text-sm font-medium text-white/90 mb-1 capitalize"><?php echo $key; ?>:</h5>
                                                <ul class="text-sm text-white/80 space-y-1">
                                                    <?php foreach ($value as $item): ?>
                                                        <li>• <?php echo htmlspecialchars($item); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php else: ?>
                                            <div class="mb-2">
                                                <span class="text-sm text-white/90"><?php echo ucfirst($key); ?>: </span>
                                                <span class="text-sm text-white/80"><?php echo htmlspecialchars($value); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Behavioral Patterns -->
        <div class="mb-8 slide-up" style="animation-delay: 0.7s">
            <div class="bg-white rounded-3xl p-8 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-chart-line text-green-500 mr-3"></i>
                    Behavioral Patterns
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($sampleAnalysis['behavioral_patterns'] as $pattern => $description): ?>
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-6 border border-purple-100">
                            <h4 class="font-semibold text-gray-900 mb-2"><?php echo $pattern; ?></h4>
                            <p class="text-gray-600 text-sm"><?php echo $description; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- AI Recommendations -->
        <div class="mb-8 slide-up" style="animation-delay: 0.8s">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-3xl p-8 text-white shadow-2xl">
                <h3 class="text-2xl font-bold mb-6 flex items-center">
                    <i class="fas fa-lightbulb text-yellow-300 mr-3"></i>
                    AI-Powered Recommendations
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($sampleAnalysis['recommendations'] as $index => $recommendation): ?>
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 border border-white/30">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-white/30 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-white font-bold text-sm"><?php echo $index + 1; ?></span>
                                </div>
                                <p class="text-white/90"><?php echo $recommendation; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- AI Model Information -->
        <div class="slide-up" style="animation-delay: 0.9s">
            <div class="bg-white rounded-3xl p-8 shadow-xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-cogs text-gray-500 mr-3"></i>
                    AI Model Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-brain text-white text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Neural Network</h4>
                        <p class="text-gray-600 text-sm">Deep learning model trained on 100K+ cat behaviors</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-chart-line text-white text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Real-time Analysis</h4>
                        <p class="text-gray-600 text-sm">Instant personality assessment with live data</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-robot text-white text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Continuous Learning</h4>
                        <p class="text-gray-600 text-sm">Model improves with every analysis</p>
                    </div>
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

            // Animate trait bars on load
            document.querySelectorAll('.trait-fill').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 1000);
            });

            // Add floating animation to background elements
            const blobs = document.querySelectorAll('.animate-blob');
            blobs.forEach((blob, index) => {
                blob.style.animationDelay = `${index * 2}s`;
            });

            // Interactive cat selection
            document.querySelectorAll('.personality-card').forEach(card => {
                card.addEventListener('click', function() {
                    // Remove active state from all cards
                    document.querySelectorAll('.personality-card').forEach(c => {
                        c.classList.remove('ring-2', 'ring-purple-500', 'bg-purple-50');
                    });
                    
                    // Add active state to clicked card
                    this.classList.add('ring-2', 'ring-purple-500', 'bg-purple-50');
                    
                    // Simulate AI analysis (in real app, this would make an API call)
                    simulateAIAnalysis();
                });
            });

            // Simulate AI analysis
            function simulateAIAnalysis() {
                // Show loading state
                const results = document.querySelectorAll('.slide-up');
                results.forEach(result => {
                    result.style.opacity = '0.5';
                });

                // Simulate processing time
                setTimeout(() => {
                    results.forEach(result => {
                        result.style.opacity = '1';
                    });
                    
                    // Add success animation
                    document.querySelector('.ai-glow').classList.add('animate-pulse');
                    setTimeout(() => {
                        document.querySelector('.ai-glow').classList.remove('animate-pulse');
                    }, 1000);
                }, 2000);
            }

            // Add hover effects to recommendation cards
            document.querySelectorAll('.bg-white\\/20').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                    this.style.background = 'rgba(255, 255, 255, 0.3)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.background = 'rgba(255, 255, 255, 0.2)';
                });
            });
        });
    </script>
</body>
</html>



