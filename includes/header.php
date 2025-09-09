<?php

// Initialize any needed configurations
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? "$page_title - Purrr.love" : "🐱 Purrr.love - The Ultimate Cat Gaming Ecosystem"; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : "Join the most advanced cat gaming platform with AI, blockchain, VR, and real-time multiplayer features. Adopt cats, play games, and earn crypto rewards!"; ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php if (isset($additional_head)): ?>
        <?php echo $additional_head; ?>
    <?php endif; ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
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
        
        .feature-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .animate-blob {
            animation: blob 7s infinite;
        }
        
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
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
        
        .cta-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        <?php if (isset($additional_styles)): ?>
            <?php echo $additional_styles; ?>
        <?php endif; ?>
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
                            <a href="/">🐱 Purrr.love</a>
                        </h1>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/web/games.php" class="nav-link text-gray-700 hover:text-purple-600 px-3 py-2 text-sm font-medium">Games</a>
                    <a href="/web/ml-personality.php" class="nav-link text-gray-700 hover:text-purple-600 px-3 py-2 text-sm font-medium">AI</a>
                    <a href="/web/blockchain-nft.php" class="nav-link text-gray-700 hover:text-purple-600 px-3 py-2 text-sm font-medium">Blockchain</a>
                    <a href="/web/metaverse-vr.php" class="nav-link text-gray-700 hover:text-purple-600 px-3 py-2 text-sm font-medium">VR</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="/web/dashboard.php" class="nav-link text-gray-700 hover:text-purple-600 px-3 py-2 text-sm font-medium">Dashboard</a>
                        <a href="/web/profile.php" class="nav-link text-gray-700 hover:text-purple-600 px-3 py-2 text-sm font-medium">Profile</a>
                        <a href="/logout.php" class="nav-link text-gray-700 hover:text-purple-600 px-3 py-2 text-sm font-medium">Logout</a>
                    <?php else: ?>
                        <a href="/web/register.php" class="cta-button text-white px-6 py-2 rounded-full font-medium">Get Started</a>
                    <?php endif; ?>
                </div>
                <div class="md:hidden">
                    <button class="text-gray-700 hover:text-purple-600" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile menu -->
        <div id="mobileMenu" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="/web/games.php" class="block text-gray-700 hover:text-purple-600 px-3 py-2 text-base font-medium">Games</a>
                <a href="/web/ml-personality.php" class="block text-gray-700 hover:text-purple-600 px-3 py-2 text-base font-medium">AI</a>
                <a href="/web/blockchain-nft.php" class="block text-gray-700 hover:text-purple-600 px-3 py-2 text-base font-medium">Blockchain</a>
                <a href="/web/metaverse-vr.php" class="block text-gray-700 hover:text-purple-600 px-3 py-2 text-base font-medium">VR</a>
                <?php if (isLoggedIn()): ?>
                    <a href="/web/dashboard.php" class="block text-gray-700 hover:text-purple-600 px-3 py-2 text-base font-medium">Dashboard</a>
                    <a href="/web/profile.php" class="block text-gray-700 hover:text-purple-600 px-3 py-2 text-base font-medium">Profile</a>
                    <a href="/logout.php" class="block text-gray-700 hover:text-purple-600 px-3 py-2 text-base font-medium">Logout</a>
                <?php else: ?>
                    <a href="/web/register.php" class="block text-purple-600 hover:text-purple-800 px-3 py-2 text-base font-medium">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
    </script>

    <!-- Main content begins here -->
    <main class="relative z-10"><?php if (isset($breadcrumbs)): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="text-sm text-gray-500">
                <?php echo $breadcrumbs; ?>
            </nav>
        </div>
    <?php endif; ?>
