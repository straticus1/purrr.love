<?php
/**
 * 🐾 Purrr.love Behavioral Tracking System
 * Real-time behavior monitoring and personality-based predictions
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Advanced Behavioral Tracking System
 */
class BehavioralTrackingSystem {
    private $pdo;
    private $aiPersonalityEngine;
    
    public function __construct() {
        $this->pdo = get_db();
        $this->aiPersonalityEngine = new AdvancedAIPersonalityEngine();
    }
    
    /**
     * Record a behavioral observation
     */
    public function recordBehavior($catId, $behaviorType, $intensity = 'medium', $duration = 1, $context = []) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO cat_behavior_observations 
                (cat_id, behavior_type, behavior_intensity, duration_minutes, environmental_context, social_context, observed_at, observer_type, confidence_score)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 'human', 1.00)
            ");
            
            $stmt->execute([
                $catId,
                $behaviorType,
                $intensity,
                $duration,
                json_encode($context['environmental'] ?? []),
                json_encode($context['social'] ?? [])
            ]);
            
            // Update personality analysis based on new behavior
            $this->updatePersonalityFromBehavior($catId, $behaviorType, $intensity);
            
            // Record emotional state based on behavior
            $this->recordEmotionalState($catId, $behaviorType, $intensity);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error recording behavior: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Record emotional state based on behavior
     */
    private function recordEmotionalState($catId, $behaviorType, $intensity) {
        $emotionMapping = [
            'play' => 'playful',
            'rest' => 'calm',
            'explore' => 'curious',
            'socialize' => 'happy',
            'groom' => 'content',
            'hunt' => 'excited',
            'eat' => 'content',
            'sleep' => 'sleepy',
            'vocalize' => 'excited',
            'aggressive' => 'irritated',
            'submissive' => 'anxious',
            'anxious' => 'anxious',
            'purr' => 'content',
            'knead' => 'relaxed',
            'stretch' => 'comfortable',
            'hide' => 'fearful',
            'mark' => 'territorial',
            'chase' => 'excited',
            'pounce' => 'playful',
            'climb' => 'confident',
            'scratch' => 'satisfied',
            'hiss' => 'defensive',
            'growl' => 'threatened',
            'meow' => 'communicative',
            'yowl' => 'distressed',
            'chirp' => 'excited',
            'trill' => 'friendly',
            'chatter' => 'frustrated'
        ];
        
        $emotionType = $emotionMapping[$behaviorType] ?? 'calm';
        $intensityScore = $this->calculateIntensityScore($intensity);
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO cat_emotional_states 
                (cat_id, emotion_type, intensity_score, duration_minutes, behavioral_indicators, recorded_at, confidence_score)
                VALUES (?, ?, ?, 1, ?, NOW(), 0.8)
            ");
            
            $stmt->execute([
                $catId,
                $emotionType,
                $intensityScore,
                json_encode([
                    'behavior_type' => $behaviorType,
                    'intensity' => $intensity,
                    'source' => 'behavioral_observation'
                ])
            ]);
            
        } catch (Exception $e) {
            error_log("Error recording emotional state: " . $e->getMessage());
        }
    }
    
    /**
     * Update personality analysis based on new behavior
     */
    private function updatePersonalityFromBehavior($catId, $behaviorType, $intensity) {
        // Get recent behavioral data
        $recentBehaviors = $this->getRecentBehaviors($catId, 7); // Last 7 days
        
        // Check if we have enough data for personality update
        if (count($recentBehaviors) >= 10) {
            // Trigger personality reanalysis
            try {
                $this->aiPersonalityEngine->predictAdvancedPersonality($catId, true);
                
                // Also update enhanced personality type if available
                if (function_exists('determineCatPersonalityType')) {
                    $newPersonalityType = determineCatPersonalityType($catId);
                    $this->updateCatPersonalityType($catId, $newPersonalityType);
                }
            } catch (Exception $e) {
                error_log("Error updating personality from behavior: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Update cat's personality type in database
     */
    private function updateCatPersonalityType($catId, $personalityType) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE cats 
                SET personality_type = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$personalityType, $catId]);
        } catch (Exception $e) {
            error_log("Error updating cat personality type: " . $e->getMessage());
        }
    }
    
    /**
     * Get recent behavioral patterns
     */
    public function getRecentBehaviors($catId, $days = 7) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    behavior_type,
                    behavior_intensity,
                    duration_minutes,
                    environmental_context,
                    social_context,
                    observed_at
                FROM cat_behavior_observations
                WHERE cat_id = ? 
                AND observed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY observed_at DESC
            ");
            
            $stmt->execute([$catId, $days]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error getting recent behaviors: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Analyze behavioral patterns
     */
    public function analyzeBehavioralPatterns($catId, $days = 30) {
        try {
            $behaviors = $this->getRecentBehaviors($catId, $days);
            
            if (empty($behaviors)) {
                return null;
            }
            
            $analysis = [
                'total_observations' => count($behaviors),
                'behavior_frequency' => [],
                'intensity_distribution' => [],
                'time_patterns' => [],
                'environmental_correlations' => [],
                'social_correlations' => []
            ];
            
            // Calculate behavior frequency
            foreach ($behaviors as $behavior) {
                $type = $behavior['behavior_type'];
                $analysis['behavior_frequency'][$type] = ($analysis['behavior_frequency'][$type] ?? 0) + 1;
                
                // Intensity distribution
                $intensity = $behavior['behavior_intensity'];
                $analysis['intensity_distribution'][$intensity] = ($analysis['intensity_distribution'][$intensity] ?? 0) + 1;
                
                // Time patterns
                $hour = date('H', strtotime($behavior['observed_at']));
                $analysis['time_patterns'][$hour] = ($analysis['time_patterns'][$hour] ?? 0) + 1;
            }
            
            // Calculate percentages
            foreach ($analysis['behavior_frequency'] as $type => $count) {
                $analysis['behavior_frequency'][$type] = [
                    'count' => $count,
                    'percentage' => round(($count / count($behaviors)) * 100, 1)
                ];
            }
            
            return $analysis;
            
        } catch (Exception $e) {
            error_log("Error analyzing behavioral patterns: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Predict next behavior based on patterns
     */
    public function predictNextBehavior($catId) {
        try {
            $patterns = $this->analyzeBehavioralPatterns($catId, 14);
            $currentHour = date('H');
            
            if (!$patterns) {
                return null;
            }
            
            $predictions = [];
            
            // Time-based predictions
            $hourlyPatterns = $patterns['time_patterns'] ?? [];
            $currentHourActivity = $hourlyPatterns[$currentHour] ?? 0;
            
            // Behavior frequency predictions
            $behaviorFreq = $patterns['behavior_frequency'] ?? [];
            
            foreach ($behaviorFreq as $behavior => $data) {
                $baseProbability = $data['percentage'];
                
                // Adjust based on time of day
                $timeAdjustment = $this->getTimeBasedAdjustment($behavior, $currentHour);
                
                // Adjust based on recent activity
                $activityAdjustment = $this->getActivityAdjustment($currentHourActivity);
                
                $finalProbability = $baseProbability * $timeAdjustment * $activityAdjustment;
                
                $predictions[$behavior] = min(100, max(0, round($finalProbability)));
            }
            
            // Sort by probability
            arsort($predictions);
            
            return $predictions;
            
        } catch (Exception $e) {
            error_log("Error predicting next behavior: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get time-based behavior adjustments
     */
    private function getTimeBasedAdjustment($behavior, $hour) {
        $timePreferences = [
            'sleep' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 22, 23], // Night hours
            'play' => [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19], // Day hours
            'eat' => [6, 7, 8, 12, 13, 18, 19, 20], // Meal times
            'explore' => [8, 9, 10, 11, 16, 17, 18, 19], // Active hours
            'rest' => [13, 14, 15, 21, 22, 23], // Rest periods
            'socialize' => [9, 10, 11, 17, 18, 19, 20], // Social hours
            'groom' => [10, 11, 12, 13, 14, 15, 16, 17], // Grooming hours
            'hunt' => [5, 6, 7, 8, 18, 19, 20, 21], // Hunting hours
            'vocalize' => [6, 7, 8, 18, 19, 20, 21], // Vocal hours
            'purr' => [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20], // Content hours
            'knead' => [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20], // Relaxed hours
            'stretch' => [6, 7, 8, 9, 18, 19, 20, 21], // Wake/sleep transitions
            'hide' => [0, 1, 2, 3, 4, 5, 22, 23], // Night hours
            'mark' => [5, 6, 7, 8, 18, 19, 20, 21], // Territory marking hours
            'chase' => [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19], // Active play hours
            'pounce' => [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19], // Play hours
            'climb' => [8, 9, 10, 11, 16, 17, 18, 19], // Active hours
            'scratch' => [6, 7, 8, 9, 18, 19, 20, 21], // Territory marking hours
            'hiss' => [0, 1, 2, 3, 4, 5, 22, 23], // Defensive hours
            'growl' => [0, 1, 2, 3, 4, 5, 22, 23], // Threat hours
            'meow' => [6, 7, 8, 9, 18, 19, 20, 21], // Communication hours
            'yowl' => [0, 1, 2, 3, 4, 5, 22, 23], // Distress hours
            'chirp' => [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19], // Excited hours
            'trill' => [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19], // Friendly hours
            'chatter' => [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19], // Frustrated hours
        ];
        
        $preferredHours = $timePreferences[$behavior] ?? [];
        
        if (in_array($hour, $preferredHours)) {
            return 1.5; // Increase probability during preferred hours
        } else {
            return 0.7; // Decrease probability during non-preferred hours
        }
    }
    
    /**
     * Get activity-based adjustments
     */
    private function getActivityAdjustment($currentActivity) {
        if ($currentActivity > 5) {
            return 0.8; // Reduce probability if already very active
        } elseif ($currentActivity < 2) {
            return 1.3; // Increase probability if low activity
        } else {
            return 1.0; // Normal probability
        }
    }
    
    /**
     * Calculate intensity score
     */
    private function calculateIntensityScore($intensity) {
        $scores = [
            'low' => 0.3,
            'medium' => 0.6,
            'high' => 0.9
        ];
        
        return $scores[$intensity] ?? 0.5;
    }
    
    /**
     * Get behavioral insights
     */
    public function getBehavioralInsights($catId) {
        try {
            $patterns = $this->analyzeBehavioralPatterns($catId, 30);
            $predictions = $this->predictNextBehavior($catId);
            
            if (!$patterns) {
                return null;
            }
            
            $insights = [
                'dominant_behaviors' => $this->getDominantBehaviors($patterns),
                'activity_patterns' => $this->getActivityPatterns($patterns),
                'behavioral_trends' => $this->getBehavioralTrends($patterns),
                'predictions' => $predictions,
                'recommendations' => $this->getBehavioralRecommendations($patterns, $predictions)
            ];
            
            return $insights;
            
        } catch (Exception $e) {
            error_log("Error getting behavioral insights: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get dominant behaviors
     */
    private function getDominantBehaviors($patterns) {
        $behaviors = $patterns['behavior_frequency'] ?? [];
        arsort($behaviors);
        
        $dominant = [];
        $count = 0;
        foreach ($behaviors as $behavior => $data) {
            if ($count >= 3) break; // Top 3 behaviors
            $dominant[] = [
                'behavior' => $behavior,
                'frequency' => $data['percentage'],
                'description' => $this->getBehaviorDescription($behavior)
            ];
            $count++;
        }
        
        return $dominant;
    }
    
    /**
     * Get activity patterns
     */
    private function getActivityPatterns($patterns) {
        $timePatterns = $patterns['time_patterns'] ?? [];
        
        // Find peak activity hours
        arsort($timePatterns);
        $peakHours = array_slice(array_keys($timePatterns), 0, 3);
        
        return [
            'peak_hours' => $peakHours,
            'activity_distribution' => $timePatterns,
            'most_active_period' => $this->getMostActivePeriod($timePatterns)
        ];
    }
    
    /**
     * Get behavioral trends
     */
    private function getBehavioralTrends($patterns) {
        // This would compare current patterns with historical data
        // For now, return basic trend analysis
        return [
            'trend_analysis' => 'Stable behavioral patterns observed',
            'change_indicators' => [],
            'stability_score' => 0.85
        ];
    }
    
    /**
     * Get behavioral recommendations
     */
    private function getBehavioralRecommendations($patterns, $predictions) {
        $recommendations = [];
        
        // Analyze behavior frequency
        $behaviors = $patterns['behavior_frequency'] ?? [];
        
        // Check for concerning patterns
        if (isset($behaviors['aggressive']) && $behaviors['aggressive']['percentage'] > 20) {
            $recommendations[] = "High aggression levels detected. Consider environmental enrichment and stress reduction.";
        }
        
        if (isset($behaviors['anxious']) && $behaviors['anxious']['percentage'] > 15) {
            $recommendations[] = "Anxiety indicators present. Provide safe spaces and reduce environmental stressors.";
        }
        
        if (isset($behaviors['play']) && $behaviors['play']['percentage'] < 10) {
            $recommendations[] = "Low play activity. Increase interactive toys and play opportunities.";
        }
        
        if (isset($behaviors['socialize']) && $behaviors['socialize']['percentage'] < 5) {
            $recommendations[] = "Minimal social interaction. Consider companion or increased human interaction.";
        }
        
        // Check for new enhanced behaviors
        if (isset($behaviors['hide']) && $behaviors['hide']['percentage'] > 25) {
            $recommendations[] = "Frequent hiding behavior. Ensure safe spaces and reduce environmental stressors.";
        }
        
        if (isset($behaviors['yowl']) && $behaviors['yowl']['percentage'] > 10) {
            $recommendations[] = "Excessive yowling detected. Check for health issues or environmental stress.";
        }
        
        if (isset($behaviors['chatter']) && $behaviors['chatter']['percentage'] > 15) {
            $recommendations[] = "Frequent chattering may indicate frustration. Provide more stimulation opportunities.";
        }
        
        // Positive reinforcement recommendations
        if (isset($behaviors['play']) && $behaviors['play']['percentage'] > 30) {
            $recommendations[] = "Excellent play activity! Continue providing engaging toys and activities.";
        }
        
        if (isset($behaviors['groom']) && $behaviors['groom']['percentage'] > 20) {
            $recommendations[] = "Good grooming habits observed. Maintain current care routine.";
        }
        
        if (isset($behaviors['purr']) && $behaviors['purr']['percentage'] > 25) {
            $recommendations[] = "High purring frequency indicates contentment. Great job maintaining a happy environment!";
        }
        
        if (isset($behaviors['trill']) && $behaviors['trill']['percentage'] > 15) {
            $recommendations[] = "Frequent trilling shows friendliness and social comfort. Excellent socialization!";
        }
        
        return $recommendations;
    }
    
    /**
     * Get personality-based behavioral recommendations
     */
    public function getPersonalityBasedRecommendations($catId) {
        try {
            // Get cat's personality type
            $stmt = $this->pdo->prepare("SELECT personality_type FROM cats WHERE id = ?");
            $stmt->execute([$catId]);
            $cat = $stmt->fetch();
            
            if (!$cat || !$cat['personality_type']) {
                return [];
            }
            
            $personalityType = $cat['personality_type'];
            
            // Get enhanced personality recommendations if available
            if (function_exists('getCatCareRecommendations')) {
                $careData = getCatCareRecommendations($catId);
                return $careData['immediate_priorities'] ?? [];
            }
            
            // Fallback to basic personality-based recommendations
            $basicRecommendations = [
                'playful' => [
                    "Provide plenty of interactive toys and play opportunities",
                    "Schedule regular play sessions throughout the day",
                    "Rotate toys to maintain interest and prevent boredom"
                ],
                'calm' => [
                    "Maintain a quiet, peaceful environment",
                    "Provide comfortable resting spots",
                    "Avoid sudden changes or loud noises"
                ],
                'curious' => [
                    "Offer new toys and environmental enrichment regularly",
                    "Provide safe exploration opportunities",
                    "Create puzzle feeders and interactive challenges"
                ],
                'social' => [
                    "Increase human interaction and attention",
                    "Consider a companion cat if appropriate",
                    "Provide social play opportunities"
                ],
                'independent' => [
                    "Respect their need for space and alone time",
                    "Provide multiple hiding spots and elevated perches",
                    "Allow them to initiate interactions"
                ],
                'territorial' => [
                    "Provide multiple resources (food, water, litter boxes)",
                    "Create vertical territory with cat trees",
                    "Maintain consistent routines and boundaries"
                ],
                'anxious' => [
                    "Create safe, quiet spaces for retreat",
                    "Use pheromone diffusers to reduce stress",
                    "Maintain predictable routines"
                ],
                'energetic' => [
                    "Provide plenty of physical exercise opportunities",
                    "Use puzzle toys and food dispensers",
                    "Consider outdoor access or catio if safe"
                ]
            ];
            
            return $basicRecommendations[$personalityType] ?? [];
            
        } catch (Exception $e) {
            error_log("Error getting personality-based recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Helper methods
     */
    private function getBehaviorDescription($behavior) {
        $descriptions = [
            'play' => 'Engaging in playful activities and games',
            'rest' => 'Relaxing and taking breaks',
            'explore' => 'Investigating new environments and objects',
            'socialize' => 'Interacting with humans or other animals',
            'groom' => 'Self-grooming and hygiene activities',
            'hunt' => 'Hunting behaviors and predatory play',
            'eat' => 'Eating and feeding behaviors',
            'sleep' => 'Sleeping and resting deeply',
            'vocalize' => 'Making sounds and vocalizations',
            'aggressive' => 'Displaying aggressive behaviors',
            'submissive' => 'Showing submissive or fearful behaviors',
            'anxious' => 'Displaying anxiety or stress indicators',
            'purr' => 'Purring contentedly and showing satisfaction',
            'knead' => 'Kneading with paws, showing comfort and relaxation',
            'stretch' => 'Stretching body, showing comfort and flexibility',
            'hide' => 'Hiding or seeking shelter, showing fear or stress',
            'mark' => 'Marking territory with scent or scratching',
            'chase' => 'Chasing objects or other animals in play',
            'pounce' => 'Pouncing on objects or prey in play',
            'climb' => 'Climbing on furniture or cat trees',
            'scratch' => 'Scratching surfaces for territory marking',
            'hiss' => 'Hissing as a defensive warning',
            'growl' => 'Growling as a threat display',
            'meow' => 'Meowing for communication or attention',
            'yowl' => 'Yowling loudly, often indicating distress',
            'chirp' => 'Making chirping sounds, often when excited',
            'trill' => 'Making trilling sounds, showing friendliness',
            'chatter' => 'Chattering teeth, often when frustrated or excited'
        ];
        
        return $descriptions[$behavior] ?? 'Unknown behavior pattern';
    }
    
    private function getMostActivePeriod($timePatterns) {
        $periods = [
            'morning' => array_sum(array_slice($timePatterns, 6, 6)), // 6-12
            'afternoon' => array_sum(array_slice($timePatterns, 12, 6)), // 12-18
            'evening' => array_sum(array_slice($timePatterns, 18, 6)), // 18-24
            'night' => array_sum(array_slice($timePatterns, 0, 6)) // 0-6
        ];
        
        return array_keys($periods, max($periods))[0];
    }
}

/**
 * Global behavioral tracking system instance
 */
$globalBehavioralTrackingSystem = new BehavioralTrackingSystem();

/**
 * Behavioral tracking wrapper functions
 */
function recordCatBehavior($catId, $behaviorType, $intensity = 'medium', $duration = 1, $context = []) {
    global $globalBehavioralTrackingSystem;
    return $globalBehavioralTrackingSystem->recordBehavior($catId, $behaviorType, $intensity, $duration, $context);
}

function getBehavioralInsights($catId) {
    global $globalBehavioralTrackingSystem;
    return $globalBehavioralTrackingSystem->getBehavioralInsights($catId);
}

function predictNextBehavior($catId) {
    global $globalBehavioralTrackingSystem;
    return $globalBehavioralTrackingSystem->predictNextBehavior($catId);
}

function analyzeBehavioralPatterns($catId, $days = 30) {
    global $globalBehavioralTrackingSystem;
    return $globalBehavioralTrackingSystem->analyzeBehavioralPatterns($catId, $days);
}

function getPersonalityBasedRecommendations($catId) {
    global $globalBehavioralTrackingSystem;
    return $globalBehavioralTrackingSystem->getPersonalityBasedRecommendations($catId);
}
?>
