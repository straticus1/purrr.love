<?php
/**
 * 🏥 Health Monitoring Dashboard
 * Real-time health tracking and monitoring for cats
 */

session_start();
require_once '../includes/functions.php';
require_once '../includes/authentication.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    try {
        $user = getUserById($_SESSION['user_id']);
    } catch (Exception $e) {
        session_destroy();
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}

// Fetch user's cats for health monitoring
$cats = [];
try {
    $stmt = $pdo->prepare("
        SELECT id, name, breed, health, happiness, energy, hunger, cleanliness, 
               last_health_check, weight, temperature, heart_rate
        FROM cats 
        WHERE owner_id = ? 
        ORDER BY health ASC, last_health_check ASC
    ");
    $stmt->execute([$user['id']]);
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Failed to fetch cats: " . $e->getMessage();
}

// Handle health check updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_health'])) {
    $catId = $_POST['cat_id'] ?? '';
    $health = $_POST['health'] ?? 0;
    $happiness = $_POST['happiness'] ?? 0;
    $energy = $_POST['energy'] ?? 0;
    $hunger = $_POST['hunger'] ?? 0;
    $cleanliness = $_POST['cleanliness'] ?? 0;
    $weight = $_POST['weight'] ?? 0;
    $temperature = $_POST['temperature'] ?? 0;
    $heartRate = $_POST['heart_rate'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    
    try {
        // Update cat health
        $stmt = $pdo->prepare("
            UPDATE cats 
            SET health = ?, happiness = ?, energy = ?, hunger = ?, cleanliness = ?,
                weight = ?, temperature = ?, heart_rate = ?, last_health_check = NOW()
            WHERE id = ? AND owner_id = ?
        ");
        $stmt->execute([$health, $happiness, $energy, $hunger, $cleanliness, 
                       $weight, $temperature, $heartRate, $catId, $user['id']]);
        
        // Log health check
        $stmt = $pdo->prepare("
            INSERT INTO health_logs (cat_id, health, happiness, energy, hunger, cleanliness,
                                   weight, temperature, heart_rate, notes, recorded_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$catId, $health, $happiness, $energy, $hunger, $cleanliness,
                       $weight, $temperature, $heartRate, $notes]);
        
        $success = "Health check updated successfully!";
        
        // Refresh cats data
        $stmt = $pdo->prepare("
            SELECT id, name, breed, health, happiness, energy, hunger, cleanliness, 
                   last_health_check, weight, temperature, heart_rate
            FROM cats 
            WHERE owner_id = ? 
            ORDER BY health ASC, last_health_check ASC
        ");
        $stmt->execute([$user['id']]);
        $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $error = "Failed to update health: " . $e->getMessage();
    }
}

// Calculate health statistics
$totalCats = count($cats);
$healthyCats = count(array_filter($cats, fn($cat) => $cat['health'] >= 80));
$atRiskCats = count(array_filter($cats, fn($cat) => $cat['health'] < 60));
$averageHealth = $totalCats > 0 ? round(array_sum(array_column($cats, 'health')) / $totalCats) : 0;
$needsAttention = count(array_filter($cats, fn($cat) => 
    $cat['health'] < 70 || $cat['happiness'] < 60 || $cat['energy'] < 50
));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏥 Health Monitoring - Purrr.love</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .health-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .health-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
        }
        
        .health-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .health-excellent { background-color: #10b981; }
        .health-good { background-color: #3b82f6; }
        .health-fair { background-color: #f59e0b; }
        .health-poor { background-color: #ef4444; }
        .health-critical { background-color: #7c2d12; }
        
        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes pulse-glow {
            from { box-shadow: 0 0 20px rgba(16, 185, 129, 0.5); }
            to { box-shadow: 0 0 30px rgba(16, 185, 129, 0.8); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/navigation.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                🏥 Health Monitoring Dashboard
            </h1>
            <p class="text-gray-600">
                Monitor your cats' health, track vital signs, and get early warning alerts
            </p>
        </div>

        <!-- Health Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 health-card">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-heartbeat text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Cats</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $totalCats ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 health-card">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Healthy Cats</p>
                        <p class="text-2xl font-bold text-green-600"><?= $healthyCats ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 health-card">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">At Risk</p>
                        <p class="text-2xl font-bold text-yellow-600"><?= $atRiskCats ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 health-card">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Avg Health</p>
                        <p class="text-2xl font-bold text-purple-600"><?= $averageHealth ?>%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Alerts -->
        <?php if ($needsAttention > 0): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-8">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-full">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-yellow-800">
                        Health Alert
                    </h3>
                    <p class="text-yellow-700">
                        <?= $needsAttention ?> cat(s) need attention. Check their health status below.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cat Health Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Health Overview -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">
                    <i class="fas fa-chart-bar mr-2 text-purple-600"></i>
                    Health Overview
                </h2>
                
                <?php if (empty($cats)): ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-cat text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500">No cats found. Add some cats to start monitoring their health!</p>
                    <a href="cats.php" class="mt-4 inline-block bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                        Add Cats
                    </a>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($cats as $cat): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-medium text-gray-900"><?= htmlspecialchars($cat['name']) ?></h3>
                            <span class="text-sm text-gray-500"><?= htmlspecialchars($cat['breed'] ?? 'Unknown') ?></span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-3">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Health</p>
                                <div class="flex items-center">
                                    <span class="health-indicator health-<?= $cat['health'] >= 80 ? 'excellent' : ($cat['health'] >= 60 ? 'good' : ($cat['health'] >= 40 ? 'fair' : ($cat['health'] >= 20 ? 'poor' : 'critical'))) ?>"></span>
                                    <span class="text-sm font-medium"><?= $cat['health'] ?>%</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Happiness</p>
                                <div class="flex items-center">
                                    <span class="health-indicator health-<?= $cat['happiness'] >= 80 ? 'excellent' : ($cat['happiness'] >= 60 ? 'good' : ($cat['happiness'] >= 40 ? 'fair' : ($cat['happiness'] >= 20 ? 'poor' : 'critical'))) ?>"></span>
                                    <span class="text-sm font-medium"><?= $cat['happiness'] ?>%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">
                                Last check: <?= $cat['last_health_check'] ? date('M j, Y', strtotime($cat['last_health_check'])) : 'Never' ?>
                            </span>
                            <button onclick="openHealthModal(<?= htmlspecialchars(json_encode($cat)) ?>)" 
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Update Health
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Health Trends Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">
                    <i class="fas fa-chart-line mr-2 text-green-600"></i>
                    Health Trends
                </h2>
                
                <div class="h-64">
                    <canvas id="healthChart"></canvas>
                </div>
                
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-500">
                        Health trends over the last 30 days
                    </p>
                </div>
            </div>
        </div>

        <!-- Health Tips -->
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-lightbulb mr-2 text-blue-600"></i>
                Health Tips
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-utensils text-blue-600"></i>
                    </div>
                    <h3 class="font-medium text-gray-900 mb-2">Proper Nutrition</h3>
                    <p class="text-sm text-gray-600">
                        Feed your cats high-quality food and maintain regular feeding schedules
                    </p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-running text-green-600"></i>
                    </div>
                    <h3 class="font-medium text-gray-900 mb-2">Regular Exercise</h3>
                    <p class="text-sm text-gray-600">
                        Engage in daily play sessions to keep your cats active and healthy
                    </p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-stethoscope text-purple-600"></i>
                    </div>
                    <h3 class="font-medium text-gray-900 mb-2">Veterinary Care</h3>
                    <p class="text-sm text-gray-600">
                        Schedule regular check-ups and vaccinations for preventive care
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Update Modal -->
    <div id="healthModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Update Health Check
                    </h3>
                    
                    <form id="healthForm" method="POST">
                        <input type="hidden" id="catId" name="cat_id">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Health (%)</label>
                                <input type="range" name="health" id="health" min="0" max="100" class="w-full" oninput="updateHealthValue(this)">
                                <span id="healthValue" class="text-sm text-gray-500">50%</span>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Happiness (%)</label>
                                <input type="range" name="happiness" id="happiness" min="0" max="100" class="w-full" oninput="updateHappinessValue(this)">
                                <span id="happinessValue" class="text-sm text-gray-500">50%</span>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Energy (%)</label>
                                <input type="range" name="energy" id="energy" min="0" max="100" class="w-full" oninput="updateEnergyValue(this)">
                                <span id="energyValue" class="text-sm text-gray-500">50%</span>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hunger (%)</label>
                                <input type="range" name="hunger" id="hunger" min="0" max="100" class="w-full" oninput="updateHungerValue(this)">
                                <span id="hungerValue" class="text-sm text-gray-500">50%</span>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cleanliness (%)</label>
                                <input type="range" name="cleanliness" id="cleanliness" min="0" max="100" class="w-full" oninput="updateCleanlinessValue(this)">
                                <span id="cleanlinessValue" class="text-sm text-gray-500">50%</span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                                    <input type="number" name="weight" id="weight" step="0.1" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Temperature (°C)</label>
                                    <input type="number" name="temperature" id="temperature" step="0.1" min="35" max="42" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Heart Rate (bpm)</label>
                                <input type="number" name="heart_rate" id="heartRate" min="60" max="200" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="notes" id="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Any observations or concerns..."></textarea>
                            </div>
                        </div>
                        
                        <div class="flex space-x-3 mt-6">
                            <button type="submit" name="update_health" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                Update Health
                            </button>
                            <button type="button" onclick="closeHealthModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Health Chart
        const ctx = document.getElementById('healthChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    label: 'Average Health',
                    data: [85, 87, 82, 89],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Average Happiness',
                    data: [78, 82, 79, 85],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // Health Modal Functions
        function openHealthModal(cat) {
            document.getElementById('catId').value = cat.id;
            document.getElementById('health').value = cat.health;
            document.getElementById('happiness').value = cat.happiness;
            document.getElementById('energy').value = cat.energy;
            document.getElementById('hunger').value = cat.hunger;
            document.getElementById('cleanliness').value = cat.cleanliness;
            document.getElementById('weight').value = cat.weight || '';
            document.getElementById('temperature').value = cat.temperature || '';
            document.getElementById('heartRate').value = cat.heart_rate || '';
            
            updateHealthValue(document.getElementById('health'));
            updateHappinessValue(document.getElementById('happiness'));
            updateEnergyValue(document.getElementById('energy'));
            updateHungerValue(document.getElementById('hunger'));
            updateCleanlinessValue(document.getElementById('cleanliness'));
            
            document.getElementById('healthModal').classList.remove('hidden');
        }

        function closeHealthModal() {
            document.getElementById('healthModal').classList.add('hidden');
        }

        function updateHealthValue(input) {
            document.getElementById('healthValue').textContent = input.value + '%';
        }

        function updateHappinessValue(input) {
            document.getElementById('happinessValue').textContent = input.value + '%';
        }

        function updateEnergyValue(input) {
            document.getElementById('energyValue').textContent = input.value + '%';
        }

        function updateHungerValue(input) {
            document.getElementById('hungerValue').textContent = input.value + '%';
        }

        function updateCleanlinessValue(input) {
            document.getElementById('cleanlinessValue').textContent = input.value + '%';
        }

        // Close modal when clicking outside
        document.getElementById('healthModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeHealthModal();
            }
        });
    </script>
</body>
</html>
