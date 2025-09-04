<?php
/**
 * 🐱 Purrr.love - Standalone Enhanced Personality System Test
 * Testing personality system without database dependencies
 */

// Define secure access for includes
define('SECURE_ACCESS', true);

// Mock database functions to avoid connection issues
function get_db() {
    // Return mock PDO for testing
    return null;
}

// Load personality system directly
require_once 'includes/enhanced_cat_personality.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 Standalone Personality Test - Purrr.love</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15); }
        .floating { animation: floating 3s ease-in-out infinite; }
        @keyframes floating { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }
        .gradient-text { background: linear-gradient(135deg, #667eea, #764ba2, #f093fb); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

<!-- Header -->
<div class="mb-8">
    <div class="gradient-bg rounded-3xl p-8 text-white shadow-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">🐱 Standalone Personality System Test</h1>
                <p class="text-xl text-purple-100">Testing enhanced cat personality system components</p>
            </div>
            <div class="hidden lg:block">
                <div class="text-8xl floating">🧪</div>
            </div>
        </div>
    </div>
</div>

<?php
try {
    
    // Test 1: Enhanced Personality Types
    echo "<div class='mb-8'>
        <div class='bg-white rounded-3xl p-8 shadow-xl card-hover'>
            <h2 class='text-2xl font-bold text-gray-900 mb-6 flex items-center'>
                <i class='fas fa-star text-yellow-500 mr-3'></i>
                Test 1: Enhanced Personality Types
            </h2>";
    
    if (defined('ENHANCED_PERSONALITY_TYPES')) {
        echo "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6'>";
        foreach (ENHANCED_PERSONALITY_TYPES as $type => $data) {
            echo "<div class='bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-6 border border-purple-100'>
                <h3 class='text-lg font-semibold text-gray-900 mb-2'>{$data['name']}</h3>
                <p class='text-sm text-gray-600 mb-4'>{$data['description']}</p>
                <div class='space-y-2'>
                    <div class='text-xs text-gray-500'><strong>Energy Level:</strong> {$data['traits']['energy_level']}</div>
                    <div class='text-xs text-gray-500'><strong>Social Preference:</strong> {$data['traits']['social_preference']}</div>
                    <div class='text-xs text-gray-500'><strong>Space Requirement:</strong> {$data['care_needs']['space_requirement']}</div>
                </div>
            </div>";
        }
        echo "</div>";
        
        echo "<div class='mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded'>
            <i class='fas fa-check-circle mr-2'></i>
            ✅ Enhanced Personality Types loaded successfully! Found " . count(ENHANCED_PERSONALITY_TYPES) . " personality types.
        </div>";
    } else {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>
            <i class='fas fa-exclamation-circle mr-2'></i>
            ❌ ENHANCED_PERSONALITY_TYPES constant not defined!
        </div>";
    }
    
    echo "</div></div>";
    
    // Test 2: Cat Needs System
    echo "<div class='mb-8'>
        <div class='bg-white rounded-3xl p-8 shadow-xl card-hover'>
            <h2 class='text-2xl font-bold text-gray-900 mb-6 flex items-center'>
                <i class='fas fa-heart text-red-500 mr-3'></i>
                Test 2: Cat Needs System
            </h2>";
    
    if (defined('CAT_NEEDS')) {
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
        echo "</div>";
        
        echo "<div class='mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded'>
            <i class='fas fa-check-circle mr-2'></i>
            ✅ Cat Needs System loaded successfully! Found " . count(CAT_NEEDS) . " need categories.
        </div>";
    } else {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>
            <i class='fas fa-exclamation-circle mr-2'></i>
            ❌ CAT_NEEDS constant not defined!
        </div>";
    }
    
    echo "</div></div>";
    
    // Test 3: Function Availability
    echo "<div class='mb-8'>
        <div class='bg-white rounded-3xl p-8 shadow-xl card-hover'>
            <h2 class='text-2xl font-bold text-gray-900 mb-6 flex items-center'>
                <i class='fas fa-code text-purple-500 mr-3'></i>
                Test 3: Function Availability
            </h2>";
    
    $functions_to_test = [
        'determineCatPersonalityType' => 'Personality Type Determination',
        'getCatCareRecommendations' => 'Care Recommendations',
        'getCatNeedsAssessment' => 'Needs Assessment',
        'trackCatNeedsFulfillment' => 'Needs Fulfillment Tracking',
        'getCatNeedsSatisfactionScore' => 'Satisfaction Score Calculation'
    ];
    
    echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-4'>";
    foreach ($functions_to_test as $function => $description) {
        $exists = function_exists($function);
        $statusColor = $exists ? 'green' : 'red';
        $statusIcon = $exists ? 'check-circle' : 'times-circle';
        $statusText = $exists ? 'Available' : 'Missing';
        
        echo "<div class='flex items-center justify-between p-4 bg-{$statusColor}-50 border border-{$statusColor}-100 rounded-lg'>
            <div>
                <span class='text-sm font-medium text-gray-900'>{$description}</span>
                <div class='text-xs text-gray-600'>{$function}()</div>
            </div>
            <div class='flex items-center'>
                <i class='fas fa-{$statusIcon} text-{$statusColor}-500 mr-2'></i>
                <span class='text-xs text-{$statusColor}-600 font-semibold'>{$statusText}</span>
            </div>
        </div>";
    }
    echo "</div></div></div>";
    
} catch (Exception $e) {
    echo "<div class='mb-8'>
        <div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>
            <i class='fas fa-exclamation-circle mr-2'></i>
            <strong>Critical Error:</strong> " . htmlspecialchars($e->getMessage()) . "
        </div>
    </div>";
}
?>

</div>

</body>
</html>
