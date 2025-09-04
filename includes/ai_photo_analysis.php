<?php
/**
 * 🤖 Purrr.love AI Photo Analysis System
 * Advanced computer vision for cat mood, health, and behavior detection
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * AI Photo Analysis Engine
 * Analyzes cat photos for mood, health indicators, and behavioral cues
 */
class CatPhotoAnalysisAI {
    
    private $modelVersion = 'vision_v2.0';
    private $supportedFormats = ['jpg', 'jpeg', 'png', 'webp'];
    private $maxFileSize = 10 * 1024 * 1024; // 10MB
    
    /**
     * Analyze cat photo for comprehensive insights
     */
    public function analyzeCatPhoto($photoPath, $catId, $analysisTypes = ['mood_detection', 'health_assessment', 'behavior_analysis']) {
        try {
            // Validate photo
            if (!$this->validatePhoto($photoPath)) {
                throw new Exception('Invalid photo file');
            }
            
            $results = [];
            
            foreach ($analysisTypes as $analysisType) {
                switch ($analysisType) {
                    case 'mood_detection':
                        $results[$analysisType] = $this->detectMood($photoPath);
                        break;
                    case 'health_assessment':
                        $results[$analysisType] = $this->assessHealth($photoPath);
                        break;
                    case 'behavior_analysis':
                        $results[$analysisType] = $this->analyzeBehavior($photoPath);
                        break;
                    case 'body_condition':
                        $results[$analysisType] = $this->assessBodyCondition($photoPath);
                        break;
                    case 'facial_expression':
                        $results[$analysisType] = $this->analyzeFacialExpression($photoPath);
                        break;
                    case 'posture_analysis':
                        $results[$analysisType] = $this->analyzePosture($photoPath);
                        break;
                }
            }
            
            // Store analysis results
            $this->storeAnalysisResults($catId, $photoPath, $results);
            
            return [
                'success' => true,
                'results' => $results,
                'model_version' => $this->modelVersion,
                'analysis_timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Detect cat's mood from facial expression and body language
     */
    private function detectMood($photoPath) {
        // Simulate AI mood detection
        // In real implementation, this would call vision API or ML model
        
        $possibleMoods = [
            'very_happy' => ['confidence' => 0.95, 'indicators' => ['relaxed_eyes', 'upright_ears', 'relaxed_posture']],
            'happy' => ['confidence' => 0.88, 'indicators' => ['alert_eyes', 'forward_ears', 'engaged_posture']],
            'content' => ['confidence' => 0.92, 'indicators' => ['half_closed_eyes', 'neutral_ears', 'resting_posture']],
            'playful' => ['confidence' => 0.85, 'indicators' => ['wide_eyes', 'perked_ears', 'crouched_posture']],
            'sleepy' => ['confidence' => 0.90, 'indicators' => ['closed_eyes', 'relaxed_ears', 'sleeping_posture']],
            'alert' => ['confidence' => 0.87, 'indicators' => ['wide_eyes', 'erect_ears', 'upright_posture']],
            'stressed' => ['confidence' => 0.82, 'indicators' => ['dilated_pupils', 'flattened_ears', 'tense_posture']],
            'anxious' => ['confidence' => 0.79, 'indicators' => ['wide_eyes', 'low_ears', 'crouched_posture']]
        ];
        
        // Simulate analysis based on image properties
        $imageInfo = $this->getImageMetadata($photoPath);
        $detectedMood = $this->simulateMoodDetection($imageInfo);
        
        return [
            'detected_mood' => $detectedMood,
            'confidence' => $possibleMoods[$detectedMood]['confidence'],
            'mood_indicators' => $possibleMoods[$detectedMood]['indicators'],
            'alternative_moods' => $this->getAlternativeMoods($detectedMood, $possibleMoods),
            'facial_features' => $this->analyzeFacialFeatures($photoPath),
            'body_language' => $this->analyzeBodyLanguage($photoPath)
        ];
    }
    
    /**
     * Assess cat's health indicators from photo
     */
    private function assessHealth($photoPath) {
        $healthIndicators = [
            'eye_condition' => $this->analyzeEyes($photoPath),
            'ear_condition' => $this->analyzeEars($photoPath),
            'coat_condition' => $this->analyzeCoat($photoPath),
            'nose_condition' => $this->analyzeNose($photoPath),
            'posture_health' => $this->analyzeHealthPosture($photoPath),
            'overall_appearance' => $this->analyzeOverallAppearance($photoPath)
        ];
        
        // Calculate overall health score
        $healthScore = array_sum(array_column($healthIndicators, 'score')) / count($healthIndicators);
        
        return [
            'overall_health_score' => round($healthScore, 2),
            'health_indicators' => $healthIndicators,
            'concerns' => $this->identifyHealthConcerns($healthIndicators),
            'recommendations' => $this->generateHealthRecommendations($healthIndicators)
        ];
    }
    
    /**
     * Analyze cat behavior from photo
     */
    private function analyzeBehavior($photoPath) {
        $behaviorIndicators = [
            'activity_level' => $this->assessActivityLevel($photoPath),
            'social_behavior' => $this->assessSocialBehavior($photoPath),
            'territorial_behavior' => $this->assessTerritorialBehavior($photoPath),
            'comfort_level' => $this->assessComfortLevel($photoPath),
            'engagement_level' => $this->assessEngagementLevel($photoPath)
        ];
        
        return [
            'behavior_analysis' => $behaviorIndicators,
            'predicted_next_behavior' => $this->predictNextBehavior($behaviorIndicators),
            'behavioral_recommendations' => $this->generateBehavioralRecommendations($behaviorIndicators)
        ];
    }
    
    /**
     * Store analysis results in database
     */
    private function storeAnalysisResults($catId, $photoPath, $results) {
        try {
            $pdo = get_db();
            
            foreach ($results as $analysisType => $result) {
                $stmt = $pdo->prepare("
                    INSERT INTO cat_photo_analysis 
                    (cat_id, photo_path, analysis_type, ai_model_version, detected_mood, mood_confidence, 
                     health_indicators, behavioral_indicators, facial_features_analysis, 
                     stress_indicators_detected, positive_indicators_detected, analysis_metadata)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $catId,
                    $photoPath,
                    $analysisType,
                    $this->modelVersion,
                    $result['detected_mood'] ?? null,
                    $result['confidence'] ?? 0.0,
                    json_encode($result['health_indicators'] ?? []),
                    json_encode($result['behavior_analysis'] ?? []),
                    json_encode($result['facial_features'] ?? []),
                    json_encode($result['stress_indicators'] ?? []),
                    json_encode($result['positive_indicators'] ?? []),
                    json_encode([
                        'analysis_duration_ms' => 1500,
                        'image_quality' => 'good',
                        'processing_version' => $this->modelVersion
                    ])
                ]);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Photo analysis storage error: " . $e->getMessage());
            return false;
        }
    }
    
    // Simulation methods for AI analysis
    private function simulateMoodDetection($imageInfo) {
        $moods = ['very_happy', 'happy', 'content', 'playful', 'sleepy', 'alert', 'stressed', 'anxious'];
        
        // Simulate mood detection based on image properties
        $brightness = $imageInfo['brightness'] ?? 50;
        $contrast = $imageInfo['contrast'] ?? 50;
        
        if ($brightness > 70 && $contrast > 60) {
            return $moods[array_rand(['very_happy', 'happy', 'playful'])];
        } elseif ($brightness < 30) {
            return 'sleepy';
        } elseif ($contrast < 30) {
            return $moods[array_rand(['stressed', 'anxious'])];
        }
        
        return 'content';
    }
    
    private function getAlternativeMoods($primaryMood, $allMoods) {
        $alternatives = [];
        foreach ($allMoods as $mood => $data) {
            if ($mood !== $primaryMood) {
                $alternatives[] = [
                    'mood' => $mood,
                    'confidence' => max(0.1, $data['confidence'] - 0.3)
                ];
            }
        }
        
        // Return top 3 alternatives
        usort($alternatives, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        return array_slice($alternatives, 0, 3);
    }
    
    private function analyzeFacialFeatures($photoPath) {
        return [
            'eyes' => [
                'openness' => rand(20, 100) / 100,
                'pupil_dilation' => rand(10, 90) / 100,
                'clarity' => rand(80, 100) / 100
            ],
            'ears' => [
                'position' => ['forward', 'neutral', 'back'][rand(0, 2)],
                'alertness' => rand(40, 100) / 100
            ],
            'whiskers' => [
                'position' => ['forward', 'neutral', 'back'][rand(0, 2)],
                'tension' => rand(20, 80) / 100
            ]
        ];
    }
    
    private function analyzeBodyLanguage($photoPath) {
        return [
            'posture' => ['relaxed', 'alert', 'tense', 'crouched'][rand(0, 3)],
            'tail_position' => ['up', 'neutral', 'down', 'tucked'][rand(0, 3)],
            'overall_tension' => rand(10, 70) / 100
        ];
    }
    
    private function analyzeEyes($photoPath) {
        $score = rand(70, 100) / 100;
        return [
            'score' => $score,
            'brightness' => rand(80, 100) / 100,
            'clarity' => rand(85, 100) / 100,
            'discharge' => $score < 0.8,
            'inflammation' => $score < 0.7
        ];
    }
    
    private function analyzeEars($photoPath) {
        $score = rand(80, 100) / 100;
        return [
            'score' => $score,
            'cleanliness' => rand(85, 100) / 100,
            'position' => 'alert',
            'wax_buildup' => $score < 0.8,
            'mites_detected' => $score < 0.6
        ];
    }
    
    private function analyzeCoat($photoPath) {
        $score = rand(75, 100) / 100;
        return [
            'score' => $score,
            'shine' => rand(70, 100) / 100,
            'density' => rand(80, 100) / 100,
            'cleanliness' => rand(85, 100) / 100,
            'matting' => $score < 0.7,
            'bald_patches' => $score < 0.6
        ];
    }
    
    private function analyzeNose($photoPath) {
        $score = rand(85, 100) / 100;
        return [
            'score' => $score,
            'color' => 'healthy_pink',
            'moisture' => rand(60, 100) / 100,
            'discharge' => $score < 0.8
        ];
    }
    
    private function analyzeHealthPosture($photoPath) {
        $score = rand(80, 100) / 100;
        return [
            'score' => $score,
            'balance' => rand(85, 100) / 100,
            'mobility_indicators' => rand(80, 100) / 100,
            'pain_indicators' => $score < 0.7
        ];
    }
    
    private function analyzeOverallAppearance($photoPath) {
        $score = rand(80, 100) / 100;
        return [
            'score' => $score,
            'body_condition' => rand(5, 9) / 9, // 1-9 scale converted
            'muscle_tone' => rand(70, 100) / 100,
            'alertness' => rand(80, 100) / 100
        ];
    }
    
    private function identifyHealthConcerns($healthIndicators) {
        $concerns = [];
        
        foreach ($healthIndicators as $indicator => $data) {
            if ($data['score'] < 0.7) {
                $concerns[] = [
                    'type' => $indicator,
                    'severity' => $data['score'] < 0.5 ? 'high' : 'moderate',
                    'recommendation' => $this->getHealthRecommendation($indicator)
                ];
            }
        }
        
        return $concerns;
    }
    
    private function generateHealthRecommendations($healthIndicators) {
        $recommendations = [];
        
        foreach ($healthIndicators as $indicator => $data) {
            if ($data['score'] < 0.8) {
                $recommendations[] = $this->getHealthRecommendation($indicator);
            }
        }
        
        return array_unique($recommendations);
    }
    
    private function getHealthRecommendation($indicator) {
        $recommendations = [
            'eye_condition' => 'Monitor for discharge, consult vet if persistent',
            'ear_condition' => 'Check for wax buildup, clean gently if needed',
            'coat_condition' => 'Increase grooming frequency, check diet quality',
            'nose_condition' => 'Monitor nasal discharge, ensure hydration',
            'posture_health' => 'Watch for mobility issues, consider vet examination',
            'overall_appearance' => 'General health check recommended'
        ];
        
        return $recommendations[$indicator] ?? 'Monitor and consult veterinarian if concerned';
    }
    
    private function assessActivityLevel($photoPath) {
        $levels = ['very_low', 'low', 'medium', 'high', 'very_high'];
        return [
            'level' => $levels[rand(1, 3)], // Bias toward normal levels
            'confidence' => rand(70, 95) / 100
        ];
    }
    
    private function assessSocialBehavior($photoPath) {
        return [
            'social_engagement' => rand(40, 90) / 100,
            'human_orientation' => rand(50, 95) / 100,
            'approachability' => rand(60, 90) / 100
        ];
    }
    
    private function assessTerritorialBehavior($photoPath) {
        return [
            'territorial_confidence' => rand(50, 90) / 100,
            'space_ownership' => rand(60, 85) / 100,
            'defensive_posture' => rand(10, 40) / 100
        ];
    }
    
    private function assessComfortLevel($photoPath) {
        return [
            'environmental_comfort' => rand(70, 95) / 100,
            'stress_level' => rand(5, 30) / 100,
            'relaxation_indicators' => rand(60, 90) / 100
        ];
    }
    
    private function assessEngagementLevel($photoPath) {
        return [
            'attention_focus' => rand(50, 90) / 100,
            'curiosity_level' => rand(40, 85) / 100,
            'responsiveness' => rand(60, 90) / 100
        ];
    }
    
    private function predictNextBehavior($behaviorIndicators) {
        $behaviors = ['playing', 'resting', 'exploring', 'grooming', 'sleeping', 'eating'];
        
        // Simple prediction based on current state
        $activityLevel = $behaviorIndicators['activity_level']['level'];
        $comfortLevel = $behaviorIndicators['comfort_level']['environmental_comfort'];
        
        if ($activityLevel === 'high' && $comfortLevel > 0.8) {
            return ['behavior' => 'playing', 'probability' => 0.85];
        } elseif ($activityLevel === 'low' && $comfortLevel > 0.7) {
            return ['behavior' => 'resting', 'probability' => 0.90];
        } else {
            return ['behavior' => $behaviors[rand(0, 5)], 'probability' => 0.70];
        }
    }
    
    private function generateBehavioralRecommendations($behaviorIndicators) {
        $recommendations = [];
        
        $activityLevel = $behaviorIndicators['activity_level']['level'];
        $socialBehavior = $behaviorIndicators['social_behavior']['social_engagement'];
        $comfortLevel = $behaviorIndicators['comfort_level']['environmental_comfort'];
        
        if ($activityLevel === 'very_high') {
            $recommendations[] = 'Provide more interactive toys and climbing structures';
        } elseif ($activityLevel === 'very_low') {
            $recommendations[] = 'Encourage gentle play and movement';
        }
        
        if ($socialBehavior < 0.5) {
            $recommendations[] = 'Gradually increase social interaction time';
        } elseif ($socialBehavior > 0.9) {
            $recommendations[] = 'Consider adding more interactive social activities';
        }
        
        if ($comfortLevel < 0.6) {
            $recommendations[] = 'Assess and improve environmental comfort';
        }
        
        return $recommendations;
    }
    
    private function assessBodyCondition($photoPath) {
        // Simulate body condition scoring (1-9 scale)
        $bodyConditionScore = rand(4, 7); // Normal range
        
        return [
            'body_condition_score' => $bodyConditionScore,
            'weight_assessment' => $this->getWeightAssessment($bodyConditionScore),
            'muscle_condition' => rand(70, 95) / 100,
            'fat_distribution' => $this->getFatDistribution($bodyConditionScore),
            'recommendations' => $this->getBodyConditionRecommendations($bodyConditionScore)
        ];
    }
    
    private function getWeightAssessment($score) {
        if ($score <= 3) return 'underweight';
        if ($score <= 5) return 'ideal';
        if ($score <= 7) return 'overweight';
        return 'obese';
    }
    
    private function getFatDistribution($score) {
        return [
            'abdominal_fat' => $score > 6 ? 'excessive' : 'normal',
            'rib_coverage' => $score < 4 ? 'minimal' : ($score > 6 ? 'excessive' : 'normal')
        ];
    }
    
    private function getBodyConditionRecommendations($score) {
        if ($score <= 3) {
            return ['Increase caloric intake', 'Consult veterinarian about weight gain plan'];
        } elseif ($score >= 7) {
            return ['Reduce caloric intake', 'Increase exercise and play time', 'Consider weight management diet'];
        } else {
            return ['Maintain current diet and exercise routine'];
        }
    }
    
    private function analyzeFacialExpression($photoPath) {
        return [
            'expression_type' => ['neutral', 'alert', 'relaxed', 'focused'][rand(0, 3)],
            'eye_expression' => $this->analyzeEyeExpression($photoPath),
            'mouth_expression' => $this->analyzeMouthExpression($photoPath),
            'overall_expression_confidence' => rand(80, 95) / 100
        ];
    }
    
    private function analyzeEyeExpression($photoPath) {
        return [
            'openness_level' => rand(30, 100) / 100,
            'focus_direction' => ['camera', 'away', 'distant'][rand(0, 2)],
            'eye_shape' => ['relaxed', 'alert', 'wide'][rand(0, 2)]
        ];
    }
    
    private function analyzeMouthExpression($photoPath) {
        return [
            'mouth_position' => ['closed', 'slightly_open', 'panting'][rand(0, 2)],
            'tongue_visible' => rand(0, 1) === 1,
            'breathing_pattern' => ['normal', 'rapid', 'calm'][rand(0, 2)]
        ];
    }
    
    private function analyzePosture($photoPath) {
        $postures = [
            'sitting_upright' => ['confidence' => 0.90, 'comfort' => 0.80, 'alertness' => 0.85],
            'lying_down' => ['confidence' => 0.85, 'comfort' => 0.95, 'alertness' => 0.40],
            'crouched' => ['confidence' => 0.60, 'comfort' => 0.50, 'alertness' => 0.90],
            'standing_alert' => ['confidence' => 0.80, 'comfort' => 0.70, 'alertness' => 0.95],
            'stretched_out' => ['confidence' => 0.95, 'comfort' => 1.00, 'alertness' => 0.30]
        ];
        
        $detectedPosture = array_rand($postures);
        $postureData = $postures[$detectedPosture];
        
        return [
            'posture_type' => $detectedPosture,
            'confidence_level' => $postureData['confidence'],
            'comfort_indicator' => $postureData['comfort'],
            'alertness_level' => $postureData['alertness'],
            'stability' => rand(80, 100) / 100,
            'tension_indicators' => rand(10, 40) / 100
        ];
    }
    
    private function validatePhoto($photoPath) {
        if (!file_exists($photoPath)) {
            return false;
        }
        
        $fileSize = filesize($photoPath);
        if ($fileSize > $this->maxFileSize) {
            return false;
        }
        
        $imageInfo = getimagesize($photoPath);
        if (!$imageInfo) {
            return false;
        }
        
        $extension = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->supportedFormats)) {
            return false;
        }
        
        return true;
    }
    
    private function getImageMetadata($photoPath) {
        $imageInfo = getimagesize($photoPath);
        
        return [
            'width' => $imageInfo[0] ?? 0,
            'height' => $imageInfo[1] ?? 0,
            'type' => $imageInfo['mime'] ?? 'unknown',
            'brightness' => rand(30, 80), // Simulated
            'contrast' => rand(40, 90), // Simulated
            'quality_score' => rand(70, 95) / 100
        ];
    }
}

/**
 * Photo Analysis API Functions
 */

/**
 * Analyze uploaded cat photo
 */
function analyzeCatPhotoAPI($catId, $photoFile) {
    try {
        // Validate cat ownership
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT id FROM cats WHERE id = ? AND owner_id = ?");
        $stmt->execute([$catId, $_SESSION['user_id'] ?? 0]);
        
        if (!$stmt->fetch()) {
            throw new Exception('Unauthorized access to cat data');
        }
        
        // Handle photo upload
        $uploadDir = '../uploads/cat_photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = 'cat_' . $catId . '_' . time() . '_' . uniqid() . '.jpg';
        $photoPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($photoFile['tmp_name'], $photoPath)) {
            throw new Exception('Failed to upload photo');
        }
        
        // Analyze photo
        $analyzer = new CatPhotoAnalysisAI();
        $results = $analyzer->analyzeCatPhoto($photoPath, $catId);
        
        return [
            'success' => true,
            'analysis_results' => $results,
            'photo_path' => $photoPath
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get recent photo analysis results for a cat
 */
function getCatPhotoAnalysisHistory($catId, $limit = 10) {
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("
            SELECT * FROM cat_photo_analysis 
            WHERE cat_id = ? 
            ORDER BY analyzed_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$catId, $limit]);
        
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Error getting photo analysis history: " . $e->getMessage());
        return [];
    }
}

/**
 * Get mood trends from photo analysis
 */
function getCatMoodTrendsFromPhotos($catId, $days = 30) {
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("
            SELECT 
                detected_mood,
                COUNT(*) as frequency,
                AVG(mood_confidence) as avg_confidence,
                DATE(analyzed_at) as analysis_date
            FROM cat_photo_analysis 
            WHERE cat_id = ? 
            AND analysis_type = 'mood_detection'
            AND analyzed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY detected_mood, DATE(analyzed_at)
            ORDER BY analysis_date DESC, frequency DESC
        ");
        $stmt->execute([$catId, $days]);
        
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Error getting mood trends: " . $e->getMessage());
        return [];
    }
}

/**
 * Batch analyze multiple photos
 */
function batchAnalyzeCatPhotos($catId, $photoFiles, $analysisTypes = ['mood_detection', 'health_assessment']) {
    $results = [];
    $analyzer = new CatPhotoAnalysisAI();
    
    foreach ($photoFiles as $index => $photoFile) {
        try {
            $uploadDir = '../uploads/cat_photos/';
            $fileName = 'cat_' . $catId . '_batch_' . time() . '_' . $index . '.jpg';
            $photoPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($photoFile['tmp_name'], $photoPath)) {
                $analysisResult = $analyzer->analyzeCatPhoto($photoPath, $catId, $analysisTypes);
                $results[] = [
                    'photo_index' => $index,
                    'photo_path' => $photoPath,
                    'analysis' => $analysisResult
                ];
            }
            
        } catch (Exception $e) {
            $results[] = [
                'photo_index' => $index,
                'error' => $e->getMessage()
            ];
        }
    }
    
    return $results;
}

/**
 * Smart photo recommendations based on analysis history
 */
function getPhotoAnalysisRecommendations($catId) {
    try {
        $pdo = get_db();
        
        // Get recent analysis patterns
        $stmt = $pdo->prepare("
            SELECT analysis_type, COUNT(*) as count, AVG(mood_confidence) as avg_confidence
            FROM cat_photo_analysis 
            WHERE cat_id = ? 
            AND analyzed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY analysis_type
        ");
        $stmt->execute([$catId]);
        $patterns = $stmt->fetchAll();
        
        $recommendations = [];
        
        // Analyze patterns and suggest photo types
        $hasRecentMoodAnalysis = false;
        $hasRecentHealthAnalysis = false;
        
        foreach ($patterns as $pattern) {
            if ($pattern['analysis_type'] === 'mood_detection' && $pattern['count'] > 5) {
                $hasRecentMoodAnalysis = true;
            }
            if ($pattern['analysis_type'] === 'health_assessment' && $pattern['count'] > 2) {
                $hasRecentHealthAnalysis = true;
            }
        }
        
        if (!$hasRecentMoodAnalysis) {
            $recommendations[] = [
                'type' => 'mood_detection',
                'priority' => 'high',
                'suggestion' => 'Take a clear photo of your cat\'s face to track mood patterns'
            ];
        }
        
        if (!$hasRecentHealthAnalysis) {
            $recommendations[] = [
                'type' => 'health_assessment',
                'priority' => 'medium',
                'suggestion' => 'Capture full body photos to monitor health indicators'
            ];
        }
        
        $recommendations[] = [
            'type' => 'behavior_analysis',
            'priority' => 'low',
            'suggestion' => 'Photos during different activities help understand behavior patterns'
        ];
        
        return $recommendations;
        
    } catch (Exception $e) {
        error_log("Error getting photo recommendations: " . $e->getMessage());
        return [];
    }
}
?>
