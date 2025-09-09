<?php
/**
 * 🧬 Purrr.love Core Evolution System
 * Handles cat evolution, trait adaptation, and experience processing
 */

namespace PurrrLove\Core;

use PurrrLove\Database\Connection;
use PurrrLove\Utils\Logger;
use PurrrLove\Core\GeneticsSystem;

class EvolutionSystem {
    private $db;
    private $geneticsSystem;
    private $logger;
    private $evolutionConfig;
    
    public function __construct(GeneticsSystem $geneticsSystem) {
        $this->db = Connection::getInstance()->getConnection();
        $this->geneticsSystem = $geneticsSystem;
        $this->logger = new Logger('evolution');
        $this->initializeConfig();
    }
    
    /**
     * Initialize evolution configuration
     */
    private function initializeConfig() {
        $this->evolutionConfig = [
            'experience_rates' => [
                'physical_activity' => 1.2,
                'mental_challenge' => 1.5,
                'social_interaction' => 1.3,
                'combat' => 1.8,
                'exploration' => 1.4,
                'training' => 2.0
            ],
            'evolution_stages' => [
                1 => ['name' => 'Basic', 'exp_threshold' => 0],
                2 => ['name' => 'Evolved', 'exp_threshold' => 1000],
                3 => ['name' => 'Advanced', 'exp_threshold' => 5000],
                4 => ['name' => 'Superior', 'exp_threshold' => 15000],
                5 => ['name' => 'Ultimate', 'exp_threshold' => 50000]
            ],
            'adaptation_factors' => [
                'environment' => 0.3,
                'challenge' => 0.4,
                'social' => 0.2,
                'random' => 0.1
            ],
            'mutation_triggers' => [
                'stress' => ['threshold' => 0.8, 'chance' => 0.2],
                'achievement' => ['threshold' => 0.9, 'chance' => 0.3],
                'mastery' => ['threshold' => 0.95, 'chance' => 0.4]
            ]
        ];
    }
    
