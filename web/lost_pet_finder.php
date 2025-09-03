<?php
/**
 * Lost Pet Finder Web Interface
 * 
 * Provides a user-friendly web interface for:
 * - Reporting lost pets
 * - Searching for lost pets
 * - Managing pet sightings
 * - Facebook integration
 * - Community support
 * 
 * @package Purrr.love
 * @version 1.0.0
 */

define('SECURE_ACCESS', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/lost_pet_finder.php';

// Initialize session and check authentication
session_start();
$isAuthenticated = isset($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;

// Initialize lost pet finder
$lostPetFinder = new LostPetFinder();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'report_lost_pet':
                if ($isAuthenticated) {
                    $result = $lostPetFinder->reportLostPet($userId, $_POST);
                    if ($result['success']) {
                        $message = "Lost pet report created successfully! Report ID: {$result['report_id']}";
                        $messageType = 'success';
                    } else {
                        $message = $result['message'];
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Please log in to report a lost pet';
                    $messageType = 'error';
                }
                break;
                
            case 'report_sighting':
                if ($isAuthenticated) {
                    $result = $lostPetFinder->reportSighting($userId, $_POST);
                    if ($result['success']) {
                        $message = 'Sighting report submitted successfully!';
                        $messageType = 'success';
                    } else {
                        $message = $result['message'];
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Please log in to report a sighting';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get statistics for display
$stats = $lostPetFinder->getStatistics($userId);
$statistics = $stats['success'] ? $stats['statistics'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Pet Finder - Purrr.love</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .transition-all {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-purple-600">
                        <i class="fas fa-paw mr-2"></i>Purrr.love
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/" class="text-gray-700 hover:text-purple-600">Home</a>
                    <a href="/game.php" class="text-gray-700 hover:text-purple-600">Game</a>
                    <?php if ($isAuthenticated): ?>
                        <a href="/profile.php" class="text-gray-700 hover:text-purple-600">Profile</a>
                        <a href="/logout.php" class="text-gray-700 hover:text-purple-600">Logout</a>
                    <?php else: ?>
                        <a href="/login.php" class="text-gray-700 hover:text-purple-600">Login</a>
                        <a href="/register.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h1 class="text-5xl font-bold mb-6">
                <i class="fas fa-search mr-4"></i>Lost Pet Finder
            </h1>
            <p class="text-xl mb-8 max-w-3xl mx-auto">
                Help reunite lost pets with their families. Our advanced search system and Facebook integration 
                provide the best chance of finding your beloved companion.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#report-lost" class="bg-white text-purple-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-all">
                    <i class="fas fa-plus mr-2"></i>Report Lost Pet
                </a>
                <a href="#search-pets" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-purple-600 transition-all">
                    <i class="fas fa-search mr-2"></i>Search Lost Pets
                </a>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <?php if (!empty($statistics)): ?>
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">Lost Pet Statistics</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl font-bold text-purple-600 mb-2">
                        <?php echo number_format($statistics['overall']['total_reports'] ?? 0); ?>
                    </div>
                    <div class="text-gray-600">Total Reports</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-red-500 mb-2">
                        <?php echo number_format($statistics['overall']['active_reports'] ?? 0); ?>
                    </div>
                    <div class="text-gray-600">Currently Missing</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-green-500 mb-2">
                        <?php echo number_format($statistics['overall']['found_pets'] ?? 0); ?>
                    </div>
                    <div class="text-gray-600">Successfully Found</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-blue-500 mb-2">
                        <?php echo number_format($statistics['overall']['success_rate'] ?? 0); ?>%
                    </div>
                    <div class="text-gray-600">Success Rate</div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
            
            <!-- Report Lost Pet Section -->
            <div id="report-lost" class="bg-white rounded-lg shadow-lg p-8 card-hover transition-all">
                <h2 class="text-3xl font-bold mb-6 text-gray-800">
                    <i class="fas fa-plus-circle text-red-500 mr-3"></i>Report Lost Pet
                </h2>
                
                <?php if (!$isAuthenticated): ?>
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Please <a href="/login.php" class="underline font-semibold">log in</a> to report a lost pet.
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="space-y-6">
                    <input type="hidden" name="action" value="report_lost_pet">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pet Name *</label>
                            <input type="text" name="name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pet Type</label>
                            <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="cat" selected>Cat</option>
                                <option value="dog">Dog</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Breed *</label>
                            <input type="text" name="breed" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color *</label>
                            <input type="text" name="color" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Age (years)</label>
                            <input type="number" name="age" min="0" max="25" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Microchip ID</label>
                            <input type="text" name="microchip_id" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Seen Location *</label>
                        <input type="text" name="last_seen_location" required 
                               placeholder="e.g., Central Park, New York"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Seen Date *</label>
                            <input type="date" name="last_seen_date" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reward Amount ($)</label>
                            <input type="number" name="reward_amount" min="0" step="0.01" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3" 
                                  placeholder="Describe your pet's appearance, behavior, and any identifying features..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="facebook_share_enabled" value="1" 
                                   class="mr-2 text-purple-600 focus:ring-purple-500">
                            <span class="text-sm text-gray-700">Share on Facebook for wider reach</span>
                        </label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Privacy Level</label>
                        <select name="privacy_level" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="public">Public - Visible to everyone</option>
                            <option value="community">Community - Visible to registered users</option>
                            <option value="private">Private - Visible only to you</option>
                        </select>
                    </div>
                    
                    <button type="submit" <?php echo $isAuthenticated ? '' : 'disabled'; ?>
                            class="w-full bg-red-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-red-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-paper-plane mr-2"></i>Report Lost Pet
                    </button>
                </form>
            </div>
            
            <!-- Search Lost Pets Section -->
            <div id="search-pets" class="bg-white rounded-lg shadow-lg p-8 card-hover transition-all">
                <h2 class="text-3xl font-bold mb-6 text-gray-800">
                    <i class="fas fa-search text-blue-500 mr-3"></i>Search Lost Pets
                </h2>
                
                <form method="GET" action="" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Breed</label>
                            <input type="text" name="breed" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                            <input type="text" name="color" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Age Range (years)</label>
                            <div class="flex space-x-2">
                                <input type="number" name="age_min" min="0" max="25" placeholder="Min" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <span class="text-gray-500 self-center">to</span>
                                <input type="number" name="age_max" min="0" max="25" placeholder="Max" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Radius</label>
                            <select name="radius_km" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="5">5 km</option>
                                <option value="10" selected>10 km</option>
                                <option value="25">25 km</option>
                                <option value="50">50 km</option>
                                <option value="100">100 km</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location (for distance-based search)</label>
                        <input type="text" name="location" 
                               placeholder="Enter your location for nearby search"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-600 transition-all">
                        <i class="fas fa-search mr-2"></i>Search Lost Pets
                    </button>
                </form>
                
                <!-- Search Results Placeholder -->
                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-semibold text-gray-700 mb-2">Search Results</h3>
                    <p class="text-gray-600 text-sm">
                        Enter search criteria above to find lost pets in your area. 
                        Results will show here with distance and contact information.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Report Sighting Section -->
        <div class="mt-16 bg-white rounded-lg shadow-lg p-8 card-hover transition-all">
            <h2 class="text-3xl font-bold mb-6 text-gray-800">
                <i class="fas fa-eye text-green-500 mr-3"></i>Report a Pet Sighting
            </h2>
            
            <?php if (!$isAuthenticated): ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Please <a href="/login.php" class="underline font-semibold">log in</a> to report a sighting.
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="action" value="report_sighting">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lost Pet Report ID *</label>
                        <input type="number" name="lost_pet_report_id" required 
                               placeholder="Enter the report ID from the lost pet listing"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sighting Date *</label>
                        <input type="date" name="sighting_date" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
                    <input type="text" name="location" required 
                           placeholder="Where did you see the pet?"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3" 
                              placeholder="Describe what you saw, the pet's condition, behavior, etc..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confidence Level</label>
                        <select name="confidence_level" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="low">Low - Not very sure</option>
                            <option value="medium" selected>Medium - Somewhat sure</option>
                            <option value="high">High - Very sure</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Your Contact Info</label>
                        <input type="text" name="contact_info" 
                               placeholder="Phone or email for follow-up"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                
                <button type="submit" <?php echo $isAuthenticated ? '' : 'disabled'; ?>
                        class="w-full bg-green-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-green-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-eye mr-2"></i>Report Sighting
                </button>
            </form>
        </div>
    </div>

    <!-- Facebook Integration Section -->
    <section class="bg-blue-50 py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">
                    <i class="fab fa-facebook text-blue-600 mr-3"></i>Facebook Integration
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Connect your Facebook account to automatically share lost pet reports and reach thousands of people in your area. 
                    Our Facebook app integration provides the widest possible reach for finding lost pets.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg p-6 text-center card-hover transition-all">
                    <div class="text-4xl text-blue-600 mb-4">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Automatic Sharing</h3>
                    <p class="text-gray-600">Lost pet reports are automatically shared to Facebook with optimized content and hashtags.</p>
                </div>
                
                <div class="bg-white rounded-lg p-6 text-center card-hover transition-all">
                    <div class="text-4xl text-blue-600 mb-4">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Community Reach</h3>
                    <p class="text-gray-600">Tap into Facebook's vast network to reach people who might have seen your lost pet.</p>
                </div>
                
                <div class="bg-white rounded-lg p-6 text-center card-hover transition-all">
                    <div class="text-4xl text-blue-600 mb-4">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Analytics & Insights</h3>
                    <p class="text-gray-600">Track how many people see and engage with your lost pet posts on Facebook.</p>
                </div>
            </div>
            
            <?php if ($isAuthenticated): ?>
                <div class="text-center mt-8">
                    <button class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all">
                        <i class="fab fa-facebook mr-2"></i>Connect Facebook Account
                    </button>
                </div>
            <?php else: ?>
                <div class="text-center mt-8">
                    <a href="/login.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login to Connect Facebook
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Community Support Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-hands-helping text-green-600 mr-3"></i>Community Support
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Join our community of pet lovers who help each other find lost pets. 
                    Every sighting report, share, or volunteer effort brings us closer to reuniting families.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-eye text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold mb-2">Report Sightings</h3>
                    <p class="text-gray-600 text-sm">Help by reporting when you see a lost pet</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-share text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold mb-2">Share Posts</h3>
                    <p class="text-gray-600 text-sm">Share lost pet posts on social media</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-volunteer text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold mb-2">Volunteer</h3>
                    <p class="text-gray-600 text-sm">Help search in your local area</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-orange-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-donate text-orange-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold mb-2">Support</h3>
                    <p class="text-gray-600 text-sm">Contribute to reward funds</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Message Display -->
    <?php if ($message): ?>
        <div class="fixed top-4 right-4 z-50">
            <div class="bg-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-500 text-white px-6 py-3 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> mr-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            </div>
        </div>
        <script>
            setTimeout(() => {
                document.querySelector('.fixed').style.display = 'none';
            }, 5000);
        </script>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Purrr.love</h3>
                    <p class="text-gray-300">Helping cats and their humans find each other.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="/" class="hover:text-white">Home</a></li>
                        <li><a href="/game.php" class="hover:text-white">Game</a></li>
                        <li><a href="#report-lost" class="hover:text-white">Report Lost Pet</a></li>
                        <li><a href="#search-pets" class="hover:text-white">Search</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Support</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="/help.php" class="hover:text-white">Help Center</a></li>
                        <li><a href="/contact.php" class="hover:text-white">Contact Us</a></li>
                        <li><a href="/faq.php" class="hover:text-white">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Connect</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-white text-2xl">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white text-2xl">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white text-2xl">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; 2024 Purrr.love. All rights reserved. | <a href="/privacy.php" class="hover:text-white">Privacy Policy</a> | <a href="/terms.php" class="hover:text-white">Terms of Service</a></p>
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

        // Form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            // Add form validation
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.classList.add('border-red-500');
                            isValid = false;
                        } else {
                            field.classList.remove('border-red-500');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                    }
                });
            });
        });
    </script>
</body>
</html>
