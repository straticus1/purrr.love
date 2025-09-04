<?php
/**
 * 🐱 Purrr.love - User Dashboard
 * Central hub for user activities and cat management
 */

// Define secure access for includes
define('SECURE_ACCESS', true);

session_start();
require_once 'includes/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

try {
    $user = get_web_user_by_id($_SESSION['user_id']);
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

// Get user statistics
$userStats = [];
try {
    $pdo = get_web_db();
    
    // Get cat count
    $stmt = $pdo->prepare("SELECT COUNT(*) as cat_count FROM cats WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userStats['cat_count'] = $stmt->fetch()['cat_count'] ?? 0;
    
    // Get total coins (add coins column if it doesn't exist)
    try {
        $stmt = $pdo->prepare("SELECT coins FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userStats['coins'] = $stmt->fetch()['coins'] ?? 0;
    } catch (Exception $e) {
        // Coins column might not exist yet
        $userStats['coins'] = 0;
    }
    
    // Get recent activities
    $stmt = $pdo->prepare("
        SELECT 'cat_created' as type, c.name, c.created_at 
        FROM cats c 
        WHERE c.owner_id = ? 
        ORDER BY c.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $userStats['recent_activities'] = $stmt->fetchAll();
    
    // Get user level and experience
    $stmt = $pdo->prepare("SELECT level, experience_points as experience FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userLevel = $stmt->fetch();
    $userStats['level'] = $userLevel['level'] ?? 1;
    $userStats['experience'] = $userLevel['experience'] ?? 0;
    
} catch (Exception $e) {
    // Handle database errors gracefully
    $userStats = [
        'cat_count' => 0,
        'coins' => 0,
        'recent_activities' => [],
        'level' => 1,
        'experience' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 Dashboard - Purrr.love</title>
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
        
        .progress-ring {
            transform: rotate(-90deg);
        }
        
        .progress-ring-circle {
            transition: stroke-dasharray 0.35s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
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
        
        .floating-cat {
            animation: floating-cat 4s ease-in-out infinite;
        }
        
        @keyframes floating-cat {
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
                            <a href="dashboard.php" class="nav-link active-nav text-purple-600 px-4 py-2 rounded-lg text-sm font-semibold bg-purple-50">
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
                            <p class="text-xs text-gray-500">Level <?php echo $userStats['level']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="mb-8 slide-up">
            <div class="bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600 rounded-3xl p-8 text-white shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-4xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($user['username'] ?? 'Cat Lover'); ?>! 🐱</h2>
                        <p class="text-xl text-purple-100">Ready for another purr-fect day with your feline friends?</p>
                    </div>
                    <div class="hidden lg:block">
                        <div class="text-8xl floating-cat">🐈</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Cat Count -->
            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Cats</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $userStats['cat_count']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-purple-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-cat text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Coins -->
            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Purrr Coins</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo number_format($userStats['coins']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-coins text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Level -->
            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Level</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $userStats['level']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-blue-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-star text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Experience -->
            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.4s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Experience</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo number_format($userStats['experience']); ?> XP</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-fire text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Quick Actions -->
                <div class="bg-white rounded-3xl p-6 shadow-xl card-hover slide-up" style="animation-delay: 0.5s">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-bolt text-purple-500 mr-3"></i>
                        Quick Actions
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <button class="bg-gradient-to-br from-pink-400 to-purple-500 text-white p-4 rounded-2xl hover:from-pink-500 hover:to-purple-600 transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-plus text-2xl mb-2"></i>
                            <p class="text-sm font-medium">Adopt Cat</p>
                        </button>
                        <button class="bg-gradient-to-br from-blue-400 to-indigo-500 text-white p-4 rounded-2xl hover:from-blue-500 hover:to-indigo-600 transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-gamepad text-2xl mb-2"></i>
                            <p class="text-sm font-medium">Play Game</p>
                        </button>
                        <button class="bg-gradient-to-br from-green-400 to-teal-500 text-white p-4 rounded-2xl hover:from-green-500 hover:to-teal-600 transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-store text-2xl mb-2"></i>
                            <p class="text-sm font-medium">Shop</p>
                        </button>
                        <button class="bg-gradient-to-br from-yellow-400 to-orange-500 text-white p-4 rounded-2xl hover:from-yellow-500 hover:to-orange-600 transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-users text-2xl mb-2"></i>
                            <p class="text-sm font-medium">Social</p>
                        </button>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white rounded-3xl p-6 shadow-xl card-hover slide-up" style="animation-delay: 0.6s">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-history text-purple-500 mr-3"></i>
                        Recent Activities
                    </h3>
                    <div class="space-y-4">
                        <?php if (!empty($userStats['recent_activities'])): ?>
                            <?php foreach ($userStats['recent_activities'] as $activity): ?>
                                <div class="flex items-center p-4 bg-gray-50 rounded-2xl hover:bg-purple-50 transition-colors duration-200">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                                        <i class="fas fa-cat text-purple-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">Adopted <?php echo htmlspecialchars($activity['name']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo date('M j, Y', strtotime($activity['created_at'])); ?></p>
                                    </div>
                                    <div class="text-purple-500">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-cat text-4xl mb-4 text-gray-300"></i>
                                <p>No activities yet. Start by adopting your first cat!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-8">
                <!-- Level Progress -->
                <div class="bg-white rounded-3xl p-6 shadow-xl card-hover slide-up" style="animation-delay: 0.7s">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Level Progress</h3>
                    <div class="text-center">
                        <div class="relative w-24 h-24 mx-auto mb-4">
                            <svg class="w-24 h-24 progress-ring">
                                <circle class="progress-ring-circle" stroke="#e5e7eb" stroke-width="8" fill="transparent" r="44" cx="48" cy="48"/>
                                <circle class="progress-ring-circle" stroke="#8b5cf6" stroke-width="8" fill="transparent" r="44" cx="48" cy="48" 
                                        stroke-dasharray="<?php echo (($userStats['experience'] % 1000) / 1000) * 276.46; ?> 276.46"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-2xl font-bold text-purple-600"><?php echo $userStats['level']; ?></span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600"><?php echo ($userStats['experience'] % 1000); ?> / 1000 XP to next level</p>
                    </div>
                </div>

                <!-- Daily Rewards -->
                <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-3xl p-6 text-white shadow-xl card-hover slide-up" style="animation-delay: 0.8s">
                    <h3 class="text-xl font-bold mb-4">Daily Rewards</h3>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-gift text-3xl"></i>
                        </div>
                        <p class="text-sm mb-4">Come back tomorrow for your daily reward!</p>
                        <button class="bg-white/20 hover:bg-white/30 px-6 py-2 rounded-full font-medium transition-all duration-300">
                            Claim Reward
                        </button>
                    </div>
                </div>

                <!-- Community Stats -->
                <div class="bg-white rounded-3xl p-6 shadow-xl card-hover slide-up" style="animation-delay: 0.9s">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Community</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Online Users</span>
                            <span class="font-semibold text-green-600">1,247</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Total Cats</span>
                            <span class="font-semibold text-purple-600">45,892</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Games Played</span>
                            <span class="font-semibold text-blue-600">89,234</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Floating Action Button -->
    <div class="fixed bottom-8 right-8 z-50">
        <button class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-110 transition-all duration-300 pulse-glow">
            <i class="fas fa-plus text-white text-2xl"></i>
        </button>
    </div>

    <script>
        // Add some interactive JavaScript for enhanced UX
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
            document.querySelectorAll('.card-hover').forEach(card => {
                observer.observe(card);
            });

            // Add hover effects to quick action buttons
            document.querySelectorAll('.grid button').forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05) translateY(-5px)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) translateY(0)';
                });
            });

            // Smooth scroll for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });

        // Add floating animation to background elements
        document.addEventListener('DOMContentLoaded', function() {
            const blobs = document.querySelectorAll('.animate-blob');
            blobs.forEach((blob, index) => {
                blob.style.animationDelay = `${index * 2}s`;
            });
        });
    </script>
</body>
</html>
