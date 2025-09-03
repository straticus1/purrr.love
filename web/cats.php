<?php
/**
 * 🐱 Purrr.love - Modern Cat Management Interface
 * ✨ Beautiful, animated, and feature-rich cat management
 */

session_start();
require_once '../includes/functions.php';
require_once '../includes/authentication.php';

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

// Get user's cats
$cats = [];
try {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COALESCE(c.health, 100) as health,
               COALESCE(c.happiness, 50) as happiness,
               COALESCE(c.energy, 75) as energy,
               COALESCE(c.hunger, 25) as hunger
        FROM cats c 
        WHERE c.owner_id = ? 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cats = $stmt->fetchAll();
} catch (Exception $e) {
    $cats = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 My Cats - Purrr.love</title>
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
        
        .progress-ring {
            transform: rotate(-90deg);
        }
        
        .progress-ring-circle {
            transition: stroke-dasharray 0.35s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        
        .cat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .cat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: #8b5cf6;
        }
        
        .stat-bar {
            height: 8px;
            border-radius: 4px;
            background: #e2e8f0;
            overflow: hidden;
        }
        
        .stat-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .health-fill { background: linear-gradient(90deg, #ef4444, #f97316); }
        .happiness-fill { background: linear-gradient(90deg, #fbbf24, #f59e0b); }
        .energy-fill { background: linear-gradient(90deg, #10b981, #059669); }
        .hunger-fill { background: linear-gradient(90deg, #8b5cf6, #7c3aed); }
        
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
                            <a href="cats.php" class="nav-link active-nav text-purple-600 px-4 py-2 rounded-lg text-sm font-semibold bg-purple-50">
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
                        <h2 class="text-4xl font-bold mb-2">My Feline Family 🐱</h2>
                        <p class="text-xl text-purple-100">Manage, care for, and play with your beloved cats</p>
                    </div>
                    <div class="hidden lg:block">
                        <div class="text-8xl floating-cat">🐈</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Cats</p>
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
                        <p class="text-sm font-medium text-gray-600">Healthy Cats</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo count(array_filter($cats, fn($cat) => $cat['health'] > 80)); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-teal-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-heart text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Happy Cats</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo count(array_filter($cats, fn($cat) => $cat['happiness'] > 70)); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-smile text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card rounded-2xl p-6 card-hover bounce-in" style="animation-delay: 0.4s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Cats</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo count(array_filter($cats, fn($cat) => $cat['energy'] > 60)); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-bolt text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mb-8 slide-up" style="animation-delay: 0.5s">
            <div class="bg-white rounded-3xl p-6 shadow-xl">
                <div class="flex flex-wrap gap-4 justify-center">
                    <button class="bg-gradient-to-br from-pink-400 to-purple-500 text-white px-6 py-3 rounded-2xl hover:from-pink-500 hover:to-purple-600 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>Adopt New Cat
                    </button>
                    <button class="bg-gradient-to-br from-blue-400 to-indigo-500 text-white px-6 py-3 rounded-2xl hover:from-blue-500 hover:to-indigo-600 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-gamepad mr-2"></i>Play with Cats
                    </button>
                    <button class="bg-gradient-to-br from-green-400 to-teal-500 text-white px-6 py-3 rounded-2xl hover:from-green-500 hover:to-teal-600 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-store mr-2"></i>Buy Supplies
                    </button>
                    <button class="bg-gradient-to-br from-yellow-400 to-orange-500 text-white px-6 py-3 rounded-2xl hover:from-yellow-500 hover:to-orange-600 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-users mr-2"></i>Social Features
                    </button>
                </div>
            </div>
        </div>

        <!-- Cats Grid -->
        <?php if (!empty($cats)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($cats as $index => $cat): ?>
                    <div class="cat-card rounded-3xl p-6 shadow-xl card-hover slide-up" style="animation-delay: <?php echo 0.6 + ($index * 0.1); ?>s">
                        <!-- Cat Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-16 h-16 cat-avatar rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    <?php echo strtoupper(substr($cat['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($cat['name']); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($cat['breed'] ?? 'Mixed Breed'); ?></p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button class="p-2 text-gray-400 hover:text-purple-600 transition-colors" title="Edit Cat">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="p-2 text-gray-400 hover:text-red-600 transition-colors" title="Delete Cat">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Cat Stats -->
                        <div class="space-y-3 mb-6">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Health</span>
                                    <span class="font-medium"><?php echo $cat['health']; ?>%</span>
                                </div>
                                <div class="stat-bar">
                                    <div class="stat-fill health-fill" style="width: <?php echo $cat['health']; ?>%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Happiness</span>
                                    <span class="font-medium"><?php echo $cat['happiness']; ?>%</span>
                                </div>
                                <div class="stat-bar">
                                    <div class="stat-fill happiness-fill" style="width: <?php echo $cat['happiness']; ?>%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Energy</span>
                                    <span class="font-medium"><?php echo $cat['energy']; ?>%</span>
                                </div>
                                <div class="stat-bar">
                                    <div class="stat-fill energy-fill" style="width: <?php echo $cat['energy']; ?>%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Hunger</span>
                                    <span class="font-medium"><?php echo $cat['hunger']; ?>%</span>
                                </div>
                                <div class="stat-bar">
                                    <div class="stat-fill hunger-fill" style="width: <?php echo $cat['hunger']; ?>%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="grid grid-cols-2 gap-3">
                            <button class="bg-purple-100 text-purple-700 px-4 py-2 rounded-xl hover:bg-purple-200 transition-colors text-sm font-medium">
                                <i class="fas fa-heart mr-2"></i>Care
                            </button>
                            <button class="bg-blue-100 text-blue-700 px-4 py-2 rounded-xl hover:bg-blue-200 transition-colors text-sm font-medium">
                                <i class="fas fa-gamepad mr-2"></i>Play
                            </button>
                        </div>

                        <!-- Cat Info -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Age: <?php echo $cat['age'] ?? 'Unknown'; ?></span>
                                <span>Created: <?php echo date('M j, Y', strtotime($cat['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-16 slide-up" style="animation-delay: 0.6s">
                <div class="w-32 h-32 bg-gradient-to-br from-purple-200 to-pink-200 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-cat text-6xl text-purple-400"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">No Cats Yet!</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    Start your feline adventure by adopting your first cat. You'll be able to care for them, 
                    play games together, and watch them grow!
                </p>
                <button class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-8 py-4 rounded-2xl text-lg font-semibold hover:from-purple-600 hover:to-pink-600 transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>Adopt Your First Cat
                </button>
            </div>
        <?php endif; ?>

        <!-- Cat Care Tips -->
        <?php if (!empty($cats)): ?>
            <div class="mt-16 slide-up" style="animation-delay: 0.8s">
                <div class="bg-white rounded-3xl p-8 shadow-xl">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                        <i class="fas fa-lightbulb text-yellow-500 mr-3"></i>
                        Cat Care Tips
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-utensils text-white text-2xl"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Feed Regularly</h4>
                            <p class="text-gray-600 text-sm">Keep your cats well-fed to maintain their health and energy levels.</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-gamepad text-white text-2xl"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Play Daily</h4>
                            <p class="text-gray-600 text-sm">Regular play sessions increase happiness and reduce stress.</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-heart text-white text-2xl"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Show Love</h4>
                            <p class="text-gray-600 text-sm">Give attention and care to boost your cat's happiness.</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Floating Action Button -->
    <div class="fixed bottom-8 right-8 z-50">
        <button class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-110 transition-all duration-300 pulse-glow floating-action">
            <i class="fas fa-plus text-white text-2xl"></i>
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

            // Add hover effects to action buttons
            document.querySelectorAll('.grid button').forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05) translateY(-2px)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) translateY(0)';
                });
            });

            // Animate stat bars on load
            document.querySelectorAll('.stat-fill').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });

            // Add floating animation to background elements
            const blobs = document.querySelectorAll('.animate-blob');
            blobs.forEach((blob, index) => {
                blob.style.animationDelay = `${index * 2}s`;
            });

            // Interactive cat cards
            document.querySelectorAll('.cat-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html>
