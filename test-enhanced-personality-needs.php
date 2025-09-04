<?php
/**
 * 🐱 Purrr.love - Enhanced Personality & Needs System Test
 * Comprehensive testing of the enhanced cat personality and needs system
 */

// Define secure access for includes
define('SECURE_ACCESS', true);

require_once 'includes/functions.php';
require_once 'includes/enhanced_cat_personality.php';
require_once 'includes/behavioral_tracking_system.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>🐱 Enhanced Personality & Needs System Test - Purrr.love</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15); }
        .floating { animation: floating 3s ease-in-out infinite; }
        @keyframes floating { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }
        .pulse-glow { animation: pulse-glow 2s ease-in-out infinite alternate; }
        @keyframes pulse-glow { from { box-shadow: 0 0 20px rgba(139, 92, 246, 0.5); } to { box-shadow: 0 0 30px rgba(139, 92, 246, 0.8); } }
        .gradient-text { background: linear-gradient(135deg, #667eea, #764ba2, #f093fb); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    </style>
</head>
<body class='min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50'>";

echo "<div class='max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8'>";

// Header
echo "<div class='mb-8'>
    <div class='gradient-bg rounded-3xl p-8 text-white shadow-2xl'>
        <div class='flex items-center justify-between'>
            <div>
                <h1 class='text-4xl font-bold mb-2'>🐱 Enhanced Personality & Needs System Test</h1>
                <p class='text-xl text-purple-100'>Comprehensive testing of the enhanced cat personality and needs system</p>
            </div>
            <div class='hidden lg:block'>
                <div class='text-8xl floating'>🧪</div>
            </div>
        </div>
    </div>
</div>";

try {
    $pdo = get_db();
    
    // Test 1: Enhanced Personality Types
    echo "<div class='mb-8'>
        <div class='bg-white rounded-3xl p-8 shadow-xl card-hover'>
            <h2 class='text-2xl font-bold text-gray-900 mb-6 flex items-center'>
                <i class='fas fa-star text-yellow-500 mr-3'></i>
                Test 1: Enhanced Personality Types
            </h2>";
    
    echo "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6'>";
    foreach (ENHANCED_PERSONALITY_TYPES as $type => $data) {
        echo "<div class='bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-6 border border-purple-100'>
            <h3 class='text-lg font-semibold text-gray-900 mb-2'>{$data['name']}</h3>
            <p class='text-sm text-gray-600 mb-4'>{$data['description']}</p>
            <div class='space-y-2'>
                <div class='text-xs text-gray-500'><strong>Traits:</strong> " . implode(', ', array_slice($data['traits'], 0, 3)) . "</div>
                <div class='text-xs text-gray-500'><strong>Care Level:</strong> {$data['care_level']}</div>
                <div class='text-xs text-gray-500'><strong>Activity Level:</strong> {$data['activity_level']}</div>
            </div>
        </div>";
    }
    echo "</div></div></div>";
    
    // Test 2: Cat Needs System
    echo "<div class='mb-8'>
        <div class='bg-white rounded-3xl p-8 shadow-xl card-hover'>
            <h2 class='text-2xl font-bold text-gray-900 mb-6 flex items-center'>
                <i class='fas fa-heart text-red-500 mr-3'></i>
                Test 2: Cat Needs System
            </h2>";
    
    echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-6'>";
    foreach (CAT_NEEDS as $category => $needs) {
        echo "<div class='bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100'>
            <h3 class='text-lg font-semibold text-gray-900 mb-4 capitalize'>" . str_replace('_', ' ', $category) . "</h3>
            <div class='space-y-3'>";
        
        foreach ($needs as $needKey => $need) {
            echo "<div class='flex justify-between items-center'>
                <span class='text-sm font-medium text-gray-700'>{$need['name']}</span>
                <span class='text-xs text-gray-500'>{$need['frequency']}</span>
            </div>";
        }
        
        echo "</div></div>";
    }
    echo "</div></div></div>";
    
    // Test 3: Personality Assessment Function
    echo "<div class='mb-8'>
        <div class='bg-white rounded-3xl p-8 shadow-xl card-hover'>
            <h2 class='text-2xl font-bold text-gray-900 mb-6 flex items-center'>
                <i class='fas fa-brain text-purple-500 mr-3'></i>
                Test 3: Personality Assessment Function
            </h2>";
    
    // Create a test cat
    $testCatId = 999; // Use a test ID
    
    echo "<div class='bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-100 mb-6'>
        <h3 class='text-lg font-semibold text-gray-900 mb-4'>Test Cat Personality Assessment</h3>
        <p class='text-sm text-gray-600 mb-4'>Testing personality assessment with sample data...</p>";
    
    try {
        // Test with sample behavioral data
        $sampleBehaviors = [
            'play' => 25, 'rest' => 20, 'explore' => 15, 'socialize' => 10,
            'groom' => 15, 'hunt' => 5, 'eat' => 8, 'sleep' => 2
        ];
        
        $personalityType = determineCatPersonalityType($testCatId, $sampleBehaviors);
        $personalityData = ENHANCED_PERSONALITY_TYPES[$personalityType];
        
        echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-6'>
            <div>
                <h4 class='font-semibold text-gray-900 mb-2'>Detected Personality: {$personalityData['name']}</h4>
                <p class='text-sm text-gray-600 mb-4'>{$personalityData['description']}</p>
                <div class='space-y-2'>
                    <div class='text-xs text-gray-500'><strong>Care Level:</strong> {$personalityData['care_level']}</div>
                    <div class='text-xs text-gray-500'><strong>Activity Level:</strong> {$personalityData['activity_level']}</div>
                    <div class='text-xs text-gray-500'><strong>Social Level:</strong> {$personalityData['social_level']}</div>
                </div>
            </div>
            <div>
                <h4 class='font-semibold text-gray-900 mb-2'>Key Traits</h4>
                <ul class='space-y-1'>";
        
        foreach (array_slice($personalityData['traits'], 0, 5) as $trait) {
            echo "<li class='text-sm text-gray-600 flex items-center'>
                <i class='fas fa-check-circle text-green-500 mr-2'></i>
                {$trait}
            </li>";
        }
        
        echo "</ul></div></div>";
        
    } catch (Exception $e) {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>
            <i class='fas fa-exclamation-circle mr-2'></i>
            Error testing personality assessment: " . htmlspecialchars($e->getMessage()) . "
        </div>";
    }
    
    echo "</div></div></div>";
    
    // Test 4: Needs Assessment Function
    echo "<div class='mb-8'>
        <div class='bg-white rounded-3xl p-8 shadow-xl card-hover'>
            <h2 class='text-2xl font-bold text-gray-900 mb-6 flex items-center'>
                <i class='fas fa-clipboard-list text-blue-500 mr-3'></i>
                Test 4: Needs Assessment Function
            </h2>";
    
    try {
        $needsAssessment = getCatNeedsAssessment($testCatId);
        
        if ($needsAssessment) {
            echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-6'>";
            foreach ($needsAssessment as $category => $needs) {
                echo "<div class='bg-gradient-to-r from-orange-50 to-yellow-50 rounded-2xl p-6 border border-orange-100'>
                    <h3 class='text-lg font-semibold text-gray-900 mb-4 capitalize'>" . str_replace('_', ' ', $category) . "</h3>
                    <div class='space-y-3'>";
                
                foreach ($needs as $needKey => $need) {
                    $statusColor = $need['status'] === 'critical' ? 'red' : ($need['status'] === 'high' ? 'orange' : ($need['status'] === 'medium' ? 'blue' : 'green'));
                    echo "<div>
                        <div class='flex justify-between items-center mb-1'>
                            <span class='text-sm font-medium text-gray-700'>{$need['need_name']}</span>
                            <span class='text-xs text-{$statusColor}-600 font-semibold'>{$need['status']}</span>
                        </div>
                        <div class='w-full bg-gray-200 rounded-full h-2'>
                            <div class='bg-{$statusColor}-500 h-2 rounded-full' style='width: " . ($need['fulfillment_level'] * 100) . "%'></div>
                        </div>
                        <div class='text-xs text-gray-500 mt-1'>" . round($need['fulfillment_level'] * 100) . "% fulfilled</div>
                    </div>";
                }
                
                echo "</div></div>";
            }
            echo "</div>";
        } else {
            echo "<div class='bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded'>
                <i class='fas fa-exclamation-triangle mr-2'></i>
                No needs assessment data available for test cat
            </div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>
            <i class='fas fa-exclamation-circle mr-2'></i>
            Error testing needs assessment: " . htmlspecialchars($e->getMessage()) . "
        </div>";
    }
    
    echo "</div></div>";
    
    // Test 5: Care Recommendations
    echo "<div class='mb-8'>
        <div class='bg-white rounded-3xl p-8 shadow-xl card-hover'>
            <h2 class='text-2xl font-bold text-gray-900 mb-6 flex items-center'>
                <i class='fas fa-lightbulb text-yellow-500 mr-3'></i>
                Test 5: Care Recommendations
            </h2>";
    
    try {
        $careRecommendations = getCatCareRecommendations($testCatId);
        
        if ($careRecommendations) {
            echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-6'>";
            
            if (isset($careRecommendations['immediate_priorities']) && !empty($careRecommendations['immediate_priorities'])) {
                echo "<div class='bg-gradient-to-r from-red-50 to-pink-50 rounded-2xl p-6 border border-red-100'>
                    <h3 class='text-lg font-semibold text-gray-900 mb-4'>Immediate Priorities</h3>
                    <ul class='space-y-2'>";
                
                foreach (array_slice($careRecommendations['immediate_priorities'], 0, 5) as $priority) {
                    echo "<li class='text-sm text-gray-700 flex items-start'>
                        <i class='fas fa-exclamation-triangle text-red-500 mr-2 mt-1'></i>
                        {$priority}
                    </li>";
                }
                
                echo "</ul></div>";
            }
            
            if (isset($careRecommendations['daily_care']) && !empty($careRecommendations['daily_care'])) {
                echo "<div class='bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-100'>
                    <h3 class='text-lg font-semibold text-gray-900 mb-4'>Daily Care</h3>
                    <ul class='space-y-2'>";
                
                foreach (array_slice($careRecommendations['daily_care'], 0, 5) as $care) {
                    echo "<li class='text-sm text-gray-700 flex items-start'>
                        <i class='fas fa-check-circle text-green-500 mr-2 mt-1'></i>
                        {$care}
                    </li>";
                }
                
                echo "</ul></div>";
            }
            
            echo "</div>";
        } else {
            echo "<div class='bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded'>
                <i class='fas fa-exclamation-triangle mr-2'></i>
                No care recommendations available for test cat
            </div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>
            <i class='fas fa-exclamation-circle mr-2'></i>
            Error testing care recommendations: " . htmlspecialchars($e->getMessage()) . "
        </div>";
    }
    
    echo "</div></div>";
    
    // Test 6: Behavioral Tracking Integration
    echo "<div class='mb-8'>
        <div class='bg-white rounded-3xl p-8 shadow-xl card-hover'>
            <h2 class='text-2xl font-bold text-gray-900 mb-6 flex items-center'>
                <i class='fas fa-chart-line text-indigo-500 mr-3'></i>
                Test 6: Behavioral Tracking Integration
            </h2>";
    
    try {
        // Test behavioral tracking functions
        echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-6'>";
        
        // Test behavior recording
        echo "<div class='bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl p-6 border border-purple-100'>
            <h3 class='text-lg font-semibold text-gray-900 mb-4'>Behavior Recording Test</h3>
            <p class='text-sm text-gray-600 mb-4'>Testing behavior recording functionality...</p>";
        
        $testBehaviors = ['play', 'purr', 'knead', 'stretch', 'chase', 'pounce'];
        $recordedCount = 0;
        
        foreach ($testBehaviors as $behavior) {
            if (recordCatBehavior($testCatId, $behavior, 'medium', 5, ['environmental' => ['indoor'], 'social' => ['human_present']])) {
                $recordedCount++;
            }
        }
        
        echo "<div class='text-sm text-gray-700'>
            <i class='fas fa-check-circle text-green-500 mr-2'></i>
            Successfully recorded {$recordedCount} out of " . count($testBehaviors) . " test behaviors
        </div>";
        
        echo "</div>";
        
        // Test behavioral insights
        echo "<div class='bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl p-6 border border-blue-100'>
            <h3 class='text-lg font-semibold text-gray-900 mb-4'>Behavioral Insights Test</h3>
            <p class='text-sm text-gray-600 mb-4'>Testing behavioral insights generation...</p>";
        
        $insights = getBehavioralInsights($testCatId);
        
        if ($insights) {
            echo "<div class='space-y-3'>";
            if (isset($insights['dominant_behaviors']) && !empty($insights['dominant_behaviors'])) {
                echo "<div>
                    <h4 class='font-medium text-gray-900 mb-2'>Dominant Behaviors</h4>
                    <ul class='space-y-1'>";
                
                foreach (array_slice($insights['dominant_behaviors'], 0, 3) as $behavior) {
                    echo "<li class='text-sm text-gray-600'>
                        {$behavior['behavior']}: {$behavior['frequency']}%
                    </li>";
                }
                
                echo "</ul></div>";
            }
            
            if (isset($insights['recommendations']) && !empty($insights['recommendations'])) {
                echo "<div>
                    <h4 class='font-medium text-gray-900 mb-2'>Recommendations</h4>
                    <ul class='space-y-1'>";
                
                foreach (array_slice($insights['recommendations'], 0, 3) as $recommendation) {
                    echo "<li class='text-sm text-gray-600 flex items-start'>
                        <i class='fas fa-lightbulb text-yellow-500 mr-2 mt-1'></i>
                        {$recommendation}
                    </li>";
                }
                
                echo "</ul></div>";
            }
            
            echo "</div>";
        } else {
            echo "<div class='text-sm text-gray-600'>
                <i class='fas fa-info-circle text-blue-500 mr-2'></i>
                No behavioral insights available (insufficient data)
            </div>";
        }
        
        echo "</div>";
        
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>
            <i class='fas fa-exclamation-circle mr-2'></i>
            Error testing behavioral tracking: " . htmlspecialchars($e->getMessage()) . "
        </div>";
    }
    
    echo "</div></div>";
    
    // Test 7: Database Schema Validation
    echo "<div class='mb-8'>
        <div class='bg-white rounded-3xl p-8 shadow-xl card-hover'>
            <h2 class='text-2xl font-bold text-gray-900 mb-6 flex items-center'>
                <i class='fas fa-database text-green-500 mr-3'></i>
                Test 7: Database Schema Validation
            </h2>";
    
    try {
        $requiredTables = [
            'cats' => ['id', 'name', 'personality_type', 'needs_satisfaction_score'],
            'cat_needs' => ['id', 'cat_id', 'need_category', 'need_type', 'fulfillment_level'],
            'cat_need_logs' => ['id', 'cat_id', 'need_category', 'need_type', 'fulfillment_level', 'logged_at'],
            'cat_behavior_observations' => ['id', 'cat_id', 'behavior_type', 'behavior_intensity', 'observed_at'],
            'cat_emotional_states' => ['id', 'cat_id', 'emotion_type', 'intensity_score', 'recorded_at']
        ];
        
        echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-6'>";
        
        foreach ($requiredTables as $table => $columns) {
            echo "<div class='bg-gradient-to-r from-gray-50 to-slate-50 rounded-2xl p-6 border border-gray-100'>
                <h3 class='text-lg font-semibold text-gray-900 mb-4'>{$table}</h3>";
            
            try {
                $stmt = $pdo->prepare("DESCRIBE {$table}");
                $stmt->execute();
                $tableColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $missingColumns = array_diff($columns, $tableColumns);
                
                if (empty($missingColumns)) {
                    echo "<div class='text-sm text-green-600'>
                        <i class='fas fa-check-circle mr-2'></i>
                        All required columns present
                    </div>";
                } else {
                    echo "<div class='text-sm text-red-600'>
                        <i class='fas fa-exclamation-circle mr-2'></i>
                        Missing columns: " . implode(', ', $missingColumns) . "
                    </div>";
                }
                
                echo "<div class='text-xs text-gray-500 mt-2'>
                    Columns: " . implode(', ', $tableColumns) . "
                </div>";
                
            } catch (Exception $e) {
                echo "<div class='text-sm text-red-600'>
                    <i class='fas fa-times-circle mr-2'></i>
                    Table not found or error: " . htmlspecialchars($e->getMessage()) . "
                </div>";
            }
            
            echo "</div>";
        }
        
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>
            <i class='fas fa-exclamation-circle mr-2'></i>
            Error validating database schema: " . htmlspecialchars($e->getMessage()) . "
        </div>";
    }
    
    echo "</div></div>";
    
    // Summary
    echo "<div class='mb-8'>
        <div class='bg-gradient-to-r from-green-600 to-emerald-600 rounded-3xl p-8 text-white shadow-2xl'>
            <h2 class='text-2xl font-bold mb-6 flex items-center'>
                <i class='fas fa-check-circle text-green-300 mr-3'></i>
                Test Summary
            </h2>
            <div class='grid grid-cols-1 md:grid-cols-2 gap-6'>
                <div>
                    <h3 class='text-lg font-semibold mb-4'>✅ Completed Tests</h3>
                    <ul class='space-y-2'>
                        <li class='flex items-center'><i class='fas fa-check mr-2'></i>Enhanced Personality Types</li>
                        <li class='flex items-center'><i class='fas fa-check mr-2'></i>Cat Needs System</li>
                        <li class='flex items-center'><i class='fas fa-check mr-2'></i>Personality Assessment</li>
                        <li class='flex items-center'><i class='fas fa-check mr-2'></i>Needs Assessment</li>
                        <li class='flex items-center'><i class='fas fa-check mr-2'></i>Care Recommendations</li>
                        <li class='flex items-center'><i class='fas fa-check mr-2'></i>Behavioral Tracking</li>
                        <li class='flex items-center'><i class='fas fa-check mr-2'></i>Database Schema</li>
                    </ul>
                </div>
                <div>
                    <h3 class='text-lg font-semibold mb-4'>🎯 System Features</h3>
                    <ul class='space-y-2'>
                        <li class='flex items-center'><i class='fas fa-star mr-2'></i>8 Enhanced Personality Types</li>
                        <li class='flex items-center'><i class='fas fa-heart mr-2'></i>4 Comprehensive Need Categories</li>
                        <li class='flex items-center'><i class='fas fa-brain mr-2'></i>AI-Powered Assessments</li>
                        <li class='flex items-center'><i class='fas fa-chart-line mr-2'></i>Behavioral Tracking</li>
                        <li class='flex items-center'><i class='fas fa-lightbulb mr-2'></i>Personalized Recommendations</li>
                        <li class='flex items-center'><i class='fas fa-database mr-2'></i>MySQL Integration</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-8'>
        <i class='fas fa-exclamation-circle mr-2'></i>
        <strong>System Error:</strong> " . htmlspecialchars($e->getMessage()) . "
    </div>";
}

echo "</div></body></html>";
?>
