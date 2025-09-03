<?php
/**
 * 🧠 Purrr.love Machine Learning Advanced Cat Personality Prediction System
 * Behavioral analysis, genetic markers, and environmental factors
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Personality dimensions and traits
 */
define('PERSONALITY_DIMENSIONS', [
    'openness' => [
        'name' => 'Openness to Experience',
        'traits' => ['curious', 'adventurous', 'creative', 'imaginative', 'variety_seeking'],
        'range' => [0, 100],
        'description' => 'How open a cat is to new experiences and stimuli'
    ],
    'conscientiousness' => [
        'name' => 'Conscientiousness',
        'traits' => ['organized', 'disciplined', 'careful', 'persistent', 'goal_directed'],
        'range' => [0, 100],
        'description' => 'How organized and goal-directed a cat is'
    ],
    'extraversion' => [
        'name' => 'Extraversion',
        'traits' => ['sociable', 'energetic', 'assertive', 'enthusiastic', 'talkative'],
        'range' => [0, 100],
        'description' => 'How outgoing and social a cat is'
    ],
    'agreeableness' => [
        'name' => 'Agreeableness',
        'traits' => ['trusting', 'altruistic', 'cooperative', 'modest', 'tender_minded'],
        'range' => [0, 100],
        'description' => 'How cooperative and trusting a cat is'
    ],
    'neuroticism' => [
        'name' => 'Neuroticism',
        'traits' => ['anxious', 'irritable', 'depressed', 'self_conscious', 'impulsive'],
        'range' => [0, 100],
        'description' => 'How emotionally stable a cat is'
    ]
]);

/**
 * Behavioral patterns and indicators
 */
define('BEHAVIORAL_PATTERNS', [
    'social_interaction' => [
        'greeting_behavior' => ['immediate', 'delayed', 'avoidant'],
        'play_style' => ['rough', 'gentle', 'mixed', 'none'],
        'grooming_behavior' => ['self', 'social', 'both', 'minimal'],
        'vocalization' => ['quiet', 'moderate', 'vocal', 'excessive']
    ],
    'environmental_response' => [
        'novelty_reaction' => ['exploratory', 'cautious', 'fearful', 'indifferent'],
        'noise_sensitivity' => ['low', 'medium', 'high', 'extreme'],
        'light_preference' => ['bright', 'dim', 'mixed', 'dark'],
        'temperature_preference' => ['warm', 'cool', 'moderate', 'variable']
    ],
    'activity_patterns' => [
        'energy_level' => ['low', 'medium', 'high', 'extreme'],
        'sleep_pattern' => ['nocturnal', 'diurnal', 'crepuscular', 'irregular'],
        'exercise_preference' => ['indoor', 'outdoor', 'both', 'minimal'],
        'hunting_instinct' => ['strong', 'moderate', 'weak', 'none']
    ]
]);

/**
 * Genetic markers and inheritance patterns
 */
define('GENETIC_MARKERS', [
    'coat_pattern' => [
        'tabby' => ['personality_influence' => ['hunting_instinct' => 0.3, 'independence' => 0.2]],
        'solid' => ['personality_influence' => ['calmness' => 0.25, 'affection' => 0.15]],
        'calico' => ['personality_influence' => ['playfulness' => 0.3, 'curiosity' => 0.25]],
        'tortoiseshell' => ['personality_influence' => ['independence' => 0.35, 'determination' => 0.2]]
    ],
    'breed_traits' => [
        'siamese' => ['vocalization' => 0.4, 'intelligence' => 0.35, 'social' => 0.3],
        'persian' => ['calmness' => 0.4, 'affection' => 0.3, 'independence' => 0.25],
        'mainecoon' => ['gentleness' => 0.35, 'intelligence' => 0.3, 'playfulness' => 0.25],
        'british_shorthair' => ['calmness' => 0.4, 'loyalty' => 0.3, 'independence' => 0.2]
    ]
]);

/**
 * Machine Learning Cat Personality Prediction System
 */
class MLCatPersonalitySystem {
    private $pdo;
    private $config;
    private $mlModels = [];
    private $trainingData = [];
    
