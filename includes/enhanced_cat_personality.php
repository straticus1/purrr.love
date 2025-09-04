<?php
/**
 * 🐱 Purrr.love Enhanced Cat Personality & Needs System
 * Comprehensive personality types and care needs based on real feline behavior
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Enhanced Cat Personality Types
 * Based on real feline behavior research and veterinary science
 */
define('ENHANCED_PERSONALITY_TYPES', [
    'the_gentle_giant' => [
        'name' => 'The Gentle Giant',
        'description' => 'Large, calm, and incredibly patient. Loves to be around people but prefers quiet environments.',
        'traits' => [
            'size_preference' => 'large',
            'energy_level' => 'low_to_medium',
            'social_preference' => 'moderate',
            'noise_tolerance' => 'low',
            'activity_preference' => 'calm',
            'independence_level' => 'medium',
            'affection_style' => 'gentle_and_steady'
        ],
        'breeds' => ['Maine Coon', 'Ragdoll', 'British Shorthair', 'Persian', 'Norwegian Forest Cat'],
        'care_needs' => [
            'space_requirement' => 'large',
            'exercise_needs' => 'moderate',
            'grooming_needs' => 'high',
            'social_interaction' => 'moderate',
            'mental_stimulation' => 'low_to_medium',
            'quiet_time' => 'high'
        ],
        'behavioral_patterns' => [
            'preferred_activities' => ['lounging', 'gentle_play', 'watching_birds', 'being_petted'],
            'avoided_activities' => ['rough_play', 'loud_noises', 'chaotic_environments'],
            'communication_style' => 'soft_vocalizations',
            'stress_indicators' => ['hiding', 'decreased_appetite', 'excessive_grooming']
        ]
    ],
    
    'the_energetic_explorer' => [
        'name' => 'The Energetic Explorer',
        'description' => 'Curious, adventurous, and always on the move. Needs constant mental and physical stimulation.',
        'traits' => [
            'size_preference' => 'any',
            'energy_level' => 'very_high',
            'social_preference' => 'high',
            'noise_tolerance' => 'high',
            'activity_preference' => 'very_active',
            'independence_level' => 'low',
            'affection_style' => 'enthusiastic_and_playful'
        ],
        'breeds' => ['Abyssinian', 'Bengal', 'Oriental Shorthair', 'Siamese', 'Devon Rex'],
        'care_needs' => [
            'space_requirement' => 'large',
            'exercise_needs' => 'very_high',
            'grooming_needs' => 'low_to_medium',
            'social_interaction' => 'very_high',
            'mental_stimulation' => 'very_high',
            'quiet_time' => 'low'
        ],
        'behavioral_patterns' => [
            'preferred_activities' => ['exploring', 'climbing', 'hunting_games', 'interactive_toys', 'puzzle_feeders'],
            'avoided_activities' => ['being_alone', 'boring_environments', 'restriction'],
            'communication_style' => 'vocal_and_expressive',
            'stress_indicators' => ['destructive_behavior', 'excessive_vocalization', 'aggression']
        ]
    ],
    
    'the_wise_observer' => [
        'name' => 'The Wise Observer',
        'description' => 'Intelligent, independent, and selective about social interactions. Prefers to watch and analyze before engaging.',
        'traits' => [
            'size_preference' => 'medium_to_large',
            'energy_level' => 'medium',
            'social_preference' => 'selective',
            'noise_tolerance' => 'medium',
            'activity_preference' => 'moderate',
            'independence_level' => 'high',
            'affection_style' => 'reserved_but_loyal'
        ],
        'breeds' => ['Russian Blue', 'Chartreux', 'Scottish Fold', 'American Shorthair', 'Bombay'],
        'care_needs' => [
            'space_requirement' => 'medium_to_large',
            'exercise_needs' => 'medium',
            'grooming_needs' => 'medium',
            'social_interaction' => 'selective',
            'mental_stimulation' => 'high',
            'quiet_time' => 'high'
        ],
        'behavioral_patterns' => [
            'preferred_activities' => ['bird_watching', 'puzzle_solving', 'gentle_play', 'sunbathing'],
            'avoided_activities' => ['forced_interaction', 'chaotic_environments', 'sudden_movements'],
            'communication_style' => 'subtle_and_meaningful',
            'stress_indicators' => ['withdrawal', 'changes_in_eating', 'excessive_observation']
        ]
    ],
    
    'the_social_butterfly' => [
        'name' => 'The Social Butterfly',
        'description' => 'Extremely social, loves attention, and thrives on human interaction. Can become anxious when alone.',
        'traits' => [
            'size_preference' => 'any',
            'energy_level' => 'high',
            'social_preference' => 'very_high',
            'noise_tolerance' => 'high',
            'activity_preference' => 'social',
            'independence_level' => 'very_low',
            'affection_style' => 'demanding_and_loving'
        ],
        'breeds' => ['Ragdoll', 'Maine Coon', 'Birman', 'Tonkinese', 'Burmese'],
        'care_needs' => [
            'space_requirement' => 'medium',
            'exercise_needs' => 'medium',
            'grooming_needs' => 'medium_to_high',
            'social_interaction' => 'very_high',
            'mental_stimulation' => 'medium',
            'quiet_time' => 'very_low'
        ],
        'behavioral_patterns' => [
            'preferred_activities' => ['cuddling', 'following_humans', 'social_play', 'attention_seeking'],
            'avoided_activities' => ['being_alone', 'ignoring', 'isolation'],
            'communication_style' => 'vocal_and_persistent',
            'stress_indicators' => ['separation_anxiety', 'excessive_vocalization', 'destructive_behavior']
        ]
    ],
    
    'the_independent_thinker' => [
        'name' => 'The Independent Thinker',
        'description' => 'Self-sufficient, intelligent, and prefers to do things on their own terms. Values personal space and routine.',
        'traits' => [
            'size_preference' => 'medium',
            'energy_level' => 'medium',
            'social_preference' => 'low',
            'noise_tolerance' => 'low',
            'activity_preference' => 'independent',
            'independence_level' => 'very_high',
            'affection_style' => 'on_their_terms'
        ],
        'breeds' => ['Norwegian Forest Cat', 'Siberian', 'American Curl', 'Manx', 'Cornish Rex'],
        'care_needs' => [
            'space_requirement' => 'large',
            'exercise_needs' => 'medium',
            'grooming_needs' => 'medium',
            'social_interaction' => 'low',
            'mental_stimulation' => 'high',
            'quiet_time' => 'very_high'
        ],
        'behavioral_patterns' => [
            'preferred_activities' => ['solitary_play', 'exploring', 'hunting_simulation', 'routine_activities'],
            'avoided_activities' => ['forced_interaction', 'unpredictable_schedules', 'crowded_spaces'],
            'communication_style' => 'minimal_but_clear',
            'stress_indicators' => ['aggression', 'hiding', 'routine_disruption']
        ]
    ],
    
    'the_playful_prankster' => [
        'name' => 'The Playful Prankster',
        'description' => 'Mischievous, playful, and always looking for fun. Loves to entertain and be entertained.',
        'traits' => [
            'size_preference' => 'small_to_medium',
            'energy_level' => 'very_high',
            'social_preference' => 'high',
            'noise_tolerance' => 'high',
            'activity_preference' => 'very_playful',
            'independence_level' => 'medium',
            'affection_style' => 'playful_and_affectionate'
        ],
        'breeds' => ['Abyssinian', 'Bengal', 'Devon Rex', 'Cornish Rex', 'Oriental Shorthair'],
        'care_needs' => [
            'space_requirement' => 'medium_to_large',
            'exercise_needs' => 'very_high',
            'grooming_needs' => 'low',
            'social_interaction' => 'high',
            'mental_stimulation' => 'very_high',
            'quiet_time' => 'low'
        ],
        'behavioral_patterns' => [
            'preferred_activities' => ['interactive_play', 'puzzle_toys', 'fetch_games', 'climbing', 'hunting_simulation'],
            'avoided_activities' => ['boring_environments', 'lack_of_attention', 'restriction'],
            'communication_style' => 'expressive_and_playful',
            'stress_indicators' => ['destructive_behavior', 'excessive_energy', 'attention_seeking']
        ]
    ],
    
    'the_anxious_angel' => [
        'name' => 'The Anxious Angel',
        'description' => 'Sensitive, nervous, and easily stressed. Needs a calm, predictable environment and gentle handling.',
        'traits' => [
            'size_preference' => 'small_to_medium',
            'energy_level' => 'low_to_medium',
            'social_preference' => 'selective',
            'noise_tolerance' => 'very_low',
            'activity_preference' => 'calm',
            'independence_level' => 'high',
            'affection_style' => 'gentle_and_cautious'
        ],
        'breeds' => ['Persian', 'Himalayan', 'Exotic Shorthair', 'British Shorthair', 'Scottish Fold'],
        'care_needs' => [
            'space_requirement' => 'medium',
            'exercise_needs' => 'low',
            'grooming_needs' => 'high',
            'social_interaction' => 'gentle_and_selective',
            'mental_stimulation' => 'low',
            'quiet_time' => 'very_high'
        ],
        'behavioral_patterns' => [
            'preferred_activities' => ['gentle_petting', 'quiet_play', 'safe_hiding_spots', 'routine_activities'],
            'avoided_activities' => ['loud_noises', 'sudden_movements', 'chaotic_environments', 'forced_interaction'],
            'communication_style' => 'subtle_and_cautious',
            'stress_indicators' => ['hiding', 'excessive_grooming', 'decreased_appetite', 'aggression']
        ]
    ],
    
    'the_hunter_warrior' => [
        'name' => 'The Hunter Warrior',
        'description' => 'Natural predator with strong hunting instincts. Needs outlets for predatory behavior and physical challenges.',
        'traits' => [
            'size_preference' => 'medium_to_large',
            'energy_level' => 'high',
            'social_preference' => 'moderate',
            'noise_tolerance' => 'medium',
            'activity_preference' => 'hunting_focused',
            'independence_level' => 'high',
            'affection_style' => 'earned_trust'
        ],
        'breeds' => ['Bengal', 'Savannah', 'Maine Coon', 'Norwegian Forest Cat', 'Siberian'],
        'care_needs' => [
            'space_requirement' => 'very_large',
            'exercise_needs' => 'very_high',
            'grooming_needs' => 'medium',
            'social_interaction' => 'moderate',
            'mental_stimulation' => 'very_high',
            'quiet_time' => 'medium'
        ],
        'behavioral_patterns' => [
            'preferred_activities' => ['hunting_simulation', 'climbing', 'stalking_games', 'outdoor_exploration'],
            'avoided_activities' => ['confinement', 'boring_environments', 'lack_of_mental_challenge'],
            'communication_style' => 'minimal_but_assertive',
            'stress_indicators' => ['aggression', 'destructive_behavior', 'excessive_hunting_behavior']
        ]
    ]
]);

