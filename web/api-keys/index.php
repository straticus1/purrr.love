<?php
/**
 * 🌐 Purrr.love Web Interface - API Key Management
 * Beautiful and intuitive web interface for managing API keys
 */

require_once '../../includes/functions.php';
require_once '../../includes/oauth2.php';
require_once '../../includes/api_keys.php';

// Start session
session_start();

// Check if user is logged in
if (!isUserLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$user = getCurrentUser();
$userId = $user['id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create_key':
                $keyData = [
                    'name' => $_POST['key_name'] ?? '',
                    'scopes' => $_POST['scopes'] ?? ['read'],
                    'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
                    'ip_whitelist' => !empty($_POST['ip_whitelist']) ? explode(',', $_POST['ip_whitelist']) : []
                ];
                
                $result = generateApiKey($userId, $keyData);
                $successMessage = "API key created successfully! Key: " . $result['api_key'];
                break;
                
            case 'revoke_key':
                $keyId = $_POST['key_id'] ?? 0;
                revokeApiKey($keyId, $userId);
                $successMessage = "API key revoked successfully!";
                break;
                
            case 'update_key':
                $keyId = $_POST['key_id'] ?? 0;
                $updateData = [
                    'name' => $_POST['key_name'] ?? '',
                    'scopes' => $_POST['scopes'] ?? ['read'],
                    'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
                    'ip_whitelist' => !empty($_POST['ip_whitelist']) ? explode(',', $_POST['ip_whitelist']) : []
                ];
                
                updateApiKey($keyId, $userId, $updateData);
                $successMessage = "API key updated successfully!";
                break;
                
            default:
                $errorMessage = "Invalid action";
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// Get user's API keys
$apiKeys = getUserApiKeys($userId);
$apiKeyStats = getUserApiKeyStats($userId);
$recentUsage = getRecentApiKeyUsage($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Key Management - Purrr.love</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .api-key {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 0.5rem;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
        }
        .scope-badge {
            background: #3b82f6;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            margin: 0.125rem;
        }
        .status-active {
            background: #10b981;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
        }
        .status-revoked {
            background: #ef4444;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="gradient-bg text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <i class="fas fa-cat text-3xl mr-3"></i>
                    <h1 class="text-2xl font-bold">Purrr.love</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/dashboard" class="hover:text-gray-200">Dashboard</a>
                    <a href="/cats" class="hover:text-gray-200">My Cats</a>
                    <a href="/api-keys" class="text-yellow-300 font-semibold">API Keys</a>
                    <a href="/logout" class="hover:text-gray-200">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">API Key Management</h2>
            <p class="text-gray-600">Manage your API keys for integrating with Purrr.love services</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($successMessage)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-key text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Keys</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $apiKeyStats['total_keys']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Keys</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $apiKeyStats['active_keys']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-chart-line text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Requests</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($apiKeyStats['total_requests']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">This Month</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($apiKeyStats['monthly_requests']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create New API Key -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Create New API Key</h3>
            </div>
            <div class="p-6">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="create_key">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="key_name" class="block text-sm font-medium text-gray-700 mb-2">Key Name</label>
                            <input type="text" id="key_name" name="key_name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="e.g., Mobile App, Web Dashboard">
                        </div>

                        <div>
                            <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">Expiration Date (Optional)</label>
                            <input type="datetime-local" id="expires_at" name="expires_at"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Scopes</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="scopes[]" value="read" checked
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Read</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="scopes[]" value="write"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Write</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="scopes[]" value="admin"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Admin</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="scopes[]" value="client"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Client</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="ip_whitelist" class="block text-sm font-medium text-gray-700 mb-2">IP Whitelist (Optional)</label>
                        <input type="text" id="ip_whitelist" name="ip_whitelist"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g., 192.168.1.1, 10.0.0.0/24">
                        <p class="mt-1 text-sm text-gray-500">Leave empty to allow all IPs. Separate multiple IPs with commas.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Create API Key
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- API Keys List -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Your API Keys</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scopes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($apiKeys)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-key text-4xl mb-4 block"></i>
                                    <p class="text-lg">No API keys created yet</p>
                                    <p class="text-sm">Create your first API key above to get started</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($apiKeys as $key): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($key['name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="api-key text-sm font-mono">
                                            <?php echo substr($key['key_hash'], 0, 8) . '...'; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap">
                                            <?php foreach (json_decode($key['scopes'], true) as $scope): ?>
                                                <span class="scope-badge"><?php echo htmlspecialchars($scope); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($key['active']): ?>
                                            <span class="status-active">Active</span>
                                        <?php else: ?>
                                            <span class="status-revoked">Revoked</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo number_format($key['usage_count'] ?? 0); ?> requests
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M j, Y', strtotime($key['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="editKey(<?php echo $key['id']; ?>)" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($key['active']): ?>
                                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to revoke this API key?')">
                                                    <input type="hidden" name="action" value="revoke_key">
                                                    <input type="hidden" name="key_id" value="<?php echo $key['id']; ?>">
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Usage -->
        <?php if (!empty($recentUsage)): ?>
            <div class="bg-white rounded-lg shadow mt-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent API Usage</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php foreach ($recentUsage as $usage): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div class="p-2 bg-blue-100 rounded-full">
                                        <i class="fas fa-globe text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($usage['endpoint']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($usage['method']); ?> • 
                                            <?php echo date('M j, Y g:i A', strtotime($usage['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                 <?php echo $usage['status_code'] < 400 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $usage['status_code']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Edit Key Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Edit API Key</h3>
                </div>
                <form id="editForm" method="POST" class="p-6 space-y-4">
                    <input type="hidden" name="action" value="update_key">
                    <input type="hidden" name="key_id" id="edit_key_id">
                    
                    <div>
                        <label for="edit_key_name" class="block text-sm font-medium text-gray-700 mb-2">Key Name</label>
                        <input type="text" id="edit_key_name" name="key_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Scopes</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="scopes[]" value="read" id="edit_scope_read"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Read</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="scopes[]" value="write" id="edit_scope_write"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Write</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="scopes[]" value="admin" id="edit_scope_admin"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Admin</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="scopes[]" value="client" id="edit_scope_client"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Client</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                            Update Key
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editKey(keyId) {
            // Fetch key data and populate modal
            fetch(`/api/v1/keys/${keyId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const key = data.data;
                        document.getElementById('edit_key_id').value = key.id;
                        document.getElementById('edit_key_name').value = key.name;
                        
                        // Reset all checkboxes
                        document.querySelectorAll('#editForm input[type="checkbox"]').forEach(cb => cb.checked = false);
                        
                        // Check appropriate scopes
                        key.scopes.forEach(scope => {
                            const checkbox = document.getElementById(`edit_scope_${scope}`);
                            if (checkbox) checkbox.checked = true;
                        });
                        
                        document.getElementById('editModal').classList.remove('hidden');
                    }
                });
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
