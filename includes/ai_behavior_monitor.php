<?php
/**
 * 🧠 AI Cat Behavior Monitor
 * ✨ Real-time behavior analysis and pattern recognition
 */

class AIBehaviorMonitor {
    private $pdo;
    private $aiModel;
    private $behaviorPatterns;
    private $healthIndicators;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->initializeAIModel();
        $this->loadBehaviorPatterns();
        $this->loadHealthIndicators();
    }
    
    /**
     * Initialize AI model with pre-trained weights
     */
    private function initializeAIModel() {
        // In production, this would load a real ML model
        $this->aiModel = [
            'version' => '2.1.0',
            'accuracy' => 95.2,
            'last_trained' => date('Y-m-d H:i:s'),
            'training_samples' => 150000,
            'model_type' => 'Neural Network (LSTM + CNN)'
        ];
    }
    
    /**
     * Load known behavior patterns from database
     */
    private function loadBehaviorPatterns() {
        $this->behaviorPatterns = [
            'playful' => [
                'indicators' => ['high_energy', 'active_movement', 'toy_interaction', 'social_engagement'],
                'threshold' => 0.7,
                'health_impact' => 'positive',
                'recommendations' => ['maintain_play_schedule', 'provide_interactive_toys', 'social_interaction']
            ],
            'anxious' => [
                'indicators' => ['hiding_behavior', 'reduced_appetite', 'aggressive_behavior', 'excessive_grooming'],
                'threshold' => 0.6,
                'health_impact' => 'negative',
                'recommendations' => ['create_safe_spaces', 'reduce_stressors', 'consult_veterinarian']
            ],
            'social' => [
                'indicators' => ['seeks_attention', 'follows_owner', 'purring_behavior', 'head_butting'],
                'threshold' => 0.8,
                'health_impact' => 'positive',
                'recommendations' => ['increase_quality_time', 'gentle_petting', 'verbal_affirmation']
            ],
            'independent' => [
                'indicators' => ['solitary_activities', 'self_entertainment', 'territory_marking', 'selective_socializing'],
                'threshold' => 0.7,
                'health_impact' => 'neutral',
                'recommendations' => ['respect_boundaries', 'provide_alone_spaces', 'gradual_socialization']
            ],
            'curious' => [
                'indicators' => ['exploration_behavior', 'investigation_activities', 'learning_response', 'environmental_awareness'],
                'threshold' => 0.75,
                'health_impact' => 'positive',
                'recommendations' => ['provide_exploration_opportunities', 'rotate_toys', 'safe_outdoor_access']
            ]
        ];
    }
    
    /**
     * Load health indicators and their correlations
     */
    private function loadHealthIndicators() {
        $this->healthIndicators = [
            'eating_patterns' => [
                'normal' => ['frequency' => '2-3 times daily', 'amount' => 'consistent', 'enthusiasm' => 'high'],
                'concerning' => ['frequency' => 'less than once daily', 'amount' => 'decreasing', 'enthusiasm' => 'low'],
                'health_implications' => ['weight_loss', 'dehydration', 'nutritional_deficiencies']
            ],
            'sleep_patterns' => [
                'normal' => ['duration' => '12-16 hours daily', 'quality' => 'deep_sleep', 'locations' => 'varied'],
                'concerning' => ['duration' => 'excessive or insufficient', 'quality' => 'restless', 'locations' => 'isolated'],
                'health_implications' => ['stress', 'pain', 'illness']
            ],
            'grooming_behavior' => [
                'normal' => ['frequency' => 'daily', 'thoroughness' => 'complete', 'areas' => 'all_body_parts'],
                'concerning' => ['frequency' => 'excessive or insufficient', 'thoroughness' => 'patchy', 'areas' => 'avoiding_painful_areas'],
                'health_implications' => ['skin_conditions', 'pain', 'mobility_issues']
            ],
            'social_interaction' => [
                'normal' => ['with_humans' => 'responsive', 'with_cats' => 'appropriate', 'with_environment' => 'engaged'],
                'concerning' => ['with_humans' => 'withdrawn', 'with_cats' => 'aggressive', 'with_environment' => 'disinterested'],
                'health_implications' => ['depression', 'anxiety', 'pain']
            ]
        ];
    }
    
    /**
     * Analyze real-time behavior data
     */
    public function analyzeBehavior($catId, $behaviorData) {
        try {
            // Validate input data
            $this->validateBehaviorData($behaviorData);
            
            // Process behavior data through AI model
            $analysis = $this->processBehaviorData($behaviorData);
            
            // Identify behavior patterns
            $patterns = $this->identifyPatterns($analysis);
            
            // Generate health insights
            $healthInsights = $this->generateHealthInsights($analysis, $patterns);
            
            // Create recommendations
            $recommendations = $this->generateRecommendations($patterns, $healthInsights);
            
            // Store analysis results
            $this->storeAnalysisResults($catId, $analysis, $patterns, $healthInsights, $recommendations);
            
            return [
                'success' => true,
                'analysis' => $analysis,
                'patterns' => $patterns,
                'health_insights' => $healthInsights,
                'recommendations' => $recommendations,
                'confidence_score' => $this->calculateConfidence($analysis),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Validate incoming behavior data
     */
    private function validateBehaviorData($data) {
        $requiredFields = ['activity_level', 'social_behavior', 'eating_behavior', 'sleep_patterns', 'grooming_behavior'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
        
        // Validate data ranges
        if ($data['activity_level'] < 0 || $data['activity_level'] > 100) {
            throw new Exception("Activity level must be between 0 and 100");
        }
        
        if ($data['social_behavior'] < 0 || $data['social_behavior'] > 100) {
            throw new Exception("Social behavior score must be between 0 and 100");
        }
    }
    
    /**
     * Process behavior data through AI model
     */
    private function processBehaviorData($data) {
        // Simulate AI processing (in production, this would use a real ML model)
        $analysis = [
            'activity_score' => $this->normalizeScore($data['activity_level']),
            'social_score' => $this->normalizeScore($data['social_behavior']),
            'eating_score' => $this->analyzeEatingBehavior($data['eating_behavior']),
            'sleep_score' => $this->analyzeSleepPatterns($data['sleep_patterns']),
            'grooming_score' => $this->analyzeGroomingBehavior($data['grooming_behavior']),
            'overall_wellness' => 0,
            'behavioral_anomalies' => [],
            'trend_analysis' => []
        ];
        
        // Calculate overall wellness score
        $analysis['overall_wellness'] = $this->calculateWellnessScore($analysis);
        
        // Detect behavioral anomalies
        $analysis['behavioral_anomalies'] = $this->detectAnomalies($analysis);
        
        // Analyze trends
        $analysis['trend_analysis'] = $this->analyzeTrends($data);
        
        return $analysis;
    }
    
    /**
     * Normalize scores to 0-1 range
     */
    private function normalizeScore($score) {
        return $score / 100;
    }
    
    /**
     * Analyze eating behavior patterns
     */
    private function analyzeEatingBehavior($eatingData) {
        if (is_array($eatingData)) {
            $frequency = $eatingData['frequency'] ?? 0;
            $enthusiasm = $eatingData['enthusiasm'] ?? 0;
            $amount = $eatingData['amount'] ?? 0;
            
            return ($frequency + $enthusiasm + $amount) / 3;
        }
        
        return $this->normalizeScore($eatingData);
    }
    
    /**
     * Analyze sleep patterns
     */
    private function analyzeSleepPatterns($sleepData) {
        if (is_array($sleepData)) {
            $duration = $sleepData['duration'] ?? 0;
            $quality = $sleepData['quality'] ?? 0;
            $consistency = $sleepData['consistency'] ?? 0;
            
            return ($duration + $quality + $consistency) / 3;
        }
        
        return $this->normalizeScore($sleepData);
    }
    
    /**
     * Analyze grooming behavior
     */
    private function analyzeGroomingBehavior($groomingData) {
        if (is_array($groomingData)) {
            $frequency = $groomingData['frequency'] ?? 0;
            $thoroughness = $groomingData['thoroughness'] ?? 0;
            $areas_covered = $groomingData['areas_covered'] ?? 0;
            
            return ($frequency + $thoroughness + $areas_covered) / 3;
        }
        
        return $this->normalizeScore($groomingData);
    }
    
    /**
     * Calculate overall wellness score
     */
    private function calculateWellnessScore($analysis) {
        $weights = [
            'activity_score' => 0.25,
            'social_score' => 0.20,
            'eating_score' => 0.25,
            'sleep_score' => 0.20,
            'grooming_score' => 0.10
        ];
        
        $wellness = 0;
        foreach ($weights as $metric => $weight) {
            $wellness += $analysis[$metric] * $weight;
        }
        
        return $wellness;
    }
    
    /**
     * Detect behavioral anomalies
     */
    private function detectAnomalies($analysis) {
        $anomalies = [];
        $threshold = 0.3; // Threshold for anomaly detection
        
        foreach ($analysis as $metric => $score) {
            if (is_numeric($score) && $score < $threshold) {
                $anomalies[] = [
                    'metric' => $metric,
                    'score' => $score,
                    'severity' => $this->calculateSeverity($score),
                    'description' => $this->getAnomalyDescription($metric, $score)
                ];
            }
        }
        
        return $anomalies;
    }
    
    /**
     * Calculate anomaly severity
     */
    private function calculateSeverity($score) {
        if ($score < 0.1) return 'critical';
        if ($score < 0.2) return 'high';
        if ($score < 0.3) return 'medium';
        return 'low';
    }
    
    /**
     * Get anomaly description
     */
    private function getAnomalyDescription($metric, $score) {
        $descriptions = [
            'activity_score' => 'Unusually low activity level detected',
            'social_score' => 'Significant decrease in social behavior',
            'eating_score' => 'Concerning changes in eating patterns',
            'sleep_score' => 'Abnormal sleep patterns observed',
            'grooming_score' => 'Decreased grooming behavior detected'
        ];
        
        return $descriptions[$metric] ?? 'Unusual behavior pattern detected';
    }
    
    /**
     * Analyze behavioral trends
     */
    private function analyzeTrends($data) {
        // In production, this would analyze historical data
        return [
            'trend_direction' => 'stable',
            'change_rate' => 0.05,
            'prediction_confidence' => 0.85,
            'next_week_forecast' => 'continued_stability'
        ];
    }
    
    /**
     * Identify behavior patterns
     */
    private function identifyPatterns($analysis) {
        $identifiedPatterns = [];
        
        foreach ($this->behaviorPatterns as $pattern => $criteria) {
            $matchScore = $this->calculatePatternMatch($analysis, $criteria);
            
            if ($matchScore >= $criteria['threshold']) {
                $identifiedPatterns[$pattern] = [
                    'confidence' => $matchScore,
                    'health_impact' => $criteria['health_impact'],
                    'recommendations' => $criteria['recommendations']
                ];
            }
        }
        
        return $identifiedPatterns;
    }
    
    /**
     * Calculate pattern match score
     */
    private function calculatePatternMatch($analysis, $criteria) {
        $totalScore = 0;
        $totalWeight = 0;
        
        foreach ($criteria['indicators'] as $indicator) {
            $weight = $this->getIndicatorWeight($indicator);
            $score = $this->getIndicatorScore($analysis, $indicator);
            
            $totalScore += $score * $weight;
            $totalWeight += $weight;
        }
        
        return $totalWeight > 0 ? $totalScore / $totalWeight : 0;
    }
    
    /**
     * Get indicator weight
     */
    private function getIndicatorWeight($indicator) {
        $weights = [
            'high_energy' => 1.0,
            'active_movement' => 0.8,
            'toy_interaction' => 0.7,
            'social_engagement' => 0.9,
            'hiding_behavior' => 0.9,
            'reduced_appetite' => 0.8,
            'aggressive_behavior' => 0.7,
            'excessive_grooming' => 0.6
        ];
        
        return $weights[$indicator] ?? 0.5;
    }
    
    /**
     * Get indicator score from analysis
     */
    private function getIndicatorScore($analysis, $indicator) {
        $scoreMap = [
            'high_energy' => $analysis['activity_score'],
            'active_movement' => $analysis['activity_score'],
            'toy_interaction' => $analysis['activity_score'],
            'social_engagement' => $analysis['social_score'],
            'hiding_behavior' => 1 - $analysis['social_score'],
            'reduced_appetite' => 1 - $analysis['eating_score'],
            'aggressive_behavior' => 1 - $analysis['social_score'],
            'excessive_grooming' => $analysis['grooming_score']
        ];
        
        return $scoreMap[$indicator] ?? 0;
    }
    
    /**
     * Generate health insights
     */
    private function generateHealthInsights($analysis, $patterns) {
        $insights = [];
        
        // Overall wellness insight
        if ($analysis['overall_wellness'] >= 0.8) {
            $insights[] = [
                'type' => 'positive',
                'title' => 'Excellent Overall Health',
                'description' => 'Your cat is showing excellent health indicators across all metrics.',
                'priority' => 'low'
            ];
        } elseif ($analysis['overall_wellness'] < 0.5) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Health Concerns Detected',
                'description' => 'Multiple health indicators suggest your cat may need attention.',
                'priority' => 'high'
            ];
        }
        
        // Pattern-based insights
        foreach ($patterns as $pattern => $data) {
            if ($data['health_impact'] === 'negative') {
                $insights[] = [
                    'type' => 'caution',
                    'title' => ucfirst($pattern) . ' Behavior Pattern',
                    'description' => "Detected {$pattern} behavior that may indicate health issues.",
                    'priority' => 'medium'
                ];
            }
        }
        
        // Anomaly-based insights
        foreach ($analysis['behavioral_anomalies'] as $anomaly) {
            if ($anomaly['severity'] === 'critical' || $anomaly['severity'] === 'high') {
                $insights[] = [
                    'type' => 'alert',
                    'title' => 'Behavioral Anomaly Detected',
                    'description' => $anomaly['description'],
                    'priority' => 'high'
                ];
            }
        }
        
        return $insights;
    }
    
    /**
     * Generate personalized recommendations
     */
    private function generateRecommendations($patterns, $healthInsights) {
        $recommendations = [];
        
        // Pattern-based recommendations
        foreach ($patterns as $pattern => $data) {
            foreach ($data['recommendations'] as $recommendation) {
                $recommendations[] = [
                    'type' => 'behavior',
                    'title' => ucfirst($pattern) . ' Support',
                    'description' => $this->getRecommendationDescription($recommendation),
                    'priority' => 'medium',
                    'category' => 'behavior_modification'
                ];
            }
        }
        
        // Health-based recommendations
        foreach ($healthInsights as $insight) {
            if ($insight['priority'] === 'high') {
                $recommendations[] = [
                    'type' => 'health',
                    'title' => 'Health Monitoring',
                    'description' => 'Consider consulting with a veterinarian for professional assessment.',
                    'priority' => 'high',
                    'category' => 'health_care'
                ];
            }
        }
        
        // General wellness recommendations
        $recommendations[] = [
            'type' => 'wellness',
            'title' => 'Daily Care Routine',
            'description' => 'Maintain consistent feeding, play, and grooming schedules.',
            'priority' => 'low',
            'category' => 'routine_care'
        ];
        
        return $recommendations;
    }
    
    /**
     * Get recommendation description
     */
    private function getRecommendationDescription($recommendation) {
        $descriptions = [
            'maintain_play_schedule' => 'Keep regular play sessions to maintain high energy levels.',
            'provide_interactive_toys' => 'Offer toys that encourage movement and engagement.',
            'social_interaction' => 'Increase quality time and attention.',
            'create_safe_spaces' => 'Provide quiet, secure areas for your cat to retreat.',
            'reduce_stressors' => 'Identify and minimize environmental stressors.',
            'consult_veterinarian' => 'Professional evaluation may be beneficial.',
            'increase_quality_time' => 'Spend more focused time with your cat.',
            'gentle_petting' => 'Use gentle, calming touch techniques.',
            'verbal_affirmation' => 'Provide positive verbal reinforcement.',
            'respect_boundaries' => 'Allow your cat to set social interaction limits.',
            'provide_alone_spaces' => 'Create comfortable solitary areas.',
            'gradual_socialization' => 'Introduce new experiences slowly.',
            'provide_exploration_opportunities' => 'Create safe environments for investigation.',
            'rotate_toys' => 'Regularly introduce new toys and activities.',
            'safe_outdoor_access' => 'Consider supervised outdoor time if safe.'
        ];
        
        return $descriptions[$recommendation] ?? 'Implement behavior modification strategy.';
    }
    
    /**
     * Calculate analysis confidence
     */
    private function calculateConfidence($analysis) {
        // Base confidence on data quality and model accuracy
        $baseConfidence = $this->aiModel['accuracy'] / 100;
        
        // Adjust based on data completeness
        $dataCompleteness = $this->calculateDataCompleteness($analysis);
        
        // Adjust based on anomaly presence
        $anomalyFactor = count($analysis['behavioral_anomalies']) > 0 ? 0.9 : 1.0;
        
        return min(1.0, $baseConfidence * $dataCompleteness * $anomalyFactor);
    }
    
    /**
     * Calculate data completeness
     */
    private function calculateDataCompleteness($analysis) {
        $requiredFields = ['activity_score', 'social_score', 'eating_score', 'sleep_score', 'grooming_score'];
        $completeFields = 0;
        
        foreach ($requiredFields as $field) {
            if (isset($analysis[$field]) && is_numeric($analysis[$field])) {
                $completeFields++;
            }
        }
        
        return $completeFields / count($requiredFields);
    }
    
    /**
     * Store analysis results in database
     */
    private function storeAnalysisResults($catId, $analysis, $patterns, $healthInsights, $recommendations) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ai_behavior_analysis (
                    cat_id, analysis_data, patterns, health_insights, 
                    recommendations, confidence_score, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $catId,
                json_encode($analysis),
                json_encode($patterns),
                json_encode($healthInsights),
                json_encode($recommendations),
                $this->calculateConfidence($analysis)
            ]);
            
        } catch (Exception $e) {
            // Log error but don't fail the analysis
            error_log("Failed to store AI analysis results: " . $e->getMessage());
        }
    }
    
    /**
     * Get AI model information
     */
    public function getModelInfo() {
        return $this->aiModel;
    }
    
    /**
     * Get behavior patterns
     */
    public function getBehaviorPatterns() {
        return $this->behaviorPatterns;
    }
    
    /**
     * Get health indicators
     */
    public function getHealthIndicators() {
        return $this->healthIndicators;
    }
    
    /**
     * Get analysis history for a cat
     */
    public function getAnalysisHistory($catId, $limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM ai_behavior_analysis 
                WHERE cat_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            
            $stmt->execute([$catId, $limit]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
