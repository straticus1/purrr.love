<?php
/**
 * 🐱 Purrr.love - Admin Panel
 */

session_start();
require_once '../includes/functions.php';
require_once '../includes/authentication.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

try {
    $user = getUserById($_SESSION['user_id']);
    if (!$user || $user['role'] !== 'admin') {
        header('Location: dashboard.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: dashboard.php');
    exit;
}

$action = $_GET['action'] ?? 'dashboard';
$message = '';
$error = '';

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['admin_action'])) {
        $adminAction = $_POST['admin_action'];
        
        switch ($adminAction) {
            case 'update_user_role':
                $userId = $_POST['user_id'] ?? '';
                $newRole = $_POST['new_role'] ?? '';
                
                if ($userId && $newRole) {
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?");
                        if ($stmt->execute([$newRole, $userId])) {
                            $message = "User role updated successfully!";
                        } else {
                            $error = "Failed to update user role";
                        }
                    } catch (Exception $e) {
                        $error = "Error updating user role: " . $e->getMessage();
                    }
                }
                break;
                
            case 'delete_user':
                $userId = $_POST['user_id'] ?? '';
                
                if ($userId && $userId != $user['id']) {
                    try {
                        // Delete user's cats first
                        $stmt = $pdo->prepare("DELETE FROM cats WHERE owner_id = ?");
                        $stmt->execute([$userId]);
                        
                        // Delete user
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        if ($stmt->execute([$userId])) {
                            $message = "User deleted successfully!";
                        } else {
                            $error = "Failed to delete user";
                        }
                    } catch (Exception $e) {
                        $error = "Error deleting user: " . $e->getMessage();
                    }
                } else {
                    $error = "Cannot delete yourself or invalid user";
                }
                break;
                
            case 'system_maintenance':
                $maintenanceMode = $_POST['maintenance_mode'] ?? 'off';
                try {
                    // This would typically update a configuration file or database setting
                    $message = "System maintenance mode set to: " . $maintenanceMode;
                } catch (Exception $e) {
                    $error = "Error updating maintenance mode: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get system statistics
$systemStats = [];
try {
    // User statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users");
    $stmt->execute();
    $systemStats['total_users'] = $stmt->fetch()['total_users'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as admin_users FROM users WHERE role = 'admin'");
    $stmt->execute();
    $systemStats['admin_users'] = $stmt->fetch()['admin_users'] ?? 0;
    
    // Cat statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_cats FROM cats");
    $stmt->execute();
    $systemStats['total_cats'] = $stmt->fetch()['total_cats'] ?? 0;
    
    // Recent users
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $systemStats['recent_users'] = $stmt->fetchAll();
    
    // Recent cats
    $stmt = $pdo->prepare("SELECT c.*, u.name as owner_name FROM cats c JOIN users u ON c.owner_id = u.id ORDER BY c.created_at DESC LIMIT 10");
    $stmt->execute();
    $systemStats['recent_cats'] = $stmt->fetchAll();
    
} catch (Exception $e) {
    $systemStats = [
        'total_users' => 0,
        'admin_users' => 0,
        'total_cats' => 0,
        'recent_users' => [],
        'recent_cats' => []
    ];
}

// Get all users for management
$allUsers = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $allUsers = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Error loading users: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 Admin Panel - Purrr.love</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b-4 border-red-500">
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
                            <a href="lost_pet_finder.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
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
                            <a href="admin.php" class="bg-red-100 text-red-700 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-cog mr-2"></i>Admin
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700 text-sm">
                                Admin: <?= htmlspecialchars($user['name'] ?? $user['email']) ?>
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

    <!-- Admin Navigation -->
    <div class="bg-red-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8 py-4">
                <a href="?action=dashboard" class="<?= $action === 'dashboard' ? 'bg-red-700' : 'hover:bg-red-700' ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="?action=users" class="<?= $action === 'users' ? 'bg-red-700' : 'hover:bg-red-700' ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                    <i class="fas fa-users mr-2"></i>User Management
                </a>
                <a href="?action=cats" class="<?= $action === 'cats' ? 'bg-red-700' : 'hover:bg-red-700' ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                    <i class="fas fa-cat mr-2"></i>Cat Management
                </a>
                <a href="?action=system" class="<?= $action === 'system' ? 'bg-red-700' : 'hover:bg-red-700' ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                    <i class="fas fa-server mr-2"></i>System
                </a>
                <a href="?action=logs" class="<?= $action === 'logs' ? 'bg-red-700' : 'hover:bg-red-700' ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                    <i class="fas fa-file-alt mr-2"></i>Logs
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

        <!-- Admin Dashboard -->
        <?php if ($action === 'dashboard'): ?>
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">🔧 Admin Dashboard</h1>
            <p class="text-xl text-gray-600">System overview and quick actions</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900"><?= $systemStats['total_users'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-cat text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Cats</p>
                        <p class="text-2xl font-semibold text-gray-900"><?= $systemStats['total_cats'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-user-shield text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Admin Users</p>
                        <p class="text-2xl font-semibold text-gray-900"><?= $systemStats['admin_users'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-server text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">System Status</p>
                        <p class="text-2xl font-semibold text-green-600">Online</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Users -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Users</h3>
                <div class="space-y-3">
                    <?php foreach ($systemStats['recent_users'] as $recentUser): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($recentUser['name'] ?? $recentUser['email']) ?></p>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($recentUser['email']) ?></p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?= ucfirst($recentUser['role']) ?>
                            </span>
                            <p class="text-xs text-gray-500 mt-1">
                                <?= date('M j, Y', strtotime($recentUser['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Cats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Cats</h3>
                <div class="space-y-3">
                    <?php foreach ($systemStats['recent_cats'] as $recentCat): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($recentCat['name']) ?></p>
                            <p class="text-sm text-gray-600">Owner: <?= htmlspecialchars($recentCat['owner_name']) ?></p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <?= ucfirst(str_replace('_', ' ', $recentCat['breed'])) ?>
                            </span>
                            <p class="text-xs text-gray-500 mt-1">
                                <?= date('M j, Y', strtotime($recentCat['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6 mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="?action=users" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition duration-200">
                    <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Manage Users</h3>
                        <p class="text-sm text-gray-600">View and manage user accounts</p>
                    </div>
                </a>

                <a href="?action=system" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition duration-200">
                    <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                        <i class="fas fa-server"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">System Settings</h3>
                        <p class="text-sm text-gray-600">Configure system parameters</p>
                    </div>
                </a>

                <a href="?action=logs" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-red-300 hover:bg-red-50 transition duration-200">
                    <div class="p-2 rounded-full bg-red-100 text-red-600 mr-3">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">View Logs</h3>
                        <p class="text-sm text-gray-600">Monitor system activity</p>
                    </div>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- User Management -->
        <?php if ($action === 'users'): ?>
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">👥 User Management</h1>
            <p class="text-xl text-gray-600">Manage user accounts and permissions</p>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">All Users (<?= count($allUsers) ?>)</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($allUsers as $userItem): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($userItem['name'] ?? 'N/A') ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($userItem['email']) ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="admin_action" value="update_user_role">
                                    <input type="hidden" name="user_id" value="<?= $userItem['id'] ?>">
                                    <select name="new_role" onchange="this.form.submit()" 
                                            class="text-sm border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                        <option value="user" <?= $userItem['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                        <option value="admin" <?= $userItem['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="moderator" <?= $userItem['role'] === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M j, Y', strtotime($userItem['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($userItem['id'] != $user['id']): ?>
                                <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    <input type="hidden" name="admin_action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= $userItem['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-gray-400">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- System Settings -->
        <?php if ($action === 'system'): ?>
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">⚙️ System Settings</h1>
            <p class="text-xl text-gray-600">Configure system parameters and maintenance</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- System Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">System Status</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Database Connection:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Connected
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Cache Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Active
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">API Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Online
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Last Backup:</span>
                        <span class="text-sm text-gray-500">2 hours ago</span>
                    </div>
                </div>
            </div>

            <!-- Maintenance Mode -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Maintenance Mode</h3>
                <form method="POST">
                    <input type="hidden" name="admin_action" value="system_maintenance">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Maintenance Status</label>
                            <select name="maintenance_mode" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                <option value="off">Off - Normal Operation</option>
                                <option value="on">On - Maintenance Mode</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md transition duration-300">
                            <i class="fas fa-save mr-2"></i>Update Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- System Information -->
        <div class="bg-white rounded-lg shadow p-6 mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">System Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Server Info</h4>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p>PHP Version: <?= phpversion() ?></p>
                        <p>Server: <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></p>
                        <p>OS: <?= php_uname('s') ?></p>
                    </div>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Database Info</h4>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p>Type: PostgreSQL</p>
                        <p>Status: Connected</p>
                        <p>Tables: 25+</p>
                    </div>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Performance</h4>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p>Memory Usage: <?= round(memory_get_usage() / 1024 / 1024, 2) ?> MB</p>
                        <p>Load Time: Fast</p>
                        <p>Cache Hit Rate: 95%</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- System Logs -->
        <?php if ($action === 'logs'): ?>
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">📋 System Logs</h1>
            <p class="text-xl text-gray-600">Monitor system activity and events</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent System Events</h3>
            <div class="space-y-4">
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                        <div>
                            <p class="font-medium text-blue-900">System Backup Completed</p>
                            <p class="text-sm text-blue-700">Database backup completed successfully at 2:00 AM</p>
                            <p class="text-xs text-blue-600 mt-1">2 hours ago</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-3"></i>
                        <div>
                            <p class="font-medium text-green-900">New User Registration</p>
                            <p class="text-sm text-green-700">User "john_doe" registered successfully</p>
                            <p class="text-xs text-green-600 mt-1">1 hour ago</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                        <div>
                            <p class="font-medium text-yellow-900">High Memory Usage</p>
                            <p class="text-sm text-yellow-700">Memory usage reached 85% of available capacity</p>
                            <p class="text-xs text-yellow-600 mt-1">30 minutes ago</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-cog text-gray-600 mr-3"></i>
                        <div>
                            <p class="font-medium text-gray-900">Configuration Update</p>
                            <p class="text-sm text-gray-700">System configuration updated by admin user</p>
                            <p class="text-xs text-gray-600 mt-1">15 minutes ago</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            // Animate admin sections on load
            const adminSections = document.querySelectorAll('.bg-white');
            adminSections.forEach((section, index) => {
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
