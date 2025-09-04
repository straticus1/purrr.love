<?php
/**
 * 🏆 Purrr.love Metaverse Gamification System
 * Achievements, competitions, leaderboards, and engagement mechanics
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Metaverse Gamification System
 */
class MetaverseGamification {
    private $pdo;
    private $config;
    
    public function __construct() {
        $this->pdo = get_db();
        $this->config = [
            'daily_quest_count' => 3,
            'weekly_challenge_count' => 1,
            'achievement_categories' => ['social', 'exploration', 'competition', 'collection', 'special'],
            'leaderboard_update_interval' => 300, // 5 minutes
            'reward_multiplier' => 1.0
        ];
    }
    
    /**
     * Achievement System
     */
    public function createAchievementSystem() {
        $achievements = [
            // Social Achievements
            'social_butterfly' => [
                'name' => 'Social Butterfly',
                'description' => 'Meet 50 different cats in the metaverse',
                'category' => 'social',
                'target_value' => 50,
                'rewards' => ['title' => 'Social Butterfly', 'currency' => 500, 'xp' => 1000],
                'icon' => '🦋'
            ],
            'party_host' => [
                'name' => 'Ultimate Party Host',
                'description' => 'Host 10 successful social events',
                'category' => 'social',
                'target_value' => 10,
                'rewards' => ['title' => 'Party Host', 'special_emote' => 'party_hat', 'currency' => 750],
                'icon' => '🎉'
            ],
            
            // Exploration Achievements
            'world_explorer' => [
                'name' => 'World Explorer',
                'description' => 'Visit all 5 metaverse world types',
                'category' => 'exploration',
                'target_value' => 5,
                'rewards' => ['title' => 'Explorer', 'map_item' => 'cosmic_compass', 'xp' => 800],
                'icon' => '🗺️'
            ],
            'hidden_secrets' => [
                'name' => 'Keeper of Secrets',
                'description' => 'Discover 25 hidden locations',
                'category' => 'exploration',
                'target_value' => 25,
                'rewards' => ['title' => 'Secret Keeper', 'ability' => 'treasure_sense', 'currency' => 1000],
                'icon' => '🔍'
            ],
            
            // Competition Achievements
            'race_champion' => [
                'name' => 'Racing Champion',
                'description' => 'Win 20 racing competitions',
                'category' => 'competition',
                'target_value' => 20,
                'rewards' => ['title' => 'Speed Demon', 'trophy' => 'golden_speedster', 'special_skin' => 'racing_stripes'],
                'icon' => '🏁'
            ],
            'tournament_master' => [
                'name' => 'Tournament Master',
                'description' => 'Place in top 3 of 15 tournaments',
                'category' => 'competition',
                'target_value' => 15,
                'rewards' => ['title' => 'Tournament Master', 'crown' => 'champion_crown', 'currency' => 2000],
                'icon' => '👑'
            ],
            
            // Collection Achievements
            'item_collector' => [
                'name' => 'Master Collector',
                'description' => 'Collect 100 unique virtual items',
                'category' => 'collection',
                'target_value' => 100,
                'rewards' => ['title' => 'Collector', 'bag' => 'infinite_storage', 'xp' => 1500],
                'icon' => '💎'
            ],
            
            // Special Achievements
            'time_traveler' => [
                'name' => 'Time Traveler',
                'description' => 'Spend 100 hours in the metaverse',
                'category' => 'special',
                'target_value' => 6000, // minutes
                'rewards' => ['title' => 'Time Traveler', 'special_effect' => 'temporal_aura', 'currency' => 5000],
                'icon' => '⏰'
            ],
            'world_builder' => [
                'name' => 'Master Architect',
                'description' => 'Create 5 custom worlds with 4+ star ratings',
                'category' => 'special',
                'target_value' => 5,
                'rewards' => ['title' => 'Architect', 'tool' => 'master_builder_kit', 'unlimited_builds' => true],
                'icon' => '🏗️'
            ]
        ];
        
        return $achievements;
    }
    