    /**
     * Process evolution event
     */
    public function processEvolutionEvent($catId, $eventType, $eventData) {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Get current evolution data
            $evolutionData = $this->getEvolutionData($catId);
            
            // Process experience gain
            $experienceGain = $this->calculateExperienceGain($eventType, $eventData);
            
            // Check for adaptations
            $adaptations = $this->processAdaptations($evolutionData, $eventData);
            
            // Check for mutations
            $mutations = $this->checkForMutations($evolutionData, $eventData);
            
            // Update evolution stage if necessary
            $newStage = $this->checkEvolutionStage(
                $evolutionData['experience_points'] + $experienceGain
            );
            
            // Record evolution event
            $eventId = $this->recordEvolutionEvent($catId, [
                'event_type' => $eventType,
                'event_data' => $eventData,
                'experience_gain' => $experienceGain,
                'adaptations' => $adaptations,
                'mutations' => $mutations,
                'new_stage' => $newStage
            ]);
            
            // Update evolution data
            $this->updateEvolutionData($catId, [
                'experience_points' => $evolutionData['experience_points'] + $experienceGain,
                'evolution_stage' => $newStage,
                'adaptations' => array_merge($evolutionData['adaptations'] ?? [], $adaptations),
                'mutations' => array_merge($evolutionData['mutations'] ?? [], $mutations)
            ]);
            
            $this->db->commit();
            
            $this->logger->info("Processed evolution event", [
                'cat_id' => $catId,
                'event_id' => $eventId,
                'event_type' => $eventType,
                'experience_gain' => $experienceGain
            ]);
            
            return $eventId;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Error processing evolution event", [
                'error' => $e->getMessage(),
                'cat_id' => $catId,
                'event_type' => $eventType
            ]);
            throw $e;
        }
    }
    
    /**
     * Calculate experience gain from event
     */
    private function calculateExperienceGain($eventType, $eventData) {
        $baseExperience = $eventData['base_experience'] ?? 10;
        $multiplier = $this->evolutionConfig['experience_rates'][$eventType] ?? 1.0;
        
        // Apply difficulty modifier
        if (isset($eventData['difficulty'])) {
            $multiplier *= (1 + ($eventData['difficulty'] * 0.2));
        }
        
        // Apply success rate modifier
        if (isset($eventData['success_rate'])) {
            $multiplier *= (0.5 + $eventData['success_rate']);
        }
        
        // Apply special condition modifiers
        if (isset($eventData['conditions'])) {
            foreach ($eventData['conditions'] as $condition) {
                $multiplier *= $this->getConditionMultiplier($condition);
            }
        }
        
        return round($baseExperience * $multiplier);
    }
    
    /**
     * Process potential adaptations based on event
     */
    private function processAdaptations($evolutionData, $eventData) {
        $adaptations = [];
        
        // Check each adaptation factor
        foreach ($this->evolutionConfig['adaptation_factors'] as $factor => $weight) {
            if ($this->shouldTriggerAdaptation($evolutionData, $eventData, $factor)) {
                $adaptations[] = $this->generateAdaptation($factor, $eventData);
            }
        }
        
        return $adaptations;
    }
    
    /**
     * Check for potential mutations
     */
    private function checkForMutations($evolutionData, $eventData) {
        $mutations = [];
        
        // Check each mutation trigger
        foreach ($this->evolutionConfig['mutation_triggers'] as $trigger => $config) {
            if ($this->shouldTriggerMutation($evolutionData, $eventData, $trigger, $config)) {
                $mutations[] = $this->generateMutation($trigger, $eventData);
            }
        }
        
        return $mutations;
    }
    
    /**
     * Determine evolution stage based on experience
     */
    private function checkEvolutionStage($totalExperience) {
        $currentStage = 1;
        
        foreach ($this->evolutionConfig['evolution_stages'] as $stage => $config) {
            if ($totalExperience >= $config['exp_threshold']) {
                $currentStage = $stage;
            } else {
                break;
            }
        }
        
        return $currentStage;
    }
    
    /**
     * Record evolution event in database
     */
    private function recordEvolutionEvent($catId, $eventData) {
        $stmt = $this->db->prepare("
            INSERT INTO cat_evolution_events (
                cat_id,
                event_type,
                event_data,
                experience_gain,
                adaptations,
                mutations,
                new_stage,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            RETURNING id
        ");
        
        $stmt->execute([
            $catId,
            $eventData['event_type'],
            json_encode($eventData['event_data']),
            $eventData['experience_gain'],
            json_encode($eventData['adaptations']),
            json_encode($eventData['mutations']),
            $eventData['new_stage']
        ]);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Get current evolution data for a cat
     */
    private function getEvolutionData($catId) {
        $stmt = $this->db->prepare("
            SELECT * FROM cat_evolution_data 
            WHERE cat_id = ?
        ");
        
        $stmt->execute([$catId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$data) {
            // Initialize evolution data if not exists
            $data = $this->initializeEvolutionData($catId);
        }
        
        return $data;
    }
    
    /**
     * Initialize evolution data for new cat
     */
    private function initializeEvolutionData($catId) {
        $stmt = $this->db->prepare("
            INSERT INTO cat_evolution_data (
                cat_id,
                experience_points,
                evolution_stage,
                adaptations,
                mutations,
                created_at,
                updated_at
            ) VALUES (?, 0, 1, '[]', '[]', NOW(), NOW())
            RETURNING *
        ");
        
        $stmt->execute([$catId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Update evolution data in database
     */
    private function updateEvolutionData($catId, $updates) {
        $stmt = $this->db->prepare("
            UPDATE cat_evolution_data
            SET experience_points = ?,
                evolution_stage = ?,
                adaptations = ?,
                mutations = ?,
                updated_at = NOW()
            WHERE cat_id = ?
        ");
        
        $stmt->execute([
            $updates['experience_points'],
            $updates['evolution_stage'],
            json_encode($updates['adaptations']),
            json_encode($updates['mutations']),
            $catId
        ]);
    }
    
    /**
     * Helper functions
     */
    private function getConditionMultiplier($condition) {
        $multipliers = [
            'first_time' => 1.5,
            'rare_event' => 1.3,
            'challenging' => 1.2,
            'mastery' => 1.4,
            'discovery' => 1.25
        ];
        
        return $multipliers[$condition] ?? 1.0;
    }
    
    private function shouldTriggerAdaptation($evolutionData, $eventData, $factor) {
        // Complex logic to determine if adaptation should trigger
        // This is a simplified version
        $threshold = 0.7;
        $random = mt_rand() / mt_getrandmax();
        
        return $random < $threshold;
    }
    
    private function generateAdaptation($factor, $eventData) {
        // Generate specific adaptation based on factor
        return [
            'factor' => $factor,
            'type' => $this->determineAdaptationType($factor, $eventData),
            'strength' => mt_rand(1, 100) / 100,
            'timestamp' => time()
        ];
    }
    
    private function determineAdaptationType($factor, $eventData) {
        $types = [
            'environment' => ['climate', 'terrain', 'habitat'],
            'challenge' => ['skill', 'strategy', 'resistance'],
            'social' => ['communication', 'empathy', 'leadership'],
            'random' => ['mutation', 'enhancement', 'specialization']
        ];
        
        $available = $types[$factor] ?? ['generic'];
        return $available[array_rand($available)];
    }
    
    private function shouldTriggerMutation($evolutionData, $eventData, $trigger, $config) {
        if (!isset($eventData[$trigger . '_level'])) {
            return false;
        }
        
        if ($eventData[$trigger . '_level'] >= $config['threshold']) {
            return mt_rand() / mt_getrandmax() < $config['chance'];
        }
        
        return false;
    }
    
    private function generateMutation($trigger, $eventData) {
        return [
            'trigger' => $trigger,
            'type' => $this->determineMutationType($trigger),
            'strength' => mt_rand(1, 100) / 100,
            'permanent' => mt_rand() / mt_getrandmax() < 0.3,
            'timestamp' => time()
        ];
    }
    
    private function determineMutationType($trigger) {
        $types = [
            'stress' => ['adaptation', 'resistance', 'enhancement'],
            'achievement' => ['specialization', 'mastery', 'evolution'],
            'mastery' => ['transcendence', 'perfection', 'breakthrough']
        ];
        
        $available = $types[$trigger] ?? ['random'];
        return $available[array_rand($available)];
    }
}
