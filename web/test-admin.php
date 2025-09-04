<?php
/**
 * 🧪 Admin Features Test Script
 * Quick test to verify admin functionality
 */

session_start();
require_once 'includes/db_config.php';

// Test database connection
echo "<h1>🧪 Admin Features Test</h1>";

try {
    $pdo = get_web_db();
    echo "<p>✅ Database connection successful</p>";
    
    // Test if tables exist
    $tables = ['users', 'cats', 'support_tickets'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p>✅ Table '$table' exists with $count records</p>";
        } catch (Exception $e) {
            echo "<p>❌ Table '$table' missing or error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test user creation
    echo "<h2>User Management Test</h2>";
    try {
        // Check if we can create a test user
        $testEmail = 'test_admin_' . time() . '@example.com';
        $testName = 'Test Admin User';
        $testPassword = 'testpassword123';
        
        $userId = create_web_user($testName, $testEmail, $testPassword);
        if ($userId) {
            echo "<p>✅ User creation successful (ID: $userId)</p>";
            
            // Test user retrieval
            $user = get_web_user_by_id($userId);
            if ($user) {
                echo "<p>✅ User retrieval successful</p>";
                echo "<p>User details: " . htmlspecialchars($user['name']) . " (" . htmlspecialchars($user['email']) . ")</p>";
                
                // Test admin role update
                $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
                if ($stmt->execute([$userId])) {
                    echo "<p>✅ Admin role update successful</p>";
                } else {
                    echo "<p>❌ Admin role update failed</p>";
                }
                
                // Clean up test user
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                echo "<p>✅ Test user cleaned up</p>";
            } else {
                echo "<p>❌ User retrieval failed</p>";
            }
        } else {
            echo "<p>❌ User creation failed</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ User management test failed: " . $e->getMessage() . "</p>";
    }
    
    // Test support ticket system
    echo "<h2>Support System Test</h2>";
    try {
        // Check if support_tickets table exists and can be queried
        $stmt = $pdo->query("SELECT COUNT(*) FROM support_tickets");
        $ticketCount = $stmt->fetchColumn();
        echo "<p>✅ Support tickets table accessible ($ticketCount tickets)</p>";
        
        // Test ticket creation
        $stmt = $pdo->prepare("
            INSERT INTO support_tickets (user_id, name, email, subject, message, priority, status) 
            VALUES (NULL, 'Test User', 'test@example.com', 'Test Ticket', 'This is a test ticket', 'medium', 'open')
        ");
        if ($stmt->execute()) {
            $ticketId = $pdo->lastInsertId();
            echo "<p>✅ Test ticket created (ID: $ticketId)</p>";
            
            // Clean up test ticket
            $stmt = $pdo->prepare("DELETE FROM support_tickets WHERE id = ?");
            $stmt->execute([$ticketId]);
            echo "<p>✅ Test ticket cleaned up</p>";
        } else {
            echo "<p>❌ Test ticket creation failed</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Support system test failed: " . $e->getMessage() . "</p>";
    }
    
    // Test admin access
    echo "<h2>Admin Access Test</h2>";
    try {
        // Check if there are any admin users
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();
        echo "<p>✅ Admin users found: $adminCount</p>";
        
        if ($adminCount > 0) {
            $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE role = 'admin' LIMIT 1");
            $stmt->execute();
            $admin = $stmt->fetch();
            if ($admin) {
                echo "<p>✅ Admin user details: " . htmlspecialchars($admin['name']) . " (" . htmlspecialchars($admin['email']) . ")</p>";
            }
        } else {
            echo "<p>⚠️ No admin users found. You may need to create one manually.</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Admin access test failed: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>✅ All tests completed!</h2>";
    echo "<p><a href='admin.php'>Go to Admin Panel</a> | <a href='dashboard.php'>Go to Dashboard</a> | <a href='index.php'>Go to Home</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in includes/db_config.php</p>";
}
?>
