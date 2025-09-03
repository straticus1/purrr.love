<?php
/**
 * 🐱 Purrr.love - Cat Management
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

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Handle cat creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_cat'])) {
    $catName = trim($_POST['cat_name'] ?? '');
    $catBreed = $_POST['cat_breed'] ?? 'domestic';
    $catColor = $_POST['cat_color'] ?? 'mixed';
    
    if (empty($catName)) {
        $error = 'Cat name is required';
    } else {
        try {
            // Generate cat stats
            $catStats = [
                'health' => rand(70, 100),
                'happiness' => rand(60, 90),
                'energy' => rand(50, 100),
                'hunger' => rand(20, 80),
                'cleanliness' => rand(40, 90)
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO cats (name, breed, color, owner_id, health, happiness, energy, hunger, cleanliness, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            if ($stmt->execute([$catName, $catBreed, $catColor, $_SESSION['user_id'], 
                              $catStats['health'], $catStats['happiness'], $catStats['energy'], 
                              $catStats['hunger'], $catStats['cleanliness']])) {
                $message = "Cat '$catName' created successfully!";
                $action = 'list';
            } else {
                $error = 'Failed to create cat';
            }
        } catch (Exception $e) {
            $error = 'Error creating cat: ' . $e->getMessage();
        }
    }
}

// Get user's cats
$cats = [];
try {
    $stmt = $pdo->prepare("
        SELECT * FROM cats 
        WHERE owner_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cats = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Error loading cats: ' . $e->getMessage();
}

// Cat breeds for selection
$catBreeds = [
    'domestic' => 'Domestic Shorthair',
    'persian' => 'Persian',
    'siamese' => 'Siamese',
    'maine_coon' => 'Maine Coon',
    'ragdoll' => 'Ragdoll',
    'british_shorthair' => 'British Shorthair',
    'abyssinian' => 'Abyssinian',
    'bengal' => 'Bengal',
    'russian_blue' => 'Russian Blue',
    'sphynx' => 'Sphynx'
];

// Cat colors for selection
$catColors = [
    'mixed' => 'Mixed Colors',
    'black' => 'Black',
    'white' => 'White',
    'orange' => 'Orange',
    'gray' => 'Gray',
    'brown' => 'Brown',
    'calico' => 'Calico',
    'tabby' => 'Tabby',
    'tuxedo' => 'Tuxedo',
    'tortoiseshell' => 'Tortoiseshell'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 My Cats - Purrr.love</title>
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
                            <a href="cats.php" class="bg-purple-100 text-purple-700 px-3 py-2 rounded-md text-sm font-medium">
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
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Cats</h1>
                <p class="text-gray-600">Manage your feline friends</p>
            </div>
            <a href="?action=create" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                <i class="fas fa-plus mr-2"></i>Create New Cat
            </a>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cat Creation Form -->
        <?php if ($action === 'create'): ?>
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Create a New Cat</h2>
            
            <form method="POST" action="">
                <div class="space-y-6">
                    <div>
                        <label for="cat_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Cat Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="cat_name" name="cat_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Enter your cat's name">
                    </div>

                    <div>
                        <label for="cat_breed" class="block text-sm font-medium text-gray-700 mb-2">
                            Breed
                        </label>
                        <select id="cat_breed" name="cat_breed" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <?php foreach ($catBreeds as $value => $label): ?>
                            <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="cat_color" class="block text-sm font-medium text-gray-700 mb-2">
                            Color
                        </label>
                        <select id="cat_color" name="cat_color" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <?php foreach ($catColors as $value => $label): ?>
                            <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" name="create_cat" 
                                class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                            <i class="fas fa-cat mr-2"></i>Create Cat
                        </button>
                        <a href="?action=list" 
                           class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-md transition duration-300 text-center">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Cat List -->
        <?php if ($action === 'list' || empty($action)): ?>
        <?php if (empty($cats)): ?>
        <div class="text-center py-16">
            <div class="text-gray-400 text-6xl mb-6">
                <i class="fas fa-cat"></i>
            </div>
            <h3 class="text-2xl font-semibold text-gray-900 mb-4">No cats yet!</h3>
            <p class="text-gray-600 mb-8">Start your cat collection by creating your first feline friend.</p>
            <a href="?action=create" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-medium text-lg transition duration-300">
                <i class="fas fa-plus mr-2"></i>Create Your First Cat
            </a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($cats as $cat): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                <!-- Cat Header -->
                <div class="bg-gradient-to-r from-purple-500 to-blue-500 p-4 text-white">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold"><?= htmlspecialchars($cat['name']) ?></h3>
                        <span class="text-sm bg-white bg-opacity-20 px-2 py-1 rounded-full">
                            <?= ucfirst(str_replace('_', ' ', $cat['breed'])) ?>
                        </span>
                    </div>
                    <p class="text-purple-100 text-sm"><?= ucfirst(str_replace('_', ' ', $cat['color'])) ?></p>
                </div>

                <!-- Cat Stats -->
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600"><?= $cat['health'] ?></div>
                            <div class="text-xs text-gray-500">Health</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600"><?= $cat['happiness'] ?></div>
                            <div class="text-xs text-gray-500">Happiness</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600"><?= $cat['energy'] ?></div>
                            <div class="text-xs text-gray-500">Energy</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600"><?= $cat['hunger'] ?></div>
                            <div class="text-xs text-gray-500">Hunger</div>
                        </div>
                    </div>

                    <!-- Progress Bars -->
                    <div class="space-y-2 mb-4">
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span>Health</span>
                                <span><?= $cat['health'] ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: <?= $cat['health'] ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span>Happiness</span>
                                <span><?= $cat['happiness'] ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: <?= $cat['happiness'] ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span>Energy</span>
                                <span><?= $cat['energy'] ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $cat['energy'] ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Cat Actions -->
                    <div class="flex space-x-2">
                        <a href="games.php?cat_id=<?= $cat['id'] ?>" 
                           class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-2 px-3 rounded text-sm font-medium transition duration-300">
                            <i class="fas fa-play mr-1"></i>Play
                        </a>
                        <a href="store.php?cat_id=<?= $cat['id'] ?>" 
                           class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded text-sm font-medium transition duration-300">
                            <i class="fas fa-shopping-cart mr-1"></i>Care
                        </a>
                        <a href="cats.php?action=view&id=<?= $cat['id'] ?>" 
                           class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-center py-2 px-3 rounded text-sm font-medium transition duration-300">
                            <i class="fas fa-eye mr-1"></i>View
                        </a>
                    </div>

                    <!-- Cat Info -->
                    <div class="mt-4 pt-4 border-t border-gray-200 text-xs text-gray-500">
                        <div class="flex justify-between">
                            <span>Created:</span>
                            <span><?= date('M j, Y', strtotime($cat['created_at'])) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Cleanliness:</span>
                            <span><?= $cat['cleanliness'] ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Cat Detail View -->
        <?php if ($action === 'view' && isset($_GET['id'])): ?>
        <?php
        $catId = $_GET['id'];
        $cat = null;
        try {
            $stmt = $pdo->prepare("SELECT * FROM cats WHERE id = ? AND owner_id = ?");
            $stmt->execute([$catId, $_SESSION['user_id']]);
            $cat = $stmt->fetch();
        } catch (Exception $e) {
            $error = 'Error loading cat details';
        }
        
        if ($cat):
        ?>
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($cat['name']) ?></h2>
                <a href="?action=list" class="text-purple-600 hover:text-purple-800">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Cats
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Cat Info -->
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Cat Information</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Breed:</span>
                            <span class="font-medium"><?= ucfirst(str_replace('_', ' ', $cat['breed'])) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Color:</span>
                            <span class="font-medium"><?= ucfirst(str_replace('_', ' ', $cat['color'])) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Created:</span>
                            <span class="font-medium"><?= date('F j, Y', strtotime($cat['created_at'])) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Age:</span>
                            <span class="font-medium"><?= date_diff(date_create($cat['created_at']), date_create('now'))->days ?> days</span>
                        </div>
                    </div>
                </div>

                <!-- Cat Stats -->
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Statistics</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Health</span>
                                <span><?= $cat['health'] ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-green-500 h-3 rounded-full" style="width: <?= $cat['health'] ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Happiness</span>
                                <span><?= $cat['happiness'] ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-purple-500 h-3 rounded-full" style="width: <?= $cat['happiness'] ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Energy</span>
                                <span><?= $cat['energy'] ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-500 h-3 rounded-full" style="width: <?= $cat['energy'] ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Hunger</span>
                                <span><?= $cat['hunger'] ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-yellow-500 h-3 rounded-full" style="width: <?= $cat['hunger'] ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Cleanliness</span>
                                <span><?= $cat['cleanliness'] ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-indigo-500 h-3 rounded-full" style="width: <?= $cat['cleanliness'] ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="games.php?cat_id=<?= $cat['id'] ?>" 
                       class="bg-green-600 hover:bg-green-700 text-white text-center py-3 px-4 rounded-lg font-medium transition duration-300">
                        <i class="fas fa-gamepad mr-2"></i>Play Games
                    </a>
                    <a href="store.php?cat_id=<?= $cat['id'] ?>" 
                       class="bg-blue-600 hover:bg-blue-700 text-white text-center py-3 px-4 rounded-lg font-medium transition duration-300">
                        <i class="fas fa-shopping-cart mr-2"></i>Buy Items
                    </a>
                    <a href="breeding.php?cat_id=<?= $cat['id'] ?>" 
                       class="bg-pink-600 hover:bg-pink-700 text-white text-center py-3 px-4 rounded-lg font-medium transition duration-300">
                        <i class="fas fa-heart mr-2"></i>Breeding
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="text-center py-16">
            <div class="text-red-400 text-6xl mb-6">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="text-2xl font-semibold text-gray-900 mb-4">Cat not found</h3>
            <p class="text-gray-600 mb-8">The cat you're looking for doesn't exist or doesn't belong to you.</p>
            <a href="?action=list" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-medium text-lg transition duration-300">
                <i class="fas fa-arrow-left mr-2"></i>Back to Cats
            </a>
        </div>
        <?php endif; ?>
        <?php endif; ?>
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
            // Animate cat cards on load
            const catCards = document.querySelectorAll('.grid > div');
            catCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
