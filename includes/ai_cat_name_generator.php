<?php
/**
 * 🤖 Purrr.love AI Cat Name Generator
 * Advanced AI-powered name suggestions based on personality, appearance, and preferences
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * AI Cat Name Generator Engine
 * Uses sophisticated algorithms to generate personalized cat names
 */
class AICatNameGenerator {
    
    private $modelVersion = 'name_ai_v1.0';
    
    // Comprehensive name databases organized by themes
    private $nameDatabase = [
        'classic_female' => [
            'Luna', 'Bella', 'Chloe', 'Sophie', 'Mia', 'Lucy', 'Lily', 'Coco', 'Nala', 'Zoe',
            'Princess', 'Angel', 'Daisy', 'Rosie', 'Ruby', 'Penny', 'Honey', 'Ginger', 'Pearl', 'Ivy'
        ],
        'classic_male' => [
            'Max', 'Charlie', 'Milo', 'Oliver', 'Leo', 'Tiger', 'Smokey', 'Oscar', 'Felix', 'Jack',
            'Simba', 'Shadow', 'Buddy', 'Zeus', 'Oreo', 'Jasper', 'Rocky', 'Duke', 'Bear', 'Chester'
        ],
        'elegant_sophisticated' => [
            'Anastasia', 'Cordelia', 'Seraphina', 'Isabella', 'Victoria', 'Penelope', 'Genevieve', 'Arabella',
            'Maximilian', 'Sebastian', 'Theodore', 'Montgomery', 'Reginald', 'Bartholomew', 'Cornelius', 'Augustus'
        ],
        'playful_quirky' => [
            'Ziggy', 'Biscuit', 'Pickles', 'Noodle', 'Pancake', 'Muffin', 'Cookie', 'Waffles', 'Peanut', 'Pepper',
            'Bubbles', 'Sprinkles', 'Jellybean', 'Cupcake', 'Marshmallow', 'Popcorn', 'Cinnamon', 'Paprika'
        ],
        'mystical_magical' => [
            'Celeste', 'Aurora', 'Mystic', 'Phoenix', 'Raven', 'Sage', 'Willow', 'Storm', 'Crystal', 'Star',
            'Merlin', 'Gandalf', 'Cosmos', 'Nebula', 'Orion', 'Atlas', 'Thor', 'Loki', 'Artemis', 'Apollo'
        ],
        'nature_inspired' => [
            'Aspen', 'Maple', 'River', 'Ocean', 'Sky', 'Rain', 'Sunny', 'Cloud', 'Pebble', 'Fern',
            'Cedar', 'Forest', 'Canyon', 'Summit', 'Meadow', 'Brook', 'Stone', 'Cliff', 'Vale', 'Grove'
        ],
        'cultural_international' => [
            'Akira', 'Sakura', 'Yuki', 'Kenzo', 'Hana', 'Kai', 'Aria', 'Enzo', 'Niko', 'Zara',
            'Diego', 'Carmen', 'Pablo', 'Isabella', 'Frida', 'Mateo', 'Sofia', 'Carlos', 'Luna', 'Rio'
        ],
        'food_inspired' => [
            'Mocha', 'Latte', 'Espresso', 'Caramel', 'Vanilla', 'Cocoa', 'Truffle', 'Mochi', 'Sushi', 'Wasabi',
            'Basil', 'Sage', 'Rosemary', 'Mint', 'Nutmeg', 'Cardamom', 'Saffron', 'Papaya', 'Mango', 'Kiwi'
        ],
        'royal_noble' => [
            'King', 'Queen', 'Prince', 'Princess', 'Duke', 'Duchess', 'Earl', 'Countess', 'Baron', 'Baroness',
            'Majesty', 'Royal', 'Noble', 'Crown', 'Scepter', 'Throne', 'Castle', 'Palace', 'Empire', 'Reign'
        ],
        'literary_artistic' => [
            'Shakespeare', 'Dickens', 'Austen', 'Byron', 'Shelley', 'Keats', 'Wilde', 'Twain', 'Poe', 'Tolkien',
            'Monet', 'Picasso', 'DaVinci', 'Rembrandt', 'Van Gogh', 'Beethoven', 'Mozart', 'Bach', 'Chopin', 'Vivaldi'
        ]
    ];
    
