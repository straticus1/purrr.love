<?php
/**
 * 🎮 Purrr.love - Games & Entertainment
 * Interactive games and activities for cats and owners
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

// Handle game actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['play_game']) && $selectedCat) {
        $gameType = $_POST['game_type'];
        $coinsEarned = 0;
        $statChanges = [];
        
        switch ($gameType) {
            case 'honeysuckle_dance':
                $coinsEarned = rand(5, 15);
                $statChanges = [
                    'happiness' => min(100, $selectedCat['happiness'] + rand(5, 15)),
                    'energy' => max(0, $selectedCat['energy'] - rand(10, 20)),
                    'hunger' => min(100, $selectedCat['hunger'] + rand(5, 10))
                ];
                $message = "🎵 $selectedCat[name] danced to the Honeysuckle rhythm and earned $coinsEarned coins!";
                break;
                
            case 'laser_chase':
                $coinsEarned = rand(3, 12);
                $statChanges = [
                    'happiness' => min(100, $selectedCat['happiness'] + rand(3, 10)),
                    'energy' => max(0, $selectedCat['energy'] - rand(15, 25)),
                    'hunger' => min(100, $selectedCat['hunger'] + rand(8, 15))
                ];
                $message = "🔴 $selectedCat[name] chased the laser dot and earned $coinsEarned coins!";
                break;
                
            case 'feather_play':
                $coinsEarned = rand(4, 14);
                $statChanges = [
                    'happiness' => min(100, $selectedCat['happiness'] + rand(4, 12)),
                    'energy' => max(0, $selectedCat['energy'] - rand(8, 18)),
                    'hunger' => min(100, $selectedCat['hunger'] + rand(3, 8))
                ];
                $message = "🪶 $selectedCat[name] played with feathers and earned $coinsEarned coins!";
                break;
                
            case 'ball_toss':
                $coinsEarned = rand(2, 10);
                $statChanges = [
                    'happiness' => min(100, $selectedCat['happiness'] + rand(2, 8)),
                    'energy' => max(0, $selectedCat['energy'] - rand(5, 15)),
                    'hunger' => min(100, $selectedCat['hunger'] + rand(2, 6))
                ];
                $message = "⚽ $selectedCat[name] played ball toss and earned $coinsEarned coins!";
                break;
        }
        
        if (!empty($statChanges)) {
            try {
                // Update cat stats
                $stmt = $pdo->prepare("
                    UPDATE cats 
                    SET happiness = ?, energy = ?, hunger = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $statChanges['happiness'],
                    $statChanges['energy'],
                    $statChanges['hunger'],
                    $selectedCat['id']
                ]);
                
                // Update user coins
                $stmt = $pdo->prepare("UPDATE users SET coins = coins + ? WHERE id = ?");
                $stmt->execute([$coinsEarned, $_SESSION['user_id']]);
                
                // Refresh selected cat data
                $stmt = $pdo->prepare("SELECT * FROM cats WHERE id = ?");
                $stmt->execute([$selectedCat['id']]);
                $selectedCat = $stmt->fetch();
                
            } catch (Exception $e) {
                $error = 'Error updating cat stats: ' . $e->getMessage();
            }
        }
    }
}

// Available games
$games = [
    'honeysuckle_dance' => [
        'name' => 'Honeysuckle Dance',
        'description' => 'A special dance that cats love, inspired by honeysuckle flowers',
        'icon' => '🎵',
        'color' => 'from-pink-500 to-purple-500',
        'energy_cost' => '15-20',
        'happiness_gain' => '5-15',
        'coins_earned' => '5-15'
    ],
    'laser_chase' => [
        'name' => 'Laser Chase',
        'description' => 'Classic laser pointer chase game',
        'icon' => '🔴',
        'color' => 'from-red-500 to-orange-500',
        'energy_cost' => '15-25',
        'happiness_gain' => '3-10',
        'coins_earned' => '3-12'
    ],
    'feather_play' => [
        'name' => 'Feather Play',
        'description' => 'Interactive feather toy playtime',
        'icon' => '🪶',
        'color' => 'from-blue-500 to-green-500',
        'energy_cost' => '8-18',
        'happiness_gain' => '4-12',
        'coins_earned' => '4-14'
    ],
    'ball_toss' => [
        'name' => 'Ball Toss',
        'description' => 'Simple ball throwing and catching',
        'icon' => '⚽',
        'color' => 'from-yellow-500 to-green-500',
        'energy_cost' => '5-15',
        'happiness_gain' => '2-8',
        'coins_earned' => '2-10'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 Cat Games - Purrr.love</title>
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
                            <a href="games.php" class="bg-purple-100 text-purple-700 px-3 py-2 rounded-md text-sm font-medium">
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
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">🎮 Cat Games</h1>
            <p class="text-xl text-gray-600">Play fun games with your feline friends and earn rewards!</p>
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
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Select a Cat to Play With</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($cats as $cat): ?>
                <a href="?cat_id=<?= $cat['id'] ?>" 
                   class="block p-4 border-2 rounded-lg transition duration-200 <?= $selectedCatId == $cat['id'] ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300' ?>">
                    <div class="text-center">
                        <div class="text-3xl mb-2">🐱</div>
                        <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($cat['name']) ?></h3>
                        <p class="text-sm text-gray-600"><?= ucfirst(str_replace('_', ' ', $cat['breed'])) ?></p>
                        <div class="mt-2 text-xs text-gray-500">
                            Energy: <?= $cat['energy'] ?>% | Happiness: <?= $cat['happiness'] ?>%
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Game Selection -->
        <?php if ($selectedCat): ?>
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 max-w-4xl mx-auto">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Playing with <?= htmlspecialchars($selectedCat['name']) ?></h2>
                <p class="text-gray-600">Choose a game to play and earn coins!</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($games as $gameKey => $game): ?>
                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition duration-200">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-2"><?= $game['icon'] ?></div>
                        <h3 class="text-xl font-semibold text-gray-900"><?= $game['name'] ?></h3>
                        <p class="text-gray-600 text-sm mt-2"><?= $game['description'] ?></p>
                    </div>

                    <div class="space-y-2 mb-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Energy Cost:</span>
                            <span class="font-medium text-red-600"><?= $game['energy_cost'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Happiness Gain:</span>
                            <span class="font-medium text-green-600">+<?= $game['happiness_gain'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Coins Earned:</span>
                            <span class="font-medium text-yellow-600"><?= $game['coins_earned'] ?></span>
                        </div>
                    </div>

                    <?php if ($selectedCat['energy'] >= 5): ?>
                    <form method="POST" class="text-center">
                        <input type="hidden" name="game_type" value="<?= $gameKey ?>">
                        <button type="submit" name="play_game" 
                                class="w-full bg-gradient-to-r <?= $game['color'] ?> text-white font-semibold py-3 px-4 rounded-lg hover:shadow-lg transition duration-300">
                            <i class="fas fa-play mr-2"></i>Play <?= $game['name'] ?>
                        </button>
                    </form>
                    <?php else: ?>
                    <div class="text-center">
                        <button disabled class="w-full bg-gray-400 text-white font-semibold py-3 px-4 rounded-lg cursor-not-allowed">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Not Enough Energy
                        </button>
                        <p class="text-xs text-gray-500 mt-2"><?= htmlspecialchars($selectedCat['name']) ?> needs to rest!</p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cat Status After Game -->
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
            <p class="text-gray-600 mb-8">You need to create a cat first before you can play games.</p>
            <a href="cats.php?action=create" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-medium text-lg transition duration-300">
                <i class="fas fa-plus mr-2"></i>Create Your First Cat
            </a>
        </div>
        <?php endif; ?>

        <!-- Game Tips -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 max-w-4xl mx-auto">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">🎯 Game Tips</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Energy Management</h4>
                    <ul class="space-y-1">
                        <li>• Cats need energy to play games</li>
                        <li>• Energy decreases with each game</li>
                        <li>• Cats recover energy over time</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Happiness & Rewards</h4>
                    <ul class="space-y-1">
                        <li>• Games increase cat happiness</li>
                        <li>• Higher happiness = better rewards</li>
                        <li>• Earn coins for each game played</li>
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
            // Animate game cards on load
            const gameCards = document.querySelectorAll('.grid > div');
            gameCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
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
