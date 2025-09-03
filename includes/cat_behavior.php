<?php
/**
 * 🐱 Purrr.love Cat Behavior System
 * Core logic for cat personalities, moods, and their influence on game outcomes
 */

define('CAT_PERSONALITY_TYPES', [
    'playful' => ['energy_boost' => 1.5, 'happiness_boost' => 1.3, 'game_bonus' => 1.2],
    'shy' => ['energy_boost' => 0.8, 'happiness_boost' => 0.9, 'game_bonus' => 0.7],
    'aggressive' => ['energy_boost' => 1.4, 'happiness_boost' => 1.1, 'game_bonus' => 1.4],
    'calm' => ['energy_boost' => 1.0, 'happiness_boost' => 1.2, 'game_bonus' => 1.0],
    'curious' => ['energy_boost' => 1.3, 'happiness_boost' => 1.4, 'game_bonus' => 1.3],
    'independent' => ['energy_boost' => 0.9, 'happiness_boost' => 1.0, 'game_bonus' => 0.9],
    'social' => ['energy_boost' => 1.2, 'happiness_boost' => 1.5, 'game_bonus' => 1.1],
    'lazy' => ['energy_boost' => 0.7, 'happiness_boost' => 0.8, 'game_bonus' => 0.6]
]);

define('CAT_MOODS', [
    'happy' => ['multiplier' => 1.2, 'description' => 'Purring with contentment'],
    'excited' => ['multiplier' => 1.4, 'description' => 'Tail twitching with excitement'],
    'calm' => ['multiplier' => 1.0, 'description' => 'Relaxed and peaceful'],
    'sleepy' => ['multiplier' => 0.7, 'description' => 'Yawning and drowsy'],
    'playful' => ['multiplier' => 1.3, 'description' => 'Ready for fun and games'],
    'hungry' => ['multiplier' => 0.8, 'description' => 'Looking for food'],
    'irritated' => ['multiplier' => 0.6, 'description' => 'Tail flicking with annoyance'],
    'sick' => ['multiplier' => 0.5, 'description' => 'Not feeling well']
]);

// 🌿 Catnip and Honeysuckle Response Types
define('CAT_RESPONSE_TYPES', [
    'catnip_responder' => [
        'description' => 'Responds strongly to catnip',
        'catnip_effect' => 2.0,
        'honeysuckle_effect' => 0.5,
        'rarity' => 0.7
    ],
    'honeysuckle_responder' => [
        'description' => 'Responds strongly to honeysuckle instead of catnip',
        'catnip_effect' => 0.3,
        'honeysuckle_effect' => 2.0,
        'rarity' => 0.2
    ],
    'dual_responder' => [
        'description' => 'Responds to both catnip and honeysuckle',
        'catnip_effect' => 1.5,
        'honeysuckle_effect' => 1.5,
        'rarity' => 0.08
    ],
    'non_responder' => [
        'description' => 'Does not respond to catnip or honeysuckle',
        'catnip_effect' => 0.1,
        'honeysuckle_effect' => 0.1,
        'rarity' => 0.02
    ]
]);

// 🎮 Cat-specific game bonuses
define('CAT_GAME_BONUSES', [
    'mouse_hunt' => [
        'playful' => 1.3,
        'curious' => 1.2,
        'aggressive' => 1.4,
        'lazy' => 0.6
    ],
    'yarn_chase' => [
        'playful' => 1.4,
        'social' => 1.2,
        'independent' => 0.8,
        'shy' => 0.7
    ],
    'cat_tower_climbing' => [
        'curious' => 1.3,
        'independent' => 1.2,
        'playful' => 1.1,
        'lazy' => 0.5
    ],
    'bird_watching' => [
        'calm' => 1.3,
        'curious' => 1.4,
        'independent' => 1.2,
        'social' => 0.9
    ],
    'laser_pointer' => [
        'playful' => 1.5,
        'aggressive' => 1.3,
        'curious' => 1.2,
        'lazy' => 0.4
    ],
    'cat_puzzle_box' => [
        'curious' => 1.4,
        'playful' => 1.2,
        'independent' => 1.1,
        'social' => 0.9
    ],
    'string_maze' => [
        'playful' => 1.3,
        'curious' => 1.2,
        'social' => 1.1,
        'shy' => 0.8
    ],
    'box_fort' => [
        'curious' => 1.3,
        'independent' => 1.2,
        'playful' => 1.1,
        'social' => 0.9
    ],
    'catnip_frenzy' => [
        'playful' => 1.4,
        'social' => 1.2,
        'curious' => 1.1,
        'calm' => 0.8
    ],
    'honeysuckle_dance' => [
        'curious' => 1.4,
        'playful' => 1.3,
        'independent' => 1.1,
        'social' => 1.0
    ],
    'laser_tag' => [
        'aggressive' => 1.5,
        'playful' => 1.4,
        'curious' => 1.2,
        'calm' => 0.7
    ]
]);

/**
 * Get cat personality bonuses
 */
function getCatPersonalityBonuses($personalityType) {
    return CAT_PERSONALITY_TYPES[$personalityType] ?? CAT_PERSONALITY_TYPES['calm'];
}

/**
 * Get cat mood multiplier
 */
function getCatMoodMultiplier($mood) {
    return CAT_MOODS[$mood]['multiplier'] ?? 1.0;
}

/**
 * Get cat mood description
 */
function getCatMoodDescription($mood) {
    return CAT_MOODS[$mood]['description'] ?? 'Unknown mood';
}

/**
 * Get game bonus for a specific cat and game
 */