    // Personality-based modifiers
    private $personalityModifiers = [
        'the_gentle_giant' => ['gentle', 'sweet', 'calm', 'peaceful', 'serene'],
        'the_energetic_explorer' => ['zippy', 'dash', 'rocket', 'zoom', 'spark'],
        'the_wise_observer' => ['sage', 'wise', 'zen', 'mystic', 'oracle'],
        'the_social_butterfly' => ['buddy', 'friend', 'sunny', 'happy', 'joy'],
        'the_independent_thinker' => ['solo', 'rebel', 'free', 'unique', 'indie'],
        'the_playful_prankster' => ['mischief', 'trick', 'jest', 'imp', 'rogue'],
        'the_anxious_angel' => ['angel', 'gentle', 'soft', 'whisper', 'dove']
    ];
    
    // Color-based name components
    private $colorNames = [
        'black' => ['Shadow', 'Midnight', 'Onyx', 'Coal', 'Raven', 'Noir', 'Eclipse', 'Phantom'],
        'white' => ['Snow', 'Pearl', 'Ivory', 'Cloud', 'Crystal', 'Angel', 'Ghost', 'Frost'],
        'gray' => ['Smokey', 'Storm', 'Silver', 'Ash', 'Slate', 'Steel', 'Misty', 'Fog'],
        'orange' => ['Sunny', 'Flame', 'Copper', 'Rusty', 'Amber', 'Tangerine', 'Marmalade', 'Blaze'],
        'brown' => ['Cocoa', 'Mocha', 'Coffee', 'Bruno', 'Chestnut', 'Hazel', 'Autumn', 'Maple'],
        'calico' => ['Patches', 'Mosaic', 'Kaleidoscope', 'Harmony', 'Prism', 'Canvas', 'Palette', 'Art'],
        'tabby' => ['Stripe', 'Tiger', 'Zebra', 'Pattern', 'Design', 'Marble', 'Swirl', 'Twist']
    ];
    
    // Size-based modifiers
    private $sizeModifiers = [
        'tiny' => ['Mini', 'Tiny', 'Pea', 'Button', 'Pip', 'Micro', 'Atom', 'Dot'],
        'small' => ['Little', 'Petite', 'Small', 'Compact', 'Dainty', 'Delicate', 'Sweet', 'Cute'],
        'medium' => ['Perfect', 'Ideal', 'Balanced', 'Harmony', 'Grace', 'Elegant', 'Classic', 'Noble'],
        'large' => ['Big', 'Grand', 'Majestic', 'Mighty', 'Strong', 'Bold', 'Proud', 'Royal'],
        'huge' => ['Giant', 'Massive', 'Titan', 'Colossal', 'Enormous', 'Mammoth', 'Supreme', 'Ultimate']
    ];
    
