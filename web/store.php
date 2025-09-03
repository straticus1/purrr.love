<?php
/**
 * 🛒 Purrr.love - Virtual Store
 * Shop for cat items, toys, and accessories
 */

// Define secure access for includes
define('SECURE_ACCESS', true);

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
    $stmt = $pdo->prepare("SELECT * FROM cats WHERE owner_id = ? ORDER BY name");
    $stmt->execute([$_SESSION['user_id']]);
    $cats = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Error loading cats: ' . $e->getMessage();
}

$selectedCatId = $_GET['cat_id'] ?? ($cats[0]['id'] ?? null);
$selectedCat = null;

if ($selectedCatId) {
    foreach ($cats as $cat) {
        if ($cat['id'] == $selectedCatId) {
            $selectedCat = $cat;
            break;
        }
    }
}

$message = '';
$error = '';

// Handle purchase actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['purchase_item']) && $selectedCat) {
        $itemType = $_POST['item_type'];
        $itemCost = 0;
        $statChanges = [];
        
        switch ($itemType) {
            case 'premium_food':
                $itemCost = 25;
                $statChanges = [
                    'hunger' => max(0, $selectedCat['hunger'] - 30),
                    'health' => min(100, $selectedCat['health'] + 5),
                    'happiness' => min(100, $selectedCat['happiness'] + 3)
                ];
                $message = "🍖 $selectedCat[name] enjoyed the premium food! Hunger decreased and health improved.";
                break;
                
            case 'honeysuckle_treats':
                $itemCost = 15;
                $statChanges = [
                    'happiness' => min(100, $selectedCat['happiness'] + 15),
                    'energy' => min(100, $selectedCat['energy'] + 10)
                ];
                $message = "🌸 $selectedCat[name] loved the honeysuckle treats! Happiness and energy increased.";
                break;
                
            case 'grooming_kit':
                $itemCost = 20;
                $statChanges = [
                    'cleanliness' => min(100, $selectedCat['cleanliness'] + 25),
                    'happiness' => min(100, $selectedCat['happiness'] + 5)
                ];
                $message = "🪮 $selectedCat[name] feels fresh and clean after grooming! Cleanliness and happiness improved.";
                break;
                
            case 'vitamin_supplements':
                $itemCost = 30;
                $statChanges = [
                    'health' => min(100, $selectedCat['health'] + 15),
                    'energy' => min(100, $selectedCat['energy'] + 10)
                ];
                $message = "💊 $selectedCat[name] took vitamins! Health and energy significantly improved.";
                break;
                
            case 'toy_mouse':
                $itemCost = 10;
                $statChanges = [
                    'happiness' => min(100, $selectedCat['happiness'] + 8),
                    'energy' => max(0, $selectedCat['energy'] - 5)
                ];
                $message = "🐭 $selectedCat[name] loves the new toy mouse! Happiness increased.";
                break;
                
            case 'catnip_spray':
                $itemCost = 18;
                $statChanges = [
                    'happiness' => min(100, $selectedCat['happiness'] + 20),
                    'energy' => min(100, $selectedCat['energy'] + 15)
                ];
                $message = "🌿 $selectedCat[name] is euphoric from the catnip spray! Happiness and energy boosted.";
                break;
        }
        
        if (!empty($statChanges) && $user['coins'] >= $itemCost) {
            try {
                // Update cat stats
                $updateFields = [];
                $updateValues = [];
                
                foreach ($statChanges as $stat => $value) {
                    $updateFields[] = "$stat = ?";
                    $updateValues[] = $value;
                }
                $updateFields[] = "updated_at = NOW()";
                $updateValues[] = $selectedCat['id'];
                
                $stmt = $pdo->prepare("
                    UPDATE cats 
                    SET " . implode(', ', $updateFields) . " 
                    WHERE id = ?
                ");
                $stmt->execute($updateValues);
                
                // Deduct coins from user
                $stmt = $pdo->prepare("UPDATE users SET coins = coins - ? WHERE id = ?");
                $stmt->execute([$itemCost, $_SESSION['user_id']]);
                
                // Refresh user and cat data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                $stmt = $pdo->prepare("SELECT * FROM cats WHERE id = ?");
                $stmt->execute([$selectedCat['id']]);
                $selectedCat = $stmt->fetch();
                
            } catch (Exception $e) {
                $error = 'Error processing purchase: ' . $e->getMessage();
            }
        } elseif ($user['coins'] < $itemCost) {
            $error = 'Not enough coins to purchase this item!';
        }
    }
}

