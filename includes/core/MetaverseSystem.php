<?php
/**
 * 🌌 Purrr.love Core Metaverse System
 * Advanced virtual environment and VR interactions for cats
 */

namespace PurrrLove\Core;

use PurrrLove\Database\Connection;
use PurrrLove\Utils\Logger;
use PurrrLove\Core\Physics\PhysicsEngine;
use PurrrLove\Core\VR\VRManager;
use PurrrLove\Core\Weather\WeatherSystem;
use PurrrLove\Core\TimeCycle\TimeCycleManager;

class MetaverseSystem {
    private $db;
    private $logger;
    private $physicsEngine;
    private $vrManager;
    private $weatherSystem;
    private $timeCycleManager;

    // Configuration constants
    private const MAX_ENVIRONMENT_CAPACITY = 50;
    private const DEFAULT_PHYSICS_CONFIG = 'default';
    private const MIN_INTERACTION_DISTANCE = 0.5;
    private const MAX_INTERACTION_DISTANCE = 10.0;

    public function __construct() {
        $this->db = Connection::getInstance()->getConnection();
        $this->logger = new Logger('metaverse');
        $this->physicsEngine = new PhysicsEngine();
        $this->vrManager = new VRManager();
        $this->weatherSystem = new WeatherSystem();
        $this->timeCycleManager = new TimeCycleManager();
    }

