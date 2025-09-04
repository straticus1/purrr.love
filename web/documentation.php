<?php
/**
 * 📚 Purrr.love - Documentation Center
 * Comprehensive platform documentation and guides
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
    <title>📚 Documentation - Purrr.love</title>
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
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md shadow-lg border-b border-purple-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="/" class="text-3xl font-black gradient-text">
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
                            <i class="fas fa-user-plus mr-2"></i>Get Started
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="gradient-bg py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl font-black text-white mb-6">📚 Documentation Center</h1>
            <p class="text-xl text-purple-100 max-w-3xl mx-auto">
                Everything you need to know about using Purrr.love - from getting started to advanced features
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Links</h3>
                    <nav class="space-y-2">
                        <a href="#getting-started" class="block py-2 px-4 text-sm text-gray-600 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-all duration-200">
                            <i class="fas fa-rocket mr-2"></i>Getting Started
                        </a>
                        <a href="#features" class="block py-2 px-4 text-sm text-gray-600 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-all duration-200">
                            <i class="fas fa-star mr-2"></i>Features
                        </a>
                        <a href="#api" class="block py-2 px-4 text-sm text-gray-600 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-all duration-200">
                            <i class="fas fa-code mr-2"></i>API Reference
                        </a>
                        <a href="#troubleshooting" class="block py-2 px-4 text-sm text-gray-600 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-all duration-200">
                            <i class="fas fa-wrench mr-2"></i>Troubleshooting
                        </a>
                        <a href="#faq" class="block py-2 px-4 text-sm text-gray-600 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-all duration-200">
                            <i class="fas fa-question-circle mr-2"></i>FAQ
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Content -->
            <div class="lg:col-span-3 space-y-12">
                <!-- Getting Started -->
                <section id="getting-started" class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-rocket text-purple-500 mr-3"></i>Getting Started
                    </h2>
                    <div class="prose max-w-none">
                        <p class="text-lg text-gray-600 mb-6">
                            Welcome to Purrr.love! Follow these simple steps to get started with your cat gaming journey.
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6">
                                <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center mb-4">
                                    <i class="fas fa-user-plus text-white text-xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">1. Create Account</h3>
                                <p class="text-gray-600 text-sm">Sign up with your email to create your free Purrr.love account.</p>
                            </div>
                            
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6">
                                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center mb-4">
                                    <i class="fas fa-cat text-white text-xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">2. Adopt Your Cat</h3>
                                <p class="text-gray-600 text-sm">Choose your first virtual cat and start building your collection.</p>
                            </div>
                            
                            <div class="bg-gradient-to-br from-green-50 to-teal-50 rounded-xl p-6">
                                <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mb-4">
                                    <i class="fas fa-gamepad text-white text-xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">3. Start Playing</h3>
                                <p class="text-gray-600 text-sm">Play games, earn coins, and explore all the amazing features!</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Features -->
                <section id="features" class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-star text-yellow-500 mr-3"></i>Platform Features
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border border-gray-200 rounded-xl p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">🧠 AI-Powered Personalities</h3>
                            <p class="text-gray-600 mb-4">Our advanced machine learning algorithms create unique, evolving cat personalities that adapt to your interactions.</p>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• 5-factor personality model</li>
                                <li>• Behavioral pattern recognition</li>
                                <li>• Environmental adaptation</li>
                            </ul>
                        </div>
                        
                        <div class="border border-gray-200 rounded-xl p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">⛓️ Blockchain Ownership</h3>
                            <p class="text-gray-600 mb-4">Own your cats as NFTs on multiple blockchains with real value and trading capabilities.</p>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• Multi-network support</li>
                                <li>• NFT marketplace</li>
                                <li>• Royalty system</li>
                            </ul>
                        </div>
                        
                        <div class="border border-gray-200 rounded-xl p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">🥽 VR Metaverse</h3>
                            <p class="text-gray-600 mb-4">Step into immersive 3D cat worlds with social VR features and interactive environments.</p>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• Cross-platform VR</li>
                                <li>• Social interactions</li>
                                <li>• Custom worlds</li>
                            </ul>
                        </div>
                        
                        <div class="border border-gray-200 rounded-xl p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">🎮 Play-to-Earn Games</h3>
                            <p class="text-gray-600 mb-4">Enjoy fun mini-games and earn cryptocurrency rewards while playing with your cats.</p>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• Multiple game types</li>
                                <li>• Crypto rewards</li>
                                <li>• Tournaments & competitions</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- API Reference -->
                <section id="api" class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-code text-blue-500 mr-3"></i>API Reference
                    </h2>
                    <p class="text-gray-600 mb-6">
                        Access Purrr.love functionality programmatically with our RESTful API.
                    </p>
                    
                    <div class="bg-gray-50 rounded-xl p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Base URL</h3>
                        <code class="bg-gray-200 px-3 py-1 rounded text-sm">https://purrr.love/api/v2/</code>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-gray-900">Get User Cats</h4>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">GET</span>
                            </div>
                            <code class="text-sm text-gray-600">/cats/{user_id}</code>
                            <p class="text-sm text-gray-500 mt-2">Retrieve all cats owned by a specific user.</p>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-gray-900">Cat Health Check</h4>
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">POST</span>
                            </div>
                            <code class="text-sm text-gray-600">/cats/{cat_id}/health</code>
                            <p class="text-sm text-gray-500 mt-2">Update cat health status and vitals.</p>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-gray-900">AI Personality Analysis</h4>
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-medium">POST</span>
                            </div>
                            <code class="text-sm text-gray-600">/ai/personality/{cat_id}</code>
                            <p class="text-sm text-gray-500 mt-2">Get AI-powered personality insights for a cat.</p>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <a href="../api/" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            View Full API Documentation
                        </a>
                    </div>
                </section>

                <!-- Troubleshooting -->
                <section id="troubleshooting" class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-wrench text-orange-500 mr-3"></i>Troubleshooting
                    </h2>
                    <div class="space-y-6">
                        <div class="border-l-4 border-red-500 pl-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Can't Login to My Account</h3>
                            <p class="text-gray-600 mb-3">If you're having trouble logging in:</p>
                            <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                <li>• Check that you're using the correct email address</li>
                                <li>• Ensure your password is entered correctly</li>
                                <li>• Try clearing your browser cache and cookies</li>
                                <li>• Contact support if the issue persists</li>
                            </ul>
                        </div>
                        
                        <div class="border-l-4 border-yellow-500 pl-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Cats Not Loading</h3>
                            <p class="text-gray-600 mb-3">If your cats aren't appearing:</p>
                            <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                <li>• Refresh the page</li>
                                <li>• Check your internet connection</li>
                                <li>• Try adopting a new cat if you have none</li>
                                <li>• Contact support for database issues</li>
                            </ul>
                        </div>
                        
                        <div class="border-l-4 border-blue-500 pl-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Games Not Working</h3>
                            <p class="text-gray-600 mb-3">If games aren't loading or working properly:</p>
                            <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                <li>• Ensure JavaScript is enabled in your browser</li>
                                <li>• Try using a different browser</li>
                                <li>• Clear your browser cache</li>
                                <li>• Update your browser to the latest version</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- FAQ -->
                <section id="faq" class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-question-circle text-green-500 mr-3"></i>Frequently Asked Questions
                    </h2>
                    <div class="space-y-6">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Is Purrr.love free to use?</h3>
                            <p class="text-gray-600">Yes! Creating an account and adopting your first cats is completely free. Some premium features may require coins or tokens.</p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">How do I earn Purrr Coins?</h3>
                            <p class="text-gray-600">You can earn coins by playing games, completing daily challenges, participating in events, and taking good care of your cats.</p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Can I trade my cats with other users?</h3>
                            <p class="text-gray-600">Yes! Once you mint your cats as NFTs on the blockchain, you can trade them in our marketplace with other players.</p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Do I need VR equipment to play?</h3>
                            <p class="text-gray-600">No! While we support VR for enhanced experiences, all core features work perfectly in your web browser without any special equipment.</p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">How does the AI personality system work?</h3>
                            <p class="text-gray-600">Our AI analyzes your interactions with your cats and develops unique personality traits using a 5-factor model, making each cat truly unique.</p>
                        </div>
                    </div>
                </section>
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
    </script>
</body>
</html>
