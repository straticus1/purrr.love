<?php
/**
 * 🌙 Purrr.love Night Watch: Save the Strays
 * Web interface for the nighttime protection system
 */

require_once 'includes/functions.php';
require_once 'includes/night_watch_system.php';
session_start();

if (!isUserLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$user = getCurrentUser();
$userId = $user['id'];

// Initialize night watch system if not already done
if (!getNightWatchStats($userId)) {
    $initResult = initializeNightWatch($userId);
    if (!$initResult['success']) {
        $errorMessage = $initResult['message'];
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'deploy_patrol':
                $catIds = $_POST['cat_ids'] ?? [];
                $patrolArea = $_POST['patrol_area'] ?? 'neighborhood';
                
                if (empty($catIds)) {
                    throw new Exception('Please select at least one cat for patrol');
                }
                
                $result = deployNightPatrol($userId, $catIds, $patrolArea);
                if ($result['success']) {
                    $successMessage = $result['message'];
                } else {
                    throw new Exception($result['message']);
                }
                break;
                
            case 'create_zone':
                $zoneType = $_POST['zone_type'] ?? '';
                $zoneName = $_POST['zone_name'] ?? '';
                $zoneLocation = $_POST['zone_location'] ?? '';
                $zoneRadius = (int)($_POST['zone_radius'] ?? 50);
                
                if (empty($zoneType) || empty($zoneName) || empty($zoneLocation)) {
                    throw new Exception('Please fill in all zone details');
                }
                
                $result = createProtectionZone($userId, $zoneType, [
                    'name' => $zoneName,
                    'location' => $zoneLocation,
                    'radius' => $zoneRadius
                ]);
                
                if ($result['success']) {
                    $successMessage = $result['message'];
                } else {
                    throw new Exception($result['message']);
                }
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// Get user's cats for patrol selection
$userCats = getUserCats($userId);
$nightWatchStats = getNightWatchStats($userId);
$protectionZones = getUserProtectionZones($userId);
$recentPatrols = getRecentNightPatrols($userId);

// Check if it's currently night time
$isNightTime = isNightTime();
$currentTime = date('H:i');
$nightStart = NIGHT_WATCH_HOURS['start'] . ':00';
$nightEnd = NIGHT_WATCH_HOURS['end'] . ':00';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌙 Night Watch: Save the Strays - Purrr.love</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .night-gradient {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }
        .moon-glow {
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.3);
        }
        .patrol-card {
            background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
            border: 2px solid #4a5568;
        }
        .protection-zone {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            border: 2px solid #48bb78;
        }
        .bobcat-alert {
            background: linear-gradient(135deg, #742a2a 0%, #4a1c1c 100%);
            border: 2px solid #f56565;
        }
    </style>
</head>
<body class="night-gradient min-h-screen text-white">
    <!-- Navigation -->
    <nav class="bg-black bg-opacity-50 backdrop-blur-sm border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-purple-400">🐱 Purrr.love</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/" class="text-gray-300 hover:text-white">🏠 Home</a>
                    <a href="/cats.php" class="text-gray-300 hover:text-white">🐱 Cats</a>
                    <a href="/night_watch.php" class="text-purple-400 font-semibold">🌙 Night Watch</a>
                    <a href="/logout.php" class="text-gray-300 hover:text-white">🚪 Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-block p-6 bg-white bg-opacity-10 rounded-full moon-glow mb-6">
                <i class="fas fa-moon text-6xl text-yellow-300"></i>
            </div>
            <h1 class="text-5xl font-bold text-white mb-4">🌙 Night Watch: Save the Strays</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Deploy your guardian cats to patrol neighborhoods and protect stray cats from bobcat attacks. 
                Inspired by BanditCat's story of being saved from euthanasia - now he saves others!
            </p>
            
            <!-- Time Status -->
            <div class="mt-6 p-4 rounded-lg <?php echo $isNightTime ? 'bg-green-900 bg-opacity-50 border border-green-500' : 'bg-red-900 bg-opacity-50 border border-red-500'; ?>">
                <div class="flex items-center justify-center space-x-2">
                    <i class="fas fa-clock text-2xl <?php echo $isNightTime ? 'text-green-400' : 'text-red-400'; ?>"></i>
                    <span class="text-lg font-semibold">
                        Current Time: <?php echo $currentTime; ?> | 
                        Night Watch Hours: <?php echo $nightStart; ?> - <?php echo $nightEnd; ?>
                    </span>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $isNightTime ? 'bg-green-600 text-white' : 'bg-red-600 text-white'; ?>">
                        <?php echo $isNightTime ? '🌙 ACTIVE' : '☀️ INACTIVE'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($successMessage)): ?>
            <div class="bg-green-900 bg-opacity-50 border border-green-500 text-green-200 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="bg-red-900 bg-opacity-50 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <!-- Night Watch Stats -->
        <?php if ($nightWatchStats): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white bg-opacity-10 rounded-lg p-6 text-center border border-gray-600">
                <i class="fas fa-shield-alt text-4xl text-blue-400 mb-4"></i>
                <div class="text-3xl font-bold text-white"><?php echo $nightWatchStats['protection_level']; ?></div>
                <div class="text-gray-300">Protection Level</div>
            </div>
            
            <div class="bg-white bg-opacity-10 rounded-lg p-6 text-center border border-gray-600">
                <i class="fas fa-cat text-4xl text-green-400 mb-4"></i>
                <div class="text-3xl font-bold text-white"><?php echo $nightWatchStats['total_cats_saved']; ?></div>
                <div class="text-gray-300">Cats Saved</div>
            </div>
            
            <div class="bg-white bg-opacity-10 rounded-lg p-6 text-center border border-gray-600">
                <i class="fas fa-paw text-4xl text-orange-400 mb-4"></i>
                <div class="text-3xl font-bold text-white"><?php echo $nightWatchStats['total_bobcat_encounters']; ?></div>
                <div class="text-gray-300">Bobcat Encounters</div>
            </div>
            
            <div class="bg-white bg-opacity-10 rounded-lg p-6 text-center border border-gray-600">
                <i class="fas fa-star text-4xl text-yellow-400 mb-4"></i>
                <div class="text-3xl font-bold text-white"><?php echo $nightWatchStats['community_reputation']; ?></div>
                <div class="text-gray-300">Community Reputation</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Deploy Night Patrol -->
        <div class="patrol-card rounded-lg p-8 mb-8">
            <h2 class="text-3xl font-bold text-white mb-6 flex items-center">
                <i class="fas fa-route text-purple-400 mr-3"></i>
                Deploy Night Patrol
            </h2>
            
            <?php if ($isNightTime): ?>
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="deploy_patrol">
                    
                    <!-- Cat Selection -->
                    <div>
                        <label class="block text-lg font-semibold text-gray-300 mb-3">
                            Select Guardian Cats for Patrol
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($userCats as $cat): ?>
                                <label class="flex items-center p-4 bg-white bg-opacity-5 rounded-lg border border-gray-600 hover:border-purple-500 cursor-pointer transition-colors">
                                    <input type="checkbox" name="cat_ids[]" value="<?php echo $cat['id']; ?>" class="mr-3">
                                    <div>
                                        <div class="font-semibold text-white"><?php echo $cat['name']; ?></div>
                                        <div class="text-sm text-gray-400">
                                            <?php echo ucfirst($cat['personality_type']); ?> • Level <?php echo $cat['level']; ?>
                                        </div>
                                        <?php if ($cat['special_cat_id']): ?>
                                            <div class="text-xs text-yellow-400 font-semibold">
                                                ⭐ Special Cat: <?php echo ucfirst($cat['special_cat_id']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Patrol Area -->
                    <div>
                        <label class="block text-lg font-semibold text-gray-300 mb-3">
                            Patrol Area
                        </label>
                        <select name="patrol_area" class="w-full p-3 bg-white bg-opacity-10 border border-gray-600 rounded-lg text-white">
                            <option value="neighborhood">🏘️ Neighborhood</option>
                            <option value="park">🌳 Park</option>
                            <option value="alley">🏚️ Alley</option>
                            <option value="pizzeria">🍕 Pizzeria Area</option>
                            <option value="downtown">🏙️ Downtown</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 px-6 rounded-lg text-lg transition-colors">
                        <i class="fas fa-moon mr-2"></i>
                        Deploy Night Patrol
                    </button>
                </form>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-sun text-6xl text-yellow-400 mb-4"></i>
                    <h3 class="text-2xl font-bold text-white mb-2">Night Patrols Not Available</h3>
                    <p class="text-gray-300">
                        Night patrols can only be deployed between 9 PM and 6 AM. 
                        Come back when it's dark to protect the strays!
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Protection Zones -->
        <div class="protection-zone rounded-lg p-8 mb-8">
            <h2 class="text-3xl font-bold text-white mb-6 flex items-center">
                <i class="fas fa-shield-alt text-green-400 mr-3"></i>
                Protection Zones
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Create New Zone -->
                <div>
                    <h3 class="text-xl font-semibold text-white mb-4">Create New Protection Zone</h3>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="create_zone">
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-2">Zone Type</label>
                            <select name="zone_type" class="w-full p-3 bg-white bg-opacity-10 border border-gray-600 rounded-lg text-white">
                                <option value="cat_condo">🏠 Cat Condo (500 coins)</option>
                                <option value="motion_sensor">📡 Motion Sensor (200 coins)</option>
                                <option value="safe_haven">🛡️ Safe Haven (300 coins)</option>
                                <option value="community_alert">📢 Community Alert (150 coins)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-2">Zone Name</label>
                            <input type="text" name="zone_name" placeholder="e.g., Home Base, Park Shelter" 
                                   class="w-full p-3 bg-white bg-opacity-10 border border-gray-600 rounded-lg text-white placeholder-gray-400">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-2">Location</label>
                            <input type="text" name="zone_location" placeholder="e.g., Central Park, Downtown" 
                                   class="w-full p-3 bg-white bg-opacity-10 border border-gray-600 rounded-lg text-white placeholder-gray-400">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-300 mb-2">Protection Radius (meters)</label>
                            <input type="number" name="zone_radius" value="50" min="25" max="200" 
                                   class="w-full p-3 bg-white bg-opacity-10 border border-gray-600 rounded-lg text-white">
                        </div>
                        
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Create Protection Zone
                        </button>
                    </form>
                </div>
                
                <!-- Existing Zones -->
                <div>
                    <h3 class="text-xl font-semibold text-white mb-4">Your Protection Zones</h3>
                    <?php if (!empty($protectionZones)): ?>
                        <div class="space-y-4">
                            <?php foreach ($protectionZones as $zone): ?>
                                <div class="bg-white bg-opacity-5 rounded-lg p-4 border border-gray-600">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-semibold text-white"><?php echo $zone['name']; ?></h4>
                                        <span class="px-2 py-1 bg-green-600 text-white text-xs rounded-full">
                                            <?php echo ucfirst($zone['zone_type']); ?>
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-400 mb-2">
                                        📍 <?php echo $zone['location']; ?> • 
                                        🛡️ <?php echo round($zone['protection_level'] * 100); ?>% protection
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Radius: <?php echo $zone['radius']; ?>m • 
                                        Created: <?php echo date('M j, Y', strtotime($zone['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-shield-alt text-4xl mb-4"></i>
                            <p>No protection zones created yet.</p>
                            <p class="text-sm">Create your first zone to start protecting strays!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Patrols -->
        <?php if (!empty($recentPatrols)): ?>
        <div class="patrol-card rounded-lg p-8 mb-8">
            <h2 class="text-3xl font-bold text-white mb-6 flex items-center">
                <i class="fas fa-history text-blue-400 mr-3"></i>
                Recent Night Patrols
            </h2>
            
            <div class="space-y-4">
                <?php foreach ($recentPatrols as $patrol): ?>
                    <div class="bg-white bg-opacity-5 rounded-lg p-4 border border-gray-600">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-semibold text-white">
                                <?php echo ucfirst($patrol['patrol_area']); ?> Patrol
                            </h4>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold 
                                       <?php echo $patrol['status'] === 'active' ? 'bg-green-600 text-white' : 'bg-gray-600 text-white'; ?>">
                                <?php echo ucfirst($patrol['status']); ?>
                            </span>
                        </div>
                        
                        <div class="text-sm text-gray-400 mb-2">
                            🕐 Started: <?php echo date('M j, Y g:i A', strtotime($patrol['start_time'])); ?>
                            <?php if ($patrol['end_time']): ?>
                                • Ended: <?php echo date('M j, Y g:i A', strtotime($patrol['end_time'])); ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($patrol['results']): ?>
                            <?php $results = json_decode($patrol['results'], true); ?>
                            <div class="text-sm text-gray-300">
                                <?php if (isset($results['cats_saved'])): ?>
                                    <span class="text-green-400">🐱 Cats Saved: <?php echo $results['cats_saved']; ?></span>
                                <?php endif; ?>
                                <?php if (isset($results['bobcat_deterred']) && $results['bobcat_deterred']): ?>
                                    <span class="text-orange-400 ml-4">🦁 Bobcat Deterred!</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- BanditCat Story -->
        <div class="bg-gradient-to-r from-yellow-900 to-orange-900 rounded-lg p-8 border border-yellow-600">
            <h2 class="text-3xl font-bold text-white mb-6 flex items-center">
                <i class="fas fa-star text-yellow-400 mr-3"></i>
                BanditCat's Story: From Rescued to Rescuer
            </h2>
            
            <div class="text-lg text-yellow-100 leading-relaxed">
                <p class="mb-4">
                    <strong>BanditCat</strong> knows what it's like to face death. When he was young, his owners wanted to put him down. 
                    But RyCat saved him, discovered his health issues, and gave him a second chance at life.
                </p>
                
                <p class="mb-4">
                    Now, every night, BanditCat patrols the neighborhood, protecting stray cats from the same fate he narrowly escaped. 
                    He's not just a cat - he's a guardian angel with a mission.
                </p>
                
                <div class="bg-white bg-opacity-10 rounded-lg p-4 mt-6">
                    <h3 class="text-xl font-semibold text-yellow-200 mb-2">BanditCat's Special Abilities:</h3>
                    <ul class="text-yellow-100 space-y-1">
                        <li>🛡️ <strong>Guardian Instinct:</strong> +100% protection bonus against bobcat attacks</li>
                        <li>🐱 <strong>Stray Savior:</strong> Can rescue cats from dangerous situations</li>
                        <li>🦁 <strong>Bobcat Deterrence Max:</strong> Maximum bobcat scare factor</li>
                        <li>⚡ <strong>Emergency Response Max:</strong> Fastest response time</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh patrol status every 30 seconds
        setInterval(function() {
            if (window.location.pathname === '/night_watch.php') {
                location.reload();
            }
        }, 30000);
        
        // Cat selection enhancement
        document.querySelectorAll('input[name="cat_ids[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const card = this.closest('label');
                if (this.checked) {
                    card.classList.add('border-purple-500', 'bg-purple-900', 'bg-opacity-20');
                } else {
                    card.classList.remove('border-purple-500', 'bg-purple-900', 'bg-opacity-20');
                }
            });
        });
    </script>
</body>
</html>
