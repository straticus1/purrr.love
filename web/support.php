<?php
/**
 * 🎫 Purrr.love - Support Ticket System
 * User support and help center
 */

session_start();
require_once 'includes/db_config.php';

$errors = [];
$success = false;
$tickets = [];

// Check if user is logged in for ticket submission
$user_logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;

// Handle ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    } elseif (strlen($message) < 10) {
        $errors[] = 'Message must be at least 10 characters long';
    }
    
    // Submit ticket if no errors
    if (empty($errors)) {
        try {
            $pdo = get_web_db();
            
            // Create support_tickets table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS support_tickets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NULL,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    subject VARCHAR(500) NOT NULL,
                    message TEXT NOT NULL,
                    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
                    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_status (status),
                    INDEX idx_priority (priority)
                )
            ");
            
            $stmt = $pdo->prepare("
                INSERT INTO support_tickets (user_id, name, email, subject, message, priority) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$user_id, $name, $email, $subject, $message, $priority])) {
                $success = true;
                // Clear form data
                $_POST = [];
            } else {
                $errors[] = 'Failed to submit ticket. Please try again.';
            }
        } catch (Exception $e) {
            $errors[] = 'System error: ' . $e->getMessage();
        }
    }
}

// Get user's tickets if logged in
if ($user_logged_in) {
    try {
        $pdo = get_web_db();
        $stmt = $pdo->prepare("
            SELECT * FROM support_tickets 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $tickets = $stmt->fetchAll();
    } catch (Exception $e) {
        // Handle error silently
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎫 Support Center - Purrr.love</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
        }
        
        .priority-high { border-left: 4px solid #ef4444; }
        .priority-medium { border-left: 4px solid #f59e0b; }
        .priority-low { border-left: 4px solid #10b981; }
        
        .status-open { background-color: #dbeafe; color: #1e40af; }
        .status-in-progress { background-color: #fef3c7; color: #d97706; }
        .status-resolved { background-color: #d1fae5; color: #059669; }
        .status-closed { background-color: #f3f4f6; color: #6b7280; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b-4 border-blue-500">
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
                            <a href="index.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-home mr-2"></i>Home
                            </a>
                            <?php if ($user_logged_in): ?>
                                <a href="dashboard.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                </a>
                            <?php else: ?>
                                <a href="register.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                    <i class="fas fa-user-plus mr-2"></i>Register
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <?php if ($user_logged_in): ?>
                        <a href="profile.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-user mr-2"></i>Profile
                        </a>
                        <a href="index.php?logout=1" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="index.php#login" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">🎫 Support Center</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Need help? We're here for you! Submit a support ticket and our team will get back to you as soon as possible.
            </p>
        </div>

        <!-- Messages -->
        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Ticket Submitted Successfully!</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>Thank you for contacting us. We'll review your ticket and get back to you within 24 hours.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Support Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">
                        <i class="fas fa-ticket-alt text-blue-500 mr-3"></i>
                        Submit a Support Ticket
                    </h2>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="submit_ticket" value="1">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                <input type="text" id="name" name="name" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                                <input type="email" id="email" name="email" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                            <input type="text" id="subject" name="subject" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Brief description of your issue"
                                   value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                        </div>
                        
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                            <select id="priority" name="priority" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="low" <?= ($_POST['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low - General inquiry</option>
                                <option value="medium" <?= ($_POST['priority'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>Medium - Standard issue</option>
                                <option value="high" <?= ($_POST['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High - Urgent issue</option>
                                <option value="urgent" <?= ($_POST['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent - Critical problem</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                            <textarea id="message" name="message" rows="6" required 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Please provide detailed information about your issue..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-md transition duration-300 transform hover:scale-105">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Submit Ticket
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- FAQ -->
                <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-question-circle text-green-500 mr-2"></i>
                        Frequently Asked Questions
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-900">How do I adopt a cat?</h4>
                            <p class="text-sm text-gray-600">Visit the Cats section and click "Adopt Cat" to choose your new feline friend.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">How do I earn coins?</h4>
                            <p class="text-sm text-gray-600">Play games, complete quests, and participate in community activities.</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Can I trade my cats?</h4>
                            <p class="text-sm text-gray-600">Yes! Visit the Blockchain section to trade cats as NFTs.</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-phone text-purple-500 mr-2"></i>
                        Other Ways to Reach Us
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-gray-400 mr-3"></i>
                            <span class="text-gray-600">support@purrr.love</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-comments text-gray-400 mr-3"></i>
                            <span class="text-gray-600">Discord Community</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-twitter text-gray-400 mr-3"></i>
                            <span class="text-gray-600">@PurrrLove</span>
                        </div>
                    </div>
                </div>

                <!-- Response Time -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                    <h3 class="text-xl font-bold mb-4">
                        <i class="fas fa-clock mr-2"></i>
                        Response Times
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Urgent:</span>
                            <span>2-4 hours</span>
                        </div>
                        <div class="flex justify-between">
                            <span>High:</span>
                            <span>4-8 hours</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Medium:</span>
                            <span>24 hours</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Low:</span>
                            <span>48 hours</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User's Tickets (if logged in) -->
        <?php if ($user_logged_in && !empty($tickets)): ?>
        <div class="mt-12">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-history text-purple-500 mr-3"></i>
                    Your Recent Tickets
                </h2>
                
                <div class="space-y-4">
                    <?php foreach ($tickets as $ticket): ?>
                    <div class="border border-gray-200 rounded-lg p-4 priority-<?= $ticket['priority'] ?>">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($ticket['subject']) ?></h3>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 rounded-full text-xs font-medium status-<?= str_replace('-', '-', $ticket['status']) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
                                </span>
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?= ucfirst($ticket['priority']) ?>
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars(substr($ticket['message'], 0, 100)) ?>...</p>
                        <p class="text-xs text-gray-500">Submitted on <?= date('M j, Y g:i A', strtotime($ticket['created_at'])) ?></p>
                    </div>
                    <?php endforeach; ?>
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
</body>
</html>