    /**
     * Competition System
     */
    public function createCompetitionSystem() {
        $competitions = [
            'daily_races' => [
                'name' => 'Daily Speed Races',
                'type' => 'racing',
                'frequency' => 'daily',
                'duration_minutes' => 5,
                'max_participants' => 12,
                'entry_fee' => 0,
                'prize_pool' => ['currency' => 300, 'xp' => 150, 'trophies' => ['gold', 'silver', 'bronze']],
                'schedule' => ['10:00', '14:00', '18:00', '22:00'] // UTC times
            ],
            'weekly_treasure_hunt' => [
                'name' => 'Weekly Treasure Hunt',
                'type' => 'exploration',
                'frequency' => 'weekly',
                'duration_minutes' => 60,
                'max_participants' => 30,
                'entry_fee' => 50,
                'prize_pool' => ['currency' => 2000, 'rare_items' => 5, 'exclusive_title' => 'Treasure Hunter'],
                'schedule' => ['saturday_15:00']
            ],
            'monthly_tournament' => [
                'name' => 'Grand Championship',
                'type' => 'multi_event',
                'frequency' => 'monthly',
                'duration_minutes' => 120,
                'max_participants' => 50,
                'entry_fee' => 200,
                'prize_pool' => ['currency' => 10000, 'legendary_items' => 3, 'hall_of_fame' => true],
                'schedule' => ['last_saturday_19:00']
            ],
            'hourly_mini_games' => [
                'name' => 'Hourly Fun Challenges',
                'type' => 'mini_game',
                'frequency' => 'hourly',
                'duration_minutes' => 8,
                'max_participants' => 8,
                'entry_fee' => 0,
                'prize_pool' => ['currency' => 75, 'xp' => 50, 'fun_tokens' => 10],
                'auto_start' => true
            ]
        ];
        
        return $competitions;
    }
    
    /**
     * Daily Quest System
     */
    public function generateTodaysDailyQuests($userId) {
        $questPool = [
            'social_quests' => [
                [
                    'title' => 'Make New Friends',
                    'description' => 'Meet and interact with 3 new cats',
                    'target_value' => 3,
                    'progress_type' => 'count',
                    'rewards' => ['xp' => 100, 'currency' => 50, 'social_points' => 25]
                ],
                [
                    'title' => 'Group Activity Champion',
                    'description' => 'Participate in 2 group activities',
                    'target_value' => 2,
                    'progress_type' => 'count',
                    'rewards' => ['xp' => 150, 'currency' => 75, 'team_spirit_badge' => 1]
                ],
                [
                    'title' => 'Social Hub Explorer',
                    'description' => 'Spend 45 minutes in social areas',
                    'target_value' => 45,
                    'progress_type' => 'time_minutes',
                    'rewards' => ['xp' => 200, 'currency' => 100, 'social_energy' => 50]
                ]
            ],
            'exploration_quests' => [
                [
                    'title' => 'Curious Explorer',
                    'description' => 'Visit 4 different world areas',
                    'target_value' => 4,
                    'progress_type' => 'unique_locations',
                    'rewards' => ['xp' => 120, 'currency' => 60, 'explorer_badge' => 1]
                ],
                [
                    'title' => 'Treasure Seeker',
                    'description' => 'Find 8 hidden items in any world',
                    'target_value' => 8,
                    'progress_type' => 'items_found',
                    'rewards' => ['xp' => 180, 'currency' => 90, 'treasure_map_piece' => 1]
                ]
            ],
            'interaction_quests' => [
                [
                    'title' => 'Caring Guardian',
                    'description' => 'Pet and groom cats 15 times',
                    'target_value' => 15,
                    'progress_type' => 'care_actions',
                    'rewards' => ['xp' => 100, 'currency' => 50, 'nurturing_points' => 30]
                ],
                [
                    'title' => 'Playful Spirit',
                    'description' => 'Play games for 30 minutes total',
                    'target_value' => 30,
                    'progress_type' => 'play_time_minutes',
                    'rewards' => ['xp' => 140, 'currency' => 70, 'play_tokens' => 20]
                ]
            ],
            'competitive_quests' => [
                [
                    'title' => 'Rising Star',
                    'description' => 'Finish in top 5 of any 2 competitions',
                    'target_value' => 2,
                    'progress_type' => 'top_placements',
                    'rewards' => ['xp' => 250, 'currency' => 125, 'star_badge' => 1]
                ]
            ]
        ];
        
        // Select random quests from each category
        $selectedQuests = [];
        foreach ($questPool as $category => $quests) {
            $selectedQuests[] = $quests[array_rand($quests)];
        }
        
        // Add one random quest from any category
        $allQuests = array_merge(...array_values($questPool));
        $selectedQuests[] = $allQuests[array_rand($allQuests)];
        
        // Store daily quests for user
        $this->storeDailyQuestsForUser($userId, $selectedQuests);
        
        return $selectedQuests;
    }
    
