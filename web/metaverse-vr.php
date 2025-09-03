<?php
/**
 * 🥽 Purrr.love - Metaverse & VR
 * Virtual reality experiences and 3D worlds
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
$message = '';
$error = '';

if ($selectedCatId) {
    foreach ($cats as $cat) {
        if ($cat['id'] == $selectedCatId) {
            $selectedCat = $cat;
            break;
        }
    }
}

// Handle metaverse actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['metaverse_action'])) {
        $metaverseAction = $_POST['metaverse_action'];
        
        switch ($metaverseAction) {
            case 'create_world':
                $worldName = trim($_POST['world_name'] ?? '');
                $worldType = $_POST['world_type'] ?? 'social';
                $worldDescription = trim($_POST['world_description'] ?? '');
                
                if ($worldName && $worldType) {
                    try {
                        // Simulate world creation
                        $worldId = 'WORLD_' . strtoupper(substr(md5($worldName . time()), 0, 8));
                        $message = "Virtual world '$worldName' created successfully! World ID: $worldId";
                        
                    } catch (Exception $e) {
                        $error = 'Error creating world: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please provide world name and type';
                }
                break;
                
            case 'join_world':
                $worldId = $_POST['world_id'] ?? '';
                
                if ($worldId) {
                    try {
                        // Simulate joining world
                        $message = "Successfully joined world $worldId! Your cat is now exploring the metaverse.";
                        
                    } catch (Exception $e) {
                        $error = 'Error joining world: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please provide world ID';
                }
                break;
                
            case 'vr_interaction':
                $interactionType = $_POST['interaction_type'] ?? '';
                $targetCat = $_POST['target_cat'] ?? '';
                
                if ($interactionType && $targetCat) {
                    try {
                        // Simulate VR interaction
                        $interactions = [
                            'play' => 'played with',
                            'groom' => 'groomed',
                            'explore' => 'explored with',
                            'socialize' => 'socialized with',
                            'compete' => 'competed against'
                        ];
                        
                        $action = $interactions[$interactionType] ?? 'interacted with';
                        $message = "Your cat " . $action . " $targetCat in VR!";
                        
                    } catch (Exception $e) {
                        $error = 'Error performing VR interaction: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please select interaction type and target cat';
                }
                break;
        }
    }
}

// Available world types
$worldTypes = [
    'social' => 'Social Hub',
    'adventure' => 'Adventure Zone',
    'training' => 'Training Grounds',
    'competition' => 'Competition Arena',
    'relaxation' => 'Relaxation Garden',
    'exploration' => 'Exploration World',
    'custom' => 'Custom World'
];

// Available VR interactions
$vrInteractions = [
    'play' => 'Play Together',
    'groom' => 'Groom Each Other',
    'explore' => 'Explore Together',
    'socialize' => 'Socialize',
    'compete' => 'Compete'
];

// Simulate available worlds
$availableWorlds = [
    [
        'id' => 'WORLD_MAIN',
        'name' => 'Purrr.love Central',
        'type' => 'social',
        'description' => 'The main social hub where cats from around the world gather to play and socialize.',
        'current_players' => rand(15, 45),
        'max_players' => 100,
        'status' => 'active'
    ],
    [
        'id' => 'WORLD_ADVENTURE',
        'name' => 'Catventure Quest',
        'type' => 'adventure',
        'description' => 'An exciting adventure world with quests, treasures, and challenges for brave cats.',
        'current_players' => rand(8, 25),
        'max_players' => 50,
        'status' => 'active'
    ],
    [
        'id' => 'WORLD_TRAINING',
        'name' => 'Skill Academy',
        'type' => 'training',
        'description' => 'Learn new skills, practice tricks, and improve your cat\'s abilities.',
        'current_players' => rand(5, 20),
        'max_players' => 30,
        'status' => 'active'
    ],
    [
        'id' => 'WORLD_COMPETITION',
        'name' => 'Championship Arena',
        'type' => 'competition',
        'description' => 'Compete against other cats in various challenges and tournaments.',
        'current_players' => rand(10, 30),
        'max_players' => 60,
        'status' => 'active'
    ]
];

// Simulate user's active world sessions
$userWorldSessions = [];
if ($selectedCat) {
    // 30% chance the cat is currently in a world
    if (rand(1, 3) == 1) {
        $randomWorld = $availableWorlds[array_rand($availableWorlds)];
        $userWorldSessions[] = [
            'world_id' => $randomWorld['id'],
            'world_name' => $randomWorld['name'],
            'cat_id' => $selectedCat['id'],
            'cat_name' => $selectedCat['name'],
            'joined_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 120) . ' minutes')),
            'status' => 'active'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 Metaverse & VR - Purrr.love</title>
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
                            <a href="metaverse-vr.php" class="bg-purple-100 text-purple-700 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-vr-cardboard mr-2"></i>Metaverse
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
            <h1 class="text-4xl font-bold text-gray-900 mb-4">🌐 Metaverse & VR</h1>
            <p class="text-xl text-gray-600">Explore virtual cat worlds and experience social VR interactions</p>
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
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Select a Cat for Metaverse</h2>
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

        <!-- Metaverse Operations -->
        <?php if ($selectedCat): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- World Creation -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Create Virtual World</h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="metaverse_action" value="create_world">
                    
                    <div>
                        <label for="world_name" class="block text-sm font-medium text-gray-700 mb-2">
                            World Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="world_name" name="world_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Enter your world name">
                    </div>

                    <div>
                        <label for="world_type" class="block text-sm font-medium text-gray-700 mb-2">
                            World Type <span class="text-red-500">*</span>
                        </label>
                        <select id="world_type" name="world_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select world type</option>
                            <?php foreach ($worldTypes as $value => $label): ?>
                            <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="world_description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea id="world_description" name="world_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                  placeholder="Describe your virtual world..."></textarea>
                    </div>

                    <button type="submit" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                        <i class="fas fa-plus mr-2"></i>Create World
                    </button>
                </form>

                <!-- VR Tips -->
                <div class="mt-6 p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg">
                    <h4 class="font-semibold text-gray-900 mb-2">💡 VR Tips</h4>
                    <p class="text-sm text-gray-700">
                        Create unique worlds for your cats to explore and interact with other feline friends. 
                        Each world type offers different experiences and activities!
                    </p>
                </div>
            </div>

            <!-- VR Interactions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">VR Interactions</h2>
                
                <?php if (!empty($userWorldSessions)): ?>
                <div class="space-y-4">
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-vr-cardboard text-green-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-green-900">Currently in World!</p>
                                <p class="text-sm text-green-700">
                                    <?= htmlspecialchars($userWorldSessions[0]['world_name']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="metaverse_action" value="vr_interaction">
                        
                        <div>
                            <label for="interaction_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Interaction Type <span class="text-red-500">*</span>
                            </label>
                            <select id="interaction_type" name="interaction_type" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Select interaction type</option>
                                <?php foreach ($vrInteractions as $value => $label): ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="target_cat" class="block text-sm font-medium text-gray-700 mb-2">
                                Target Cat <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="target_cat" name="target_cat" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Enter target cat's name">
                        </div>

                        <button type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                            <i class="fas fa-vr-cardboard mr-2"></i>Perform Interaction
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-4">
                        <i class="fas fa-vr-cardboard"></i>
                    </div>
                    <p class="text-gray-600 mb-6">Not currently in any virtual world</p>
                    <p class="text-sm text-gray-500">Join a world first to enable VR interactions</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Available Worlds -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Available Virtual Worlds</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($availableWorlds as $world): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:border-purple-300 transition duration-200">
                    <div class="flex justify-between items-start mb-3">
                        <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($world['name']) ?></h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <?= ucfirst($world['type']) ?>
                        </span>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($world['description']) ?></p>
                    
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm text-gray-500">
                            Players: <?= $world['current_players'] ?>/<?= $world['max_players'] ?>
                        </span>
                        <span class="text-xs text-gray-400">ID: <?= $world['id'] ?></span>
                    </div>
                    
                    <form method="POST" class="inline-block">
                        <input type="hidden" name="metaverse_action" value="join_world">
                        <input type="hidden" name="world_id" value="<?= $world['id'] ?>">
                        <button type="submit" 
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i>Join World
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Active Sessions -->
        <?php if (!empty($userWorldSessions)): ?>
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Active World Sessions</h3>
            <div class="space-y-4">
                <?php foreach ($userWorldSessions as $session): ?>
                <div class="border border-gray-200 rounded-lg p-4 bg-green-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($session['world_name']) ?></h4>
                            <p class="text-sm text-gray-600">
                                <?= htmlspecialchars($session['cat_name']) ?> joined 
                                <?= date('g:i A', strtotime($session['joined_at'])) ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                            <p class="text-xs text-gray-500 mt-1">World ID: <?= $session['world_id'] ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cat Information -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4"><?= htmlspecialchars($selectedCat['name']) ?>'s Metaverse Profile</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600"><?= $selectedCat['health'] ?></div>
                    <div class="text-xs text-gray-500">Health</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600"><?= $selectedCat['happiness'] ?></div>
                    <div class="text-xs text-gray-500">Happiness</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600"><?= $selectedCat['energy'] ?></div>
                    <div class="text-xs text-gray-500">Energy</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600"><?= $selectedCat['hunger'] ?></div>
                    <div class="text-xs text-gray-500">Hunger</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600"><?= $selectedCat['cleanliness'] ?></div>
                    <div class="text-xs text-gray-500">Cleanliness</div>
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
            <p class="text-gray-600 mb-8">You need to create a cat first before you can explore the metaverse.</p>
            <a href="cats.php?action=create" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-medium text-lg transition duration-300">
                <i class="fas fa-plus mr-2"></i>Create Your First Cat
            </a>
        </div>
        <?php endif; ?>

        <!-- Metaverse Information -->
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-6 mt-8 max-w-4xl mx-auto">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">🌐 How Metaverse & VR Work</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-700">
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Virtual Worlds</h4>
                    <ul class="space-y-1">
                        <li>• Create custom environments</li>
                        <li>• Socialize with other cats</li>
                        <li>• Complete quests and challenges</li>
                        <li>• Build and customize spaces</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">VR Features</h4>
                    <ul class="space-y-1">
                        <li>• Immersive 3D experiences</li>
                        <li>• Real-time interactions</li>
                        <li>• Cross-platform compatibility</li>
                        <li>• Social VR capabilities</li>
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
            // Animate sections on load
            const sections = document.querySelectorAll('.bg-white');
            sections.forEach((section, index) => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    section.style.opacity = '1';
                    section.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>