/**
 * Comprehensive Cat Needs System
 * Based on feline welfare science and veterinary recommendations
 */
define('CAT_NEEDS_SYSTEM', [
    'physical_needs' => [
        'nutrition' => [
            'name' => 'Nutrition',
            'description' => 'Proper diet and feeding schedule',
            'requirements' => [
                'high_quality_protein' => ['importance' => 'critical', 'frequency' => 'daily'],
                'fresh_water' => ['importance' => 'critical', 'frequency' => 'constant'],
                'balanced_nutrients' => ['importance' => 'critical', 'frequency' => 'daily'],
                'appropriate_portions' => ['importance' => 'high', 'frequency' => 'daily'],
                'feeding_schedule' => ['importance' => 'high', 'frequency' => 'daily']
            ],
            'personality_modifiers' => [
                'the_anxious_angel' => ['feeding_environment' => 'quiet', 'schedule_consistency' => 'critical'],
                'the_hunter_warrior' => ['hunting_simulation' => 'high', 'puzzle_feeders' => 'recommended'],
                'the_energetic_explorer' => ['interactive_feeding' => 'high', 'food_variety' => 'moderate']
            ]
        ],
        
        'exercise' => [
            'name' => 'Physical Exercise',
            'description' => 'Regular physical activity and movement',
            'requirements' => [
                'daily_activity' => ['importance' => 'high', 'duration' => '30-60_minutes'],
                'play_sessions' => ['importance' => 'high', 'frequency' => 'multiple_daily'],
                'climbing_opportunities' => ['importance' => 'medium', 'frequency' => 'daily'],
                'hunting_simulation' => ['importance' => 'medium', 'frequency' => 'daily'],
                'stretching_space' => ['importance' => 'medium', 'frequency' => 'constant']
            ],
            'personality_modifiers' => [
                'the_energetic_explorer' => ['intensity' => 'very_high', 'duration' => 'extended'],
                'the_hunter_warrior' => ['hunting_focus' => 'high', 'climbing' => 'essential'],
                'the_gentle_giant' => ['intensity' => 'low', 'gentle_activities' => 'preferred'],
                'the_anxious_angel' => ['intensity' => 'low', 'stress_free' => 'critical']
            ]
        ],
        
        'grooming' => [
            'name' => 'Grooming & Hygiene',
            'description' => 'Coat care, nail trimming, and dental health',
            'requirements' => [
                'coat_brushing' => ['importance' => 'high', 'frequency' => 'daily_to_weekly'],
                'nail_trimming' => ['importance' => 'medium', 'frequency' => 'bi_weekly'],
                'dental_care' => ['importance' => 'high', 'frequency' => 'daily'],
                'ear_cleaning' => ['importance' => 'medium', 'frequency' => 'weekly'],
                'bath_when_needed' => ['importance' => 'low', 'frequency' => 'as_needed']
            ],
            'personality_modifiers' => [
                'the_gentle_giant' => ['grooming_tolerance' => 'high', 'frequency' => 'increased'],
                'the_anxious_angel' => ['gentle_approach' => 'critical', 'positive_reinforcement' => 'essential'],
                'the_energetic_explorer' => ['patience_required' => 'high', 'short_sessions' => 'recommended']
            ]
        ]
    ],
    
    'mental_needs' => [
        'stimulation' => [
            'name' => 'Mental Stimulation',
            'description' => 'Cognitive challenges and environmental enrichment',
            'requirements' => [
                'puzzle_toys' => ['importance' => 'high', 'frequency' => 'daily'],
                'environmental_enrichment' => ['importance' => 'high', 'frequency' => 'constant'],
                'learning_opportunities' => ['importance' => 'medium', 'frequency' => 'daily'],
                'novel_experiences' => ['importance' => 'medium', 'frequency' => 'weekly'],
                'problem_solving' => ['importance' => 'medium', 'frequency' => 'daily']
            ],
            'personality_modifiers' => [
                'the_wise_observer' => ['complexity' => 'high', 'challenge_level' => 'advanced'],
                'the_playful_prankster' => ['interactive_toys' => 'essential', 'variety' => 'high'],
                'the_independent_thinker' => ['self_directed' => 'preferred', 'complexity' => 'high']
            ]
        ],
        
        'routine' => [
            'name' => 'Predictable Routine',
            'description' => 'Consistent schedule and familiar patterns',
            'requirements' => [
                'feeding_times' => ['importance' => 'high', 'consistency' => 'strict'],
                'play_sessions' => ['importance' => 'medium', 'consistency' => 'regular'],
                'quiet_time' => ['importance' => 'medium', 'consistency' => 'daily'],
                'sleep_schedule' => ['importance' => 'medium', 'consistency' => 'natural'],
                'environmental_stability' => ['importance' => 'high', 'consistency' => 'constant']
            ],
            'personality_modifiers' => [
                'the_anxious_angel' => ['routine_importance' => 'critical', 'flexibility' => 'low'],
                'the_independent_thinker' => ['routine_importance' => 'high', 'predictability' => 'essential'],
                'the_energetic_explorer' => ['routine_importance' => 'medium', 'flexibility' => 'moderate']
            ]
        ]
    ],
    
    'social_needs' => [
        'interaction' => [
            'name' => 'Social Interaction',
            'description' => 'Human and feline social contact',
            'requirements' => [
                'human_bonding' => ['importance' => 'high', 'frequency' => 'daily'],
                'affection_time' => ['importance' => 'high', 'frequency' => 'multiple_daily'],
                'play_interaction' => ['importance' => 'medium', 'frequency' => 'daily'],
                'communication' => ['importance' => 'medium', 'frequency' => 'constant'],
                'respect_for_boundaries' => ['importance' => 'high', 'frequency' => 'constant']
            ],
            'personality_modifiers' => [
                'the_social_butterfly' => ['interaction_need' => 'very_high', 'alone_time' => 'minimal'],
                'the_independent_thinker' => ['interaction_need' => 'low', 'quality_over_quantity' => 'true'],
                'the_anxious_angel' => ['gentle_interaction' => 'essential', 'trust_building' => 'gradual']
            ]
        ],
        
        'territory' => [
            'name' => 'Territorial Security',
            'description' => 'Safe spaces and territory management',
            'requirements' => [
                'safe_hiding_spots' => ['importance' => 'high', 'frequency' => 'constant'],
                'elevated_perches' => ['importance' => 'medium', 'frequency' => 'constant'],
                'territory_marking' => ['importance' => 'medium', 'frequency' => 'natural'],
                'privacy_zones' => ['importance' => 'high', 'frequency' => 'constant'],
                'escape_routes' => ['importance' => 'medium', 'frequency' => 'constant']
            ],
            'personality_modifiers' => [
                'the_hunter_warrior' => ['territory_size' => 'large', 'marking_behavior' => 'high'],
                'the_anxious_angel' => ['hiding_spots' => 'essential', 'privacy' => 'critical'],
                'the_gentle_giant' => ['comfort_zones' => 'important', 'quiet_spaces' => 'preferred']
            ]
        ]
    ],
    
    'emotional_needs' => [
        'security' => [
            'name' => 'Emotional Security',
            'description' => 'Feeling safe and secure in environment',
            'requirements' => [
                'predictable_environment' => ['importance' => 'high', 'frequency' => 'constant'],
                'positive_associations' => ['importance' => 'high', 'frequency' => 'daily'],
                'stress_reduction' => ['importance' => 'high', 'frequency' => 'constant'],
                'confidence_building' => ['importance' => 'medium', 'frequency' => 'daily'],
                'trust_development' => ['importance' => 'high', 'frequency' => 'ongoing']
            ],
            'personality_modifiers' => [
                'the_anxious_angel' => ['security_need' => 'critical', 'stress_sensitivity' => 'very_high'],
                'the_wise_observer' => ['security_need' => 'high', 'trust_building' => 'gradual'],
                'the_social_butterfly' => ['security_need' => 'medium', 'social_security' => 'important']
            ]
        ],
        
        'fulfillment' => [
            'name' => 'Purpose & Fulfillment',
            'description' => 'Having meaningful activities and purpose',
            'requirements' => [
                'hunting_simulation' => ['importance' => 'medium', 'frequency' => 'daily'],
                'problem_solving' => ['importance' => 'medium', 'frequency' => 'daily'],
                'skill_development' => ['importance' => 'low', 'frequency' => 'weekly'],
                'achievement_opportunities' => ['importance' => 'low', 'frequency' => 'daily'],
                'natural_behaviors' => ['importance' => 'high', 'frequency' => 'daily']
            ],
            'personality_modifiers' => [
                'the_hunter_warrior' => ['hunting_fulfillment' => 'critical', 'challenge_level' => 'high'],
                'the_wise_observer' => ['intellectual_fulfillment' => 'high', 'complexity' => 'advanced'],
                'the_playful_prankster' => ['play_fulfillment' => 'critical', 'variety' => 'essential']
            ]
        ]
    ]
]);