    /**
     * Generate AI-powered cat names based on criteria
     */
    public function generateNames($criteria = []) {
        try {
            // Parse input criteria
            $personality = $criteria['personality'] ?? 'the_gentle_giant';
            $gender = $criteria['gender'] ?? 'unknown';
            $color = $criteria['color'] ?? 'mixed';
            $size = $criteria['size'] ?? 'medium';
            $style = $criteria['style'] ?? 'classic';
            $count = min($criteria['count'] ?? 10, 20); // Max 20 names
            
            // Generate names using AI algorithm
            $generatedNames = $this->generateAINames($personality, $gender, $color, $size, $style, $count);
            
            // Add confidence scores and explanations
            $results = [];
            foreach ($generatedNames as $name) {
                $results[] = [
                    'name' => $name,
                    'confidence' => $this->calculateNameConfidence($name, $criteria),
                    'reasoning' => $this->generateNameReasoning($name, $criteria),
                    'personality_match' => $this->getPersonalityMatch($name, $personality),
                    'uniqueness_score' => $this->calculateUniquenessScore($name),
                    'pronunciation_guide' => $this->getPronunciationGuide($name)
                ];
            }
            
            // Sort by confidence score
            usort($results, function($a, $b) {
                return $b['confidence'] <=> $a['confidence'];
            });
            
            return [
                'success' => true,
                'names' => $results,
                'generation_criteria' => $criteria,
                'model_version' => $this->modelVersion,
                'total_generated' => count($results),
                'generation_timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Advanced AI name generation algorithm
     */
    private function generateAINames($personality, $gender, $color, $size, $style, $count) {
        $names = [];
        $usedNames = [];
        
        // Generate names using different strategies
        $strategies = [
            'database_match' => 0.4,      // 40% from curated database
            'personality_blend' => 0.25,   // 25% personality-based blends
            'color_combination' => 0.15,   // 15% color-based combinations
            'creative_fusion' => 0.20      // 20% creative AI fusion
        ];
        
        foreach ($strategies as $strategy => $percentage) {
            $strategyCount = ceil($count * $percentage);
            
            switch ($strategy) {
                case 'database_match':
                    $names = array_merge($names, $this->getDatabaseNames($gender, $style, $strategyCount, $usedNames));
                    break;
                    
                case 'personality_blend':
                    $names = array_merge($names, $this->getPersonalityBlendNames($personality, $strategyCount, $usedNames));
                    break;
                    
                case 'color_combination':
                    $names = array_merge($names, $this->getColorBasedNames($color, $size, $strategyCount, $usedNames));
                    break;
                    
                case 'creative_fusion':
                    $names = array_merge($names, $this->getCreativeFusionNames($personality, $color, $strategyCount, $usedNames));
                    break;
            }
            
            $usedNames = array_merge($usedNames, $names);
        }
        
        // Ensure uniqueness and trim to requested count
        $names = array_unique($names);
        return array_slice($names, 0, $count);
    }
    
    /**
     * Get names from curated database
     */
    private function getDatabaseNames($gender, $style, $count, $usedNames) {
        $names = [];
        $pools = [];
        
        // Select appropriate name pools
        if ($gender === 'female') {
            $pools[] = $this->nameDatabase['classic_female'];
        } elseif ($gender === 'male') {
            $pools[] = $this->nameDatabase['classic_male'];
        } else {
            $pools[] = array_merge($this->nameDatabase['classic_female'], $this->nameDatabase['classic_male']);
        }
        
        // Add style-specific pools
        switch ($style) {
            case 'elegant':
                $pools[] = $this->nameDatabase['elegant_sophisticated'];
                break;
            case 'playful':
                $pools[] = $this->nameDatabase['playful_quirky'];
                break;
            case 'mystical':
                $pools[] = $this->nameDatabase['mystical_magical'];
                break;
            case 'nature':
                $pools[] = $this->nameDatabase['nature_inspired'];
                break;
            case 'cultural':
                $pools[] = $this->nameDatabase['cultural_international'];
                break;
            case 'food':
                $pools[] = $this->nameDatabase['food_inspired'];
                break;
            case 'royal':
                $pools[] = $this->nameDatabase['royal_noble'];
                break;
            case 'literary':
                $pools[] = $this->nameDatabase['literary_artistic'];
                break;
        }
        
        // Merge all pools and select randomly
        $allNames = array_merge(...$pools);
        $availableNames = array_diff($allNames, $usedNames);
        
        shuffle($availableNames);
        return array_slice($availableNames, 0, $count);
    }
    
    /**
     * Generate personality-blended names
     */
    private function getPersonalityBlendNames($personality, $count, $usedNames) {
        $names = [];
        $modifiers = $this->personalityModifiers[$personality] ?? ['special'];
        
        $baseNames = array_merge(
            $this->nameDatabase['classic_female'],
            $this->nameDatabase['classic_male']
        );
        
        for ($i = 0; $i < $count * 2 && count($names) < $count; $i++) {
            $baseName = $baseNames[array_rand($baseNames)];
            $modifier = $modifiers[array_rand($modifiers)];
            
            // Create blended names
            $blendedNames = [
                $baseName . ucfirst($modifier),
                ucfirst($modifier) . $baseName,
                $baseName . 'the' . ucfirst($modifier),
                'Little' . $baseName,
                $baseName . 'Star'
            ];
            
            foreach ($blendedNames as $name) {
                if (!in_array($name, $usedNames) && !in_array($name, $names) && strlen($name) <= 20) {
                    $names[] = $name;
                    if (count($names) >= $count) break 2;
                }
            }
        }
        
        return $names;
    }
    
    /**
     * Generate color-based names
     */
    private function getColorBasedNames($color, $size, $count, $usedNames) {
        $names = [];
        $colorNames = $this->colorNames[$color] ?? $this->colorNames['gray'];
        $sizeModifiers = $this->sizeModifiers[$size] ?? $this->sizeModifiers['medium'];
        
        // Combine color names with size modifiers
        for ($i = 0; $i < $count * 2 && count($names) < $count; $i++) {
            $colorName = $colorNames[array_rand($colorNames)];
            $sizeModifier = $sizeModifiers[array_rand($sizeModifiers)];
            
            $combinations = [
                $colorName,
                $sizeModifier . $colorName,
                $colorName . $sizeModifier,
                'Sir' . $colorName,
                'Lady' . $colorName,
                $colorName . 'paws',
                $colorName . 'whiskers'
            ];
            
            foreach ($combinations as $name) {
                if (!in_array($name, $usedNames) && !in_array($name, $names) && strlen($name) <= 20) {
                    $names[] = $name;
                    if (count($names) >= $count) break 2;
                }
            }
        }
        
        return $names;
    }
    
    /**
     * Generate creative fusion names using AI creativity
     */
    private function getCreativeFusionNames($personality, $color, $count, $usedNames) {
        $names = [];
        
        // Creative prefixes and suffixes
        $prefixes = ['Mr', 'Miss', 'Captain', 'Professor', 'Sir', 'Lady', 'Princess', 'Prince', 'Lord', 'Dame'];
        $suffixes = ['paws', 'whiskers', 'tail', 'ears', 'eyes', 'fur', 'heart', 'soul', 'spirit', 'magic'];
        
        // Base creative words
        $creativeBase = [
            'Stardust', 'Moonbeam', 'Sunray', 'Rainbow', 'Sparkle', 'Glitter', 'Shimmer', 'Twinkle',
            'Adventure', 'Journey', 'Quest', 'Dream', 'Wonder', 'Magic', 'Miracle', 'Treasure',
            'Whisper', 'Echo', 'Melody', 'Harmony', 'Symphony', 'Rhythm', 'Beat', 'Song',
            'Breeze', 'Wind', 'Storm', 'Thunder', 'Lightning', 'Rain', 'Snow', 'Frost'
        ];
        
        for ($i = 0; $i < $count * 3 && count($names) < $count; $i++) {
            $base = $creativeBase[array_rand($creativeBase)];
            $prefix = $prefixes[array_rand($prefixes)];
            $suffix = $suffixes[array_rand($suffixes)];
            
            $fusionNames = [
                $prefix . $base,
                $base . ucfirst($suffix),
                $base . rand(1, 99),
                substr($base, 0, 4) . substr($creativeBase[array_rand($creativeBase)], -4),
                $base . 'the' . ucfirst($this->personalityModifiers[$personality][array_rand($this->personalityModifiers[$personality])] ?? 'Great')
            ];
            
            foreach ($fusionNames as $name) {
                if (!in_array($name, $usedNames) && !in_array($name, $names) && strlen($name) >= 3 && strlen($name) <= 20) {
                    $names[] = $name;
                    if (count($names) >= $count) break 2;
                }
            }
        }
        
        return $names;
    }
    
    /**
     * Calculate name confidence score
     */
    private function calculateNameConfidence($name, $criteria) {
        $confidence = 0.5; // Base confidence
        
        // Length bonus (sweet spot 4-10 characters)
        $length = strlen($name);
        if ($length >= 4 && $length <= 10) {
            $confidence += 0.15;
        } elseif ($length >= 3 && $length <= 12) {
            $confidence += 0.10;
        }
        
        // Pronunciation bonus (avoid difficult combinations)
        if (!preg_match('/[xqz]{2,}|[bcdfghjklmnpqrstvwxyz]{4,}/i', $name)) {
            $confidence += 0.10;
        }
        
        // Gender match bonus
        if (isset($criteria['gender']) && $criteria['gender'] !== 'unknown') {
            if ($this->genderMatches($name, $criteria['gender'])) {
                $confidence += 0.15;
            }
        }
        
        // Style consistency bonus
        if (isset($criteria['style'])) {
            if ($this->styleMatches($name, $criteria['style'])) {
                $confidence += 0.10;
            }
        }
        
        // Uniqueness bonus (avoid overly common names)
        $uniqueness = $this->calculateUniquenessScore($name);
        $confidence += ($uniqueness - 0.5) * 0.2;
        
        return min(1.0, max(0.1, $confidence));
    }
    
    /**
     * Generate reasoning for name suggestion
     */
    private function generateNameReasoning($name, $criteria) {
        $reasons = [];
        
        $personality = $criteria['personality'] ?? 'the_gentle_giant';
        $color = $criteria['color'] ?? 'mixed';
        $style = $criteria['style'] ?? 'classic';
        
        // Personality reasoning
        $personalityReasons = [
            'the_gentle_giant' => "Perfect for a calm, loving cat who brings peace to the household",
            'the_energetic_explorer' => "Ideal for an adventurous cat who loves to explore and play",
            'the_wise_observer' => "Suits a thoughtful, intelligent cat who watches the world with wisdom",
            'the_social_butterfly' => "Great for a friendly, outgoing cat who loves attention and social time",
            'the_independent_thinker' => "Perfect for a self-reliant cat who does things on their own terms",
            'the_playful_prankster' => "Ideal for a mischievous, fun-loving cat who brings joy and laughter",
            'the_anxious_angel' => "Gentle name for a sensitive cat who needs extra love and care"
        ];
        
        $reasons[] = $personalityReasons[$personality] ?? "Matches your cat's unique personality perfectly";
        
        // Style reasoning
        $styleReasons = [
            'classic' => "A timeless name that never goes out of style",
            'elegant' => "Sophisticated and refined, perfect for a distinguished cat",
            'playful' => "Fun and whimsical, capturing your cat's playful spirit",
            'mystical' => "Mysterious and enchanting, for a cat with magical qualities",
            'nature' => "Inspired by the natural world, perfect for an outdoorsy cat",
            'cultural' => "Rich cultural heritage adds depth and meaning",
            'food' => "Deliciously cute, perfect for a sweet cat",
            'royal' => "Regal and majestic, fit for a cat of noble bearing",
            'literary' => "Intellectual and cultured, perfect for a bookish household"
        ];
        
        if (isset($styleReasons[$style])) {
            $reasons[] = $styleReasons[$style];
        }
        
        // Name-specific reasoning
        if (strlen($name) <= 6) {
            $reasons[] = "Short and easy to call out";
        } elseif (strlen($name) >= 10) {
            $reasons[] = "Distinguished longer name with nickname potential";
        }
        
        if (preg_match('/[aeiou]/i', $name)) {
            $reasons[] = "Pleasant vowel sounds make it easy to pronounce";
        }
        
        return implode('. ', $reasons) . '.';
    }
    
    /**
     * Calculate personality match score
     */
    private function getPersonalityMatch($name, $personality) {
        $matches = [
            'the_gentle_giant' => ['gentle', 'soft', 'calm', 'peace', 'love', 'sweet', 'angel', 'cloud'],
            'the_energetic_explorer' => ['zip', 'dash', 'rocket', 'spark', 'bolt', 'flash', 'zoom', 'swift'],
            'the_wise_observer' => ['sage', 'wise', 'zen', 'mystic', 'oracle', 'scholar', 'think', 'mind'],
            'the_social_butterfly' => ['buddy', 'friend', 'social', 'happy', 'joy', 'sunny', 'bright', 'star'],
            'the_independent_thinker' => ['solo', 'rebel', 'free', 'indie', 'unique', 'lone', 'wild', 'spirit'],
            'the_playful_prankster' => ['trick', 'jest', 'imp', 'mischief', 'fun', 'play', 'joke', 'laugh'],
            'the_anxious_angel' => ['angel', 'gentle', 'soft', 'whisper', 'dove', 'tender', 'care', 'quiet']
        ];
        
        $keywords = $matches[$personality] ?? [];
        $nameLower = strtolower($name);
        
        $matchCount = 0;
        foreach ($keywords as $keyword) {
            if (strpos($nameLower, $keyword) !== false) {
                $matchCount++;
            }
        }
        
        return min(1.0, $matchCount / 3); // Normalize to 0-1 scale
    }
    
    /**
     * Calculate uniqueness score
     */
    private function calculateUniquenessScore($name) {
        // Common names get lower scores
        $commonNames = ['Max', 'Bella', 'Charlie', 'Luna', 'Lucy', 'Leo', 'Milo', 'Kitty', 'Cat', 'Whiskers'];
        
        if (in_array($name, $commonNames)) {
            return 0.2;
        }
        
        // Length and complexity factor into uniqueness
        $length = strlen($name);
        $uniqueness = 0.5;
        
        if ($length >= 8) $uniqueness += 0.2;
        if (preg_match('/[A-Z].*[A-Z]/', $name)) $uniqueness += 0.1; // Multiple capitals
        if (preg_match('/\d/', $name)) $uniqueness += 0.1; // Contains numbers
        
        return min(1.0, $uniqueness);
    }
    
    /**
     * Get pronunciation guide
     */
    private function getPronunciationGuide($name) {
        // Simplified pronunciation guide
        $guide = strtolower($name);
        
        // Common pronunciation patterns
        $patterns = [
            '/ph/' => 'f',
            '/ough/' => 'uff',
            '/augh/' => 'aff',
            '/tion/' => 'shun',
            '/sion/' => 'zhun',
            '/cia/' => 'sha',
            '/x/' => 'ks'
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $guide = preg_replace($pattern, $replacement, $guide);
        }
        
        // Add syllable breaks for longer names
        if (strlen($name) > 6) {
            $guide = chunk_split($guide, 3, '-');
            $guide = rtrim($guide, '-');
        }
        
        return strtoupper($guide);
    }
    
    /**
     * Check if name matches gender
     */
    private function genderMatches($name, $gender) {
        $femaleIndicators = ['bella', 'princess', 'lady', 'miss', 'queen', 'girl', 'she', 'her'];
        $maleIndicators = ['king', 'prince', 'sir', 'mister', 'duke', 'boy', 'he', 'his'];
        
        $nameLower = strtolower($name);
        
        if ($gender === 'female') {
            return array_some($femaleIndicators, function($indicator) use ($nameLower) {
                return strpos($nameLower, $indicator) !== false;
            });
        } elseif ($gender === 'male') {
            return array_some($maleIndicators, function($indicator) use ($nameLower) {
                return strpos($nameLower, $indicator) !== false;
            });
        }
        
        return true; // Unknown gender matches anything
    }
    
    /**
     * Check if name matches style
     */
    private function styleMatches($name, $style) {
        $styleKeywords = [
            'classic' => ['max', 'bella', 'charlie', 'luna', 'milo'],
            'elegant' => ['princess', 'duke', 'lady', 'sir', 'royal'],
            'playful' => ['ziggy', 'bubbles', 'cookie', 'muffin', 'pickle'],
            'mystical' => ['star', 'moon', 'magic', 'crystal', 'mystic'],
            'nature' => ['river', 'forest', 'stone', 'sky', 'storm'],
            'cultural' => ['akira', 'diego', 'aria', 'kai', 'zara'],
            'food' => ['mocha', 'cookie', 'pepper', 'ginger', 'cocoa'],
            'royal' => ['king', 'queen', 'prince', 'royal', 'crown'],
            'literary' => ['shakespeare', 'dickens', 'monet', 'beethoven']
        ];
        
        $keywords = $styleKeywords[$style] ?? [];
        $nameLower = strtolower($name);
        
        foreach ($keywords as $keyword) {
            if (strpos($nameLower, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
}

/**
 * Helper function for array_some functionality
 */
function array_some($array, $callback) {
    foreach ($array as $item) {
        if ($callback($item)) {
            return true;
        }
    }
    return false;
}

/**
 * API Functions for Name Generation
 */

/**
 * Generate cat names based on criteria
 */
function generateCatNames($criteria = []) {
    try {
        $generator = new AICatNameGenerator();
        return $generator->generateNames($criteria);
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get name suggestions for personality type
 */
function getNamesForPersonality($personalityType, $count = 10) {
    return generateCatNames([
        'personality' => $personalityType,
        'count' => $count,
        'style' => 'classic'
    ]);
}

/**
 * Generate themed name collections
 */
function getThemedNameCollection($theme, $count = 15) {
    return generateCatNames([
        'style' => $theme,
        'count' => $count
    ]);
}

/**
 * Get random name suggestions
 */
function getRandomCatNames($count = 10) {
    $styles = ['classic', 'elegant', 'playful', 'mystical', 'nature', 'cultural'];
    $personalities = ['the_gentle_giant', 'the_energetic_explorer', 'the_wise_observer', 'the_social_butterfly'];
    
    return generateCatNames([
        'personality' => $personalities[array_rand($personalities)],
        'style' => $styles[array_rand($styles)],
        'count' => $count
    ]);
}

/**
 * Advanced name generation with multiple criteria
 */
function generateAdvancedCatNames($criteria) {
    // Validate and sanitize input
    $cleanCriteria = [
        'personality' => $criteria['personality'] ?? 'the_gentle_giant',
        'gender' => in_array($criteria['gender'] ?? '', ['male', 'female', 'unknown']) ? $criteria['gender'] : 'unknown',
        'color' => $criteria['color'] ?? 'mixed',
        'size' => $criteria['size'] ?? 'medium',
        'style' => $criteria['style'] ?? 'classic',
        'count' => min(max($criteria['count'] ?? 10, 1), 20)
    ];
    
    return generateCatNames($cleanCriteria);
}
?>