    /**
     * Create a new virtual environment
     */
    public function createEnvironment($data) {
        try {
            // Validate environment data
            $this->validateEnvironmentData($data);

            // Start transaction
            $this->db->beginTransaction();

            // Create environment
            $stmt = $this->db->prepare("
                INSERT INTO virtual_environments (
                    name, description, type, difficulty,
                    physics_config, weather_enabled, time_cycle_enabled,
                    capacity, creator_id, is_template, parent_template_id,
                    status, metadata
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                ) RETURNING id
            ");

            $stmt->execute([
                $data['name'],
                $data['description'],
                $data['type'],
                $data['difficulty'] ?? 1,
                json_encode($data['physics_config'] ?? $this->getDefaultPhysicsConfig()),
                $data['weather_enabled'] ?? true,
                $data['time_cycle_enabled'] ?? true,
                min($data['capacity'] ?? 10, self::MAX_ENVIRONMENT_CAPACITY),
                $data['creator_id'],
                $data['is_template'] ?? false,
                $data['parent_template_id'] ?? null,
                'active',
                json_encode($data['metadata'] ?? [])
            ]);

            $environmentId = $stmt->fetchColumn();

            // Create environment objects
            if (!empty($data['objects'])) {
                foreach ($data['objects'] as $object) {
                    $this->createEnvironmentObject($environmentId, $object);
                }
            }

            // Set up weather if enabled
            if ($data['weather_enabled'] ?? true) {
                $this->weatherSystem->initializeWeather($environmentId);
            }

            // Set up time cycle if enabled
            if ($data['time_cycle_enabled'] ?? true) {
                $this->timeCycleManager->initializeTimeCycle($environmentId);
            }

            // Initialize physics simulation
            $this->physicsEngine->initializeEnvironment($environmentId);

            $this->db->commit();

            $this->logger->info("Created new virtual environment", [
                'environment_id' => $environmentId,
                'name' => $data['name'],
                'type' => $data['type']
            ]);

            return $environmentId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Failed to create virtual environment", [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Start a VR session for a cat
     */
    public function startVRSession($catId, $environmentId, $userId = null) {
        try {
            // Validate session requirements
            $this->validateSessionRequirements($catId, $environmentId);

            // Check environment capacity
            if (!$this->checkEnvironmentCapacity($environmentId)) {
                throw new \Exception("Environment is at maximum capacity");
            }

            // Start transaction
            $this->db->beginTransaction();

            // Create session
            $stmt = $this->db->prepare("
                INSERT INTO vr_sessions (
                    environment_id, cat_id, user_id,
                    session_type, start_time
                ) VALUES (?, ?, ?, ?, NOW())
                RETURNING id
            ");

            $stmt->execute([
                $environmentId,
                $catId,
                $userId,
                'standard'
            ]);

            $sessionId = $stmt->fetchColumn();

            // Initialize VR manager for this session
            $this->vrManager->initializeSession($sessionId, $environmentId, $catId);

            // Set up physics simulation for the cat
            $this->physicsEngine->initializeEntity($sessionId, $catId);

            $this->db->commit();

            $this->logger->info("Started VR session", [
                'session_id' => $sessionId,
                'cat_id' => $catId,
                'environment_id' => $environmentId
            ]);

            return $sessionId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Failed to start VR session", [
                'error' => $e->getMessage(),
                'cat_id' => $catId,
                'environment_id' => $environmentId
            ]);
            throw $e;
        }
    }

    /**
     * Process VR interaction
     */
    public function processInteraction($sessionId, $interactionData) {
        try {
            // Validate interaction data
            $this->validateInteractionData($interactionData);

            // Get session info
            $session = $this->getSessionInfo($sessionId);
            if (!$session) {
                throw new \Exception("Invalid session");
            }

            // Start transaction
            $this->db->beginTransaction();

            // Record interaction
            $stmt = $this->db->prepare("
                INSERT INTO vr_interactions (
                    session_id, cat_id, interaction_type,
                    target_type, target_id, position,
                    rotation, force, duration, result_data
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                RETURNING id
            ");

            $stmt->execute([
                $sessionId,
                $session['cat_id'],
                $interactionData['type'],
                $interactionData['target_type'],
                $interactionData['target_id'],
                json_encode($interactionData['position']),
                json_encode($interactionData['rotation']),
                $interactionData['force'] ?? 0,
                $interactionData['duration'] ?? 0,
                json_encode($interactionData['result_data'] ?? [])
            ]);

            $interactionId = $stmt->fetchColumn();

            // Process physics if applicable
            if ($interactionData['physics_enabled'] ?? true) {
                $this->physicsEngine->processInteraction($interactionId, $interactionData);
            }

            // Update session statistics
            $this->updateSessionStats($sessionId);

            $this->db->commit();

            $this->logger->info("Processed VR interaction", [
                'interaction_id' => $interactionId,
                'session_id' => $sessionId,
                'type' => $interactionData['type']
            ]);

            return $interactionId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Failed to process VR interaction", [
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ]);
            throw $e;
        }
    }

    /**
     * Process social interaction between cats
     */
    public function processSocialInteraction($initiatorSessionId, $targetCatId, $interactionData) {
        try {
            // Validate social interaction
            $this->validateSocialInteraction($initiatorSessionId, $targetCatId, $interactionData);

            // Start transaction
            $this->db->beginTransaction();

            // Get initiator session info
            $initiatorSession = $this->getSessionInfo($initiatorSessionId);

            // Record social interaction
            $stmt = $this->db->prepare("
                INSERT INTO vr_social_interactions (
                    session_id, initiator_cat_id, target_cat_id,
                    interaction_type, start_time, interaction_data
                ) VALUES (?, ?, ?, ?, NOW(), ?)
                RETURNING id
            ");

            $stmt->execute([
                $initiatorSessionId,
                $initiatorSession['cat_id'],
                $targetCatId,
                $interactionData['type'],
                json_encode($interactionData)
            ]);

            $interactionId = $stmt->fetchColumn();

            // Update both cats' stats
            $this->updateCatSocialStats($initiatorSession['cat_id'], $targetCatId, $interactionData);

            $this->db->commit();

            $this->logger->info("Processed social interaction", [
                'interaction_id' => $interactionId,
                'initiator_cat_id' => $initiatorSession['cat_id'],
                'target_cat_id' => $targetCatId,
                'type' => $interactionData['type']
            ]);

            return $interactionId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Failed to process social interaction", [
                'error' => $e->getMessage(),
                'session_id' => $initiatorSessionId,
                'target_cat_id' => $targetCatId
            ]);
            throw $e;
        }
    }

    /**
     * End VR session
     */
    public function endSession($sessionId) {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Update session end time and duration
            $stmt = $this->db->prepare("
                UPDATE vr_sessions
                SET end_time = NOW(),
                    duration = EXTRACT(EPOCH FROM (NOW() - start_time))::INTEGER
                WHERE id = ?
                RETURNING environment_id, cat_id
            ");

            $stmt->execute([$sessionId]);
            $sessionInfo = $stmt->fetch();

            if (!$sessionInfo) {
                throw new \Exception("Invalid session ID");
            }

            // Clean up physics simulation
            $this->physicsEngine->cleanupEntity($sessionId);

            // Update environment statistics
            $this->updateEnvironmentStats($sessionInfo['environment_id']);

            // Process session rewards
            $this->processSessionRewards($sessionId, $sessionInfo['cat_id']);

            $this->db->commit();

            $this->logger->info("Ended VR session", [
                'session_id' => $sessionId,
                'environment_id' => $sessionInfo['environment_id'],
                'cat_id' => $sessionInfo['cat_id']
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Failed to end VR session", [
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ]);
            throw $e;
        }
    }

    /**
     * Helper functions
     */
    private function validateEnvironmentData($data) {
        if (empty($data['name'])) {
            throw new \Exception("Environment name is required");
        }
        if (empty($data['type'])) {
            throw new \Exception("Environment type is required");
        }
        if (empty($data['creator_id'])) {
            throw new \Exception("Creator ID is required");
        }
    }

    private function getDefaultPhysicsConfig() {
        $stmt = $this->db->prepare("
            SELECT config 
            FROM physics_configurations 
            WHERE name = ?
        ");
        $stmt->execute([self::DEFAULT_PHYSICS_CONFIG]);
        return $stmt->fetchColumn() ?: [];
    }

    private function validateSessionRequirements($catId, $environmentId) {
        // Check if cat exists and is active
        $stmt = $this->db->prepare("
            SELECT status FROM cats WHERE id = ?
        ");
        $stmt->execute([$catId]);
        $catStatus = $stmt->fetchColumn();

        if (!$catStatus || $catStatus !== 'active') {
            throw new \Exception("Invalid or inactive cat");
        }

        // Check if environment exists and is active
        $stmt = $this->db->prepare("
            SELECT status FROM virtual_environments WHERE id = ?
        ");
        $stmt->execute([$environmentId]);
        $envStatus = $stmt->fetchColumn();

        if (!$envStatus || $envStatus !== 'active') {
            throw new \Exception("Invalid or inactive environment");
        }

        // Check if cat is already in a session
        $stmt = $this->db->prepare("
            SELECT id FROM vr_sessions 
            WHERE cat_id = ? AND end_time IS NULL
        ");
        $stmt->execute([$catId]);
        if ($stmt->fetchColumn()) {
            throw new \Exception("Cat is already in an active session");
        }
    }

    private function checkEnvironmentCapacity($environmentId) {
        $stmt = $this->db->prepare("
            WITH env_capacity AS (
                SELECT capacity FROM virtual_environments WHERE id = ?
            ),
            active_sessions AS (
                SELECT COUNT(*) as count
                FROM vr_sessions
                WHERE environment_id = ? AND end_time IS NULL
            )
            SELECT 
                env_capacity.capacity >= active_sessions.count
            FROM env_capacity, active_sessions
        ");
        $stmt->execute([$environmentId, $environmentId]);
        return $stmt->fetchColumn();
    }

    private function getSessionInfo($sessionId) {
        $stmt = $this->db->prepare("
            SELECT * FROM vr_sessions WHERE id = ?
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetch();
    }

    private function validateInteractionData($data) {
        if (empty($data['type'])) {
            throw new \Exception("Interaction type is required");
        }
        if (empty($data['position'])) {
            throw new \Exception("Interaction position is required");
        }

        // Validate interaction distance
        if (isset($data['distance']) &&
            ($data['distance'] < self::MIN_INTERACTION_DISTANCE ||
             $data['distance'] > self::MAX_INTERACTION_DISTANCE)) {
            throw new \Exception("Invalid interaction distance");
        }
    }

    private function updateSessionStats($sessionId) {
        $stmt = $this->db->prepare("
            UPDATE vr_sessions
            SET interaction_count = interaction_count + 1,
                performance_metrics = performance_metrics || ?::jsonb
            WHERE id = ?
        ");

        $metrics = [
            'last_interaction_time' => date('Y-m-d H:i:s'),
            'total_interactions' => 'interaction_count + 1'
        ];

        $stmt->execute([json_encode($metrics), $sessionId]);
    }

    private function validateSocialInteraction($initiatorSessionId, $targetCatId, $data) {
        if (empty($data['type'])) {
            throw new \Exception("Social interaction type is required");
        }

        // Check if target cat is in the same environment
        $stmt = $this->db->prepare("
            SELECT vs1.environment_id = vs2.environment_id as same_env
            FROM vr_sessions vs1
            JOIN vr_sessions vs2 ON vs2.cat_id = ?
            WHERE vs1.id = ?
            AND vs2.end_time IS NULL
        ");
        $stmt->execute([$targetCatId, $initiatorSessionId]);

        if (!$stmt->fetchColumn()) {
            throw new \Exception("Target cat must be in the same environment");
        }
    }

    private function updateCatSocialStats($initiatorCatId, $targetCatId, $interactionData) {
        // Implementation depends on how you want to track social statistics
        // This is a placeholder for the actual implementation
    }

    private function updateEnvironmentStats($environmentId) {
        // Trigger the statistics calculation
        $stmt = $this->db->prepare("
            SELECT calculate_environment_statistics(?)
        ");
        $stmt->execute([$environmentId]);
    }

    private function processSessionRewards($sessionId, $catId) {
        // Implementation depends on your reward system
        // This is a placeholder for the actual implementation
    }
}
