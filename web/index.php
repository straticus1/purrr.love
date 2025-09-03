<?php
/**
 * 🐱 Purrr.love - Main Web Interface
 * The Ultimate Cat Gaming Platform
 */

session_start();
require_once 'includes/db_config.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    try {
        $user = get_web_user_by_id($_SESSION['user_id']);
        if (!$user) {
            // User not found, clear session
            session_destroy();
            $isLoggedIn = false;
        }
    } catch (Exception $e) {
        // User not found, clear session
        session_destroy();
        $isLoggedIn = false;
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle login form submission
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $user = authenticate_web_user($email, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: dashboard.php');
            exit;
        } else {
            $loginError = 'Invalid email or password';
        }
    } catch (Exception $e) {
        $loginError = 'Login failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 Purrr.love - The Ultimate Cat Gaming Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .cat-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b-4 border-purple-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-purple-600">
                            🐱 Purrr.love
                        </h1>
                    </div>
                    <?php if ($isLoggedIn): ?>
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-4">
                            <a href="dashboard.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                            <a href="cats.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-cat mr-2"></i>My Cats
                            </a>
                            <a href="games.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-gamepad mr-2"></i>Games
                            </a>
                            <a href="store.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-store mr-2"></i>Store
                            </a>
                            <a href="lost-pet-finder.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-search mr-2"></i>Lost Pet Finder
                            </a>
                            <a href="ml-personality.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-brain mr-2"></i>ML Personality
                            </a>
                            <a href="blockchain-nft.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-link mr-2"></i>Blockchain
                            </a>
                            <a href="metaverse-vr.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-vr-cardboard mr-2"></i>Metaverse
                            </a>
                            <a href="webhooks.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-link mr-2"></i>Webhooks
                            </a>
                            <?php if ($user['role'] === 'admin'): ?>
                            <a href="admin.php" class="text-red-600 hover:text-red-800 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-cog mr-2"></i>Admin
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center">
                    <?php if ($isLoggedIn): ?>
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700 text-sm">
                                Welcome, <?= htmlspecialchars($user['name'] ?? $user['email']) ?>
                            </span>
                            <a href="profile.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-user mr-2"></i>Profile
                            </a>
                            <a href="?logout=1" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="flex items-center space-x-4">
                        <a href="#login" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="#register" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="gradient-bg cat-pattern">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
                    Welcome to Purrr.love
                </h1>
                <p class="text-xl md:text-2xl text-white mb-8 max-w-3xl mx-auto">
                    The ultimate cat gaming platform where your feline friends come to life through AI, blockchain, and virtual reality experiences.
                </p>
                <?php if (!$isLoggedIn): ?>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#login" class="bg-white text-purple-600 hover:bg-gray-100 px-8 py-3 rounded-lg text-lg font-semibold transition duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i>Get Started
                    </a>
                    <a href="#features" class="border-2 border-white text-white hover:bg-white hover:text-purple-600 px-8 py-3 rounded-lg text-lg font-semibold transition duration-300">
                        <i class="fas fa-info-circle mr-2"></i>Learn More
                    </a>
                </div>
                <?php else: ?>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="dashboard.php" class="bg-white text-purple-600 hover:bg-gray-100 px-8 py-3 rounded-lg text-lg font-semibold transition duration-300">
                        <i class="fas fa-tachometer-alt mr-2"></i>Go to Dashboard
                    </a>
                    <a href="cats.php" class="border-2 border-white text-white hover:bg-white hover:text-purple-600 px-8 py-3 rounded-lg text-lg font-semibold transition duration-300">
                        <i class="fas fa-cat mr-2"></i>Manage Cats
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Amazing Features for Cat Lovers
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Discover a world of possibilities with our cutting-edge cat gaming platform
                </p>
            </div>
            
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- AI Cat Behavior -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">AI Cat Behavior Learning</h3>
                    <p class="text-gray-600">
                        Advanced machine learning algorithms that understand and predict your cat's personality and behavior patterns.
                    </p>
                </div>

                <!-- Blockchain NFTs -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    <div class="text-green-600 text-4xl mb-4">
                        <i class="fas fa-link"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Blockchain Cat NFTs</h3>
                    <p class="text-gray-600">
                        Own your cats as unique digital assets on the blockchain with verifiable ownership and trading capabilities.
                    </p>
                </div>

                <!-- Metaverse VR -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    <div class="text-purple-600 text-4xl mb-4">
                        <i class="fas fa-vr-cardboard"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Metaverse VR Worlds</h3>
                    <p class="text-gray-600">
                        Explore virtual cat worlds with VR technology, interact with other cats, and create your own feline paradise.
                    </p>
                </div>

                                        <!-- Lost Pet Finder -->
                        <div class="bg-gradient-to-br from-red-50 to-red-100 p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                            <div class="text-red-600 text-4xl mb-4">
                                <i class="fas fa-search"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">Lost Pet Finder</h3>
                            <p class="text-gray-600">
                                Advanced lost pet recovery system with Facebook integration and community support to help reunite lost cats.
                            </p>
                        </div>

                        <!-- ML Personality -->
                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                            <div class="text-indigo-600 text-4xl mb-4">
                                <i class="fas fa-brain"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">AI Personality Analysis</h3>
                            <p class="text-gray-600">
                                Machine learning algorithms that understand and predict your cat's unique personality and behavior patterns.
                            </p>
                        </div>

                        <!-- Blockchain NFTs -->
                        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                            <div class="text-emerald-600 text-4xl mb-4">
                                <i class="fas fa-link"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">Blockchain Ownership</h3>
                            <p class="text-gray-600">
                                Own your cats as unique digital assets on the blockchain with verifiable ownership and trading capabilities.
                            </p>
                        </div>

                <!-- Health Monitoring -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    <div class="text-yellow-600 text-4xl mb-4">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Health Monitoring</h3>
                    <p class="text-gray-600">
                        Track your cat's health, activity levels, and wellness with smart monitoring and predictive health insights.
                    </p>
                </div>

                <!-- Multiplayer Games -->
                <div class="bg-gradient-to-br from-pink-50 to-pink-100 p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    <div class="text-pink-600 text-4xl mb-4">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Multiplayer Games</h3>
                    <p class="text-gray-600">
                        Play with other cat owners in real-time multiplayer games, competitions, and social activities.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Login/Register Section -->
    <?php if (!$isLoggedIn): ?>
    <div id="login" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Login Form -->
                <div class="bg-white p-8 rounded-xl shadow-lg">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Welcome Back!</h2>
                    
                    <?php if ($loginError): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($loginError) ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        
                        <div class="mb-6">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" id="password" name="password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        
                        <button type="submit" name="login" 
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-md transition duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                        </button>
                    </form>
                    
                    <div class="mt-6 text-center">
                        <a href="#" class="text-purple-600 hover:text-purple-800 text-sm">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <!-- Register Form -->
                <div class="bg-white p-8 rounded-xl shadow-lg">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Join Purrr.love</h2>
                    <p class="text-gray-600 mb-6">
                        Create your account and start your cat gaming journey today!
                    </p>
                    
                    <form method="POST" action="register.php">
                        <div class="mb-4">
                            <label for="reg_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" id="reg_name" name="name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        
                        <div class="mb-4">
                            <label for="reg_email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="reg_email" name="email" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        
                        <div class="mb-4">
                            <label for="reg_password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" id="reg_password" name="password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        
                        <div class="mb-6">
                            <label for="reg_confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                            <input type="password" id="reg_confirm_password" name="confirm_password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md transition duration-300">
                            <i class="fas fa-user-plus mr-2"></i>Create Account
                        </button>
                    </form>
                    
                    <div class="mt-6 text-center text-sm text-gray-600">
                        By creating an account, you agree to our 
                        <a href="#" class="text-purple-600 hover:text-purple-800">Terms of Service</a> and 
                        <a href="#" class="text-purple-600 hover:text-purple-800">Privacy Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">🐱 Purrr.love</h3>
                    <p class="text-gray-400">
                        The ultimate cat gaming platform combining AI, blockchain, and virtual reality.
                    </p>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold mb-4">Platform</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Features</a></li>
                        <li><a href="#" class="hover:text-white">Pricing</a></li>
                        <li><a href="#" class="hover:text-white">API</a></li>
                        <li><a href="#" class="hover:text-white">Documentation</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold mb-4">Support</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Help Center</a></li>
                        <li><a href="#" class="hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white">Community</a></li>
                        <li><a href="#" class="hover:text-white">Status</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-md font-semibold mb-4">Connect</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white text-xl">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl">
                            <i class="fab fa-discord"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl">
                            <i class="fab fa-telegram"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 Purrr.love. All rights reserved. Made with ❤️ for cat lovers everywhere.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
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

        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate features on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.grid > div').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
