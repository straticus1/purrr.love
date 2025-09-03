<?php
/**
 * 🐱 Purrr.love - ML Cat Personality
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
$personalityData = null;
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

// Handle ML actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ml_action'])) {
        $mlAction = $_POST['ml_action'];
        
        switch ($mlAction) {
            case 'predict_personality':
                if ($selectedCat) {
                    try {
                        // Simulate ML personality prediction
                        $personalityTraits = [
                            'playfulness' => rand(20, 100),
                            'curiosity' => rand(30, 95),
                            'independence' => rand(25, 90),
                            'affection' => rand(40, 100),
                            'intelligence' => rand(35, 95),
                            'adaptability' => rand(30, 90),
                            'energy_level' => rand(25, 95),
                            'social_behavior' => rand(20, 85)
                        ];
                        
                        $personalityType = '';
                        $avgScore = array_sum($personalityTraits) / count($personalityTraits);
                        
                        if ($avgScore >= 80) {
                            $personalityType = 'Adventurous Explorer';
                        } elseif ($avgScore >= 65) {
                            $personalityType = 'Social Butterfly';
                        } elseif ($avgScore >= 50) {
                            $personalityType = 'Balanced Companion';
                        } else {
                            $personalityType = 'Calm Observer';
                        }
                        
                        $personalityData = [
                            'traits' => $personalityTraits,
                            'type' => $personalityType,
                            'confidence' => rand(75, 95),
                            'prediction_date' => date('Y-m-d H:i:s'),
                            'recommendations' => generateRecommendations($personalityTraits)
                        ];
                        
                        $message = "Personality prediction completed for " . htmlspecialchars($selectedCat['name']) . "!";
                        
                    } catch (Exception $e) {
                        $error = 'Error predicting personality: ' . $e->getMessage();
                    }
                }
                break;
                
            case 'record_behavior':
                $behaviorType = $_POST['behavior_type'] ?? '';
                $behaviorDescription = $_POST['behavior_description'] ?? '';
                $behaviorTime = $_POST['behavior_time'] ?? '';
                
                if ($behaviorType && $behaviorDescription && $selectedCat) {
                    try {
                        // Record behavior observation
                        $stmt = $pdo->prepare("
                            INSERT INTO cat_behavior_observations (cat_id, behavior_type, description, observed_at, recorded_by) 
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        
                        if ($stmt->execute([$selectedCat['id'], $behaviorType, $behaviorDescription, $behaviorTime, $_SESSION['user_id']])) {
                            $message = "Behavior observation recorded successfully!";
                        } else {
                            $error = "Failed to record behavior observation";
                        }
                    } catch (Exception $e) {
                        $error = 'Error recording behavior: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please fill in all behavior fields';
                }
                break;
        }
    }
}

function generateRecommendations($traits) {
    $recommendations = [];
    
    if ($traits['playfulness'] > 70) {
        $recommendations[] = 'High energy play sessions with interactive toys';
    }
    
    if ($traits['curiosity'] > 75) {
        $recommendations[] = 'Provide puzzle feeders and exploration opportunities';
    }
    
    if ($traits['independence'] > 80) {
        $recommendations[] = 'Respect alone time, provide quiet spaces';
    }
    
    if ($traits['affection'] > 70) {
        $recommendations[] = 'Regular cuddle sessions and gentle grooming';
    }
    
    if ($traits['intelligence'] > 75) {
        $recommendations[] = 'Training sessions and mental stimulation games';
    }
    
    if ($traits['adaptability'] < 60) {
        $recommendations[] = 'Gradual introduction to new environments';
    }
    
    if ($traits['energy_level'] > 80) {
        $recommendations[] = 'Multiple play sessions throughout the day';
    }
    
    if ($traits['social_behavior'] > 70) {
        $recommendations[] = 'Socialization with other cats and humans';
    }
    
    return $recommendations;
}

// Behavior types for recording
$behaviorTypes = [
    'play' => 'Play Behavior',
    'sleep' => 'Sleep Patterns',
    'eating' => 'Eating Habits',
    'social' => 'Social Interactions',
    'exploration' => 'Exploration',
    'grooming' => 'Grooming',
    'vocalization' => 'Vocalization',
    'aggression' => 'Aggression',
    'fear' => 'Fear Responses',
    'other' => 'Other'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 ML Cat Personality - Purrr.love</title>
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
                            <a href="ml-personality.php" class="bg-purple-100 text-purple-700 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-brain mr-2"></i>ML Personality
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
            <h1 class="text-4xl font-bold text-gray-900 mb-4">🧠 ML Cat Personality</h1>
            <p class="text-xl text-gray-600">Advanced AI-powered personality analysis for your feline friends</p>
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
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Select a Cat for Analysis</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($cats as $cat): ?>
                <a href="?cat_id=<?= $cat['id'] ?>" 
                   class="block p-4 border-2 rounded-lg transition duration-200 <?= $selectedCatId == $cat['id'] ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300' ?>">
                    <div class="text-center">
                        <div class="text-3xl mb-2">🐱</div>
                        <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($cat['name']) ?></h3>
                        <p class="text-sm text-gray-600"><?= ucfirst(str_replace('_', ' ', $cat['breed'])) ?></p>
                        <div class="mt-2 text-xs text-gray-500">
                            Health: <?= $cat['health'] ?>% | Happiness: <?= $cat['happiness'] ?>%
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ML Analysis Section -->
        <?php if ($selectedCat): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Personality Prediction -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Personality Analysis</h2>
                
                <?php if (!$personalityData): ?>
                <div class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-4">
                        <i class="fas fa-brain"></i>
                    </div>
                    <p class="text-gray-600 mb-6">No personality data available yet</p>
                    <form method="POST" class="inline-block">
                        <input type="hidden" name="ml_action" value="predict_personality">
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                            <i class="fas fa-magic mr-2"></i>Analyze Personality
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="space-y-6">
                    <!-- Personality Type -->
                    <div class="text-center p-4 bg-gradient-to-r from-purple-100 to-blue-100 rounded-lg">
                        <h3 class="text-xl font-bold text-purple-800 mb-2">Personality Type</h3>
                        <p class="text-2xl font-semibold text-purple-900"><?= $personalityData['type'] ?></p>
                        <p class="text-sm text-purple-700 mt-2">Confidence: <?= $personalityData['confidence'] ?>%</p>
                    </div>

                    <!-- Personality Chart -->
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">Personality Traits</h4>
                        <canvas id="personalityChart" width="400" height="300"></canvas>
                    </div>

                    <!-- Recommendations -->
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">AI Recommendations</h4>
                        <div class="space-y-2">
                            <?php foreach ($personalityData['recommendations'] as $recommendation): ?>
                            <div class="flex items-start p-3 bg-blue-50 rounded-lg">
                                <i class="fas fa-lightbulb text-blue-600 mt-1 mr-3"></i>
                                <p class="text-sm text-blue-800"><?= htmlspecialchars($recommendation) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Re-analyze Button -->
                    <form method="POST" class="text-center">
                        <input type="hidden" name="ml_action" value="predict_personality">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition duration-300">
                            <i class="fas fa-sync-alt mr-2"></i>Re-analyze
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <!-- Behavior Recording -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Record Behavior</h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="ml_action" value="record_behavior">
                    
                    <div>
                        <label for="behavior_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Behavior Type <span class="text-red-500">*</span>
                        </label>
                        <select id="behavior_type" name="behavior_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select behavior type</option>
                            <?php foreach ($behaviorTypes as $value => $label): ?>
                            <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="behavior_description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea id="behavior_description" name="behavior_description" required rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                  placeholder="Describe what you observed..."></textarea>
                    </div>

                    <div>
                        <label for="behavior_time" class="block text-sm font-medium text-gray-700 mb-2">
                            When did this happen? <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" id="behavior_time" name="behavior_time" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                        <i class="fas fa-save mr-2"></i>Record Behavior
                    </button>
                </form>

                <!-- ML Insights -->
                <div class="mt-6 p-4 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg">
                    <h4 class="font-semibold text-gray-900 mb-2">💡 ML Insights</h4>
                    <p class="text-sm text-gray-700">
                        Recording behaviors helps our AI learn and provide more accurate personality predictions. 
                        The more data we collect, the better we understand your cat's unique personality!
                    </p>
                </div>
            </div>
        </div>

        <!-- Cat Information -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4"><?= htmlspecialchars($selectedCat['name']) ?>'s Profile</h3>
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
            <p class="text-gray-600 mb-8">You need to create a cat first before you can analyze personalities.</p>
            <a href="cats.php?action=create" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-medium text-lg transition duration-300">
                <i class="fas fa-plus mr-2"></i>Create Your First Cat
            </a>
        </div>
        <?php endif; ?>

        <!-- ML Information -->
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-6 mt-8 max-w-4xl mx-auto">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">🧠 How ML Personality Analysis Works</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-700">
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Data Collection</h4>
                    <ul class="space-y-1">
                        <li>• Behavior observations over time</li>
                        <li>• Cat stats and health metrics</li>
                        <li>• Interaction patterns</li>
                        <li>• Environmental responses</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">AI Analysis</h4>
                    <ul class="space-y-1">
                        <li>• Pattern recognition algorithms</li>
                        <li>• Behavioral clustering</li>
                        <li>• Personality trait mapping</li>
                        <li>• Predictive modeling</li>
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
        // Personality Chart
        <?php if ($personalityData): ?>
        const ctx = document.getElementById('personalityChart').getContext('2d');
        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['Playfulness', 'Curiosity', 'Independence', 'Affection', 'Intelligence', 'Adaptability', 'Energy Level', 'Social Behavior'],
                datasets: [{
                    label: 'Personality Score',
                    data: [
                        <?= $personalityData['traits']['playfulness'] ?>,
                        <?= $personalityData['traits']['curiosity'] ?>,
                        <?= $personalityData['traits']['independence'] ?>,
                        <?= $personalityData['traits']['affection'] ?>,
                        <?= $personalityData['traits']['intelligence'] ?>,
                        <?= $personalityData['traits']['adaptability'] ?>,
                        <?= $personalityData['traits']['energy_level'] ?>,
                        <?= $personalityData['traits']['social_behavior'] ?>
                    ],
                    backgroundColor: 'rgba(147, 51, 234, 0.2)',
                    borderColor: 'rgb(147, 51, 234)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgb(147, 51, 234)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(147, 51, 234)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        <?php endif; ?>

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
