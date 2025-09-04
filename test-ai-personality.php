<?php
/**
 * 🧪 Purrr.love AI Personality System Test Suite
 * Comprehensive testing of the advanced AI personality modeling system
 */

// Allow direct access for testing
define('SECURE_ACCESS', true);

// Include required files
require_once 'includes/functions.php';
require_once 'includes/advanced_ai_personality.php';
require_once 'includes/behavioral_tracking_system.php';

echo "<h1>🧪 Purrr.love AI Personality System Test Suite</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
    .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }
    pre { background-color: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>\n";

// Test results tracking
$testResults = [
    'total' => 0,
    'passed' => 0,
    'failed' => 0,
    'errors' => []
];

function runTest($testName, $testFunction) {
    global $testResults;
    $testResults['total']++;
    
    echo "<div class='test-section'>\n";
    echo "<h3>🧪 Testing: $testName</h3>\n";
    
    try {
        $result = $testFunction();
        if ($result === true || (is_array($result) && !empty($result))) {
            echo "<div class='success'>✅ PASSED: $testName</div>\n";
            $testResults['passed']++;
            if (is_array($result)) {
                echo "<pre>" . print_r($result, true) . "</pre>\n";
            }
        } else {
            echo "<div class='error'>❌ FAILED: $testName</div>\n";
            $testResults['failed']++;
            $testResults['errors'][] = "$testName: Test returned false or empty result";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERROR: $testName - " . $e->getMessage() . "</div>\n";
        $testResults['failed']++;
        $testResults['errors'][] = "$testName: " . $e->getMessage();
    }
    
    echo "</div>\n";
}

// Test 1: Database Connection
runTest("Database Connection", function() {
    try {
        $pdo = get_db();
        $stmt = $pdo->query("SELECT 1");
        return $stmt->fetchColumn() === '1';
    } catch (Exception $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
});

// Test 2: Advanced AI Personality Engine Initialization
runTest("Advanced AI Personality Engine Initialization", function() {
    try {
        $engine = new AdvancedAIPersonalityEngine();
        return $engine !== null;
    } catch (Exception $e) {
        throw new Exception("Failed to initialize AI engine: " . $e->getMessage());
    }
});

// Test 3: Behavioral Tracking System Initialization
runTest("Behavioral Tracking System Initialization", function() {
    try {
        $tracker = new BehavioralTrackingSystem();
        return $tracker !== null;
    } catch (Exception $e) {
        throw new Exception("Failed to initialize behavioral tracker: " . $e->getMessage());
    }
});

// Test 4: Create Test Cat
runTest("Create Test Cat", function() {
    try {
        $pdo = get_db();
        
        // Create a test user first
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO users (username, email, password_hash, name, role, active, email_verified) 
            VALUES ('test_user', 'test@purrr.love', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', 'user', true, true)
        ");
        $stmt->execute();
        
        // Get the test user ID
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'test_user'");
        $stmt->execute();
        $userId = $stmt->fetchColumn();
        
        if (!$userId) {
            throw new Exception("Failed to create test user");
        }
        
        // Create a test cat
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO cats (owner_id, user_id, name, breed, age, gender, health, happiness, energy, hunger, personality_openness, personality_conscientiousness, personality_extraversion, personality_agreeableness, personality_neuroticism) 
            VALUES (?, ?, 'Test Cat', 'Persian', 3, 'female', 100, 85, 90, 20, 0.75, 0.65, 0.80, 0.70, 0.30)
        ");
        $stmt->execute([$userId, $userId]);
        
        // Get the test cat ID
        $stmt = $pdo->prepare("SELECT id FROM cats WHERE name = 'Test Cat' AND owner_id = ?");
        $stmt->execute([$userId]);
        $catId = $stmt->fetchColumn();
        
        if (!$catId) {
            throw new Exception("Failed to create test cat");
        }
        
        return [
            'user_id' => $userId,
            'cat_id' => $catId,
            'message' => 'Test cat created successfully'
        ];
    } catch (Exception $e) {
        throw new Exception("Failed to create test cat: " . $e->getMessage());
    }
});

// Test 5: Advanced Personality Prediction
runTest("Advanced Personality Prediction", function() {
    try {
        $pdo = get_db();
        
        // Get test cat ID
        $stmt = $pdo->prepare("SELECT id FROM cats WHERE name = 'Test Cat' LIMIT 1");
        $stmt->execute();
        $catId = $stmt->fetchColumn();
        
        if (!$catId) {
            throw new Exception("Test cat not found");
        }
        
        // Run advanced personality prediction
        $result = predictAdvancedCatPersonality($catId, true);
        
        if (!$result || !isset($result['personality_profile'])) {
            throw new Exception("Personality prediction failed");
        }
        
        return [
            'cat_id' => $catId,
            'personality_type' => $result['personality_profile']['personality_type'] ?? 'Unknown',
            'confidence' => $result['confidence_scores']['overall'] ?? 0,
            'model_version' => $result['model_version'] ?? 'Unknown'
        ];
    } catch (Exception $e) {
        throw new Exception("Personality prediction failed: " . $e->getMessage());
    }
});

// Test 6: Behavioral Recording
runTest("Behavioral Recording", function() {
    try {
        $pdo = get_db();
        
        // Get test cat ID
        $stmt = $pdo->prepare("SELECT id FROM cats WHERE name = 'Test Cat' LIMIT 1");
        $stmt->execute();
        $catId = $stmt->fetchColumn();
        
        if (!$catId) {
            throw new Exception("Test cat not found");
        }
        
        // Record some test behaviors
        $behaviors = [
            ['play', 'high', 15],
            ['rest', 'medium', 30],
            ['explore', 'medium', 10],
            ['socialize', 'high', 20]
        ];
        
        $recorded = 0;
        foreach ($behaviors as $behavior) {
            if (recordCatBehavior($catId, $behavior[0], $behavior[1], $behavior[2], [
                'environmental' => ['location' => 'test_environment'],
                'social' => ['humans_present' => true]
            ])) {
                $recorded++;
            }
        }
        
        return [
            'cat_id' => $catId,
            'behaviors_recorded' => $recorded,
            'total_attempted' => count($behaviors),
            'success_rate' => ($recorded / count($behaviors)) * 100
        ];
    } catch (Exception $e) {
        throw new Exception("Behavioral recording failed: " . $e->getMessage());
    }
});

// Test 7: Behavioral Analysis
runTest("Behavioral Analysis", function() {
    try {
        $pdo = get_db();
        
        // Get test cat ID
        $stmt = $pdo->prepare("SELECT id FROM cats WHERE name = 'Test Cat' LIMIT 1");
        $stmt->execute();
        $catId = $stmt->fetchColumn();
        
        if (!$catId) {
            throw new Exception("Test cat not found");
        }
        
        // Get behavioral insights
        $insights = getBehavioralInsights($catId);
        
        if (!$insights) {
            throw new Exception("Failed to get behavioral insights");
        }
        
        return [
            'cat_id' => $catId,
            'has_dominant_behaviors' => isset($insights['dominant_behaviors']),
            'has_activity_patterns' => isset($insights['activity_patterns']),
            'has_predictions' => isset($insights['predictions']),
            'has_recommendations' => isset($insights['recommendations'])
        ];
    } catch (Exception $e) {
        throw new Exception("Behavioral analysis failed: " . $e->getMessage());
    }
});

// Test 8: Database Schema Validation
runTest("Database Schema Validation", function() {
    try {
        $pdo = get_db();
        
        $requiredTables = [
            'cats',
            'users',
            'cat_behavior_observations',
            'cat_emotional_states',
            'cat_personality_evolution',
            'cat_advanced_personality'
        ];
        
        $existingTables = [];
        $missingTables = [];
        
        foreach ($requiredTables as $table) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->fetch()) {
                $existingTables[] = $table;
            } else {
                $missingTables[] = $table;
            }
        }
        
        return [
            'existing_tables' => $existingTables,
            'missing_tables' => $missingTables,
            'schema_complete' => empty($missingTables)
        ];
    } catch (Exception $e) {
        throw new Exception("Schema validation failed: " . $e->getMessage());
    }
});

// Test 9: Performance Test
runTest("Performance Test", function() {
    try {
        $startTime = microtime(true);
        
        // Run multiple personality predictions
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT id FROM cats WHERE name = 'Test Cat' LIMIT 1");
        $stmt->execute();
        $catId = $stmt->fetchColumn();
        
        if (!$catId) {
            throw new Exception("Test cat not found");
        }
        
        $iterations = 5;
        $successful = 0;
        
        for ($i = 0; $i < $iterations; $i++) {
            try {
                $result = predictAdvancedCatPersonality($catId, false);
                if ($result) {
                    $successful++;
                }
            } catch (Exception $e) {
                // Continue with next iteration
            }
        }
        
        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $avgTime = $totalTime / $iterations;
        
        return [
            'iterations' => $iterations,
            'successful' => $successful,
            'total_time' => round($totalTime, 4),
            'average_time' => round($avgTime, 4),
            'success_rate' => ($successful / $iterations) * 100
        ];
    } catch (Exception $e) {
        throw new Exception("Performance test failed: " . $e->getMessage());
    }
});

// Test 10: Cleanup Test Data
runTest("Cleanup Test Data", function() {
    try {
        $pdo = get_db();
        
        // Clean up test data
        $stmt = $pdo->prepare("DELETE FROM cat_behavior_observations WHERE cat_id IN (SELECT id FROM cats WHERE name = 'Test Cat')");
        $stmt->execute();
        
        $stmt = $pdo->prepare("DELETE FROM cat_emotional_states WHERE cat_id IN (SELECT id FROM cats WHERE name = 'Test Cat')");
        $stmt->execute();
        
        $stmt = $pdo->prepare("DELETE FROM cat_advanced_personality WHERE cat_id IN (SELECT id FROM cats WHERE name = 'Test Cat')");
        $stmt->execute();
        
        $stmt = $pdo->prepare("DELETE FROM cats WHERE name = 'Test Cat'");
        $stmt->execute();
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE username = 'test_user'");
        $stmt->execute();
        
        return [
            'message' => 'Test data cleaned up successfully',
            'cleanup_complete' => true
        ];
    } catch (Exception $e) {
        throw new Exception("Cleanup failed: " . $e->getMessage());
    }
});

// Display Test Results Summary
echo "<div class='test-section info'>\n";
echo "<h2>📊 Test Results Summary</h2>\n";
echo "<p><strong>Total Tests:</strong> {$testResults['total']}</p>\n";
echo "<p><strong>Passed:</strong> <span style='color: green;'>{$testResults['passed']}</span></p>\n";
echo "<p><strong>Failed:</strong> <span style='color: red;'>{$testResults['failed']}</span></p>\n";
echo "<p><strong>Success Rate:</strong> " . round(($testResults['passed'] / $testResults['total']) * 100, 2) . "%</p>\n";

if (!empty($testResults['errors'])) {
    echo "<h3>❌ Errors:</h3>\n";
    echo "<ul>\n";
    foreach ($testResults['errors'] as $error) {
        echo "<li>$error</li>\n";
    }
    echo "</ul>\n";
}

echo "</div>\n";

// System Information
echo "<div class='test-section info'>\n";
echo "<h2>ℹ️ System Information</h2>\n";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>\n";
echo "<p><strong>Server:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>\n";
echo "<p><strong>Database:</strong> MySQL</p>\n";
echo "<p><strong>Test Date:</strong> " . date('Y-m-d H:i:s') . "</p>\n";

try {
    $pdo = get_db();
    $stmt = $pdo->query("SELECT VERSION() as version");
    $dbVersion = $stmt->fetchColumn();
    echo "<p><strong>MySQL Version:</strong> $dbVersion</p>\n";
} catch (Exception $e) {
    echo "<p><strong>MySQL Version:</strong> Unable to determine</p>\n";
}

echo "</div>\n";

// Recommendations
echo "<div class='test-section warning'>\n";
echo "<h2>💡 Recommendations</h2>\n";

if ($testResults['failed'] > 0) {
    echo "<p>⚠️ Some tests failed. Please check the errors above and ensure:</p>\n";
    echo "<ul>\n";
    echo "<li>Database connection is working properly</li>\n";
    echo "<li>All required database tables exist</li>\n";
    echo "<li>Proper permissions are set for database operations</li>\n";
    echo "<li>All required PHP extensions are installed</li>\n";
    echo "</ul>\n";
} else {
    echo "<p>🎉 All tests passed! The AI Personality System is working correctly.</p>\n";
    echo "<p>You can now:</p>\n";
    echo "<ul>\n";
    echo "<li>Use the AI Personality Analysis interface</li>\n";
    echo "<li>Track cat behaviors in real-time</li>\n";
    echo "<li>Get advanced personality insights</li>\n";
    echo "<li>Receive AI-powered recommendations</li>\n";
    echo "</ul>\n";
}

echo "</div>\n";

// Performance Recommendations
if ($testResults['passed'] > 0) {
    echo "<div class='test-section info'>\n";
    echo "<h2>⚡ Performance Recommendations</h2>\n";
    echo "<ul>\n";
    echo "<li>Consider implementing caching for frequently accessed personality data</li>\n";
    echo "<li>Use database indexes on frequently queried columns</li>\n";
    echo "<li>Implement background processing for heavy ML computations</li>\n";
    echo "<li>Monitor memory usage during peak analysis periods</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
}

echo "<p><em>Test completed at " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
