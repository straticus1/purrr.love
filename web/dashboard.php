<?php
/**
 * 🐱 Purrr.love - User Dashboard
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

// Get user statistics
$userStats = [];
try {
    // Get cat count
    $stmt = $pdo->prepare("SELECT COUNT(*) as cat_count FROM cats WHERE owner_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userStats['cat_count'] = $stmt->fetch()['cat_count'] ?? 0;
    
    // Get total coins
    $stmt = $pdo->prepare("SELECT coins FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userStats['coins'] = $stmt->fetch()['coins'] ?? 0;
    
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
    
} catch (Exception $e) {
    // Handle database errors gracefully
    $userStats = [
        'cat_count' => 0,
        'coins' => 0,
        'recent_activities' => []
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
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-4">
                            <a href="dashboard.php" class="bg-purple-100 text-purple-700 px-3 py-2 rounded-md text-sm font-medium">
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
                </div>
                
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700 text-sm">
                                Welcome, <?= htmlspecialchars($user['name'] ?? $user['email']) ?>
                            </span>
                            <a href="profile.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-user mr-2"></i>Profile
                            </a>
                            <a href="index.php?logout=1" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">
                        Welcome back, <?= htmlspecialchars($user['name'] ?? 'Cat Lover') ?>! 🐱
                    </h1>
                    <p class="text-purple-100 text-lg">
                        Ready to play with your feline friends today?
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-white">
                        <?= number_format($userStats['coins']) ?> 🪙
                    </div>
                    <div class="text-purple-100">Total Coins</div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Cats -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-cat text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Cats</p>
                        <p class="text-2xl font-semibold text-gray-900"><?= $userStats['cat_count'] ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Coins -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-coins text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Coins</p>
                        <p class="text-2xl font-semibold text-gray-900"><?= number_format($userStats['coins']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Games Played -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-gamepad text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Games Played</p>
                        <p class="text-2xl font-semibold text-gray-900">0</p>
                    </div>
                </div>
            </div>

            <!-- Achievements -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-trophy text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Achievements</p>
                        <p class="text-2xl font-semibold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Quick Actions -->
            <div class="lg:col-span-2">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="cats.php?action=create" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition duration-200">
                            <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-3">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Create New Cat</h3>
                                <p class="text-sm text-gray-600">Generate a new feline friend</p>
                            </div>
                        </a>

                        <a href="games.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition duration-200">
                            <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                                <i class="fas fa-play"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Play Games</h3>
                                <p class="text-sm text-gray-600">Entertain your cats</p>
                            </div>
                        </a>

                        <a href="store.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition duration-200">
                            <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Visit Store</h3>
                                <p class="text-sm text-gray-600">Buy items for your cats</p>
                            </div>
                        </a>

                                                        <a href="lost-pet-finder.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-red-300 hover:bg-red-50 transition duration-200">
                                    <div class="p-2 rounded-full bg-red-100 text-red-600 mr-3">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900">Lost Pet Finder</h3>
                                        <p class="text-sm text-gray-600">Help find lost pets</p>
                                    </div>
                                </a>

                                <a href="ml-personality.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 transition duration-200">
                                    <div class="p-2 rounded-full bg-indigo-100 text-indigo-600 mr-3">
                                        <i class="fas fa-brain"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900">AI Personality</h3>
                                        <p class="text-sm text-gray-600">Analyze cat behavior</p>
                                    </div>
                                </a>

                                <a href="blockchain-nft.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-emerald-300 hover:bg-emerald-50 transition duration-200">
                                    <div class="p-2 rounded-full bg-emerald-100 text-emerald-600 mr-3">
                                        <i class="fas fa-link"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900">Blockchain</h3>
                                        <p class="text-sm text-gray-600">Mint cat NFTs</p>
                                    </div>
                                </a>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Activities</h2>
                    <?php if (!empty($userStats['recent_activities'])): ?>
                    <div class="space-y-4">
                        <?php foreach ($userStats['recent_activities'] as $activity): ?>
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-3">
                                <i class="fas fa-cat"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">Created <?= htmlspecialchars($activity['name']) ?></p>
                                <p class="text-sm text-gray-600">
                                    <?= date('M j, Y', strtotime($activity['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <div class="text-gray-400 text-4xl mb-4">
                            <i class="fas fa-cat"></i>
                        </div>
                        <p class="text-gray-600">No activities yet</p>
                        <p class="text-sm text-gray-500">Start by creating your first cat!</p>
                        <a href="cats.php?action=create" class="mt-4 inline-block bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md">
                            Create Your First Cat
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column - Sidebar -->
            <div class="space-y-8">
                <!-- Profile Summary -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Profile Summary</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Member Since:</span>
                            <span class="font-medium text-gray-900">
                                <?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Role:</span>
                            <span class="font-medium text-gray-900 capitalize">
                                <?= htmlspecialchars($user['role'] ?? 'user') ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="profile.php" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                            Edit Profile <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Stats Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Weekly Activity</h2>
                    <canvas id="weeklyChart" width="300" height="200"></canvas>
                </div>

                <!-- System Status -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">System Status</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">API Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Online
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Database:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Connected
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Cache:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Active
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2024 Purrr.love. All rights reserved. Made with ❤️ for cat lovers everywhere.</p>
        </div>
    </footer>

    <script>
        // Weekly Activity Chart
        const ctx = document.getElementById('weeklyChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Games Played',
                    data: [3, 5, 2, 8, 6, 10, 4],
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 2
                        }
                    }
                }
            }
        });

        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stats on load
            const stats = document.querySelectorAll('.text-2xl');
            stats.forEach(stat => {
                const finalValue = parseInt(stat.textContent.replace(/,/g, ''));
                let currentValue = 0;
                const increment = finalValue / 20;
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(timer);
                    }
                    
                    if (finalValue >= 1000) {
                        stat.textContent = Math.floor(currentValue).toLocaleString();
                    } else {
                        stat.textContent = Math.floor(currentValue);
                    }
                }, 50);
            });
        });
    </script>
</body>
</html>
