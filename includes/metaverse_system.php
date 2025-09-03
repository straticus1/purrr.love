<?php
/**
 * 🌍 Purrr.love Metaverse Integration System
 * Virtual 3D cat worlds and social VR experiences
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Metaverse world types
 */
define('METAVERSE_WORLDS', [
    'cat_paradise' => [
        'name' => 'Cat Paradise',
        'description' => 'A lush tropical island where cats roam freely',
        'max_players' => 100,
        'features' => ['fishing', 'climbing', 'social', 'trading'],
        'environment' => 'tropical',
        'difficulty' => 'easy'
    ],
    'mystic_forest' => [
        'name' => 'Mystic Forest',
        'description' => 'Enchanted forest with magical creatures and hidden treasures',
        'max_players' => 75,
        'features' => ['exploration', 'quests', 'magic', 'puzzles'],
        'environment' => 'forest',
        'difficulty' => 'medium'
    ],
    'cosmic_city' => [
        'name' => 'Cosmic City',
        'description' => 'Futuristic city in space with advanced technology',
        'max_players' => 150,
        'features' => ['technology', 'racing', 'social', 'innovation'],
        'environment' => 'futuristic',
        'difficulty' => 'hard'
    ],
    'winter_wonderland' => [
        'name' => 'Winter Wonderland',
        'description' => 'Snowy mountains and frozen lakes for winter adventures',
        'max_players' => 60,
        'features' => ['snow_sports', 'ice_fishing', 'winter_games', 'cozy_spots'],
        'environment' => 'winter',
        'difficulty' => 'medium'
    ],
    'desert_oasis' => [
        'name' => 'Desert Oasis',
        'description' => 'Vast desert with hidden oases and ancient ruins',
        'max_players' => 80,
        'features' => ['exploration', 'treasure_hunting', 'survival', 'mystery'],
        'environment' => 'desert',
        'difficulty' => 'hard'
    ]
]);

/**
 * VR interaction types
 */
define('VR_INTERACTIONS', [
    'petting' => 'Pet and interact with cats',
    'playing' => 'Play games and activities',
    'exploring' => 'Explore virtual environments',
    'socializing' => 'Meet other players',
    'trading' => 'Trade items and cats',
    'building' => 'Build and customize spaces',
    'questing' => 'Complete quests and missions',
    'racing' => 'Participate in races and competitions'
]);

/**
 * Metaverse System Class
 */
class MetaverseSystem {
    private $pdo;
    private $config;
    
    public function __construct() {
        $this->pdo = get_db();
        $this->config = [
            'max_concurrent_worlds' => 10,
            'max_players_per_world' => 200,
            'session_timeout' => 300, // 5 minutes
            'world_rotation_interval' => 3600, // 1 hour
            'vr_support_enabled' => true,
            'webvr_enabled' => true,
            'haptic_feedback' => true
        ];
    }
    
