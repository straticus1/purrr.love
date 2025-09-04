<?php
/**
 * 🤖 Purrr.love Metaverse AI Activity System
 * Autonomous cat behaviors and dynamic world events
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * AI Cat NPC System for autonomous world activity
 */
class MetaverseAIActivities {
    private $pdo;
    private $config;
    
    public function __construct() {
        $this->pdo = get_db();
        $this->config = [
            'npc_spawn_rate' => 0.3, // 30% chance per minute
            'event_frequency' => 900, // 15 minutes between events
            'max_npcs_per_world' => 20,
            'activity_boost_threshold' => 5, // Trigger events if < 5 players
            'autonomous_behaviors_enabled' => true
        ];
    }
    
    /**
     * Spawn AI cats in worlds with low activity
     */
    public function spawnAICatsInLowActivityWorlds() {
        try {
            $lowActivityWorlds = $this->getLowActivityWorlds();
            
            foreach ($lowActivityWorlds as $world) {
                $this->spawnAICatsInWorld($world['world_id'], $world['current_players']);
            }
            
        } catch (Exception $e) {
            error_log("Error spawning AI cats: " . $e->getMessage());
        }
    }
    
    /**
     * Create autonomous world events
     */
    public function triggerAutonomousEvents() {
        $activeWorlds = $this->getActiveWorldsForEvents();
        
        foreach ($activeWorlds as $world) {
            // Random chance to trigger event
            if (rand(1, 100) <= 30) { // 30% chance
                $this->triggerRandomWorldEvent($world['world_id'], $world['world_type']);
            }
        }
    }
    
    /**
     * Spawn AI cats in specific world
     */
    private function spawnAICatsInWorld($worldId, $currentPlayers) {
        $idealPlayerCount = 12; // Target player count
        $playersNeeded = max(0, $idealPlayerCount - $currentPlayers);
        $npcCount = min(8, ceil($playersNeeded * 0.6)); // Spawn 60% as NPCs
        
        for ($i = 0; $i < $npcCount; $i++) {
            $this->createAICatNPC($worldId);
        }
    }
    