function getCatGameBonus($personalityType, $gameType) {
    $gameBonuses = CAT_GAME_BONUSES[$gameType] ?? [];
    return $gameBonuses[$personalityType] ?? 1.0;
}

/**
 * Calculate cat's response to catnip or honeysuckle
 */
function calculateCatResponse($cat, $stimulant) {
    $responseType = $cat['response_type'] ?? 'catnip_responder';
    $responseConfig = CAT_RESPONSE_TYPES[$responseType];
    
    if ($stimulant === 'catnip') {
        return $responseConfig['catnip_effect'];
    } elseif ($stimulant === 'honeysuckle') {
        return $responseConfig['honeysuckle_effect'];
    }
    
    return 1.0; // No effect
}

/**
 * Get cat's preferred stimulant
 */
function getCatPreferredStimulant($cat) {
    $responseType = $cat['response_type'] ?? 'catnip_responder';
    
    switch ($responseType) {
        case 'honeysuckle_responder':
            return 'honeysuckle';
        case 'dual_responder':
            return ['catnip', 'honeysuckle'];
        case 'non_responder':
            return null;
        default:
            return 'catnip';
    }
}

/**
 * Apply stimulant effect to cat
 */
function applyStimulantEffect($cat, $stimulant) {
    $response = calculateCatResponse($cat, $stimulant);
    
    if ($response > 0.5) {
        $cat['energy'] = min(100, $cat['energy'] + (20 * $response));
        $cat['happiness'] = min(100, $cat['happiness'] + (15 * $response));
        $cat['mood'] = 'excited';
        
        return [
            'success' => true,
            'message' => $cat['name'] . ' responds ' . ($response > 1.5 ? 'strongly' : 'moderately') . ' to ' . $stimulant . '!',
            'energy_boost' => 20 * $response,
            'happiness_boost' => 15 * $response,
            'mood_change' => 'excited'
        ];
    } else {
        return [
            'success' => false,
            'message' => $cat['name'] . ' shows little interest in ' . $stimulant . '.',
            'energy_boost' => 0,
            'happiness_boost' => 0,
            'mood_change' => null
        ];
    }
}

/**
 * Get random response type for new cats
 */
function getRandomResponseType() {
    $rand = mt_rand(1, 100);
    
    if ($rand <= 70) {
        return 'catnip_responder';
    } elseif ($rand <= 90) {
        return 'honeysuckle_responder';
    } elseif ($rand <= 98) {
        return 'dual_responder';
    } else {
        return 'non_responder';
    }
}

/**
 * Get cat personality description
 */
function getCatPersonalityDescription($personalityType) {
    $descriptions = [
        'playful' => 'Always ready for fun and games, loves interactive toys and playtime',
        'shy' => 'Timid and cautious, prefers quiet environments and gentle interactions',
        'aggressive' => 'Bold and assertive, excels in competitive activities and hunting',
        'calm' => 'Relaxed and peaceful, enjoys quiet activities and gentle petting',
        'curious' => 'Explorer at heart, loves investigating new things and solving puzzles',
        'independent' => 'Self-reliant and confident, prefers solo activities and personal space',
        'social' => 'Friendly and outgoing, loves company and group activities',
        'lazy' => 'Relaxed and laid-back, enjoys napping and low-energy activities'
    ];
    
    return $descriptions[$personalityType] ?? 'A cat with a unique personality';
}

/**
 * Get cat mood change suggestion
 */
function getMoodChangeSuggestion($currentMood, $personalityType) {
    $suggestions = [
        'sleepy' => [
            'playful' => 'Try playing with a laser pointer to wake them up!',
            'curious' => 'Show them a new toy to spark their interest',
            'social' => 'Give them some gentle attention and petting',
            'default' => 'Let them rest, they\'ll be more active later'
        ],
        'hungry' => [
            'aggressive' => 'Feed them quickly before they get hangry!',
            'shy' => 'Offer food gently and give them space',
            'default' => 'Time for a meal to boost their energy'
        ],
        'irritated' => [
            'independent' => 'Give them some alone time to calm down',
            'social' => 'Try gentle grooming or quiet companionship',
            'default' => 'Best to let them cool off for a while'
        ],
        'sick' => [
            'default' => 'They need rest and care, consider a vet visit'
        ]
    ];
    
    $moodSuggestions = $suggestions[$currentMood] ?? [];
    return $moodSuggestions[$personalityType] ?? $moodSuggestions['default'] ?? 'Try changing their environment or activity';
}

/**
 * Calculate cat's overall well-being score
 */
function calculateCatWellbeing($cat) {
    $health = $cat['health'] ?? 100;
    $hunger = $cat['hunger'] ?? 100;
    $happiness = $cat['happiness'] ?? 100;
    $energy = $cat['energy'] ?? 100;
    
    return ($health + $hunger + $happiness + $energy) / 4;
}

/**
 * Get cat's current needs
 */
function getCatNeeds($cat) {
    $needs = [];
    
    if (($cat['hunger'] ?? 100) < 30) {
        $needs[] = 'urgent_food';
    } elseif (($cat['hunger'] ?? 100) < 60) {
        $needs[] = 'food';
    }
    
    if (($cat['energy'] ?? 100) < 20) {
        $needs[] = 'urgent_rest';
    } elseif (($cat['energy'] ?? 100) < 50) {
        $needs[] = 'rest';
    }
    
    if (($cat['happiness'] ?? 100) < 40) {
        $needs[] = 'attention';
    }
    
    if (($cat['health'] ?? 100) < 70) {
        $needs[] = 'health_care';
    }
    
    return $needs;
}
?>