    /**
     * Create metaverse world instance
     */
    public function createWorldInstance($worldType, $creatorId, $customSettings = []) {
        try {
            if (!isset(METAVERSE_WORLDS[$worldType])) {
                throw new Exception("Invalid world type: $worldType");
            }
            
            $worldConfig = METAVERSE_WORLDS[$worldType];
            
            // Check if user can create world
            if (!$this->canUserCreateWorld($creatorId)) {
                throw new Exception('User cannot create more worlds');
            }
            
            // Generate unique world ID
            $worldId = $this->generateWorldId();
            
            // Create world instance
            $stmt = $this->pdo->prepare("
                INSERT INTO metaverse_worlds 
                (world_id, world_type, creator_id, name, description, max_players, 
                 features, environment, difficulty, custom_settings, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)
            ");
            
            $stmt->execute([
                $worldId,
                $worldType,
                $creatorId,
                $worldConfig['name'],
                $worldConfig['description'],
                $worldConfig['max_players'],
                json_encode($worldConfig['features']),
                $worldConfig['environment'],
                $worldConfig['difficulty'],
                json_encode($customSettings),
                date('Y-m-d H:i:s')
            ]);
            
            // Log world creation
            logSecurityEvent('metaverse_world_created', [
                'creator_id' => $creatorId,
                'world_id' => $worldId,
                'world_type' => $worldType,
                'custom_settings' => $customSettings
            ]);
            
            return [
                'world_id' => $worldId,
                'world_type' => $worldType,
                'name' => $worldConfig['name'],
                'description' => $worldConfig['description'],
                'max_players' => $worldConfig['max_players'],
                'features' => $worldConfig['features'],
                'environment' => $worldConfig['environment'],
                'difficulty' => $worldConfig['difficulty'],
                'custom_settings' => $customSettings,
                'status' => 'active'
            ];
            
        } catch (Exception $e) {
            error_log("Error creating metaverse world: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Join metaverse world
     */
    public function joinWorld($worldId, $userId, $catId = null) {
        try {
            // Get world information
            $stmt = $this->pdo->prepare("
                SELECT * FROM metaverse_worlds 
                WHERE world_id = ? AND status = 'active'
            ");
            $stmt->execute([$worldId]);
            $world = $stmt->fetch();
            
            if (!$world) {
                throw new Exception('World not found or inactive');
            }
            
            // Check if world is full
            $currentPlayers = $this->getWorldPlayerCount($worldId);
            if ($currentPlayers >= $world['max_players']) {
                throw new Exception('World is full');
            }
            
            // Check if user is already in world
            if ($this->isUserInWorld($userId, $worldId)) {
                throw new Exception('User already in world');
            }
            
            // Get user's cat for the world
            if (!$catId) {
                $catId = $this->getUserDefaultCat($userId);
            }
            
            if (!$catId) {
                throw new Exception('No cat available for world entry');
            }
            
            // Create world session
            $stmt = $this->pdo->prepare("
                INSERT INTO metaverse_sessions 
                (world_id, user_id, cat_id, status, joined_at, last_activity)
                VALUES (?, ?, ?, 'active', ?, ?)
            ");
            
            $stmt->execute([
                $worldId,
                $userId,
                $catId,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            // Update world player count
            $this->updateWorldPlayerCount($worldId);
            
            // Log world join
            logSecurityEvent('metaverse_world_joined', [
                'user_id' => $userId,
                'world_id' => $worldId,
                'cat_id' => $catId
            ]);
            
            // Trigger webhook event
            triggerWebhookEvent('metaverse.world_joined', [
                'user_id' => $userId,
                'world_id' => $worldId,
                'cat_id' => $catId,
                'world_type' => $world['world_type']
            ]);
            
            return [
                'world_id' => $worldId,
                'world_type' => $world['world_type'],
                'world_name' => $world['name'],
                'cat_id' => $catId,
                'player_count' => $currentPlayers + 1,
                'max_players' => $world['max_players'],
                'features' => json_decode($world['features'], true),
                'environment' => $world['environment']
            ];
            
        } catch (Exception $e) {
            error_log("Error joining metaverse world: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Leave metaverse world
     */
    public function leaveWorld($worldId, $userId) {
        try {
            // Check if user is in world
            if (!$this->isUserInWorld($userId, $worldId)) {
                throw new Exception('User not in world');
            }
            
            // End world session
            $stmt = $this->pdo->prepare("
                UPDATE metaverse_sessions 
                SET status = 'left', left_at = ?, last_activity = ?
                WHERE world_id = ? AND user_id = ? AND status = 'active'
            ");
            
            $stmt->execute([
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s'),
                $worldId,
                $userId
            ]);
            
            // Update world player count
            $this->updateWorldPlayerCount($worldId);
            
            // Log world leave
            logSecurityEvent('metaverse_world_left', [
                'user_id' => $userId,
                'world_id' => $worldId
            ]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error leaving metaverse world: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get active worlds
     */
    public function getActiveWorlds($filters = []) {
        try {
            $sql = "
                SELECT 
                    w.*,
                    COUNT(s.id) as current_players,
                    u.username as creator_name
                FROM metaverse_worlds w
                LEFT JOIN metaverse_sessions s ON w.world_id = s.world_id AND s.status = 'active'
                LEFT JOIN users u ON w.creator_id = u.id
                WHERE w.status = 'active'
            ";
            
            $params = [];
            $conditions = [];
            
            if (isset($filters['world_type'])) {
                $conditions[] = "w.world_type = ?";
                $params[] = $filters['world_type'];
            }
            
            if (isset($filters['environment'])) {
                $conditions[] = "w.environment = ?";
                $params[] = $filters['environment'];
            }
            
            if (isset($filters['difficulty'])) {
                $conditions[] = "w.difficulty = ?";
                $params[] = $filters['difficulty'];
            }
            
            if (isset($filters['min_players'])) {
                $conditions[] = "COUNT(s.id) >= ?";
                $params[] = $filters['min_players'];
            }
            
            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }
            
            $sql .= " GROUP BY w.world_id, w.world_type, w.creator_id, w.name, w.description, w.max_players, w.features, w.environment, w.difficulty, w.custom_settings, w.status, w.created_at, u.username";
            $sql .= " ORDER BY w.created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $worlds = [];
            while ($row = $stmt->fetch()) {
                $worlds[] = [
                    'world_id' => $row['world_id'],
                    'world_type' => $row['world_type'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'max_players' => (int)$row['max_players'],
                    'current_players' => (int)$row['current_players'],
                    'features' => json_decode($row['features'], true),
                    'environment' => $row['environment'],
                    'difficulty' => $row['difficulty'],
                    'creator_name' => $row['creator_name'],
                    'created_at' => $row['created_at'],
                    'available_slots' => (int)$row['max_players'] - (int)$row['current_players']
                ];
            }
            
            return $worlds;
            
        } catch (Exception $e) {
            error_log("Error getting active worlds: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get world players
     */
    public function getWorldPlayers($worldId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    s.user_id,
                    s.cat_id,
                    s.joined_at,
                    s.last_activity,
                    u.username,
                    c.name as cat_name,
                    c.breed as cat_breed,
                    c.personality as cat_personality
                FROM metaverse_sessions s
                JOIN users u ON s.user_id = u.id
                JOIN cats c ON s.cat_id = c.id
                WHERE s.world_id = ? AND s.status = 'active'
                ORDER BY s.joined_at ASC
            ");
            
            $stmt->execute([$worldId]);
            
            $players = [];
            while ($row = $stmt->fetch()) {
                $players[] = [
                    'user_id' => $row['user_id'],
                    'username' => $row['username'],
                    'cat_id' => $row['cat_id'],
                    'cat_name' => $row['cat_name'],
                    'cat_breed' => $row['cat_breed'],
                    'cat_personality' => json_decode($row['cat_personality'], true),
                    'joined_at' => $row['joined_at'],
                    'last_activity' => $row['last_activity'],
                    'online_status' => $this->getUserOnlineStatus($row['user_id'])
                ];
            }
            
            return $players;
            
        } catch (Exception $e) {
            error_log("Error getting world players: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Perform VR interaction
     */
    public function performVRInteraction($worldId, $userId, $interactionType, $targetData = []) {
        try {
            if (!isset(VR_INTERACTIONS[$interactionType])) {
                throw new Exception("Invalid interaction type: $interactionType");
            }
            
            // Check if user is in world
            if (!$this->isUserInWorld($userId, $worldId)) {
                throw new Exception('User not in world');
            }
            
            // Validate interaction based on type
            $this->validateVRInteraction($interactionType, $targetData);
            
            // Process interaction
            $result = $this->processVRInteraction($interactionType, $userId, $worldId, $targetData);
            
            // Log interaction
            $this->logVRInteraction($worldId, $userId, $interactionType, $targetData, $result);
            
            // Trigger webhook event
            triggerWebhookEvent('metaverse.vr_interaction', [
                'user_id' => $userId,
                'world_id' => $worldId,
                'interaction_type' => $interactionType,
                'target_data' => $targetData,
                'result' => $result
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error performing VR interaction: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create social VR space
     */
    public function createSocialVRSpace($worldId, $creatorId, $spaceData) {
        try {
            // Check if user is in world
            if (!$this->isUserInWorld($creatorId, $worldId)) {
                throw new Exception('User not in world');
            }
            
            // Validate space data
            $this->validateSocialVRSpace($spaceData);
            
            // Create space
            $stmt = $this->pdo->prepare("
                INSERT INTO metaverse_social_spaces 
                (world_id, creator_id, name, description, space_type, coordinates, 
                 max_occupants, privacy_settings, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $worldId,
                $creatorId,
                $spaceData['name'],
                $spaceData['description'],
                $spaceData['space_type'],
                json_encode($spaceData['coordinates']),
                $spaceData['max_occupants'],
                json_encode($spaceData['privacy_settings']),
                date('Y-m-d H:i:s')
            ]);
            
            $spaceId = $this->pdo->lastInsertId();
            
            // Log space creation
            logSecurityEvent('metaverse_social_space_created', [
                'creator_id' => $creatorId,
                'world_id' => $worldId,
                'space_id' => $spaceId,
                'space_data' => $spaceData
            ]);
            
            return [
                'space_id' => $spaceId,
                'world_id' => $worldId,
                'name' => $spaceData['name'],
                'description' => $spaceData['description'],
                'space_type' => $spaceData['space_type'],
                'coordinates' => $spaceData['coordinates'],
                'max_occupants' => $spaceData['max_occupants'],
                'privacy_settings' => $spaceData['privacy_settings'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            error_log("Error creating social VR space: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get metaverse statistics
     */
    public function getMetaverseStats() {
        try {
            $stats = [];
            
            // Total worlds
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM metaverse_worlds WHERE status = 'active'");
            $stmt->execute();
            $stats['total_worlds'] = $stmt->fetchColumn();
            
            // Total players online
            $stmt = $this->pdo->prepare("
                SELECT COUNT(DISTINCT user_id) 
                FROM metaverse_sessions 
                WHERE status = 'active' AND last_activity >= NOW() - INTERVAL '5 minutes'
            ");
            $stmt->execute();
            $stats['players_online'] = $stmt->fetchColumn();
            
            // World types distribution
            $stmt = $this->pdo->prepare("
                SELECT world_type, COUNT(*) as count
                FROM metaverse_worlds 
                WHERE status = 'active'
                GROUP BY world_type
            ");
            $stmt->execute();
            $stats['world_types'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Popular worlds
            $stmt = $this->pdo->prepare("
                SELECT 
                    w.name,
                    w.world_type,
                    COUNT(s.id) as player_count
                FROM metaverse_worlds w
                LEFT JOIN metaverse_sessions s ON w.world_id = s.world_id AND s.status = 'active'
                WHERE w.status = 'active'
                GROUP BY w.world_id, w.name, w.world_type
                ORDER BY player_count DESC
                LIMIT 5
            ");
            $stmt->execute();
            $stats['popular_worlds'] = $stmt->fetchAll();
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error getting metaverse stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Helper methods
     */
    private function canUserCreateWorld($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM metaverse_worlds 
                WHERE creator_id = ? AND status = 'active'
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn() < 3; // Max 3 worlds per user
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function generateWorldId() {
        return 'world_' . uniqid() . '_' . substr(md5(microtime()), 0, 8);
    }
    
    private function getWorldPlayerCount($worldId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM metaverse_sessions 
                WHERE world_id = ? AND status = 'active'
            ");
            $stmt->execute([$worldId]);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function isUserInWorld($userId, $worldId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM metaverse_sessions 
                WHERE user_id = ? AND world_id = ? AND status = 'active'
            ");
            $stmt->execute([$userId, $worldId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function getUserDefaultCat($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id FROM cats 
                WHERE user_id = ? AND active = 1 
                ORDER BY created_at ASC 
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function updateWorldPlayerCount($worldId) {
        // This would update a cached player count for performance
        // Implementation depends on caching strategy
    }
    
    private function getUserOnlineStatus($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM metaverse_sessions 
                WHERE user_id = ? AND status = 'active' 
                AND last_activity >= NOW() - INTERVAL '5 minutes'
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn() > 0 ? 'online' : 'offline';
        } catch (Exception $e) {
            return 'unknown';
        }
    }
    
    private function validateVRInteraction($interactionType, $targetData) {
        // Implement validation logic based on interaction type
        // This is a placeholder for actual validation
    }
    
    private function processVRInteraction($interactionType, $userId, $worldId, $targetData) {
        // Implement interaction processing logic
        // This is a placeholder for actual processing
        return [
            'success' => true,
            'interaction_type' => $interactionType,
            'result' => 'Interaction completed successfully'
        ];
    }
    
    private function logVRInteraction($worldId, $userId, $interactionType, $targetData, $result) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO metaverse_interactions 
                (world_id, user_id, interaction_type, target_data, result, created_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $worldId,
                $userId,
                $interactionType,
                json_encode($targetData),
                json_encode($result),
                date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Error logging VR interaction: " . $e->getMessage());
        }
    }
    
    private function validateSocialVRSpace($spaceData) {
        // Implement validation logic for social VR space
        // This is a placeholder for actual validation
    }
}

/**
 * Global metaverse system instance
 */
$globalMetaverseSystem = new MetaverseSystem();

/**
 * Metaverse wrapper functions
 */
function createMetaverseWorld($worldType, $creatorId, $customSettings = []) {
    global $globalMetaverseSystem;
    return $globalMetaverseSystem->createWorldInstance($worldType, $creatorId, $customSettings);
}

function joinMetaverseWorld($worldId, $userId, $catId = null) {
    global $globalMetaverseSystem;
    return $globalMetaverseSystem->joinWorld($worldId, $userId, $catId);
}

function leaveMetaverseWorld($worldId, $userId) {
    global $globalMetaverseSystem;
    return $globalMetaverseSystem->leaveWorld($worldId, $userId);
}

function getActiveMetaverseWorlds($filters = []) {
    global $globalMetaverseSystem;
    return $globalMetaverseSystem->getActiveWorlds($filters);
}

function getMetaverseWorldPlayers($worldId) {
    global $globalMetaverseSystem;
    return $globalMetaverseSystem->getWorldPlayers($worldId);
}

function performMetaverseVRInteraction($worldId, $userId, $interactionType, $targetData = []) {
    global $globalMetaverseSystem;
    return $globalMetaverseSystem->performVRInteraction($worldId, $userId, $interactionType, $targetData);
}

function createMetaverseSocialVRSpace($worldId, $creatorId, $spaceData) {
    global $globalMetaverseSystem;
    return $globalMetaverseSystem->createSocialVRSpace($worldId, $creatorId, $spaceData);
}

function getMetaverseStats() {
    global $globalMetaverseSystem;
    return $globalMetaverseSystem->getMetaverseStats();
}
?>
