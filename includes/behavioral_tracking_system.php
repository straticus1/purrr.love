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
            'anxious' => 'anxious'
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
            } catch (Exception $e) {
                error_log("Error updating personality from behavior: " . $e->getMessage());
            }
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
        
        // Positive reinforcement recommendations
        if (isset($behaviors['play']) && $behaviors['play']['percentage'] > 30) {
            $recommendations[] = "Excellent play activity! Continue providing engaging toys and activities.";
        }
        
        if (isset($behaviors['groom']) && $behaviors['groom']['percentage'] > 20) {
            $recommendations[] = "Good grooming habits observed. Maintain current care routine.";
        }
        
        return $recommendations;
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
            'anxious' => 'Displaying anxiety or stress indicators'
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
?>
