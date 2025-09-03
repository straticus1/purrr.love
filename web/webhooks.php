<?php
/**
 * 🐱 Purrr.love - Webhook Management
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

$message = '';
$error = '';

// Handle webhook actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['webhook_action'])) {
        $webhookAction = $_POST['webhook_action'];
        
        switch ($webhookAction) {
            case 'create_webhook':
                $webhookName = trim($_POST['webhook_name'] ?? '');
                $webhookUrl = trim($_POST['webhook_url'] ?? '');
                $webhookEvents = $_POST['webhook_events'] ?? [];
                $webhookSecret = trim($_POST['webhook_secret'] ?? '');
                
                if ($webhookName && $webhookUrl && !empty($webhookEvents)) {
                    try {
                        // Simulate webhook creation
                        $webhookId = 'WH_' . strtoupper(substr(md5($webhookName . time()), 0, 8));
                        $message = "Webhook '$webhookName' created successfully! Webhook ID: $webhookId";
                        
                    } catch (Exception $e) {
                        $error = 'Error creating webhook: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please provide webhook name, URL, and select at least one event type';
                }
                break;
                
            case 'test_webhook':
                $webhookId = $_POST['webhook_id'] ?? '';
                
                if ($webhookId) {
                    try {
                        // Simulate webhook test
                        $message = "Test event sent to webhook $webhookId successfully!";
                        
                    } catch (Exception $e) {
                        $error = 'Error testing webhook: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please provide webhook ID';
                }
                break;
                
            case 'delete_webhook':
                $webhookId = $_POST['webhook_id'] ?? '';
                
                if ($webhookId) {
                    try {
                        // Simulate webhook deletion
                        $message = "Webhook $webhookId deleted successfully!";
                        
                    } catch (Exception $e) {
                        $error = 'Error deleting webhook: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please provide webhook ID';
                }
                break;
        }
    }
}

// Available webhook events
$webhookEvents = [
    'cat_created' => 'Cat Created',
    'cat_updated' => 'Cat Updated',
    'cat_deleted' => 'Cat Deleted',
    'game_played' => 'Game Played',
    'item_purchased' => 'Item Purchased',
    'nft_minted' => 'NFT Minted',
    'nft_transferred' => 'NFT Transferred',
    'personality_analyzed' => 'Personality Analyzed',
    'world_joined' => 'World Joined',
    'vr_interaction' => 'VR Interaction',
    'lost_pet_reported' => 'Lost Pet Reported',
    'pet_sighting' => 'Pet Sighting',
    'user_registered' => 'User Registered',
    'user_login' => 'User Login',
    'system_maintenance' => 'System Maintenance'
];

// Simulate existing webhooks for the user
$userWebhooks = [
    [
        'id' => 'WH_MAIN',
        'name' => 'Main Integration',
        'url' => 'https://api.example.com/webhooks/purrr',
        'events' => ['cat_created', 'game_played', 'nft_minted'],
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
        'last_delivery' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'delivery_count' => 156,
        'success_rate' => 98.5
    ],
    [
        'id' => 'WH_NOTIFICATIONS',
        'name' => 'Push Notifications',
        'url' => 'https://notifications.example.com/webhook',
        'events' => ['cat_updated', 'item_purchased', 'personality_analyzed'],
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
        'last_delivery' => date('Y-m-d H:i:s', strtotime('-45 minutes')),
        'delivery_count' => 89,
        'success_rate' => 99.2
    ]
];

// Simulate webhook delivery logs
$webhookLogs = [
    [
        'webhook_id' => 'WH_MAIN',
        'event_type' => 'cat_created',
        'status' => 'delivered',
        'response_code' => 200,
        'delivery_time' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'payload_size' => '2.3 KB'
    ],
    [
        'webhook_id' => 'WH_NOTIFICATIONS',
        'event_type' => 'personality_analyzed',
        'status' => 'delivered',
        'response_code' => 200,
        'delivery_time' => date('Y-m-d H:i:s', strtotime('-45 minutes')),
        'payload_size' => '1.8 KB'
    ],
    [
        'webhook_id' => 'WH_MAIN',
        'event_type' => 'nft_minted',
        'status' => 'failed',
        'response_code' => 500,
        'delivery_time' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'payload_size' => '3.1 KB'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 Webhook Management - Purrr.love</title>
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
                            <a href="metaverse-vr.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-vr-cardboard mr-2"></i>Metaverse
                            </a>
                            <a href="webhooks.php" class="bg-purple-100 text-purple-700 px-3 py-2 rounded-md text-sm font-medium">
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
            <h1 class="text-4xl font-bold text-gray-900 mb-4">🔗 Webhook Management</h1>
            <p class="text-xl text-gray-600">Manage real-time notifications and third-party integrations</p>
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

        <!-- Webhook Creation -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 max-w-4xl mx-auto">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Create New Webhook</h2>
            
            <form method="POST" class="space-y-6">
                <input type="hidden" name="webhook_action" value="create_webhook">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="webhook_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Webhook Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="webhook_name" name="webhook_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Enter webhook name">
                    </div>

                    <div>
                        <label for="webhook_url" class="block text-sm font-medium text-gray-700 mb-2">
                            Webhook URL <span class="text-red-500">*</span>
                        </label>
                        <input type="url" id="webhook_url" name="webhook_url" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="https://your-domain.com/webhook">
                    </div>
                </div>

                <div>
                    <label for="webhook_secret" class="block text-sm font-medium text-gray-700 mb-2">
                        Webhook Secret
                    </label>
                    <input type="text" id="webhook_secret" name="webhook_secret"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Optional secret for signature verification">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate a secure secret</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Event Types <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <?php foreach ($webhookEvents as $value => $label): ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="webhook_events[]" value="<?= $value ?>"
                                   class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700"><?= $label ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Create Webhook
                </button>
            </form>

            <!-- Webhook Tips -->
            <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-2">💡 Webhook Tips</h4>
                <p class="text-sm text-gray-700">
                    Webhooks allow you to receive real-time notifications when events occur in Purrr.love. 
                    Your endpoint should respond with a 2xx status code to acknowledge receipt.
                </p>
            </div>
        </div>

        <!-- Existing Webhooks -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Your Webhooks (<?= count($userWebhooks) ?>)</h3>
            
            <?php if (empty($userWebhooks)): ?>
            <div class="text-center py-8">
                <div class="text-gray-400 text-4xl mb-4">
                    <i class="fas fa-link"></i>
                </div>
                <p class="text-gray-600">No webhooks configured yet</p>
                <p class="text-sm text-gray-500 mt-2">Create your first webhook to start receiving notifications</p>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($userWebhooks as $webhook): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($webhook['name']) ?></h4>
                            <p class="text-sm text-gray-600 font-mono"><?= htmlspecialchars($webhook['url']) ?></p>
                            <p class="text-xs text-gray-500 mt-1">ID: <?= $webhook['id'] ?></p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <?= ucfirst($webhook['status']) ?>
                            </span>
                            <p class="text-xs text-gray-500 mt-1">
                                Success Rate: <?= $webhook['success_rate'] ?>%
                            </p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3 text-sm">
                        <div>
                            <span class="text-gray-600">Events:</span>
                            <span class="text-gray-900"><?= count($webhook['events']) ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Deliveries:</span>
                            <span class="text-gray-900"><?= $webhook['delivery_count'] ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Created:</span>
                            <span class="text-gray-900"><?= date('M j, Y', strtotime($webhook['created_at'])) ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Last Delivery:</span>
                            <span class="text-gray-900"><?= date('M j, Y g:i A', strtotime($webhook['last_delivery'])) ?></span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <form method="POST" class="inline-block">
                            <input type="hidden" name="webhook_action" value="test_webhook">
                            <input type="hidden" name="webhook_id" value="<?= $webhook['id'] ?>">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition duration-300">
                                <i class="fas fa-play mr-1"></i>Test
                            </button>
                        </form>
                        
                        <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this webhook?')">
                            <input type="hidden" name="webhook_action" value="delete_webhook">
                            <input type="hidden" name="webhook_id" value="<?= $webhook['id'] ?>">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition duration-300">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Webhook Delivery Logs -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Recent Delivery Logs</h3>
            
            <?php if (empty($webhookLogs)): ?>
            <div class="text-center py-8">
                <div class="text-gray-400 text-4xl mb-4">
                    <i class="fas fa-file-alt"></i>
                </div>
                <p class="text-gray-600">No delivery logs yet</p>
                <p class="text-sm text-gray-500 mt-2">Logs will appear here once webhooks start delivering events</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Webhook</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($webhookLogs as $log): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= $log['webhook_id'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= ucwords(str_replace('_', ' ', $log['event_type'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $log['status'] === 'delivered' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= ucfirst($log['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= $log['response_code'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M j, g:i A', strtotime($log['delivery_time'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $log['payload_size'] ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Webhook Information -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 max-w-4xl mx-auto">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">🔗 How Webhooks Work</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-700">
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Event Delivery</h4>
                    <ul class="space-y-1">
                        <li>• Real-time HTTP POST requests</li>
                        <li>• JSON payload with event data</li>
                        <li>• Automatic retry on failure</li>
                        <li>• Signature verification support</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Security Features</h4>
                    <ul class="space-y-1">
                        <li>• HMAC signature verification</li>
                        <li>• IP whitelisting support</li>
                        <li>• Rate limiting protection</li>
                        <li>• Secure secret management</li>
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