// Available store items
$storeItems = [
    'premium_food' => [
        'name' => 'Premium Cat Food',
        'description' => 'High-quality nutritious food for your cat',
        'icon' => '🍖',
        'color' => 'from-green-500 to-blue-500',
        'cost' => 25,
        'effects' => 'Hunger -30, Health +5, Happiness +3'
    ],
    'honeysuckle_treats' => [
        'name' => 'Honeysuckle Treats',
        'description' => 'Special treats made with honeysuckle flowers',
        'icon' => '🌸',
        'color' => 'from-pink-500 to-purple-500',
        'cost' => 15,
        'effects' => 'Happiness +15, Energy +10'
    ],
    'grooming_kit' => [
        'name' => 'Grooming Kit',
        'description' => 'Complete grooming set for your cat',
        'icon' => '🪮',
        'color' => 'from-blue-500 to-indigo-500',
        'cost' => 20,
        'effects' => 'Cleanliness +25, Happiness +5'
    ],
    'vitamin_supplements' => [
        'name' => 'Vitamin Supplements',
        'description' => 'Essential vitamins for cat health',
        'icon' => '💊',
        'color' => 'from-yellow-500 to-orange-500',
        'cost' => 30,
        'effects' => 'Health +15, Energy +10'
    ],
    'toy_mouse' => [
        'name' => 'Toy Mouse',
        'description' => 'Interactive toy mouse for playtime',
        'icon' => '🐭',
        'color' => 'from-gray-500 to-blue-500',
        'cost' => 10,
        'effects' => 'Happiness +8, Energy -5'
    ],
    'catnip_spray' => [
        'name' => 'Catnip Spray',
        'description' => 'Natural catnip spray for excitement',
        'icon' => '🌿',
        'color' => 'from-green-500 to-yellow-500',
        'cost' => 18,
        'effects' => 'Happiness +20, Energy +15'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 Cat Store - Purrr.love</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b-4 border-purple-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="index.php" class="text-2xl font-bold text-purple-600">
                            🐱 Purrr.love
                        </a>
                    </div>
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
                            <a href="store.php" class="bg-purple-100 text-purple-700 px-3 py-2 rounded-md text-sm font-medium">
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
                            <div class="flex items-center bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full">
                                <i class="fas fa-coins mr-2"></i>
                                <span class="font-semibold"><?= number_format($user['coins']) ?></span>
                            </div>
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
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">🛒 Cat Store</h1>
            <p class="text-xl text-gray-600">Buy items to care for and pamper your feline friends!</p>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 max-w-2xl mx-auto">
            <div class="flex items-center justify-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 max-w-2xl mx-auto">
            <div class="flex items-center justify-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cat Selection -->
        <?php if (!empty($cats)): ?>
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 max-w-4xl mx-auto">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Select a Cat to Care For</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($cats as $cat): ?>
                <a href="?cat_id=<?= $cat['id'] ?>" 
                   class="block p-4 border-2 rounded-lg transition duration-200 <?= $selectedCatId == $cat['id'] ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300' ?>">
                    <div class="text-center">
                        <div class="text-3xl mb-2">🐱</div>
                        <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($cat['name']) ?></h3>
                        <p class="text-sm text-gray-600"><?= ucfirst(str_replace('_', ' ', $cat['breed'])) ?></p>
                        <div class="mt-2 text-xs text-gray-500">
                            Health: <?= $cat['health'] ?>% | Hunger: <?= $cat['hunger'] ?>%
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Store Items -->
        <?php if ($selectedCat): ?>
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 max-w-6xl mx-auto">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Shopping for <?= htmlspecialchars($selectedCat['name']) ?></h2>
                <p class="text-gray-600">Choose items to improve your cat's stats!</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($storeItems as $itemKey => $item): ?>
                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition duration-200">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-2"><?= $item['icon'] ?></div>
                        <h3 class="text-xl font-semibold text-gray-900"><?= $item['name'] ?></h3>
                        <p class="text-gray-600 text-sm mt-2"><?= $item['description'] ?></p>
                    </div>

                    <div class="space-y-2 mb-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Cost:</span>
                            <span class="font-medium text-yellow-600"><?= $item['cost'] ?> 🪙</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Effects:</span>
                            <span class="font-medium text-green-600 text-xs"><?= $item['effects'] ?></span>
                        </div>
                    </div>

                    <?php if ($user['coins'] >= $item['cost']): ?>
                    <form method="POST" class="text-center">
                        <input type="hidden" name="item_type" value="<?= $itemKey ?>">
                        <button type="submit" name="purchase_item" 
                                class="w-full bg-gradient-to-r <?= $item['color'] ?> text-white font-semibold py-3 px-4 rounded-lg hover:shadow-lg transition duration-300">
                            <i class="fas fa-shopping-cart mr-2"></i>Purchase for <?= $item['cost'] ?> 🪙
                        </button>
                    </form>
                    <?php else: ?>
                    <div class="text-center">
                        <button disabled class="w-full bg-gray-400 text-white font-semibold py-3 px-4 rounded-lg cursor-not-allowed">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Not Enough Coins
                        </button>
                        <p class="text-xs text-gray-500 mt-2">You need <?= $item['cost'] - $user['coins'] ?> more coins</p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cat Status After Purchase -->
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-4xl mx-auto">
            <h3 class="text-xl font-semibold text-gray-900 mb-4"><?= htmlspecialchars($selectedCat['name']) ?>'s Current Status</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600"><?= $selectedCat['health'] ?></div>
                    <div class="text-xs text-gray-500">Health</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: <?= $selectedCat['health'] ?>%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600"><?= $selectedCat['happiness'] ?></div>
                    <div class="text-xs text-gray-500">Happiness</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-purple-500 h-2 rounded-full" style="width: <?= $selectedCat['happiness'] ?>%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600"><?= $selectedCat['energy'] ?></div>
                    <div class="text-xs text-gray-500">Energy</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $selectedCat['energy'] ?>%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600"><?= $selectedCat['hunger'] ?></div>
                    <div class="text-xs text-gray-500">Hunger</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-yellow-500 h-2 rounded-full" style="width: <?= $selectedCat['hunger'] ?>%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600"><?= $selectedCat['cleanliness'] ?></div>
                    <div class="text-xs text-gray-500">Cleanliness</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: <?= $selectedCat['cleanliness'] ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- No Cats Available -->
        <div class="text-center py-16">
            <div class="text-gray-400 text-6xl mb-6">
                <i class="fas fa-cat"></i>
            </div>
            <h3 class="text-2xl font-semibold text-gray-900 mb-4">No cats available!</h3>
            <p class="text-gray-600 mb-8">You need to create a cat first before you can buy items.</p>
            <a href="cats.php?action=create" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-medium text-lg transition duration-300">
                <i class="fas fa-plus mr-2"></i>Create Your First Cat
            </a>
        </div>
        <?php endif; ?>

        <!-- Shopping Tips -->
        <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 max-w-4xl mx-auto">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">🛍️ Shopping Tips</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Smart Shopping</h4>
                    <ul class="space-y-1">
                        <li>• Premium food reduces hunger significantly</li>
                        <li>• Honeysuckle treats boost happiness</li>
                        <li>• Grooming improves cleanliness</li>
                        <li>• Vitamins boost health and energy</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Earning Coins</h4>
                    <ul class="space-y-1">
                        <li>• Play games with your cats</li>
                        <li>• Complete daily quests</li>
                        <li>• Participate in cat shows</li>
                        <li>• Trade cats with other users</li>
                    </ul>
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
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate store items on load
            const storeItems = document.querySelectorAll('.grid > div');
            storeItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add hover effects for cat selection
            const catCards = document.querySelectorAll('a[href*="cat_id"]');
            catCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('border-purple-500')) {
                        this.classList.add('border-purple-300', 'bg-purple-25');
                    }
                });
                
                card.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('border-purple-500')) {
                        this.classList.remove('border-purple-300', 'bg-purple-25');
                    }
                });
            });
        });
    </script>
</body>
</html>
