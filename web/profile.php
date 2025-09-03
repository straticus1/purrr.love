<?php
/**
 * 🐱 Purrr.love - User Profile
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

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Full name is required';
        } elseif (strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters long';
        }
        
        if (empty($email)) {
            $errors[] = 'Email address is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        }
        
        // Check if email is already taken by another user
        if ($email !== $user['email']) {
            try {
                $existingUser = getUserByEmail($email);
                if ($existingUser && $existingUser['id'] !== $user['id']) {
                    $errors[] = 'This email address is already taken';
                }
            } catch (Exception $e) {
                // Email doesn't exist, which is what we want
            }
        }
        
        // Password change validation
        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                $errors[] = 'Current password is required to change password';
            } elseif (!password_verify($currentPassword, $user['password_hash'])) {
                $errors[] = 'Current password is incorrect';
            } elseif (strlen($newPassword) < 8) {
                $errors[] = 'New password must be at least 8 characters long';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'New passwords do not match';
            }
        }
        
        if (empty($errors)) {
            try {
                $updateFields = ['name = ?', 'email = ?', 'updated_at = NOW()'];
                $updateValues = [$name, $email];
                
                // Add password update if changing
                if (!empty($newPassword)) {
                    $updateFields[] = 'password_hash = ?';
                    $updateValues[] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
                
                $updateValues[] = $user['id'];
                
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET " . implode(', ', $updateFields) . " 
                    WHERE id = ?
                ");
                
                if ($stmt->execute($updateValues)) {
                    $message = 'Profile updated successfully!';
                    
                    // Update session data
                    $_SESSION['user_email'] = $email;
                    
                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    $user = $stmt->fetch();
                } else {
                    $error = 'Failed to update profile';
                }
            } catch (Exception $e) {
                $error = 'Error updating profile: ' . $e->getMessage();
            }
        } else {
            $error = implode(', ', $errors);
        }
    }
}

// Get user statistics
$userStats = [];
try {
    // Get cat count
    $stmt = $pdo->prepare("SELECT COUNT(*) as cat_count FROM cats WHERE owner_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userStats['cat_count'] = $stmt->fetch()['cat_count'] ?? 0;
    
    // Get total coins
    $stmt = $pdo->prepare("SELECT coins FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userStats['coins'] = $stmt->fetch()['coins'] ?? 0;
    
    // Get member since
    $userStats['member_since'] = $user['created_at'] ?? 'now';
    
} catch (Exception $e) {
    $userStats = [
        'cat_count' => 0,
        'coins' => 0,
        'member_since' => 'now'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 My Profile - Purrr.love</title>
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
                            <a href="profile.php" class="bg-purple-100 text-purple-700 px-3 py-2 rounded-md text-sm font-medium">
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
            <h1 class="text-4xl font-bold text-gray-900 mb-4">👤 My Profile</h1>
            <p class="text-xl text-gray-600">Manage your account settings and preferences</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Profile Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Profile Information</h2>
                    
                    <form method="POST" action="">
                        <div class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" required
                                       value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="Enter your full name">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="email" name="email" required
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="Enter your email address">
                            </div>

                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h3>
                                <p class="text-sm text-gray-600 mb-4">Leave blank if you don't want to change your password</p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                            Current Password
                                        </label>
                                        <input type="password" id="current_password" name="current_password"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                               placeholder="Enter current password">
                                    </div>

                                    <div>
                                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                                            New Password
                                        </label>
                                        <input type="password" id="new_password" name="new_password"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                               placeholder="Enter new password">
                                        <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters long</p>
                                    </div>

                                    <div>
                                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                                            Confirm New Password
                                        </label>
                                        <input type="password" id="confirm_password" name="confirm_password"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                               placeholder="Confirm new password">
                                    </div>
                                </div>
                            </div>

                            <div class="flex space-x-4">
                                <button type="submit" name="update_profile" 
                                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                                    <i class="fas fa-save mr-2"></i>Update Profile
                                </button>
                                <a href="dashboard.php" 
                                   class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-md transition duration-300 text-center">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column - Profile Summary -->
            <div class="space-y-6">
                <!-- Profile Summary -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Profile Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Member Since:</span>
                            <span class="font-medium text-gray-900">
                                <?= date('M Y', strtotime($userStats['member_since'])) ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Role:</span>
                            <span class="font-medium text-gray-900 capitalize">
                                <?= htmlspecialchars($user['role'] ?? 'user') ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Last Updated:</span>
                            <span class="font-medium text-gray-900">
                                <?= date('M j, Y', strtotime($user['updated_at'] ?? $user['created_at'] ?? 'now')) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-3">
                                <i class="fas fa-cat text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Cats</p>
                                <p class="text-2xl font-semibold text-gray-900"><?= $userStats['cat_count'] ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                                <i class="fas fa-coins text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Coins</p>
                                <p class="text-2xl font-semibold text-gray-900"><?= number_format($userStats['coins']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Actions</h3>
                    <div class="space-y-3">
                        <a href="dashboard.php" 
                           class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-md font-medium transition duration-300">
                            <i class="fas fa-tachometer-alt mr-2"></i>Go to Dashboard
                        </a>
                        
                        <a href="cats.php" 
                           class="block w-full bg-purple-600 hover:bg-purple-700 text-white text-center py-2 px-4 rounded-md font-medium transition duration-300">
                            <i class="fas fa-cat mr-2"></i>Manage Cats
                        </a>
                        
                        <a href="games.php" 
                           class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-md font-medium transition duration-300">
                            <i class="fas fa-gamepad mr-2"></i>Play Games
                        </a>
                        
                        <a href="store.php" 
                           class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-4 rounded-md font-medium transition duration-300">
                            <i class="fas fa-store mr-2"></i>Visit Store
                        </a>
                    </div>
                </div>

                <!-- Security Tips -->
                <div class="bg-gradient-to-r from-red-50 to-orange-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🔒 Security Tips</h3>
                    <div class="space-y-2 text-sm text-gray-700">
                        <p>• Use a strong, unique password</p>
                        <p>• Never share your login credentials</p>
                        <p>• Log out when using shared devices</p>
                        <p>• Keep your email address updated</p>
                    </div>
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
        // Password confirmation validation
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        if (newPasswordInput && confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== newPasswordInput.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
            
            newPasswordInput.addEventListener('input', function() {
                if (confirmPasswordInput.value && this.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            });
        }

        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate profile sections on load
            const profileSections = document.querySelectorAll('.bg-white');
            profileSections.forEach((section, index) => {
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