    /**
     * Leaderboard System
     */
    public function createLeaderboards() {
        $leaderboards = [
            'weekly_active_players' => [
                'name' => 'Most Active This Week',
                'description' => 'Players who spent the most time in metaverse worlds',
                'update_frequency' => 'daily',
                'reset_frequency' => 'weekly',
                'metric' => 'total_metaverse_time',
                'reward_top_3' => ['crown_effect', 'weekly_champion_badge', 'bonus_currency']
            ],
            'social_champions' => [
                'name' => 'Social Champions',
                'description' => 'Players with the most social interactions',
                'update_frequency' => 'hourly',
                'reset_frequency' => 'monthly',
                'metric' => 'social_interaction_count',
                'reward_top_5' => ['social_crown', 'friend_multiplier_boost', 'special_emotes']
            ],
            'competition_winners' => [
                'name' => 'Competition Champions',
                'description' => 'Players with the most competition victories',
                'update_frequency' => 'real_time',
                'reset_frequency' => 'monthly',
                'metric' => 'competition_wins',
                'reward_top_10' => ['champion_title', 'golden_trophy', 'winner_aura']
            ],
            'world_builders' => [
                'name' => 'Master Builders',
                'description' => 'Creators of the highest-rated custom worlds',
                'update_frequency' => 'daily',
                'reset_frequency' => 'quarterly',
                'metric' => 'avg_world_rating',
                'reward_top_5' => ['architect_tools', 'builder_crown', 'featured_world_slot']
            ]
        ];
        
        return $leaderboards;
    }
    
    /**
     * Progress Tracking System
     */
    public function trackUserProgress($userId, $action, $data = []) {
        $progressUpdates = [];
        
        switch ($action) {
            case 'world_join':
                $progressUpdates[] = ['type' => 'worlds_visited', 'value' => 1];
                $progressUpdates[] = ['type' => 'metaverse_time_start', 'value' => time()];
                break;
                
            case 'world_leave':
                if (isset($data['session_duration'])) {
                    $progressUpdates[] = ['type' => 'total_metaverse_time', 'value' => $data['session_duration']];
                }
                break;
                
            case 'social_interaction':
                $progressUpdates[] = ['type' => 'social_interactions', 'value' => 1];
                if (isset($data['new_cat'])) {
                    $progressUpdates[] = ['type' => 'unique_cats_met', 'value' => 1];
                }
                break;
                
            case 'competition_result':
                $progressUpdates[] = ['type' => 'competitions_entered', 'value' => 1];
                if ($data['placement'] <= 3) {
                    $progressUpdates[] = ['type' => 'top_3_finishes', 'value' => 1];
                }
                if ($data['placement'] == 1) {
                    $progressUpdates[] = ['type' => 'competition_wins', 'value' => 1];
                }
                break;
                
            case 'item_collect':
                $progressUpdates[] = ['type' => 'items_collected', 'value' => 1];
                if (isset($data['unique_item'])) {
                    $progressUpdates[] = ['type' => 'unique_items', 'value' => 1];
                }
                break;
                
            case 'quest_complete':
                $progressUpdates[] = ['type' => 'quests_completed', 'value' => 1];
                $progressUpdates[] = ['type' => 'quest_xp', 'value' => $data['xp'] ?? 0];
                break;
        }
        
        // Apply progress updates
        foreach ($progressUpdates as $update) {
            $this->updateUserProgress($userId, $update['type'], $update['value']);
        }
        
        // Check for achievement unlocks
        $this->checkAchievementProgress($userId);
        
        // Update leaderboards
        $this->updateLeaderboards($userId);
    }
    
