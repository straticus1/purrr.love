<?php
/**
 * 🤖 Purrr.love AI Cat Name Generator Interface
 * Beautiful web interface for AI-powered cat name generation
 */

// Define secure access for includes
define('SECURE_ACCESS', true);

session_start();
require_once '../includes/functions.php';
require_once '../includes/authentication.php';
require_once '../includes/ai_cat_name_generator.php';

// Initialize variables
$generatedNames = null;
$message = '';
$messageType = '';

// Handle form submission
if ($_POST) {
    try {
        $criteria = [
            'personality' => $_POST['personality'] ?? 'the_gentle_giant',
            'gender' => $_POST['gender'] ?? 'unknown',
            'color' => $_POST['color'] ?? 'mixed',
            'size' => $_POST['size'] ?? 'medium',
            'style' => $_POST['style'] ?? 'classic',
            'count' => min(max((int)($_POST['count'] ?? 12), 5), 20)
        ];
        
        $generatedNames = generateCatNames($criteria);
        
        if ($generatedNames['success']) {
            $message = "Generated " . count($generatedNames['names']) . " perfect names for your feline friend!";
            $messageType = 'success';
        } else {
            $message = "Error generating names: " . $generatedNames['error'];
            $messageType = 'error';
        }
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🤖 AI Cat Name Generator - Purrr.love</title>
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
        
        .ai-glow {
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
            animation: pulse-glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes pulse-glow {
            from { box-shadow: 0 0 20px rgba(102, 126, 234, 0.3); }
            to { box-shadow: 0 0 30px rgba(118, 75, 162, 0.5); }
        }
        
        .name-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
        }
        
        .name-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
            border-left-color: #667eea;
        }
        
        .confidence-bar {
            background: linear-gradient(90deg, #ef4444, #f59e0b, #10b981);
            height: 4px;
            border-radius: 2px;
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .sparkle {
            animation: sparkle 2s ease-in-out infinite;
        }
        
        @keyframes sparkle {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; transform: scale(0.95); }
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .form-glow {
            transition: all 0.3s ease;
        }
        
        .form-glow:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            border-color: #667eea;
        }
        
        .personality-option {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .personality-option:hover {
            border-color: #667eea;
            transform: scale(1.02);
        }
        
        .personality-option.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea10, #764ba210);
        }
        
        .copy-button {
            transition: all 0.2s ease;
        }
        
        .copy-button:hover {
            background-color: #667eea;
            color: white;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">

<!-- Navigation -->
<nav class="bg-white shadow-lg border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="dashboard.php" class="text-2xl font-bold text-purple-600">🐱 Purrr.love</a>
                <span class="ml-4 text-sm text-gray-500">AI Name Generator</span>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-robot mr-2 text-purple-500"></i>
                    <span>Powered by AI</span>
                </div>
                <a href="realtime-dashboard.php" class="text-purple-600 hover:text-purple-700">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

<!-- Header -->
<div class="mb-8">
    <div class="gradient-bg rounded-3xl p-8 text-white shadow-2xl ai-glow">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2 flex items-center">
                    🤖 AI Cat Name Generator
                    <span class="sparkle ml-3 text-3xl">✨</span>
                </h1>
                <p class="text-xl text-purple-100 mb-4">Discover the perfect name for your feline friend using advanced AI</p>
                <div class="flex space-x-6 text-sm">
                    <div class="flex items-center">
                        <i class="fas fa-brain mr-2"></i>
                        <span>AI-Powered Analysis</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-heart mr-2"></i>
                        <span>Personality-Based</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-star mr-2"></i>
                        <span>Unique Suggestions</span>
                    </div>
                </div>
            </div>
            <div class="hidden lg:block">
                <div class="text-8xl floating">🎭</div>
            </div>
        </div>
    </div>
</div>

<!-- Message Display -->
<?php if ($message): ?>
<div class="mb-6">
    <div class="<?php echo $messageType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> border px-4 py-3 rounded-lg">
        <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

<!-- Name Generation Form -->
<div class="lg:col-span-1">
    <div class="bg-white rounded-3xl p-6 shadow-xl">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 gradient-text">
            <i class="fas fa-sliders-h mr-2"></i>
            Customize Your Search
        </h2>
        
        <form method="POST" class="space-y-6">
            <!-- Personality Type -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-3">
                    <i class="fas fa-user-friends mr-2 text-purple-500"></i>
                    Cat Personality Type
                </label>
                <div class="space-y-2">
                    <?php
                    $personalities = [
                        'the_gentle_giant' => ['🐘', 'The Gentle Giant', 'Calm, loving, and peaceful'],
                        'the_energetic_explorer' => ['🚀', 'The Energetic Explorer', 'Adventurous and playful'],
                        'the_wise_observer' => ['🦉', 'The Wise Observer', 'Thoughtful and intelligent'],
                        'the_social_butterfly' => ['🦋', 'The Social Butterfly', 'Friendly and outgoing'],
                        'the_independent_thinker' => ['🎯', 'The Independent Thinker', 'Self-reliant and unique'],
                        'the_playful_prankster' => ['🎭', 'The Playful Prankster', 'Mischievous and fun-loving'],
                        'the_anxious_angel' => ['😇', 'The Anxious Angel', 'Gentle and sensitive']
                    ];
                    
                    $selectedPersonality = $_POST['personality'] ?? 'the_gentle_giant';
                    
                    foreach ($personalities as $key => $data):
                    ?>
                    <label class="personality-option block p-3 rounded-xl <?php echo $selectedPersonality === $key ? 'selected' : ''; ?>">
                        <input type="radio" name="personality" value="<?php echo $key; ?>" 
                               class="hidden" <?php echo $selectedPersonality === $key ? 'checked' : ''; ?>>
                        <div class="flex items-center">
                            <span class="text-2xl mr-3"><?php echo $data[0]; ?></span>
                            <div>
                                <div class="font-semibold text-gray-900"><?php echo $data[1]; ?></div>
                                <div class="text-xs text-gray-600"><?php echo $data[2]; ?></div>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Gender -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-3">
                    <i class="fas fa-venus-mars mr-2 text-purple-500"></i>
                    Gender
                </label>
                <select name="gender" class="w-full px-4 py-3 border border-gray-300 rounded-xl form-glow focus:outline-none">
                    <option value="unknown" <?php echo ($_POST['gender'] ?? '') === 'unknown' ? 'selected' : ''; ?>>Unknown / Any</option>
                    <option value="female" <?php echo ($_POST['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                    <option value="male" <?php echo ($_POST['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                </select>
            </div>
            
            <!-- Color -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-3">
                    <i class="fas fa-palette mr-2 text-purple-500"></i>
                    Color Pattern
                </label>
                <select name="color" class="w-full px-4 py-3 border border-gray-300 rounded-xl form-glow focus:outline-none">
                    <option value="mixed" <?php echo ($_POST['color'] ?? '') === 'mixed' ? 'selected' : ''; ?>>Mixed / Any</option>
                    <option value="black" <?php echo ($_POST['color'] ?? '') === 'black' ? 'selected' : ''; ?>>Black</option>
                    <option value="white" <?php echo ($_POST['color'] ?? '') === 'white' ? 'selected' : ''; ?>>White</option>
                    <option value="gray" <?php echo ($_POST['color'] ?? '') === 'gray' ? 'selected' : ''; ?>>Gray</option>
                    <option value="orange" <?php echo ($_POST['color'] ?? '') === 'orange' ? 'selected' : ''; ?>>Orange/Ginger</option>
                    <option value="brown" <?php echo ($_POST['color'] ?? '') === 'brown' ? 'selected' : ''; ?>>Brown</option>
                    <option value="calico" <?php echo ($_POST['color'] ?? '') === 'calico' ? 'selected' : ''; ?>>Calico</option>
                    <option value="tabby" <?php echo ($_POST['color'] ?? '') === 'tabby' ? 'selected' : ''; ?>>Tabby</option>
                </select>
            </div>
            
            <!-- Size -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-3">
                    <i class="fas fa-expand-arrows-alt mr-2 text-purple-500"></i>
                    Size
                </label>
                <select name="size" class="w-full px-4 py-3 border border-gray-300 rounded-xl form-glow focus:outline-none">
                    <option value="medium" <?php echo ($_POST['size'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="tiny" <?php echo ($_POST['size'] ?? '') === 'tiny' ? 'selected' : ''; ?>>Tiny</option>
                    <option value="small" <?php echo ($_POST['size'] ?? '') === 'small' ? 'selected' : ''; ?>>Small</option>
                    <option value="large" <?php echo ($_POST['size'] ?? '') === 'large' ? 'selected' : ''; ?>>Large</option>
                    <option value="huge" <?php echo ($_POST['size'] ?? '') === 'huge' ? 'selected' : ''; ?>>Huge</option>
                </select>
            </div>
            
            <!-- Style -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-3">
                    <i class="fas fa-magic mr-2 text-purple-500"></i>
                    Name Style
                </label>
                <select name="style" class="w-full px-4 py-3 border border-gray-300 rounded-xl form-glow focus:outline-none">
                    <option value="classic" <?php echo ($_POST['style'] ?? '') === 'classic' ? 'selected' : ''; ?>>Classic & Timeless</option>
                    <option value="elegant" <?php echo ($_POST['style'] ?? '') === 'elegant' ? 'selected' : ''; ?>>Elegant & Sophisticated</option>
                    <option value="playful" <?php echo ($_POST['style'] ?? '') === 'playful' ? 'selected' : ''; ?>>Playful & Quirky</option>
                    <option value="mystical" <?php echo ($_POST['style'] ?? '') === 'mystical' ? 'selected' : ''; ?>>Mystical & Magical</option>
                    <option value="nature" <?php echo ($_POST['style'] ?? '') === 'nature' ? 'selected' : ''; ?>>Nature Inspired</option>
                    <option value="cultural" <?php echo ($_POST['style'] ?? '') === 'cultural' ? 'selected' : ''; ?>>Cultural & International</option>
                    <option value="food" <?php echo ($_POST['style'] ?? '') === 'food' ? 'selected' : ''; ?>>Food Inspired</option>
                    <option value="royal" <?php echo ($_POST['style'] ?? '') === 'royal' ? 'selected' : ''; ?>>Royal & Noble</option>
                    <option value="literary" <?php echo ($_POST['style'] ?? '') === 'literary' ? 'selected' : ''; ?>>Literary & Artistic</option>
                </select>
            </div>
            
            <!-- Number of Names -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-3">
                    <i class="fas fa-list-ol mr-2 text-purple-500"></i>
                    Number of Names
                </label>
                <select name="count" class="w-full px-4 py-3 border border-gray-300 rounded-xl form-glow focus:outline-none">
                    <option value="8" <?php echo ($_POST['count'] ?? '12') === '8' ? 'selected' : ''; ?>>8 names</option>
                    <option value="12" <?php echo ($_POST['count'] ?? '12') === '12' ? 'selected' : ''; ?>>12 names</option>
                    <option value="16" <?php echo ($_POST['count'] ?? '12') === '16' ? 'selected' : ''; ?>>16 names</option>
                    <option value="20" <?php echo ($_POST['count'] ?? '12') === '20' ? 'selected' : ''; ?>>20 names</option>
                </select>
            </div>
            
            <!-- Generate Button -->
            <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-4 px-6 rounded-xl font-bold text-lg hover:from-purple-700 hover:to-indigo-700 transition-all shadow-lg">
                <i class="fas fa-magic mr-2"></i>
                Generate AI Names
            </button>
        </form>
    </div>
</div>

<!-- Generated Names Display -->
<div class="lg:col-span-2">
    <?php if ($generatedNames && $generatedNames['success']): ?>
    <div class="bg-white rounded-3xl p-6 shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 gradient-text">
                <i class="fas fa-stars mr-2"></i>
                AI-Generated Names
            </h2>
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-robot mr-2 text-purple-500"></i>
                <span>Model: <?php echo $generatedNames['model_version']; ?></span>
            </div>
        </div>
        
        <!-- Generation Info -->
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-4 mb-6 border border-purple-100">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="font-semibold text-gray-700">Personality:</span>
                    <div class="text-purple-600"><?php echo ucfirst(str_replace('_', ' ', $generatedNames['generation_criteria']['personality'])); ?></div>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Style:</span>
                    <div class="text-purple-600"><?php echo ucfirst($generatedNames['generation_criteria']['style']); ?></div>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Generated:</span>
                    <div class="text-purple-600"><?php echo count($generatedNames['names']); ?> names</div>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Timestamp:</span>
                    <div class="text-purple-600"><?php echo date('H:i:s', strtotime($generatedNames['generation_timestamp'])); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Names Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($generatedNames['names'] as $index => $nameData): ?>
            <div class="name-card bg-gradient-to-br from-white to-gray-50 rounded-xl p-6 shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($nameData['name']); ?></h3>
                    <button onclick="copyToClipboard('<?php echo htmlspecialchars($nameData['name']); ?>', this)" 
                            class="copy-button px-3 py-1 text-xs border border-gray-300 rounded-lg hover:bg-purple-500 hover:text-white transition-all">
                        <i class="fas fa-copy mr-1"></i>Copy
                    </button>
                </div>
                
                <!-- Confidence Score -->
                <div class="mb-3">
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600">AI Confidence</span>
                        <span class="font-semibold text-gray-900"><?php echo round($nameData['confidence'] * 100); ?>%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="confidence-bar rounded-full h-2" style="width: <?php echo round($nameData['confidence'] * 100); ?>%"></div>
                    </div>
                </div>
                
                <!-- Metrics -->
                <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 mb-3">
                    <div>
                        <span class="font-semibold">Personality Match:</span>
                        <span><?php echo round($nameData['personality_match'] * 100); ?>%</span>
                    </div>
                    <div>
                        <span class="font-semibold">Uniqueness:</span>
                        <span><?php echo round($nameData['uniqueness_score'] * 100); ?>%</span>
                    </div>
                </div>
                
                <!-- Pronunciation Guide -->
                <div class="text-xs text-gray-500 mb-3">
                    <span class="font-semibold">Pronunciation:</span>
                    <span class="font-mono"><?php echo htmlspecialchars($nameData['pronunciation_guide']); ?></span>
                </div>
                
                <!-- Reasoning -->
                <div class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                    <?php echo htmlspecialchars($nameData['reasoning']); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Actions -->
        <div class="mt-6 flex flex-col sm:flex-row gap-4">
            <button onclick="generateMore()" class="flex-1 bg-gradient-to-r from-green-500 to-emerald-500 text-white py-3 px-6 rounded-xl font-semibold hover:from-green-600 hover:to-emerald-600 transition-all">
                <i class="fas fa-sync-alt mr-2"></i>
                Generate More Names
            </button>
            <button onclick="exportNames()" class="flex-1 bg-gradient-to-r from-blue-500 to-cyan-500 text-white py-3 px-6 rounded-xl font-semibold hover:from-blue-600 hover:to-cyan-600 transition-all">
                <i class="fas fa-download mr-2"></i>
                Export Names
            </button>
            <button onclick="shareNames()" class="flex-1 bg-gradient-to-r from-pink-500 to-rose-500 text-white py-3 px-6 rounded-xl font-semibold hover:from-pink-600 hover:to-rose-600 transition-all">
                <i class="fas fa-share mr-2"></i>
                Share Results
            </button>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Getting Started -->
    <div class="bg-white rounded-3xl p-8 shadow-xl text-center">
        <div class="text-6xl mb-4">🎯</div>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Ready to Find the Perfect Name?</h2>
        <p class="text-gray-600 mb-6">Use our advanced AI to generate personalized cat names based on your feline's unique personality, appearance, and your preferences.</p>
        
        <!-- Quick Start Options -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <button onclick="quickGenerate('the_playful_prankster', 'playful')" 
                    class="p-4 border border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 transition-all">
                <div class="text-2xl mb-2">🎭</div>
                <div class="font-semibold">Playful Names</div>
                <div class="text-xs text-gray-500">Fun & quirky suggestions</div>
            </button>
            <button onclick="quickGenerate('the_gentle_giant', 'elegant')" 
                    class="p-4 border border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 transition-all">
                <div class="text-2xl mb-2">👑</div>
                <div class="font-semibold">Elegant Names</div>
                <div class="text-xs text-gray-500">Sophisticated & refined</div>
            </button>
            <button onclick="quickGenerate('the_wise_observer', 'mystical')" 
                    class="p-4 border border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 transition-all">
                <div class="text-2xl mb-2">🔮</div>
                <div class="font-semibold">Mystical Names</div>
                <div class="text-xs text-gray-500">Magical & enchanting</div>
            </button>
        </div>
        
        <p class="text-sm text-gray-500">Or customize your search using the form on the left</p>
    </div>
    <?php endif; ?>
</div>

</div>

<!-- JavaScript -->
<script>
// Handle personality option selection
document.addEventListener('DOMContentLoaded', function() {
    const personalityOptions = document.querySelectorAll('.personality-option');
    personalityOptions.forEach(option => {
        option.addEventListener('click', function() {
            personalityOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input').checked = true;
        });
    });
});

// Copy name to clipboard
function copyToClipboard(name, button) {
    navigator.clipboard.writeText(name).then(function() {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-1"></i>Copied!';
        button.classList.add('bg-green-500', 'text-white');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('bg-green-500', 'text-white');
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
        alert('Failed to copy name to clipboard');
    });
}

// Quick generate function
function quickGenerate(personality, style) {
    const form = document.querySelector('form');
    form.querySelector('[name="personality"][value="' + personality + '"]').checked = true;
    form.querySelector('[name="style"]').value = style;
    form.submit();
}

// Generate more names
function generateMore() {
    document.querySelector('form').submit();
}

// Export names
function exportNames() {
    <?php if ($generatedNames && $generatedNames['success']): ?>
    const names = <?php echo json_encode(array_column($generatedNames['names'], 'name')); ?>;
    const criteria = <?php echo json_encode($generatedNames['generation_criteria']); ?>;
    
    let exportText = `🤖 AI-Generated Cat Names\n`;
    exportText += `Generated on: ${new Date().toLocaleString()}\n`;
    exportText += `Personality: ${criteria.personality.replace(/_/g, ' ')}\n`;
    exportText += `Style: ${criteria.style}\n`;
    exportText += `\nSuggested Names:\n`;
    exportText += names.map((name, index) => `${index + 1}. ${name}`).join('\n');
    
    const blob = new Blob([exportText], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `cat-names-${Date.now()}.txt`;
    a.click();
    window.URL.revokeObjectURL(url);
    <?php else: ?>
    alert('No names to export. Please generate names first.');
    <?php endif; ?>
}

// Share names
function shareNames() {
    <?php if ($generatedNames && $generatedNames['success']): ?>
    const names = <?php echo json_encode(array_slice(array_column($generatedNames['names'], 'name'), 0, 5)); ?>;
    const shareText = `Check out these AI-generated cat names from Purrr.love: ${names.join(', ')}... 🐱✨`;
    
    if (navigator.share) {
        navigator.share({
            title: '🤖 AI Cat Names from Purrr.love',
            text: shareText,
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(shareText + '\n' + window.location.href);
        alert('Share text copied to clipboard!');
    }
    <?php else: ?>
    alert('No names to share. Please generate names first.');
    <?php endif; ?>
}

// Add sparkle animation to AI icon
setInterval(() => {
    const sparkles = document.querySelectorAll('.sparkle');
    sparkles.forEach(sparkle => {
        sparkle.style.transform = `rotate(${Math.random() * 360}deg)`;
    });
}, 2000);
</script>

</body>
</html>