    public function __construct() {
        $this->pdo = get_db();
        $this->config = [
            'prediction_accuracy_threshold' => 0.75,
            'min_training_samples' => 100,
            'feature_importance_threshold' => 0.1,
            'personality_update_frequency' => 7, // days
            'behavioral_observation_period' => 30, // days
            'genetic_influence_weight' => 0.3,
            'environmental_influence_weight' => 0.4,
            'behavioral_influence_weight' => 0.3
        ];
        
        $this->initializeMLModels();
        $this->loadTrainingData();
    }
    
    /**
     * Initialize machine learning models
     */
    private function initializeMLModels() {
        // Initialize different ML models for different aspects
        $this->mlModels = [
            'personality_core' => $this->createPersonalityCoreModel(),
            'behavior_prediction' => $this->createBehaviorPredictionModel(),
            'compatibility_analysis' => $this->createCompatibilityAnalysisModel(),
            'health_correlation' => $this->createHealthCorrelationModel()
        ];
    }
    
    /**
     * Create personality core prediction model
     */
    private function createPersonalityCoreModel() {
        return [
            'type' => 'ensemble',
            'algorithms' => ['random_forest', 'gradient_boosting', 'neural_network'],
            'features' => ['genetic_markers', 'breed_traits', 'early_behavior', 'environmental_factors'],
            'target' => 'personality_dimensions',
            'accuracy' => 0.0,
            'last_trained' => null
        ];
    }
    
    /**
     * Create behavior prediction model
     */
    private function createBehaviorPredictionModel() {
        return [
            'type' => 'classification',
            'algorithms' => ['support_vector_machine', 'decision_tree', 'naive_bayes'],
            'features' => ['personality_scores', 'current_environment', 'health_status', 'age'],
            'target' => 'behavioral_patterns',
            'accuracy' => 0.0,
            'last_trained' => null
        ];
    }
    
    /**
     * Create compatibility analysis model
     */
    private function createCompatibilityAnalysisModel() {
        return [
            'type' => 'regression',
            'algorithms' => ['linear_regression', 'ridge_regression', 'elastic_net'],
            'features' => ['personality_compatibility', 'energy_level_match', 'social_preference_match'],
            'target' => 'compatibility_score',
            'accuracy' => 0.0,
            'last_trained' => null
        ];
    }
    
    /**
     * Create health correlation model
     */
    private function createHealthCorrelationModel() {
        return [
            'type' => 'correlation',
            'algorithms' => ['pearson_correlation', 'spearman_correlation', 'canonical_correlation'],
            'features' => ['personality_traits', 'behavioral_patterns', 'genetic_markers'],
            'target' => 'health_indicators',
            'accuracy' => 0.0,
            'last_trained' => null
        ];
    }
    
    /**
     * Load training data from database
     */
    private function loadTrainingData() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    c.id, c.breed, c.personality, c.health_status,
                    b.behavioral_data, b.observation_period,
                    g.genetic_markers, g.heritage_score
                FROM cats c
                LEFT JOIN cat_behavioral_data b ON c.id = b.cat_id
                LEFT JOIN cat_genetic_data g ON c.id = g.cat_id
                WHERE c.personality IS NOT NULL 
                AND c.active = 1
                ORDER BY c.created_at DESC
                LIMIT 1000
            ");
            