    /**
     * Weekly Challenge System
     */
    public function createWeeklyChallenges() {
        $challenges = [
            'metaverse_marathon' => [
                'name' => 'Metaverse Marathon',
                'description' => 'Spend 20 hours in metaverse worlds this week',
                'type' => 'endurance',
                'target_value' => 1200, // minutes
                'difficulty' => 'hard',
                'rewards' => [
                    'completion' => ['currency' => 2000, 'xp' => 5000, 'marathon_badge' => 1],
                    'milestones' => [
                        '25%' => ['currency' => 200, 'xp' => 500],
                        '50%' => ['currency' => 500, 'xp' => 1000],
                        '75%' => ['currency' => 1000, 'xp' => 2000]
                    ]
                ]
            ],
            'social_network' => [
                'name' => 'Social Network Challenge',
                'description' => 'Make 25 new friends and host 3 group activities',
                'type' => 'social',
                'targets' => ['new_friends' => 25, 'hosted_events' => 3],
                'difficulty' => 'medium',
                'rewards' => [
                    'completion' => ['title' => 'Social Catalyst', 'special_emote_pack' => 1, 'friend_boost' => '2x'],
                    'bonus_rewards' => ['exclusive_party_decorations', 'vip_social_status']
                ]
            ],
            'master_of_worlds' => [
                'name' => 'Master of All Worlds',
                'description' => 'Complete specific objectives in each world type',
                'type' => 'exploration',
                'targets' => [
                    'cat_paradise' => 'catch_10_fish',
                    'mystic_forest' => 'solve_3_magic_puzzles',
                    'cosmic_city' => 'win_hover_race',
                    'winter_wonderland' => 'build_snowcat',
                    'desert_oasis' => 'find_ancient_artifact'
                ],
                'difficulty' => 'expert',
                'rewards' => [
                    'completion' => ['title' => 'World Master', 'legendary_item' => 'worldwalker_staff', 'currency' => 10000],
                    'world_mastery_effects' => true
                ]
            ]
        ];
        
        return $challenges;
    }
    
    /**
     * Live Event System
     */
    public function scheduleLiveEvents() {
        $events = [
            'flash_mob_dance' => [
                'name' => 'Flash Mob Dance Party',
                'type' => 'spontaneous',
                'trigger' => 'player_threshold', // When 15+ players online
                'duration_minutes' => 10,
                'rewards' => ['dance_moves', 'party_points', 'group_selfie'],
                'announcement_time' => 300 // 5 minutes notice
            ],
            'treasure_storm' => [
                'name' => 'Treasure Storm Event',
                'type' => 'scheduled',
                'frequency' => 'daily',
                'times' => ['12:00', '20:00'], // UTC
                'duration_minutes' => 15,
                'rewards' => ['rare_treasures', 'storm_badges', 'weather_resistance'],
                'world_effects' => ['treasure_rain', 'lightning_effects']
            ],
            'mystery_visitor' => [
                'name' => 'Mysterious Visitor Appears',
                'type' => 'random',
                'probability' => 0.1, // 10% chance per hour
                'duration_minutes' => 30,
                'rewards' => ['ancient_wisdom', 'mysterious_gifts', 'visitor_blessing'],
                'special_interactions' => true
            ],
            'double_xp_hour' => [
                'name' => 'Double XP Happy Hour',
                'type' => 'scheduled',
                'frequency' => 'daily',
                'times' => ['17:00', '21:00'], // Peak hours
                'duration_minutes' => 60,
                'effects' => ['2x_experience', 'bonus_currency', 'faster_skill_progress']
            ]
        ];
        
        return $events;
    }
    