    /**
     * Create AI cat NPC
     */
    private function createAICatNPC($worldId) {
        $aiCat = $this->generateAICatProfile();
        
        $stmt = $this->pdo->prepare("
            INSERT INTO metaverse_ai_npcs 
            (world_id, cat_name, cat_breed, personality_type, behavior_patterns, 
             activity_level, social_preferences, spawn_time, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $stmt->execute([
            $worldId,
            $aiCat['name'],
            $aiCat['breed'],
            $aiCat['personality'],
            json_encode($aiCat['behaviors']),
            $aiCat['activity_level'],
            json_encode($aiCat['social_preferences']),
            date('Y-m-d H:i:s')
        ]);
        
        $npcId = $this->pdo->lastInsertId();
        
        // Schedule initial behaviors
        $this->scheduleNPCBehaviors($npcId, $worldId, $aiCat);
        
        return $npcId;
    }
    
    /**
     * Generate random AI cat profile
     */
    private function generateAICatProfile() {
        $names = ['Luna', 'Whiskers', 'Shadow', 'Mittens', 'Oreo', 'Simba', 'Nala', 'Felix', 'Cleo', 'Mochi'];
        $breeds = ['persian', 'siamese', 'maine_coon', 'ragdoll', 'british_shorthair', 'bengal'];
        $personalities = ['playful', 'curious', 'lazy', 'social_butterfly', 'aloof', 'territorial'];
        
        $personality = $personalities[array_rand($personalities)];
        
        return [
            'name' => $names[array_rand($names)] . '_AI',
            'breed' => $breeds[array_rand($breeds)],
            'personality' => $personality,
            'activity_level' => rand(60, 95),
            'behaviors' => $this->generateBehaviorPatterns($personality),
            'social_preferences' => $this->generateSocialPreferences($personality)
        ];
    }
    
    /**
     * Generate behavior patterns based on personality
     */
    private function generateBehaviorPatterns($personality) {
        $patterns = [
            'playful' => [
                'chase_objects' => 0.8,
                'explore_areas' => 0.7,
                'interact_with_players' => 0.9,
                'play_with_toys' => 0.9,
                'rest_frequency' => 0.3
            ],
            'curious' => [
                'explore_areas' => 0.9,
                'investigate_new_players' => 0.8,
                'interact_with_objects' => 0.7,
                'follow_players' => 0.6,
                'rest_frequency' => 0.4
            ],
            'lazy' => [
                'find_sunny_spots' => 0.8,
                'sleep_in_cozy_areas' => 0.9,
                'slow_movements' => 0.8,
                'minimal_play' => 0.3,
                'rest_frequency' => 0.8
            ],
            'social_butterfly' => [
                'greet_new_players' => 0.9,
                'follow_groups' => 0.8,
                'perform_for_attention' => 0.7,
                'seek_petting' => 0.8,
                'rest_frequency' => 0.2
            ]
        ];
        
        return $patterns[$personality] ?? $patterns['curious'];
    }
    
    /**
     * Trigger random world events
     */
    private function triggerRandomWorldEvent($worldId, $worldType) {
        $events = $this->getWorldTypeEvents($worldType);
        $event = $events[array_rand($events)];
        
        $this->executeWorldEvent($worldId, $event);
    }
    
    /**
     * Get events for specific world type
     */
    private function getWorldTypeEvents($worldType) {
        $worldEvents = [
            'cat_paradise' => [
                'fish_spawn' => 'A school of fish appears in the lagoon!',
                'butterfly_swarm' => 'Colorful butterflies fill the air!',
                'treasure_chest' => 'A mysterious treasure chest washes ashore!',
                'rainbow_bridge' => 'A rainbow bridge appears leading to a secret area!',
                'catnip_growth' => 'Wild catnip begins growing in the garden!'
            ],
            'mystic_forest' => [
                'magical_portal' => 'A glowing portal opens to a hidden grove!',
                'fairy_lights' => 'Magical fairy lights dance through the trees!',
                'ancient_rune' => 'Ancient runes glow on a mysterious stone!',
                'spirit_cat' => 'A mystical spirit cat appears and offers wisdom!',
                'enchanted_stream' => 'The stream begins to glow with magical energy!'
            ],
            'cosmic_city' => [
                'meteor_shower' => 'A spectacular meteor shower lights up the sky!',
                'hover_race' => 'A hover-board racing tournament begins!',
                'tech_expo' => 'A technology expo opens with new gadgets!',
                'space_storm' => 'Beautiful aurora effects dance across the dome!',
                'robot_parade' => 'Friendly robots parade through the plaza!'
            ],
            'winter_wonderland' => [
                'aurora_borealis' => 'The northern lights paint the sky!',
                'snowball_fight' => 'A friendly snowball fight breaks out!',
                'ice_fishing' => 'The lake opens perfect ice fishing spots!',
                'hot_cocoa_stand' => 'A cozy hot cocoa stand appears!',
                'avalanche_safe' => 'A controlled avalanche creates new slopes!'
            ],
            'desert_oasis' => [
                'sandstorm_clearing' => 'A sandstorm clears, revealing hidden ruins!',
                'mirage_city' => 'A magnificent mirage city appears on the horizon!',
                'oasis_bloom' => 'The oasis blooms with exotic flowers!',
                'treasure_map' => 'Ancient treasure maps are discovered!',
                'desert_festival' => 'A desert festival begins with music and dancing!'
            ]
        ];
        
        return $worldEvents[$worldType] ?? $worldEvents['cat_paradise'];
    }
    
    /**
     * Execute world event
     */
    private function executeWorldEvent($worldId, $event) {
        // Log the event
        $stmt = $this->pdo->prepare("
            INSERT INTO metaverse_world_events 
            (world_id, event_type, event_data, triggered_at, status)
            VALUES (?, ?, ?, ?, 'active')
        ");
        
        $eventData = [
            'description' => $event,
            'duration_minutes' => rand(15, 60),
            'participants_affected' => 'all',
            'rewards' => $this->generateEventRewards()
        ];
        
        $stmt->execute([
            $worldId,
            array_search($event, $this->getWorldTypeEvents('cat_paradise')), // Get event key
            json_encode($eventData),
            date('Y-m-d H:i:s')
        ]);
        
        // Notify all players in the world
        $this->notifyWorldPlayers($worldId, $event, $eventData);
    }
    
    /**
     * Create dynamic daily quests
     */
    public function generateDailyQuests() {
        $questTemplates = [
            'social' => [
                'Meet 3 new cats in the metaverse',
                'Spend 30 minutes in social areas',
                'Participate in a group activity',
                'Help a new player explore their first world'
            ],
            'exploration' => [
                'Visit 5 different areas in any world',
                'Discover 3 hidden locations',
                'Collect 10 virtual items',
                'Explore a world for 1 hour'
            ],
            'interaction' => [
                'Pet 20 different cats',
                'Play with 10 different toys',
                'Complete 5 training sessions',
                'Groom 3 cats to perfect cleanliness'
            ],
            'competitive' => [
                'Win a race against 3 other cats',
                'Score in the top 5 of any mini-game',
                'Complete an adventure quest',
                'Earn 100 experience points'
            ]
        ];
        
        foreach ($questTemplates as $category => $quests) {
            $this->createDailyQuestsForCategory($category, $quests);
        }
    }
    
    /**
     * Schedule NPC behaviors
     */
    private function scheduleNPCBehaviors($npcId, $worldId, $aiCat) {
        $behaviors = [
            'wander_around' => ['frequency' => 120, 'duration' => 300], // Every 2 min, 5 min duration
            'interact_with_objects' => ['frequency' => 180, 'duration' => 60],
            'socialize_with_players' => ['frequency' => 300, 'duration' => 120],
            'perform_tricks' => ['frequency' => 600, 'duration' => 30],
            'rest_in_sunny_spot' => ['frequency' => 900, 'duration' => 600]
        ];
        
        foreach ($behaviors as $behavior => $timing) {
            $this->scheduleNPCBehavior($npcId, $behavior, $timing);
        }
    }
    
    /**
     * Create mini-games and activities
     */
    public function createMetaverseMiniGames() {
        $miniGames = [
            'cat_racing' => [
                'name' => 'Virtual Cat Racing',
                'max_participants' => 8,
                'duration_minutes' => 5,
                'rewards' => ['experience', 'virtual_currency', 'trophies']
            ],
            'treasure_hunt' => [
                'name' => 'Treasure Hunt Adventure',
                'max_participants' => 12,
                'duration_minutes' => 15,
                'rewards' => ['rare_items', 'experience', 'achievements']
            ],
            'dance_party' => [
                'name' => 'Cat Dance Party',
                'max_participants' => 20,
                'duration_minutes' => 10,
                'rewards' => ['happiness_boost', 'social_points', 'dance_moves']
            ],
            'puzzle_solving' => [
                'name' => 'Mystery Puzzle Challenge',
                'max_participants' => 6,
                'duration_minutes' => 8,
                'rewards' => ['intelligence_boost', 'puzzle_pieces', 'wisdom_points']
            ]
        ];
        
        return $miniGames;
    }
    
    /**
     * Get worlds with low activity
     */
    private function getLowActivityWorlds() {
        $stmt = $this->pdo->prepare("
            SELECT 
                w.world_id,
                w.world_type,
                w.max_players,
                COUNT(s.id) as current_players
            FROM metaverse_worlds w
            LEFT JOIN metaverse_sessions s ON w.world_id = s.world_id AND s.status = 'active'
            WHERE w.status = 'active'
            GROUP BY w.world_id, w.world_type, w.max_players
            HAVING COUNT(s.id) < ?
            ORDER BY current_players ASC
        ");
        
        $stmt->execute([$this->config['activity_boost_threshold']]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create social activities
     */
    public function createSocialActivities() {
        return [
            'group_grooming_session' => [
                'name' => 'Community Grooming Circle',
                'description' => 'Cats help groom each other in a relaxing circle',
                'min_participants' => 3,
                'max_participants' => 8,
                'duration_minutes' => 10,
                'benefits' => ['cleanliness_boost', 'social_bonding', 'relaxation']
            ],
            'storytelling_circle' => [
                'name' => 'Cat Tales Storytelling',
                'description' => 'Cats share adventures and stories',
                'min_participants' => 4,
                'max_participants' => 12,
                'duration_minutes' => 15,
                'benefits' => ['wisdom_points', 'social_connection', 'inspiration']
            ],
            'collaborative_building' => [
                'name' => 'Build Together Project',
                'description' => 'Work together to build something awesome',
                'min_participants' => 2,
                'max_participants' => 10,
                'duration_minutes' => 20,
                'benefits' => ['creativity_points', 'teamwork_achievement', 'unique_items']
            ],
            'talent_show' => [
                'name' => 'Cat Talent Showcase',
                'description' => 'Show off your cat\'s special abilities',
                'min_participants' => 3,
                'max_participants' => 15,
                'duration_minutes' => 12,
                'benefits' => ['confidence_boost', 'applause_points', 'fame']
            ]
        ];
    }
    
    /**
     * Generate event rewards
     */
    private function generateEventRewards() {
        return [
            'virtual_currency' => rand(10, 50),
            'experience_points' => rand(25, 100),
            'rare_items' => rand(1, 3),
            'achievement_progress' => rand(5, 15),
            'happiness_boost' => rand(10, 25)
        ];
    }
    
    /**
     * Get active worlds for events
     */
    private function getActiveWorldsForEvents() {
        $stmt = $this->pdo->prepare("
            SELECT world_id, world_type, name 
            FROM metaverse_worlds 
            WHERE status = 'active'
            AND updated_at >= NOW() - INTERVAL '1 hour'
        ");
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Notify players of world events
     */
    private function notifyWorldPlayers($worldId, $event, $eventData) {
        // This would integrate with your notification system
        $notification = [
            'type' => 'world_event',
            'world_id' => $worldId,
            'event' => $event,
            'data' => $eventData,
            'timestamp' => time()
        ];
        
        // Log notification for webhook delivery
        $this->logWorldEventNotification($worldId, $notification);
    }
    
    /**
     * Log world event notifications
     */
    private function logWorldEventNotification($worldId, $notification) {
        $stmt = $this->pdo->prepare("
            INSERT INTO metaverse_notifications 
            (world_id, notification_type, notification_data, created_at)
            VALUES (?, 'world_event', ?, ?)
        ");
        
        $stmt->execute([
            $worldId,
            json_encode($notification),
            date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Dynamic content generation system
 */
class MetaverseDynamicContent {
    private $pdo;
    
    public function __construct() {
        $this->pdo = get_db();
    }
    
    /**
     * Generate seasonal content
     */
    public function updateSeasonalContent() {
        $season = $this->getCurrentSeason();
        $seasonalUpdates = $this->getSeasonalUpdates($season);
        
        foreach ($seasonalUpdates as $worldType => $updates) {
            $this->applySeasonalUpdates($worldType, $updates);
        }
    }
    
    /**
     * Create time-based world variations
     */
    public function updateTimeBasedContent() {
        $timeOfDay = $this->getTimeOfDay();
        $timeBasedChanges = $this->getTimeBasedChanges($timeOfDay);
        
        $this->applyTimeBasedChanges($timeBasedChanges);
    }
    
    /**
     * Generate weather effects
     */
    public function updateWeatherEffects() {
        $weatherTypes = ['sunny', 'cloudy', 'rainy', 'stormy', 'foggy', 'snowy'];
        $activeWorlds = $this->getActiveWorlds();
        
        foreach ($activeWorlds as $world) {
            $weather = $weatherTypes[array_rand($weatherTypes)];
            $this->applyWeatherToWorld($world['world_id'], $weather);
        }
    }
    
    /**
     * Create special limited-time areas
     */
    public function createLimitedTimeAreas() {
        $specialAreas = [
            'rainbow_meadow' => [
                'name' => 'Rainbow Meadow',
                'duration_hours' => 2,
                'special_rewards' => ['rainbow_collar', 'spectrum_points'],
                'rarity' => 'rare'
            ],
            'starlight_observatory' => [
                'name' => 'Starlight Observatory',
                'duration_hours' => 4,
                'special_rewards' => ['star_map', 'cosmic_wisdom'],
                'rarity' => 'epic'
            ],
            'crystal_cave' => [
                'name' => 'Singing Crystal Cave',
                'duration_hours' => 1,
                'special_rewards' => ['crystal_collar', 'harmony_points'],
                'rarity' => 'legendary'
            ]
        ];
        
        // Random chance to spawn special area
        if (rand(1, 100) <= 15) { // 15% chance
            $area = $specialAreas[array_rand($specialAreas)];
            $this->spawnSpecialArea($area);
        }
    }
    
    /**
     * Get current season
     */
    private function getCurrentSeason() {
        $month = (int)date('n');
        if ($month >= 3 && $month <= 5) return 'spring';
        if ($month >= 6 && $month <= 8) return 'summer';
        if ($month >= 9 && $month <= 11) return 'autumn';
        return 'winter';
    }
    
    /**
     * Get time of day category
     */
    private function getTimeOfDay() {
        $hour = (int)date('G');
        if ($hour >= 6 && $hour < 12) return 'morning';
        if ($hour >= 12 && $hour < 17) return 'afternoon';
        if ($hour >= 17 && $hour < 21) return 'evening';
        return 'night';
    }
}

/**
 * Engagement monitoring and auto-boosting
 */
class MetaverseEngagementBooster {
    private $pdo;
    private $aiActivities;
    private $dynamicContent;
    
    public function __construct() {
        $this->pdo = get_db();
        $this->aiActivities = new MetaverseAIActivities();
        $this->dynamicContent = new MetaverseDynamicContent();
    }
    
    /**
     * Monitor and boost world engagement
     */
    public function monitorAndBoostEngagement() {
        $engagementMetrics = $this->getEngagementMetrics();
        
        foreach ($engagementMetrics as $worldId => $metrics) {
            if ($metrics['engagement_score'] < 0.4) { // Low engagement threshold
                $this->boostWorldActivity($worldId, $metrics);
            }
        }
    }
    
    /**
     * Boost activity in specific world
     */
    private function boostWorldActivity($worldId, $metrics) {
        $boostStrategies = [
            'spawn_ai_cats' => 0.7,     // 70% chance
            'trigger_event' => 0.8,     // 80% chance
            'start_mini_game' => 0.6,   // 60% chance
            'create_special_area' => 0.3, // 30% chance
            'announce_bonus_rewards' => 0.9 // 90% chance
        ];
        
        foreach ($boostStrategies as $strategy => $probability) {
            if (rand(1, 100) <= ($probability * 100)) {
                $this->executeBoostStrategy($worldId, $strategy);
            }
        }
    }
    
    /**
     * Get engagement metrics for all worlds
     */
    private function getEngagementMetrics() {
        $stmt = $this->pdo->prepare("
            SELECT 
                w.world_id,
                w.name,
                COUNT(s.id) as current_players,
                w.max_players,
                AVG(TIMESTAMPDIFF(MINUTE, s.joined_at, s.last_activity)) as avg_session_minutes,
                COUNT(i.id) as recent_interactions
            FROM metaverse_worlds w
            LEFT JOIN metaverse_sessions s ON w.world_id = s.world_id AND s.status = 'active'
            LEFT JOIN metaverse_interactions i ON s.world_id = i.world_id 
                AND i.created_at >= NOW() - INTERVAL '30 minutes'
            WHERE w.status = 'active'
            GROUP BY w.world_id, w.name, w.max_players
        ");
        
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $metrics = [];
        foreach ($results as $row) {
            $playerRatio = $row['current_players'] / $row['max_players'];
            $interactionRate = $row['recent_interactions'] / max(1, $row['current_players']);
            $sessionQuality = min(1.0, $row['avg_session_minutes'] / 30); // 30 min ideal
            
            $metrics[$row['world_id']] = [
                'engagement_score' => ($playerRatio + $interactionRate + $sessionQuality) / 3,
                'current_players' => $row['current_players'],
                'interaction_rate' => $interactionRate,
                'session_quality' => $sessionQuality
            ];
        }
        
        return $metrics;
    }
}

/**
 * Global instances
 */
$globalMetaverseAI = new MetaverseAIActivities();
$globalMetaverseDynamicContent = new MetaverseDynamicContent();
$globalMetaverseEngagementBooster = new MetaverseEngagementBooster();

/**
 * Wrapper functions for easy access
 */
function spawnAICatsInLowActivityWorlds() {
    global $globalMetaverseAI;
    return $globalMetaverseAI->spawnAICatsInLowActivityWorlds();
}

function triggerAutonomousMetaverseEvents() {
    global $globalMetaverseAI;
    return $globalMetaverseAI->triggerAutonomousEvents();
}

function updateSeasonalMetaverseContent() {
    global $globalMetaverseDynamicContent;
    return $globalMetaverseDynamicContent->updateSeasonalContent();
}

function monitorAndBoostMetaverseEngagement() {
    global $globalMetaverseEngagementBooster;
    return $globalMetaverseEngagementBooster->monitorAndBoostEngagement();
}

function generateDailyMetaverseQuests() {
    global $globalMetaverseAI;
    return $globalMetaverseAI->generateDailyQuests();
}
?>
