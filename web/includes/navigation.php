<?php
/**
 * 🧭 Shared Navigation Component
 * Provides consistent navigation across all pages
 */

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Check if user is logged in and get user info
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    try {
        $user = getUserById($_SESSION['user_id']);
    } catch (Exception $e) {
        $isLoggedIn = false;
    }
}

// Navigation items with proper structure
$navigationItems = [
    'dashboard' => [
        'url' => 'dashboard.php',
        'icon' => 'fas fa-tachometer-alt',
        'label' => 'Dashboard',
        'description' => 'Overview & Stats'
    ],
    'cats' => [
        'url' => 'cats.php',
        'icon' => 'fas fa-cat',
        'label' => 'My Cats',
        'description' => 'Manage Cats'
    ],
    'health' => [
        'url' => 'health-monitoring.php',
        'icon' => 'fas fa-heartbeat',
        'label' => 'Health',
        'description' => 'Health Monitoring'
    ],
    'games' => [
        'url' => 'games.php',
        'icon' => 'fas fa-gamepad',
        'label' => 'Games',
        'description' => 'Play Games'
    ],
    'store' => [
        'url' => 'store.php',
        'icon' => 'fas fa-store',
        'label' => 'Store',
        'description' => 'Shop Items'
    ],
    'ai-personality' => [
        'url' => 'ml-personality.php',
        'icon' => 'fas fa-brain',
        'label' => 'AI Personality',
        'description' => 'AI Analysis'
    ],
    'ai-behavior' => [
        'url' => 'ai-behavior-monitor.php',
        'icon' => 'fas fa-chart-line',
        'label' => 'AI Behavior',
        'description' => 'Behavior Monitor'
    ],
    'lost-pet' => [
        'url' => 'lost_pet_finder.php',
        'icon' => 'fas fa-search',
        'label' => 'Lost Pet Finder',
        'description' => 'Find Lost Pets'
    ],
    'blockchain' => [
        'url' => 'blockchain-nft.php',
        'icon' => 'fas fa-link',
        'label' => 'Blockchain',
        'description' => 'NFT & Crypto'
    ],
    'metaverse' => [
        'url' => 'metaverse-vr.php',
        'icon' => 'fas fa-vr-cardboard',
        'label' => 'Metaverse',
        'description' => 'VR Worlds'
    ],
    'webhooks' => [
        'url' => 'webhooks.php',
        'icon' => 'fas fa-plug',
        'label' => 'Webhooks',
        'description' => 'API Integration'
    ],
    'analytics' => [
        'url' => 'analytics_dashboard.php',
        'icon' => 'fas fa-chart-bar',
        'label' => 'Analytics',
        'description' => 'Data Insights'
    ]
];

// Admin-only navigation items
$adminNavigationItems = [
    'admin' => [
        'url' => 'admin.php',
        'icon' => 'fas fa-cog',
        'label' => 'Admin',
        'description' => 'System Admin',
        'admin_only' => true
    ]
];

// Function to render navigation
function renderNavigation($navigationItems, $currentPage, $isLoggedIn, $user) {
    $html = '';
    
    foreach ($navigationItems as $key => $item) {
        // Skip admin items for non-admin users
        if (isset($item['admin_only']) && $item['admin_only'] && 
            (!$isLoggedIn || $user['role'] !== 'admin')) {
            continue;
        }
        
        $isActive = ($currentPage === $key || $currentPage === str_replace('-', '_', $key));
        $activeClass = $isActive ? 'active-nav text-purple-600 bg-purple-50' : 'text-gray-700 hover:text-purple-600 hover:bg-purple-50';
        
        $html .= sprintf(
            '<a href="%s" class="nav-link %s px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300" title="%s">
                <i class="%s mr-2"></i>%s
            </a>',
            $item['url'],
            $activeClass,
            $item['description'],
            $item['icon'],
            $item['label']
        );
    }
    
    return $html;
}
?>

<!-- Main Navigation -->
<nav class="bg-white shadow-lg border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <a href="index.php" class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center text-white text-lg font-bold">
                            🐱
                        </div>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                            Purrr.love
                        </h1>
                    </a>
                </div>
                
                <!-- Main Navigation Links -->
                <?php if ($isLoggedIn): ?>
                <div class="hidden lg:block ml-10">
                    <div class="flex items-baseline space-x-1">
                        <?php echo renderNavigation($navigationItems, $currentPage, $isLoggedIn, $user); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- User Menu and Actions -->
            <div class="flex items-center">
                <?php if ($isLoggedIn): ?>
                <!-- User Profile and Actions -->
                <div class="ml-3 relative">
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-400 hover:text-gray-500 transition-colors duration-200">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                                3
                            </span>
                        </button>
                        
                        <!-- User Info -->
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                <?= strtoupper(substr($user['name'] ?? $user['email'], 0, 1)) ?>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($user['name'] ?? 'User') ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    Level <?= $user['level'] ?? 1 ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Profile Dropdown -->
                        <div class="relative">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-purple-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50 hidden group-hover:block">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">
                                    <i class="fas fa-cog mr-2"></i>Settings
                                </a>
                                <hr class="my-2">
                                <a href="?logout=1" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Login/Register Buttons -->
                <div class="flex items-center space-x-4">
                    <a href="#login" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    <a href="register.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Navigation (Hamburger Menu) -->
<?php if ($isLoggedIn): ?>
<div class="lg:hidden bg-white border-b border-gray-200">
    <div class="px-4 py-3">
        <div class="grid grid-cols-2 gap-2">
            <?php 
            // Show only essential mobile navigation items
            $mobileItems = ['dashboard', 'cats', 'games', 'store'];
            foreach ($mobileItems as $key) {
                if (isset($navigationItems[$key])) {
                    $item = $navigationItems[$key];
                    $isActive = ($currentPage === $key);
                    $activeClass = $isActive ? 'bg-purple-100 text-purple-700' : 'bg-gray-50 text-gray-700 hover:bg-gray-100';
                    
                    echo sprintf(
                        '<a href="%s" class="%s px-3 py-2 rounded-lg text-sm font-medium text-center transition-colors duration-200">
                            <i class="%s block text-lg mb-1"></i>
                            <span class="text-xs">%s</span>
                        </a>',
                        $item['url'],
                        $activeClass,
                        $item['icon'],
                        $item['label']
                    );
                }
            }
            ?>
        </div>
    </div>
</div>
<?php endif; ?>