    /**
     * Reward System
     */
    public function createRewardSystem() {
        $rewardTypes = [
            'currency' => [
                'purrr_coins' => 'Primary virtual currency',
                'gem_stones' => 'Premium currency for special items',
                'star_dust' => 'Rare currency from achievements'
            ],
            'items' => [
                'toys' => ['laser_pointer_pro', 'magical_feather_wand', 'cosmic_ball'],
                'accessories' => ['rainbow_collar', 'star_crown', 'adventure_cape'],
                'decorations' => ['floating_castle', 'crystal_garden', 'disco_ball'],
                'tools' => ['world_builder_kit', 'treasure_detector', 'friendship_compass']
            ],
            'abilities' => [
                'movement' => ['double_jump', 'speed_boost', 'wall_climb'],
                'social' => ['charm_aura', 'friendship_magnet', 'party_starter'],
                'exploration' => ['treasure_sense', 'hidden_door_finder', 'map_revealer']
            ],
            'cosmetics' => [
                'skins' => ['galactic_fur', 'rainbow_stripes', 'shadow_phantom'],
                'effects' => ['sparkle_trail', 'floating_hearts', 'cosmic_aura'],
                'emotes' => ['victory_dance', 'happy_spin', 'friendship_hug']
            ]
        ];
        
        return $rewardTypes;
    }
    
    /**
     * Social Features Enhancement
     */
    public function createSocialFeatures() {
        $socialFeatures = [
            'friend_system' => [
                'max_friends' => 200,
                'friend_benefits' => ['shared_worlds', 'group_discounts', 'buddy_bonuses'],
                'friend_activities' => ['duo_quests', 'friend_races', 'collaborative_building']
            ],
            'guilds_clans' => [
                'max_members' => 50,
                'guild_benefits' => ['shared_resources', 'guild_quests', 'territory_control'],
                'guild_activities' => ['guild_wars', 'group_expeditions', 'clan_tournaments']
            ],
            'mentorship_program' => [
                'mentor_benefits' => ['teaching_rewards', 'wisdom_points', 'mentor_titles'],
                'mentee_benefits' => ['learning_boosts', 'guided_tours', 'starter_packs'],
                'activities' => ['guided_exploration', 'skill_training', 'world_introduction']
            ],
            'community_events' => [
                'cat_shows' => 'Showcase your cats in themed competitions',
                'build_contests' => 'Community votes on best custom worlds',
                'photo_contests' => 'VR photography competitions',
                'storytelling_nights' => 'Share adventures and stories'
            ]
        ];
        
        return $socialFeatures;
    }
    
