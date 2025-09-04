<?php
/**
 * 🌟 Purrr.love - Community Hub
 * Connect with other cat lovers and share experiences
 */

session_start();
require_once 'includes/db_config.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    try {
        $user = get_web_user_by_id($_SESSION['user_id']);
    } catch (Exception $e) {
        $isLoggedIn = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌟 Community - Purrr.love</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-text {
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md shadow-lg border-b border-purple-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="/" class="text-3xl font-black gradient-text floating">
                            🐱 Purrr.love
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-user-plus mr-2"></i>Join Community
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="gradient-bg py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl font-black text-white mb-6">🌟 Community Hub</h1>
            <p class="text-xl text-purple-100 max-w-3xl mx-auto">
                Connect with fellow cat lovers, share experiences, and build lasting friendships in the Purrr.love community
            </p>
        </div>
    </div>

    <!-- Community Stats -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="text-4xl font-bold text-purple-600 mb-2">25,432</div>
                <p class="text-gray-600">Active Members</p>
            </div>
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="text-4xl font-bold text-blue-600 mb-2">89,234</div>
                <p class="text-gray-600">Cats Adopted</p>
            </div>
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="text-4xl font-bold text-green-600 mb-2">156,789</div>
                <p class="text-gray-600">Games Played</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Community Features -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-users text-purple-500 mr-3"></i>Community Features
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6">
                            <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center mb-4">
                                <i class="fas fa-comments text-white text-xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">Discussion Forums</h3>
                            <p class="text-gray-600 mb-4">Join conversations about cat care, gaming strategies, and share your experiences.</p>
                            <a href="#forums" class="text-purple-600 hover:text-purple-700 font-medium">Join Discussions →</a>
                        </div>
                        
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6">
                            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center mb-4">
                                <i class="fas fa-trophy text-white text-xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">Tournaments & Events</h3>
                            <p class="text-gray-600 mb-4">Compete in community tournaments and participate in seasonal events.</p>
                            <a href="#events" class="text-blue-600 hover:text-blue-700 font-medium">View Events →</a>
                        </div>
                        
                        <div class="bg-gradient-to-br from-green-50 to-teal-50 rounded-xl p-6">
                            <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mb-4">
                                <i class="fas fa-exchange-alt text-white text-xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">Cat Trading</h3>
                            <p class="text-gray-600 mb-4">Trade cats with other players and discover rare breeds in our marketplace.</p>
                            <a href="#trading" class="text-green-600 hover:text-green-700 font-medium">Start Trading →</a>
                        </div>
                        
                        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl p-6">
                            <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center mb-4">
                                <i class="fas fa-graduation-cap text-white text-xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">Learning Hub</h3>
                            <p class="text-gray-600 mb-4">Access tutorials, guides, and tips from experienced community members.</p>
                            <a href="#learning" class="text-yellow-600 hover:text-yellow-700 font-medium">Learn More →</a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-clock text-blue-500 mr-3"></i>Recent Community Activity
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-start p-4 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-900">CatLover123</h3>
                                    <span class="text-sm text-gray-500">2 hours ago</span>
                                </div>
                                <p class="text-gray-600 mt-1">Just adopted a beautiful Persian cat named Luna! 🐱✨</p>
                                <div class="flex items-center mt-2 space-x-4">
                                    <button class="text-purple-600 hover:text-purple-700 text-sm">
                                        <i class="fas fa-heart mr-1"></i>24 likes
                                    </button>
                                    <button class="text-blue-600 hover:text-blue-700 text-sm">
                                        <i class="fas fa-comment mr-1"></i>7 comments
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-start p-4 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-trophy text-white"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-900">FelineGamer</h3>
                                    <span class="text-sm text-gray-500">4 hours ago</span>
                                </div>
                                <p class="text-gray-600 mt-1">Won first place in the weekly Cat Olympics tournament! 🏆</p>
                                <div class="flex items-center mt-2 space-x-4">
                                    <button class="text-purple-600 hover:text-purple-700 text-sm">
                                        <i class="fas fa-heart mr-1"></i>56 likes
                                    </button>
                                    <button class="text-blue-600 hover:text-blue-700 text-sm">
                                        <i class="fas fa-comment mr-1"></i>12 comments
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-start p-4 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-star text-white"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-900">VRCatExplorer</h3>
                                    <span class="text-sm text-gray-500">6 hours ago</span>
                                </div>
                                <p class="text-gray-600 mt-1">Exploring the new VR cat park is amazing! The graphics are incredible 🥽</p>
                                <div class="flex items-center mt-2 space-x-4">
                                    <button class="text-purple-600 hover:text-purple-700 text-sm">
                                        <i class="fas fa-heart mr-1"></i>31 likes
                                    </button>
                                    <button class="text-blue-600 hover:text-blue-700 text-sm">
                                        <i class="fas fa-comment mr-1"></i>9 comments
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!$isLoggedIn): ?>
                    <div class="mt-6 p-4 bg-purple-50 rounded-xl">
                        <p class="text-purple-700 mb-3">
                            <i class="fas fa-info-circle mr-2"></i>
                            Join our community to participate in discussions and share your own cat adventures!
                        </p>
                        <a href="register.php" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors duration-200">
                            <i class="fas fa-user-plus mr-2"></i>
                            Join Now
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Top Contributors -->
                <div class="bg-white rounded-2xl shadow-xl p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-medal text-yellow-500 mr-2"></i>Top Contributors
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-white text-sm font-bold">1</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">CatMaster2024</p>
                                    <p class="text-xs text-gray-500">Level 47</p>
                                </div>
                            </div>
                            <span class="text-sm text-yellow-600 font-medium">15,420 XP</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-r from-gray-300 to-gray-400 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-white text-sm font-bold">2</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">FelineQueen</p>
                                    <p class="text-xs text-gray-500">Level 43</p>
                                </div>
                            </div>
                            <span class="text-sm text-gray-600 font-medium">12,890 XP</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-r from-orange-400 to-red-500 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-white text-sm font-bold">3</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">WhiskerWizard</p>
                                    <p class="text-xs text-gray-500">Level 41</p>
                                </div>
                            </div>
                            <span class="text-sm text-orange-600 font-medium">11,756 XP</span>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="bg-white rounded-2xl shadow-xl p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-calendar text-green-500 mr-2"></i>Upcoming Events
                    </h3>
                    <div class="space-y-4">
                        <div class="border-l-4 border-purple-500 pl-4">
                            <h4 class="font-medium text-gray-900">Weekly Cat Olympics</h4>
                            <p class="text-sm text-gray-600">Competitive gaming tournament</p>
                            <p class="text-xs text-gray-500">Tomorrow at 8:00 PM</p>
                        </div>
                        
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-medium text-gray-900">VR Meetup</h4>
                            <p class="text-sm text-gray-600">Explore new VR worlds together</p>
                            <p class="text-xs text-gray-500">Friday at 7:00 PM</p>
                        </div>
                        
                        <div class="border-l-4 border-green-500 pl-4">
                            <h4 class="font-medium text-gray-900">Rare Cat Auction</h4>
                            <p class="text-sm text-gray-600">Bid on exclusive cat NFTs</p>
                            <p class="text-xs text-gray-500">Saturday at 6:00 PM</p>
                        </div>
                    </div>
                </div>

                <!-- Discord Community -->
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white">
                    <h3 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fab fa-discord mr-2"></i>Join Our Discord
                    </h3>
                    <p class="text-purple-100 mb-4">
                        Connect with thousands of cat lovers in real-time chat, voice channels, and exclusive events!
                    </p>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-lg font-bold">8,432</div>
                            <div class="text-xs text-purple-200">Online Members</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold">24/7</div>
                            <div class="text-xs text-purple-200">Active Support</div>
                        </div>
                    </div>
                    <button class="w-full bg-white/20 hover:bg-white/30 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200">
                        <i class="fab fa-discord mr-2"></i>Join Discord Server
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-400">&copy; 2024 Purrr.love. All rights reserved. Made with ❤️ for cat lovers everywhere.</p>
        </div>
    </footer>

    <script>
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate community stats
            const stats = document.querySelectorAll('.text-4xl.font-bold');
            stats.forEach(stat => {
                const finalValue = parseInt(stat.textContent.replace(/,/g, ''));
                let currentValue = 0;
                const increment = finalValue / 100;
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        stat.textContent = finalValue.toLocaleString();
                        clearInterval(timer);
                    } else {
                        stat.textContent = Math.floor(currentValue).toLocaleString();
                    }
                }, 20);
            });
        });
    </script>
</body>
</html>