/**
 * Enhanced Cat Personality & Needs Manager
 */
class EnhancedCatPersonalityManager {
    private $pdo;
    
    public function __construct() {
        $this->pdo = get_db();
    }
    
    /**
     * Determine cat's personality type based on traits and behavior
     */
    public function determinePersonalityType($catId) {
        try {
            // Get cat data
            $cat = $this->getCatData($catId);
            if (!$cat) {
                throw new Exception('Cat not found');
            }
            
            // Analyze traits
            $traitScores = $this->analyzeTraits($cat);
            
            // Match to personality type
            $personalityType = $this->matchPersonalityType($traitScores, $cat);
            
            // Store personality type
            $this->storePersonalityType($catId, $personalityType);
            
            return $personalityType;
            
        } catch (Exception $e) {
            error_log("Error determining personality type: " . $e->getMessage());
            return 'the_gentle_giant'; // Default fallback
        }
    }
    
    /**
     * Get comprehensive needs assessment for a cat
     */
    public function getNeedsAssessment($catId) {
        try {
            $personalityType = $this->getPersonalityType($catId);
            $personalityConfig = ENHANCED_PERSONALITY_TYPES[$personalityType];
            
            $needsAssessment = [];
            
            // Analyze each need category
            foreach (CAT_NEEDS_SYSTEM as $category => $needs) {
                $needsAssessment[$category] = [];
                
                foreach ($needs as $needKey => $needConfig) {
                    $assessment = $this->assessNeed($needKey, $needConfig, $personalityConfig, $catId);
                    $needsAssessment[$category][$needKey] = $assessment;
                }
            }
            
            return $needsAssessment;
            
        } catch (Exception $e) {
            error_log("Error getting needs assessment: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get personalized care recommendations
     */
    public function getCareRecommendations($catId) {
        try {
            $personalityType = $this->getPersonalityType($catId);
            $personalityConfig = ENHANCED_PERSONALITY_TYPES[$personalityType];
            $needsAssessment = $this->getNeedsAssessment($catId);
            
            $recommendations = [
                'personality_type' => $personalityType,
                'personality_name' => $personalityConfig['name'],
                'description' => $personalityConfig['description'],
                'immediate_priorities' => [],
                'daily_care' => [],
                'weekly_care' => [],
                'environmental_setup' => [],
                'behavioral_tips' => [],
                'warning_signs' => []
            ];
            
            // Generate recommendations based on needs assessment
            $this->generateRecommendations($needsAssessment, $personalityConfig, $recommendations);
            
            return $recommendations;
            
        } catch (Exception $e) {
            error_log("Error getting care recommendations: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Track needs fulfillment
     */
    public function trackNeedsFulfillment($catId, $needCategory, $needType, $fulfillmentLevel) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO cat_needs_tracking 
                (cat_id, need_category, need_type, fulfillment_level, recorded_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([$catId, $needCategory, $needType, $fulfillmentLevel]);
            
            // Update overall needs satisfaction
            $this->updateNeedsSatisfaction($catId);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error tracking needs fulfillment: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get needs satisfaction score
     */
    public function getNeedsSatisfactionScore($catId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    need_category,
                    AVG(fulfillment_level) as avg_fulfillment,
                    COUNT(*) as tracking_count
                FROM cat_needs_tracking 
                WHERE cat_id = ? 
                AND recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY need_category
            ");
            
            $stmt->execute([$catId]);
            $results = $stmt->fetchAll();
            
            if (empty($results)) {
                return null;
            }
            
            $totalScore = 0;
            $categoryCount = 0;
            
            foreach ($results as $result) {
                $totalScore += $result['avg_fulfillment'];
                $categoryCount++;
            }
            
            $overallScore = $categoryCount > 0 ? $totalScore / $categoryCount : 0;
            
            return [
                'overall_score' => round($overallScore, 2),
                'category_scores' => $results,
                'last_updated' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            error_log("Error getting needs satisfaction score: " . $e->getMessage());
            return null;
        }
    }
    
    // Helper methods
    private function getCatData($catId) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.username as owner_name
            FROM cats c
            LEFT JOIN users u ON c.owner_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$catId]);
        return $stmt->fetch();
    }
    
    private function analyzeTraits($cat) {
        // Analyze personality traits based on cat data
        $traits = [
            'energy_level' => $this->calculateEnergyLevel($cat),
            'social_preference' => $this->calculateSocialPreference($cat),
            'independence_level' => $this->calculateIndependenceLevel($cat),
            'activity_preference' => $this->calculateActivityPreference($cat),
            'noise_tolerance' => $this->calculateNoiseTolerance($cat)
        ];
        
        return $traits;
    }
    
    private function matchPersonalityType($traitScores, $cat) {
        $bestMatch = 'the_gentle_giant';
        $bestScore = 0;
        
        foreach (ENHANCED_PERSONALITY_TYPES as $typeKey => $typeConfig) {
            $score = $this->calculatePersonalityMatch($traitScores, $typeConfig['traits'], $cat);
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $typeKey;
            }
        }
        
        return $bestMatch;
    }
    
    private function calculatePersonalityMatch($traitScores, $personalityTraits, $cat) {
        $score = 0;
        $totalTraits = 0;
        
        foreach ($traitScores as $trait => $value) {
            if (isset($personalityTraits[$trait])) {
                $expectedValue = $personalityTraits[$trait];
                $match = $this->calculateTraitMatch($value, $expectedValue);
                $score += $match;
                $totalTraits++;
            }
        }
        
        // Breed bonus
        if (isset($personalityTraits['breeds']) && in_array($cat['breed'], $personalityTraits['breeds'])) {
            $score += 0.2;
        }
        
        return $totalTraits > 0 ? $score / $totalTraits : 0;
    }
    
    private function calculateTraitMatch($actual, $expected) {
        // Simple matching logic - in real implementation, this would be more sophisticated
        if ($actual === $expected) {
            return 1.0;
        } elseif (is_array($expected) && in_array($actual, $expected)) {
            return 0.8;
        } else {
            return 0.3;
        }
    }
    
    private function assessNeed($needKey, $needConfig, $personalityConfig, $catId) {
        // Get recent fulfillment data
        $recentFulfillment = $this->getRecentFulfillment($catId, $needKey);
        
        // Calculate need importance based on personality
        $importance = $this->calculateNeedImportance($needKey, $needConfig, $personalityConfig);
        
        // Calculate current fulfillment level
        $fulfillmentLevel = $this->calculateFulfillmentLevel($recentFulfillment);
        
        // Determine status
        $status = $this->determineNeedStatus($importance, $fulfillmentLevel);
        
        return [
            'need_name' => $needConfig['name'],
            'description' => $needConfig['description'],
            'importance' => $importance,
            'fulfillment_level' => $fulfillmentLevel,
            'status' => $status,
            'recommendations' => $this->getNeedRecommendations($needKey, $status, $personalityConfig)
        ];
    }
    
    private function generateRecommendations($needsAssessment, $personalityConfig, &$recommendations) {
        // Generate recommendations based on needs assessment
        foreach ($needsAssessment as $category => $needs) {
            foreach ($needs as $needKey => $assessment) {
                if ($assessment['status'] === 'critical' || $assessment['status'] === 'high') {
                    $recommendations['immediate_priorities'][] = $assessment['recommendations'];
                }
                
                if ($assessment['importance'] === 'critical' || $assessment['importance'] === 'high') {
                    $recommendations['daily_care'][] = $assessment['need_name'];
                }
            }
        }
        
        // Add personality-specific recommendations
        $recommendations['behavioral_tips'] = $personalityConfig['behavioral_patterns']['preferred_activities'];
        $recommendations['warning_signs'] = $personalityConfig['behavioral_patterns']['stress_indicators'];
    }
    
    // Additional helper methods would be implemented here...
    private function calculateEnergyLevel($cat) {
        // Analyze energy level based on cat data
        return 'medium'; // Simplified for now
    }
    
    private function calculateSocialPreference($cat) {
        return 'moderate'; // Simplified for now
    }
    
    private function calculateIndependenceLevel($cat) {
        return 'medium'; // Simplified for now
    }
    
    private function calculateActivityPreference($cat) {
        return 'moderate'; // Simplified for now
    }
    
    private function calculateNoiseTolerance($cat) {
        return 'medium'; // Simplified for now
    }
    
    private function storePersonalityType($catId, $personalityType) {
        $stmt = $this->pdo->prepare("
            UPDATE cats 
            SET personality_type = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$personalityType, $catId]);
    }
    
    private function getPersonalityType($catId) {
        $stmt = $this->pdo->prepare("SELECT personality_type FROM cats WHERE id = ?");
        $stmt->execute([$catId]);
        $result = $stmt->fetchColumn();
        return $result ?: 'the_gentle_giant';
    }
    
    private function getRecentFulfillment($catId, $needKey) {
        $stmt = $this->pdo->prepare("
            SELECT fulfillment_level, recorded_at
            FROM cat_needs_tracking
            WHERE cat_id = ? AND need_type = ?
            AND recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY recorded_at DESC
            LIMIT 10
        ");
        $stmt->execute([$catId, $needKey]);
        return $stmt->fetchAll();
    }
    
    private function calculateNeedImportance($needKey, $needConfig, $personalityConfig) {
        // Calculate importance based on personality modifiers
        $baseImportance = $needConfig['requirements'][$needKey]['importance'] ?? 'medium';
        
        // Apply personality modifiers
        if (isset($personalityConfig['care_needs'][$needKey])) {
            $personalityModifier = $personalityConfig['care_needs'][$needKey];
            if ($personalityModifier === 'very_high' || $personalityModifier === 'critical') {
                return 'critical';
            } elseif ($personalityModifier === 'high') {
                return 'high';
            }
        }
        
        return $baseImportance;
    }
    
    private function calculateFulfillmentLevel($recentFulfillment) {
        if (empty($recentFulfillment)) {
            return 0.5; // Default neutral level
        }
        
        $total = 0;
        foreach ($recentFulfillment as $record) {
            $total += $record['fulfillment_level'];
        }
        
        return $total / count($recentFulfillment);
    }
    
    private function determineNeedStatus($importance, $fulfillmentLevel) {
        if ($importance === 'critical' && $fulfillmentLevel < 0.7) {
            return 'critical';
        } elseif ($importance === 'high' && $fulfillmentLevel < 0.6) {
            return 'high';
        } elseif ($fulfillmentLevel < 0.5) {
            return 'medium';
        } else {
            return 'good';
        }
    }
    
    private function getNeedRecommendations($needKey, $status, $personalityConfig) {
        // Generate specific recommendations based on need and status
        $recommendations = [];
        
        switch ($needKey) {
            case 'nutrition':
                if ($status === 'critical' || $status === 'high') {
                    $recommendations[] = "Ensure high-quality protein diet";
                    $recommendations[] = "Maintain consistent feeding schedule";
                }
                break;
            case 'exercise':
                if ($status === 'critical' || $status === 'high') {
                    $recommendations[] = "Increase daily play sessions";
                    $recommendations[] = "Provide climbing opportunities";
                }
                break;
            // Add more cases as needed
        }
        
        return $recommendations;
    }
    
    private function updateNeedsSatisfaction($catId) {
        // Update overall needs satisfaction score
        $satisfactionScore = $this->getNeedsSatisfactionScore($catId);
        
        if ($satisfactionScore) {
            $stmt = $this->pdo->prepare("
                UPDATE cats 
                SET needs_satisfaction_score = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$satisfactionScore['overall_score'], $catId]);
        }
    }
}

/**
 * Global enhanced personality manager instance
 */
$globalEnhancedPersonalityManager = new EnhancedCatPersonalityManager();

/**
 * Enhanced personality wrapper functions
 */
function determineCatPersonalityType($catId) {
    global $globalEnhancedPersonalityManager;
    return $globalEnhancedPersonalityManager->determinePersonalityType($catId);
}

function getCatNeedsAssessment($catId) {
    global $globalEnhancedPersonalityManager;
    return $globalEnhancedPersonalityManager->getNeedsAssessment($catId);
}

function getCatCareRecommendations($catId) {
    global $globalEnhancedPersonalityManager;
    return $globalEnhancedPersonalityManager->getCareRecommendations($catId);
}

function trackCatNeedsFulfillment($catId, $needCategory, $needType, $fulfillmentLevel) {
    global $globalEnhancedPersonalityManager;
    return $globalEnhancedPersonalityManager->trackNeedsFulfillment($catId, $needCategory, $needType, $fulfillmentLevel);
}

function getCatNeedsSatisfactionScore($catId) {
    global $globalEnhancedPersonalityManager;
    return $globalEnhancedPersonalityManager->getNeedsSatisfactionScore($catId);
}
?>