            $stmt->execute();
            $this->trainingData = $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error loading training data: " . $e->getMessage());
            $this->trainingData = [];
        }
    }
    
    /**
     * Predict cat personality based on multiple factors
     */
    public function predictCatPersonality($catId, $includeConfidence = true) {
        try {
            // Get cat information
            $cat = $this->getCatInformation($catId);
            if (!$cat) {
                throw new Exception('Cat not found');
            }
            
            // Collect prediction factors
            $geneticFactors = $this->analyzeGeneticFactors($cat);
            $environmentalFactors = $this->analyzeEnvironmentalFactors($cat);
            $behavioralFactors = $this->analyzeBehavioralFactors($cat);
            
            // Calculate personality scores
            $personalityScores = $this->calculatePersonalityScores(
                $geneticFactors,
                $environmentalFactors,
                $behavioralFactors
            );
            
            // Apply machine learning predictions
            $mlPredictions = $this->applyMLPredictions($cat, $personalityScores);
            
            // Combine predictions with base scores
            $finalPersonality = $this->combinePredictions($personalityScores, $mlPredictions);
            
            // Calculate confidence scores
            $confidenceScores = [];
            if ($includeConfidence) {
                $confidenceScores = $this->calculateConfidenceScores(
                    $geneticFactors,
                    $environmentalFactors,
                    $behavioralFactors,
                    $mlPredictions
                );
            }
            
            // Store prediction results
            $this->storePersonalityPrediction($catId, $finalPersonality, $confidenceScores);
            
            return [
                'cat_id' => $catId,
                'personality' => $finalPersonality,
                'confidence_scores' => $confidenceScores,
                'prediction_factors' => [
                    'genetic' => $geneticFactors,
                    'environmental' => $environmentalFactors,
                    'behavioral' => $behavioralFactors
                ],
                'ml_predictions' => $mlPredictions,
                'prediction_timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            error_log("Error predicting cat personality: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Analyze genetic factors affecting personality
     */
    private function analyzeGeneticFactors($cat) {
        $factors = [];
        
        // Breed-based personality traits
        if (isset($cat['breed']) && isset(GENETIC_MARKERS['breed_traits'][$cat['breed']])) {
            $breedTraits = GENETIC_MARKERS['breed_traits'][$cat['breed']];
            foreach ($breedTraits as $trait => $influence) {
                $factors[$trait] = [
                    'value' => $influence,
                    'source' => 'breed',
                    'confidence' => 0.8
                ];
            }
        }
        
        // Coat pattern influence
        if (isset($cat['coat_pattern']) && isset(GENETIC_MARKERS['coat_pattern'][$cat['coat_pattern']])) {
            $coatTraits = GENETIC_MARKERS['coat_pattern'][$cat['coat_pattern']]['personality_influence'];
            foreach ($coatTraits as $trait => $influence) {
                $factors[$trait] = [
                    'value' => $influence,
                    'source' => 'coat_pattern',
                    'confidence' => 0.6
                ];
            }
        }
        
        // Heritage score (if available)
        if (isset($cat['heritage_score'])) {
            $factors['heritage_quality'] = [
                'value' => $cat['heritage_score'] / 100,
                'source' => 'heritage',
                'confidence' => 0.7
            ];
        }
        
        return $factors;
    }
    
    /**
     * Analyze environmental factors affecting personality
     */
    private function analyzeEnvironmentalFactors($cat) {
        $factors = [];
        
        // Living environment
        $factors['living_space'] = [
            'value' => $this->calculateLivingSpaceScore($cat),
            'source' => 'environment',
            'confidence' => 0.75
        ];
        
        // Social environment
        $factors['social_environment'] = [
            'value' => $this->calculateSocialEnvironmentScore($cat),
            'source' => 'environment',
            'confidence' => 0.8
        ];
        
        // Stimulation level
        $factors['stimulation_level'] = [
            'value' => $this->calculateStimulationLevel($cat),
            'source' => 'environment',
            'confidence' => 0.7
        ];
        
        // Routine stability
        $factors['routine_stability'] = [
            'value' => $this->calculateRoutineStability($cat),
            'source' => 'environment',
            'confidence' => 0.65
        ];
        
        return $factors;
    }
    
    /**
     * Analyze behavioral factors affecting personality
     */
    private function analyzeBehavioralFactors($cat) {
        $factors = [];
        
        // Recent behavioral observations
        $recentBehaviors = $this->getRecentBehavioralData($cat['id']);
        
        foreach ($recentBehaviors as $behavior) {
            $factors[$behavior['type']] = [
                'value' => $behavior['frequency'] / $behavior['total_observations'],
                'source' => 'behavioral_observation',
                'confidence' => min(0.9, $behavior['total_observations'] / 100),
                'observations' => $behavior['total_observations']
            ];
        }
        
        // Interaction patterns
        $interactionPatterns = $this->analyzeInteractionPatterns($cat['id']);
        foreach ($interactionPatterns as $pattern => $data) {
            $factors["interaction_$pattern"] = [
                'value' => $data['score'],
                'source' => 'interaction_analysis',
                'confidence' => $data['confidence']
            ];
        }
        
        return $factors;
    }
    
    /**
     * Calculate personality scores based on factors
     */
    private function calculatePersonalityScores($geneticFactors, $environmentalFactors, $behavioralFactors) {
        $scores = [];
        
        foreach (PERSONALITY_DIMENSIONS as $dimension => $config) {
            $score = 0;
            $totalWeight = 0;
            
            // Genetic influence
            if (isset($geneticFactors[$dimension])) {
                $score += $geneticFactors[$dimension]['value'] * $this->config['genetic_influence_weight'];
                $totalWeight += $this->config['genetic_influence_weight'];
            }
            
            // Environmental influence
            if (isset($environmentalFactors[$dimension])) {
                $score += $environmentalFactors[$dimension]['value'] * $this->config['environmental_influence_weight'];
                $totalWeight += $this->config['environmental_influence_weight'];
            }
            
            // Behavioral influence
            if (isset($behavioralFactors[$dimension])) {
                $score += $behavioralFactors[$dimension]['value'] * $this->config['behavioral_influence_weight'];
                $totalWeight += $this->config['behavioral_influence_weight'];
            }
            
            // Normalize score
            if ($totalWeight > 0) {
                $scores[$dimension] = min(100, max(0, ($score / $totalWeight) * 100));
            } else {
                $scores[$dimension] = 50; // Default neutral score
            }
        }
        
        return $scores;
    }
    
    /**
     * Apply machine learning predictions
     */
    private function applyMLPredictions($cat, $baseScores) {
        $predictions = [];
        
        // Check if we have enough training data
        if (count($this->trainingData) < $this->config['min_training_samples']) {
            return $baseScores; // Return base scores if insufficient data
        }
        
        // Apply different ML models
        foreach ($this->mlModels as $modelName => $model) {
            try {
                $modelPrediction = $this->runMLModel($modelName, $cat, $baseScores);
                if ($modelPrediction) {
                    $predictions[$modelName] = $modelPrediction;
                }
            } catch (Exception $e) {
                error_log("Error running ML model $modelName: " . $e->getMessage());
            }
        }
        
        return $predictions;
    }
    
    /**
     * Run specific ML model
     */
    private function runMLModel($modelName, $cat, $baseScores) {
        switch ($modelName) {
            case 'personality_core':
                return $this->runPersonalityCoreModel($cat, $baseScores);
            case 'behavior_prediction':
                return $this->runBehaviorPredictionModel($cat, $baseScores);
            case 'compatibility_analysis':
                return $this->runCompatibilityAnalysisModel($cat, $baseScores);
            case 'health_correlation':
                return $this->runHealthCorrelationModel($cat, $baseScores);
            default:
                return null;
        }
    }
    
    /**
     * Run personality core prediction model
     */
    private function runPersonalityCoreModel($cat, $baseScores) {
        // Simulate ML prediction based on training data patterns
        $prediction = [];
        
        foreach (PERSONALITY_DIMENSIONS as $dimension => $config) {
            $baseScore = $baseScores[$dimension] ?? 50;
            
            // Find similar cats in training data
            $similarCats = $this->findSimilarCats($cat, $dimension);
            
            if (!empty($similarCats)) {
                // Calculate weighted average from similar cats
                $weightedSum = 0;
                $totalWeight = 0;
                
                foreach ($similarCats as $similarCat) {
                    $similarity = $this->calculateCatSimilarity($cat, $similarCat);
                    $weight = $similarity * ($similarCat['confidence'] ?? 0.5);
                    
                    $weightedSum += $similarCat['personality_score'] * $weight;
                    $totalWeight += $weight;
                }
                
                if ($totalWeight > 0) {
                    $prediction[$dimension] = $weightedSum / $totalWeight;
                } else {
                    $prediction[$dimension] = $baseScore;
                }
            } else {
                $prediction[$dimension] = $baseScore;
            }
        }
        
        return $prediction;
    }
    
    /**
     * Find similar cats for ML prediction
     */
    private function findSimilarCats($cat, $dimension, $limit = 10) {
        $similarCats = [];
        
        foreach ($this->trainingData as $trainingCat) {
            if ($trainingCat['id'] == $cat['id']) continue;
            
            $similarity = $this->calculateCatSimilarity($cat, $trainingCat);
            if ($similarity > 0.3) { // Minimum similarity threshold
                $personality = json_decode($trainingCat['personality'], true);
                if (isset($personality[$dimension])) {
                    $similarCats[] = [
                        'id' => $trainingCat['id'],
                        'personality_score' => $personality[$dimension],
                        'similarity' => $similarity,
                        'confidence' => 0.8
                    ];
                }
            }
        }
        
        // Sort by similarity and limit results
        usort($similarCats, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        return array_slice($similarCats, 0, $limit);
    }
    
    /**
     * Calculate similarity between two cats
     */
    private function calculateCatSimilarity($cat1, $cat2) {
        $similarity = 0;
        $totalFactors = 0;
        
        // Breed similarity
        if ($cat1['breed'] === $cat2['breed']) {
            $similarity += 0.4;
            $totalFactors++;
        }
        
        // Age similarity (within 2 years)
        if (abs($cat1['age'] - $cat2['age']) <= 2) {
            $similarity += 0.2;
            $totalFactors++;
        }
        
        // Health status similarity
        if ($cat1['health_status'] === $cat2['health_status']) {
            $similarity += 0.2;
            $totalFactors++;
        }
        
        // Environmental similarity (if available)
        if (isset($cat1['environment']) && isset($cat2['environment'])) {
            if ($cat1['environment'] === $cat2['environment']) {
                $similarity += 0.2;
                $totalFactors++;
            }
        }
        
        return $totalFactors > 0 ? $similarity / $totalFactors : 0;
    }
    
    /**
     * Combine base scores with ML predictions
     */
    private function combinePredictions($baseScores, $mlPredictions) {
        $finalPersonality = $baseScores;
        
        if (empty($mlPredictions)) {
            return $finalPersonality;
        }
        
        // Combine predictions from different models
        foreach (PERSONALITY_DIMENSIONS as $dimension => $config) {
            $predictions = [];
            $weights = [];
            
            // Base score
            $predictions[] = $baseScores[$dimension] ?? 50;
            $weights[] = 0.3; // Base score weight
            
            // ML predictions
            foreach ($mlPredictions as $modelName => $modelPrediction) {
                if (isset($modelPrediction[$dimension])) {
                    $predictions[] = $modelPrediction[$dimension];
                    $weights[] = 0.7 / count($mlPredictions); // Distribute remaining weight
                }
            }
            
            // Calculate weighted average
            if (!empty($predictions)) {
                $weightedSum = 0;
                $totalWeight = 0;
                
                for ($i = 0; $i < count($predictions); $i++) {
                    $weightedSum += $predictions[$i] * $weights[$i];
                    $totalWeight += $weights[$i];
                }
                
                if ($totalWeight > 0) {
                    $finalPersonality[$dimension] = min(100, max(0, $weightedSum / $totalWeight));
                }
            }
        }
        
        return $finalPersonality;
    }
    
    /**
     * Calculate confidence scores for predictions
     */
    private function calculateConfidenceScores($geneticFactors, $environmentalFactors, $behavioralFactors, $mlPredictions) {
        $confidence = [];
        
        foreach (PERSONALITY_DIMENSIONS as $dimension => $config) {
            $confidence[$dimension] = 0;
            $totalConfidence = 0;
            $factors = 0;
            
            // Genetic confidence
            if (isset($geneticFactors[$dimension])) {
                $confidence[$dimension] += $geneticFactors[$dimension]['confidence'] * $this->config['genetic_influence_weight'];
                $totalConfidence += $this->config['genetic_influence_weight'];
                $factors++;
            }
            
            // Environmental confidence
            if (isset($environmentalFactors[$dimension])) {
                $confidence[$dimension] += $environmentalFactors[$dimension]['confidence'] * $this->config['environmental_influence_weight'];
                $totalConfidence += $this->config['environmental_influence_weight'];
                $factors++;
            }
            
            // Behavioral confidence
            if (isset($behavioralFactors[$dimension])) {
                $confidence[$dimension] += $behavioralFactors[$dimension]['confidence'] * $this->config['behavioral_influence_weight'];
                $totalConfidence += $this->config['behavioral_influence_weight'];
                $factors++;
            }
            
            // ML prediction confidence
            if (!empty($mlPredictions)) {
                $mlConfidence = 0.8; // Base ML confidence
                $confidence[$dimension] += $mlConfidence * 0.2; // ML weight
                $totalConfidence += 0.2;
                $factors++;
            }
            
            // Normalize confidence
            if ($totalConfidence > 0) {
                $confidence[$dimension] = min(1.0, $confidence[$dimension] / $totalConfidence);
            }
        }
        
        return $confidence;
    }
    
    /**
     * Store personality prediction results
     */
    private function storePersonalityPrediction($catId, $personality, $confidenceScores) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO cat_personality_predictions 
                (cat_id, personality_scores, confidence_scores, prediction_method, created_at)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                personality_scores = VALUES(personality_scores),
                confidence_scores = VALUES(confidence_scores),
                prediction_method = VALUES(prediction_method),
                updated_at = VALUES(created_at)
            ");
            
            $stmt->execute([
                $catId,
                json_encode($personality),
                json_encode($confidenceScores),
                'ml_enhanced',
                date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Error storing personality prediction: " . $e->getMessage());
        }
    }
    
    /**
     * Helper methods
     */
    private function getCatInformation($catId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    c.*,
                    b.behavioral_data,
                    g.genetic_markers,
                    g.heritage_score
                FROM cats c
                LEFT JOIN cat_behavioral_data b ON c.id = b.cat_id
                LEFT JOIN cat_genetic_data g ON c.id = g.cat_id
                WHERE c.id = ?
            ");
            $stmt->execute([$catId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function calculateLivingSpaceScore($cat) {
        // Placeholder for living space calculation
        return 0.7;
    }
    
    private function calculateSocialEnvironmentScore($cat) {
        // Placeholder for social environment calculation
        return 0.6;
    }
    
    private function calculateStimulationLevel($cat) {
        // Placeholder for stimulation level calculation
        return 0.5;
    }
    
    private function calculateRoutineStability($cat) {
        // Placeholder for routine stability calculation
        return 0.8;
    }
    
    private function getRecentBehavioralData($catId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    behavior_type as type,
                    COUNT(*) as frequency,
                    COUNT(*) as total_observations
                FROM cat_behavior_observations
                WHERE cat_id = ? 
                AND observed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY behavior_type
            ");
            
            $stmt->execute([$catId, $this->config['behavioral_observation_period']]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function analyzeInteractionPatterns($catId) {
        // Placeholder for interaction pattern analysis
        return [
            'human' => ['score' => 0.7, 'confidence' => 0.8],
            'other_cats' => ['score' => 0.5, 'confidence' => 0.6],
            'toys' => ['score' => 0.8, 'confidence' => 0.9]
        ];
    }
    
    private function runBehaviorPredictionModel($cat, $baseScores) {
        // Placeholder for behavior prediction model
        return null;
    }
    
    private function runCompatibilityAnalysisModel($cat, $baseScores) {
        // Placeholder for compatibility analysis model
        return null;
    }
    
    private function runHealthCorrelationModel($cat, $baseScores) {
        // Placeholder for health correlation model
        return null;
    }
    
    /**
     * Get personality insights and recommendations
     */
    public function getPersonalityInsights($catId) {
        try {
            $personality = $this->predictCatPersonality($catId, true);
            
            $insights = [];
            $recommendations = [];
            
            foreach ($personality['personality'] as $dimension => $score) {
                $insights[$dimension] = $this->generateDimensionInsight($dimension, $score);
                $recommendations[$dimension] = $this->generateDimensionRecommendations($dimension, $score);
            }
            
            return [
                'cat_id' => $catId,
                'insights' => $insights,
                'recommendations' => $recommendations,
                'overall_assessment' => $this->generateOverallAssessment($personality['personality']),
                'confidence_level' => $this->calculateOverallConfidence($personality['confidence_scores'])
            ];
            
        } catch (Exception $e) {
            error_log("Error getting personality insights: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate insight for specific personality dimension
     */
    private function generateDimensionInsight($dimension, $score) {
        $config = PERSONALITY_DIMENSIONS[$dimension];
        
        if ($score >= 80) {
            return "Your cat shows very high {$config['name']}. This suggests {$config['description']}.";
        } elseif ($score >= 60) {
            return "Your cat shows above average {$config['name']}. This indicates {$config['description']}.";
        } elseif ($score >= 40) {
            return "Your cat shows moderate {$config['name']}. This means {$config['description']}.";
        } elseif ($score >= 20) {
            return "Your cat shows below average {$config['name']}. This suggests {$config['description']}.";
        } else {
            return "Your cat shows very low {$config['name']}. This indicates {$config['description']}.";
        }
    }
    
    /**
     * Generate recommendations for specific personality dimension
     */
    private function generateDimensionRecommendations($dimension, $score) {
        $config = PERSONALITY_DIMENSIONS[$dimension];
        
        if ($score >= 80) {
            return "Provide plenty of opportunities for {$config['name']} activities. Consider advanced enrichment.";
        } elseif ($score >= 60) {
            return "Encourage {$config['name']} through regular activities and positive reinforcement.";
        } elseif ($score >= 40) {
            return "Maintain balanced {$config['name']} activities. Monitor for changes over time.";
        } elseif ($score >= 20) {
            return "Gently encourage {$config['name']} through gradual exposure and positive experiences.";
        } else {
            return "Focus on building confidence and gradually introducing {$config['name']} activities.";
        }
    }
    
    /**
     * Generate overall personality assessment
     */
    private function generateOverallAssessment($personality) {
        $highTraits = [];
        $lowTraits = [];
        
        foreach ($personality as $dimension => $score) {
            if ($score >= 70) {
                $highTraits[] = PERSONALITY_DIMENSIONS[$dimension]['name'];
            } elseif ($score <= 30) {
                $lowTraits[] = PERSONALITY_DIMENSIONS[$dimension]['name'];
            }
        }
        
        $assessment = "Your cat has a unique personality profile. ";
        
        if (!empty($highTraits)) {
            $assessment .= "They excel in: " . implode(', ', $highTraits) . ". ";
        }
        
        if (!empty($lowTraits)) {
            $assessment .= "Areas for growth include: " . implode(', ', $lowTraits) . ". ";
        }
        
        $assessment .= "This combination creates a distinctive feline character that makes them special.";
        
        return $assessment;
    }
    
    /**
     * Calculate overall confidence level
     */
    private function calculateOverallConfidence($confidenceScores) {
        if (empty($confidenceScores)) {
            return 'low';
        }
        
        $averageConfidence = array_sum($confidenceScores) / count($confidenceScores);
        
        if ($averageConfidence >= 0.8) {
            return 'very_high';
        } elseif ($averageConfidence >= 0.6) {
            return 'high';
        } elseif ($averageConfidence >= 0.4) {
            return 'moderate';
        } else {
            return 'low';
        }
    }
}

/**
 * Global ML personality system instance
 */
$globalMLPersonalitySystem = new MLCatPersonalitySystem();

/**
 * ML personality wrapper functions
 */
function predictCatPersonality($catId, $includeConfidence = true) {
    global $globalMLPersonalitySystem;
    return $globalMLPersonalitySystem->predictCatPersonality($catId, $includeConfidence);
}

function getPersonalityInsights($catId) {
    global $globalMLPersonalitySystem;
    return $globalMLPersonalitySystem->getPersonalityInsights($catId);
}
?>