    /**
     * Store daily quests for user
     */
    private function storeDailyQuestsForUser($userId, $quests) {\n        try {\n            // Clear existing daily quests\n            $stmt = $this->pdo->prepare(\"\n                DELETE FROM user_daily_quests \n                WHERE user_id = ? AND quest_date = CURDATE()\n            \");\n            $stmt->execute([$userId]);\n            \n            // Insert new daily quests\n            foreach ($quests as $quest) {\n                $stmt = $this->pdo->prepare(\"\n                    INSERT INTO user_daily_quests \n                    (user_id, quest_title, quest_description, target_value, progress_type, \n                     rewards, quest_date, status, created_at)\n                    VALUES (?, ?, ?, ?, ?, ?, CURDATE(), 'active', ?)\n                \");\n                \n                $stmt->execute([\n                    $userId,\n                    $quest['title'],\n                    $quest['description'],\n                    $quest['target_value'],\n                    $quest['progress_type'],\n                    json_encode($quest['rewards']),\n                    date('Y-m-d H:i:s')\n                ]);\n            }\n            \n        } catch (Exception $e) {\n            error_log(\"Error storing daily quests: \" . $e->getMessage());\n        }\n    }\n    \n    /**\n     * Update user progress\n     */\n    private function updateUserProgress($userId, $progressType, $value) {\n        $stmt = $this->pdo->prepare(\"\n            INSERT INTO user_metaverse_progress \n            (user_id, progress_type, progress_value, updated_at)\n            VALUES (?, ?, ?, ?)\n            ON DUPLICATE KEY UPDATE \n            progress_value = progress_value + VALUES(progress_value),\n            updated_at = VALUES(updated_at)\n        \");\n        \n        $stmt->execute([$userId, $progressType, $value, date('Y-m-d H:i:s')]);\n    }\n    \n    /**\n     * Check achievement progress\n     */\n    private function checkAchievementProgress($userId) {\n        $achievements = $this->createAchievementSystem();\n        $userProgress = $this->getUserProgress($userId);\n        \n        foreach ($achievements as $achievementId => $achievement) {\n            $progressKey = $this->getProgressKeyForAchievement($achievementId);\n            $currentValue = $userProgress[$progressKey] ?? 0;\n            \n            if ($currentValue >= $achievement['target_value']) {\n                $this->unlockAchievement($userId, $achievementId, $achievement);\n            }\n        }\n    }\n    \n    /**\n     * Update leaderboards\n     */\n    private function updateLeaderboards($userId) {\n        // This would update various leaderboard positions\n        // Implementation depends on specific leaderboard metrics\n    }\n}\n\n/**\n * Mini-Game Tournament System\n */\nclass MetaverseMiniGameTournaments {\n    private $pdo;\n    \n    public function __construct() {\n        $this->pdo = get_db();\n    }\n    \n    /**\n     * Auto-start tournaments when enough players are online\n     */\n    public function checkAndStartTournaments() {\n        $activePlayerCount = $this->getActivePlayerCount();\n        \n        if ($activePlayerCount >= 8) {\n            $this->startMiniGameTournament('racing', $activePlayerCount);\n        }\n        \n        if ($activePlayerCount >= 12) {\n            $this->startMiniGameTournament('treasure_hunt', $activePlayerCount);\n        }\n        \n        if ($activePlayerCount >= 20) {\n            $this->startMiniGameTournament('dance_party', $activePlayerCount);\n        }\n    }\n    \n    /**\n     * Start specific mini-game tournament\n     */\n    private function startMiniGameTournament($gameType, $playerCount) {\n        // Implementation for starting tournaments\n        $tournament = [\n            'game_type' => $gameType,\n            'max_participants' => min($playerCount, $this->getMaxParticipants($gameType)),\n            'start_time' => date('Y-m-d H:i:s', time() + 300), // 5 minutes from now\n            'registration_deadline' => date('Y-m-d H:i:s', time() + 240), // 4 minutes to register\n            'estimated_duration' => $this->getGameDuration($gameType),\n            'prizes' => $this->generateTournamentPrizes($gameType, $playerCount)\n        ];\n        \n        $this->broadcastTournamentAnnouncement($tournament);\n    }\n}\n\n/**\n * Global gamification instance\n */\n$globalMetaverseGamification = new MetaverseGamification();\n$globalMiniGameTournaments = new MetaverseMiniGameTournaments();\n\n/**\n * Wrapper functions\n */\nfunction trackMetaverseProgress($userId, $action, $data = []) {\n    global $globalMetaverseGamification;\n    return $globalMetaverseGamification->trackUserProgress($userId, $action, $data);\n}\n\nfunction generateUserDailyQuests($userId) {\n    global $globalMetaverseGamification;\n    return $globalMetaverseGamification->generateTodaysDailyQuests($userId);\n}\n\nfunction checkAndStartMetaverseTournaments() {\n    global $globalMiniGameTournaments;\n    return $globalMiniGameTournaments->checkAndStartTournaments();\n}\n\nfunction getMetaverseLeaderboards() {\n    global $globalMetaverseGamification;\n    return $globalMetaverseGamification->createLeaderboards();\n}\n\nfunction getMetaverseRewardSystem() {\n    global $globalMetaverseGamification;\n    return $globalMetaverseGamification->createRewardSystem();\n}\n?>
