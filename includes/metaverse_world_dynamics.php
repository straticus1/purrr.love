<?php
/**
 * 🌦️ Purrr.love Metaverse World Dynamics System
 * Weather, seasons, time-based events, and dynamic environments
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Dynamic World Environment System
 */
class MetaverseWorldDynamics {
    private $pdo;
    private $config;
    
    public function __construct() {
        $this->pdo = get_db();
        $this->config = [
            'weather_update_interval' => 1800, // 30 minutes
            'seasonal_update_frequency' => 'daily',
            'time_cycle_duration' => 86400, // 24 hours real = 24 hours virtual
            'special_area_spawn_chance' => 0.15, // 15% per hour
            'event_cascade_probability' => 0.3 // 30% chance events trigger other events
        ];
    }
    
    /**
     * Weather System
     */
    public function updateWorldWeather() {
        $activeWorlds = $this->getActiveWorlds();
        
        foreach ($activeWorlds as $world) {
            $currentWeather = $this->getCurrentWeather($world['world_id']);
            $newWeather = $this->calculateNextWeather($world['world_type'], $currentWeather);
            
            $this->applyWeatherToWorld($world['world_id'], $newWeather);
            $this->triggerWeatherEvents($world['world_id'], $newWeather);
        }
    }
    
    /**
     * Calculate next weather based on world type and patterns
     */
    private function calculateNextWeather($worldType, $currentWeather) {
        $weatherTransitions = [
            'cat_paradise' => [
                'sunny' => ['sunny' => 0.6, 'partly_cloudy' => 0.3, 'tropical_storm' => 0.1],
                'partly_cloudy' => ['sunny' => 0.4, 'partly_cloudy' => 0.4, 'light_rain' => 0.2],
                'light_rain' => ['partly_cloudy' => 0.5, 'heavy_rain' => 0.3, 'rainbow' => 0.2],
                'tropical_storm' => ['heavy_rain' => 0.6, 'light_rain' => 0.3, 'clear_skies' => 0.1],
                'rainbow' => ['sunny' => 0.8, 'partly_cloudy' => 0.2]
            ],
            'mystic_forest' => [
                'misty' => ['misty' => 0.5, 'magical_fog' => 0.3, 'clear' => 0.2],
                'magical_fog' => ['aurora_mist' => 0.4, 'misty' => 0.4, 'enchanted_rain' => 0.2],
                'enchanted_rain' => ['misty' => 0.6, 'magical_fog' => 0.4],
                'aurora_mist' => ['clear' => 0.5, 'starlight' => 0.3, 'misty' => 0.2],
                'starlight' => ['clear' => 0.7, 'aurora_mist' => 0.3]
            ],
            'cosmic_city' => [
                'clear_dome' => ['clear_dome' => 0.5, 'nebula_view' => 0.3, 'meteor_shower' => 0.2],
                'nebula_view' => ['star_storm' => 0.4, 'clear_dome' => 0.4, 'cosmic_aurora' => 0.2],
                'meteor_shower' => ['clear_dome' => 0.6, 'nebula_view' => 0.4],
                'cosmic_aurora' => ['clear_dome' => 0.6, 'nebula_view' => 0.4],
                'star_storm' => ['cosmic_aurora' => 0.5, 'clear_dome' => 0.5]
            ],
            'winter_wonderland' => [
                'light_snow' => ['light_snow' => 0.4, 'heavy_snow' => 0.3, 'clear_cold' => 0.3],
                'heavy_snow' => ['blizzard' => 0.3, 'light_snow' => 0.5, 'snow_stop' => 0.2],
                'blizzard' => ['heavy_snow' => 0.6, 'clear_cold' => 0.4],
                'clear_cold' => ['aurora_borealis' => 0.3, 'light_snow' => 0.5, 'frosty' => 0.2],
                'aurora_borealis' => ['clear_cold' => 0.7, 'frosty' => 0.3]
            ],
            'desert_oasis' => [
                'hot_sunny' => ['hot_sunny' => 0.5, 'dust_storm' => 0.2, 'scorching' => 0.3],
                'dust_storm' => ['clear_hot' => 0.6, 'mild_breeze' => 0.4],
                'scorching' => ['mirage_conditions' => 0.4, 'hot_sunny' => 0.6],
                'mirage_conditions' => ['clear_hot' => 0.6, 'cool_evening' => 0.4],
                'cool_evening' => ['starry_night' => 0.6, 'mild_breeze' => 0.4],
                'starry_night' => ['cool_evening' => 0.5, 'hot_sunny' => 0.5]
            ]
        ];
        
        $transitions = $weatherTransitions[$worldType] ?? $weatherTransitions['cat_paradise'];
        $possibleWeathers = $transitions[$currentWeather] ?? $transitions['sunny'] ?? ['sunny' => 1.0];
        
        return $this->weightedRandomChoice($possibleWeathers);
    }
    
    /**
     * Apply weather effects to world
     */
    private function applyWeatherToWorld($worldId, $weather) {
        $weatherEffects = $this->getWeatherEffects($weather);
        
        // Update world weather
        $stmt = $this->pdo->prepare("
            INSERT INTO world_weather_states 
            (world_id, weather_type, weather_effects, visibility, temperature, 
             special_effects, started_at, duration_minutes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            weather_type = VALUES(weather_type),
            weather_effects = VALUES(weather_effects),
            visibility = VALUES(visibility),
            temperature = VALUES(temperature),
            special_effects = VALUES(special_effects),
            started_at = VALUES(started_at),
            duration_minutes = VALUES(duration_minutes)
        ");
        
        $stmt->execute([
            $worldId,
            $weather,
            json_encode($weatherEffects['effects']),
            $weatherEffects['visibility'],
            $weatherEffects['temperature'],
            json_encode($weatherEffects['special_effects']),
            date('Y-m-d H:i:s'),
            $weatherEffects['duration']
        ]);
        
        // Notify players of weather change
        $this->notifyWeatherChange($worldId, $weather, $weatherEffects);
    }
    
    /**
     * Get weather effects configuration
     */
    private function getWeatherEffects($weather) {
        $effects = [
            'sunny' => [
                'effects' => ['bright_lighting', 'warm_temperature', 'clear_visibility'],
                'visibility' => 100,
                'temperature' => 25,
                'special_effects' => ['sunbeams', 'shadow_casting'],
                'duration' => rand(60, 180),
                'mood_bonus' => 10
            ],
            'light_rain' => [
                'effects' => ['rain_drops', 'puddle_formation', 'fresh_air'],
                'visibility' => 80,
                'temperature' => 18,
                'special_effects' => ['rain_particles', 'water_reflections'],
                'duration' => rand(30, 90),
                'mood_bonus' => 5
            ],
            'magical_fog' => [
                'effects' => ['mystical_atmosphere', 'reduced_visibility', 'magical_particles'],
                'visibility' => 40,
                'temperature' => 15,
                'special_effects' => ['floating_sparkles', 'magical_whispers', 'hidden_path_reveal'],
                'duration' => rand(45, 120),
                'mystery_bonus' => 15
            ],
            'aurora_borealis' => [
                'effects' => ['dancing_lights', 'ethereal_glow', 'mystical_energy'],
                'visibility' => 90,
                'temperature' => -5,
                'special_effects' => ['light_ribbons', 'color_waves', 'spiritual_presence'],
                'duration' => rand(20, 60),
                'inspiration_bonus' => 20
            ],
            'meteor_shower' => [
                'effects' => ['falling_stars', 'cosmic_display', 'wish_opportunities'],
                'visibility' => 95,
                'temperature' => 20,
                'special_effects' => ['shooting_stars', 'cosmic_dust', 'rare_items_rain'],
                'duration' => rand(15, 45),
                'luck_bonus' => 25
            ]
        ];
        
        return $effects[$weather] ?? $effects['sunny'];
    }
    
    /**
     * Seasonal Content System
     */
    public function updateSeasonalContent() {
        $season = $this->getCurrentSeason();
        $seasonalContent = $this->getSeasonalContent($season);
        
        foreach ($seasonalContent as $worldType => $content) {
            $this->applySeasonalContentToWorldType($worldType, $content);
        }
    }
    
    /**
     * Get seasonal content for each world type
     */
    private function getSeasonalContent($season) {
        $content = [
            'spring' => [
                'cat_paradise' => [
                    'decorations' => ['blooming_flowers', 'butterfly_swarms', 'new_growth'],
                    'activities' => ['flower_collecting', 'butterfly_catching', 'nest_building'],
                    'special_items' => ['spring_crown', 'flower_collar', 'butterfly_net'],
                    'events' => ['spring_festival', 'flower_crown_contest', 'baby_animal_visits']
                ],
                'mystic_forest' => [
                    'decorations' => ['magical_blossoms', 'fairy_rings', 'awakening_trees'],
                    'activities' => ['fairy_hunting', 'magic_flower_growing', 'spirit_awakening'],
                    'special_items' => ['fairy_dust', 'magical_seeds', 'spring_wand'],
                    'events' => ['fairy_gathering', 'tree_awakening_ceremony', 'magical_rain']
                ]
            ],
            'summer' => [
                'cat_paradise' => [
                    'decorations' => ['tropical_fruits', 'beach_umbrellas', 'swimming_spots'],
                    'activities' => ['beach_volleyball', 'swimming', 'fruit_gathering'],
                    'special_items' => ['sunglasses', 'beach_ball', 'tropical_drink'],
                    'events' => ['beach_party', 'surfing_contest', 'fruit_festival']
                ],
                'cosmic_city' => [
                    'decorations' => ['solar_panels_active', 'bright_neon_lights', 'energy_crystals'],
                    'activities' => ['solar_energy_games', 'light_speed_races', 'crystal_collecting'],
                    'special_items' => ['solar_collar', 'energy_drink', 'speed_boots'],
                    'events' => ['solar_festival', 'neon_party', 'energy_crystal_hunt']
                ]
            ],
            'autumn' => [
                'mystic_forest' => [
                    'decorations' => ['golden_leaves', 'harvest_decorations', 'cozy_fires'],
                    'activities' => ['leaf_collecting', 'acorn_gathering', 'storytelling'],
                    'special_items' => ['leaf_crown', 'acorn_pouch', 'cozy_scarf'],
                    'events' => ['harvest_festival', 'storytelling_night', 'leaf_pile_jumping']
                ]
            ],
            'winter' => [
                'winter_wonderland' => [
                    'decorations' => ['ice_sculptures', 'holiday_lights', 'snow_forts'],
                    'activities' => ['ice_skating', 'snowman_building', 'sledding'],
                    'special_items' => ['winter_coat', 'ice_skates', 'snow_boots'],
                    'events' => ['winter_carnival', 'ice_sculpture_contest', 'aurora_viewing']
                ]
            ]
        ];
        
        return $content[$season] ?? [];
    }
    
    /**
     * Time-Based Events System
     */
    public function createTimeCycleEvents() {
        $timeEvents = [
            'dawn' => [
                'cat_paradise' => ['sunrise_yoga', 'early_bird_fishing', 'morning_meditation'],
                'mystic_forest' => ['dew_collecting', 'fairy_awakening', 'sunrise_spirits'],
                'cosmic_city' => ['solar_panel_activation', 'morning_commute_race', 'energy_boost'],
                'winter_wonderland' => ['frost_formation', 'ice_crystal_hunting', 'aurora_fade'],
                'desert_oasis' => ['cool_morning_breeze', 'cactus_bloom', 'treasure_visibility']
            ],
            'morning' => [
                'global' => ['daily_quest_refresh', 'npc_activity_increase', 'social_boost'],
                'special' => ['morning_treasure_spawns', 'friendship_bonus_hour', 'learning_boost']
            ],
            'noon' => [
                'cat_paradise' => ['peak_sunshine', 'beach_activities', 'siesta_spots'],
                'cosmic_city' => ['solar_maximum', 'energy_overflow', 'racing_peak'],
                'desert_oasis' => ['mirage_peak', 'heat_challenge', 'shade_seeking']
            ],
            'afternoon' => [
                'global' => ['activity_peak_time', 'tournament_hour', 'social_events'],
                'special' => ['competition_bonuses', 'group_activity_rewards', 'friendship_multipliers']
            ],
            'evening' => [
                'mystic_forest' => ['magical_hour', 'spirit_activity', 'enchanted_glow'],
                'cosmic_city' => ['neon_activation', 'night_life_prep', 'hologram_shows'],
                'global' => ['social_hour', 'relaxation_time', 'storytelling']
            ],
            'night' => [
                'mystic_forest' => ['full_magic_power', 'nocturnal_creatures', 'starlight_paths'],
                'cosmic_city' => ['full_neon_glory', 'space_view_optimal', 'night_racing'],
                'winter_wonderland' => ['aurora_borealis', 'stargazing', 'night_skiing'],
                'global' => ['dream_mode', 'peaceful_activities', 'constellation_events']
            ]
        ];
        
        $currentTimeSlot = $this->getCurrentTimeSlot();
        return $timeEvents[$currentTimeSlot] ?? [];
    }
    
    /**
     * Special Limited-Time Areas System
     */
    public function manageSpecialAreas() {
        // Check for new special area spawns
        if (rand(1, 100) <= ($this->config['special_area_spawn_chance'] * 100)) {
            $this->spawnRandomSpecialArea();
        }
        
        // Update existing special areas
        $this->updateActiveSpecialAreas();
        
        // Remove expired special areas
        $this->removeExpiredSpecialAreas();
    }
    
    /**
     * Special area templates
     */
    private function getSpecialAreaTemplates() {
        return [
            'rainbow_bridge' => [
                'name' => 'Rainbow Bridge Sanctuary',
                'description' => 'A mystical bridge where cats can visit with past companions',
                'rarity' => 'legendary',
                'duration_hours' => 1,
                'max_visitors' => 25,
                'spawn_worlds' => ['cat_paradise', 'mystic_forest'],
                'activities' => ['memory_sharing', 'spirit_communion', 'rainbow_walking'],
                'rewards' => ['rainbow_blessing', 'spirit_orb', 'eternal_friendship_badge'],
                'special_effects' => ['rainbow_particles', 'ethereal_music', 'gentle_glow']
            ],
            'time_rift_chamber' => [
                'name' => 'Temporal Rift Chamber',
                'description' => 'A mysterious chamber where time flows differently',
                'rarity' => 'epic',
                'duration_hours' => 2,
                'max_visitors' => 15,
                'spawn_worlds' => ['cosmic_city', 'mystic_forest'],
                'activities' => ['time_travel_glimpses', 'past_exploration', 'future_visions'],
                'rewards' => ['time_crystal', 'temporal_wisdom', 'chronos_collar'],
                'special_effects' => ['time_distortion', 'chronological_echoes', 'temporal_aura']
            ],
            'crystal_singing_cavern' => [
                'name' => 'Crystal Singing Cavern',
                'description' => 'Crystals that sing harmoniously when cats gather',
                'rarity' => 'rare',
                'duration_hours' => 3,
                'max_visitors' => 30,
                'spawn_worlds' => ['winter_wonderland', 'desert_oasis'],
                'activities' => ['crystal_harmonizing', 'music_creation', 'resonance_meditation'],
                'rewards' => ['harmony_crystal', 'musical_collar', 'acoustic_blessing'],
                'special_effects' => ['crystal_resonance', 'harmonic_waves', 'sound_visualization']
            ],
            'floating_garden_island' => [
                'name' => 'Floating Garden Paradise',
                'description' => 'A garden island that floats among the clouds',
                'rarity' => 'rare',
                'duration_hours' => 4,
                'max_visitors' => 20,
                'spawn_worlds' => ['cat_paradise', 'cosmic_city'],
                'activities' => ['cloud_jumping', 'aerial_gardening', 'sky_meditation'],
                'rewards' => ['cloud_walker_boots', 'sky_garden_seeds', 'levitation_charm'],
                'special_effects' => ['floating_platforms', 'cloud_streams', 'sky_bridges']
            ]
        ];
    }
    
    /**
     * Dynamic Event Cascade System
     */
    public function triggerEventCascades() {
        $recentEvents = $this->getRecentWorldEvents(30); // Last 30 minutes
        
        foreach ($recentEvents as $event) {
            if (rand(1, 100) <= ($this->config['event_cascade_probability'] * 100)) {
                $cascadeEvent = $this->generateCascadeEvent($event);
                if ($cascadeEvent) {
                    $this->triggerCascadeEvent($event['world_id'], $cascadeEvent);
                }
            }
        }
    }
    
    /**
     * Generate cascade events based on trigger events
     */
    private function generateCascadeEvent($triggerEvent) {
        $cascadeMap = [
            'fish_spawn' => 'seagull_flock_arrives',
            'treasure_chest' => 'treasure_map_pieces_scatter',
            'magical_portal' => 'ancient_spirits_emerge',
            'meteor_shower' => 'space_debris_treasures',
            'aurora_borealis' => 'mystical_energy_surge',
            'rainbow_bridge' => 'pot_of_gold_appears',
            'butterfly_swarm' => 'flower_bloom_explosion',
            'crystal_resonance' => 'harmonic_portal_opens'
        ];
        
        return $cascadeMap[$triggerEvent['event_type']] ?? null;
    }
    
    /**
     * Environmental Storytelling System
     */
    public function createEnvironmentalStoryEvents() {
        $storyEvents = [
            'ancient_cat_legend' => [
                'title' => 'The Legend Awakens',
                'description' => 'Ancient cat spirits share tales of old adventures',
                'trigger_conditions' => ['night_time', 'mystic_forest', 'full_moon'],
                'duration_minutes' => 45,
                'interactive_elements' => ['listen_to_tales', 'ask_questions', 'receive_wisdom'],
                'rewards' => ['ancient_knowledge', 'legend_fragment', 'wisdom_points']
            ],
            'cosmic_cat_migration' => [
                'title' => 'The Great Cosmic Migration',
                'description' => 'Space cats migrate across the cosmic city skies',
                'trigger_conditions' => ['cosmic_city', 'clear_dome_weather', 'player_threshold_10'],
                'duration_minutes' => 30,
                'interactive_elements' => ['follow_migration', 'communicate_with_space_cats', 'learn_star_maps'],
                'rewards' => ['cosmic_map', 'star_navigator_badge', 'space_friend']
            ],
            'winter_festival_of_lights' => [
                'title' => 'Festival of Aurora Lights',
                'description' => 'Cats from all worlds gather to celebrate the aurora',
                'trigger_conditions' => ['winter', 'aurora_borealis', 'multi_world_event'],
                'duration_minutes' => 90,
                'interactive_elements' => ['light_dancing', 'aurora_painting', 'group_celebration'],
                'rewards' => ['festival_crown', 'aurora_painter_kit', 'celebration_memories']
            ]
        ];
        
        return $storyEvents;
    }
    
    /**
     * Dynamic Resource System
     */
    public function manageWorldResources() {
        $resourceTypes = [
            'renewable' => [
                'catnip_patches' => ['growth_rate' => 'hourly', 'max_capacity' => 50],
                'fish_schools' => ['respawn_rate' => '30_minutes', 'max_capacity' => 30],
                'toy_spawns' => ['appearance_rate' => '45_minutes', 'max_capacity' => 15]
            ],
            'limited' => [
                'rare_crystals' => ['spawn_rate' => 'daily', 'max_capacity' => 5],
                'ancient_artifacts' => ['spawn_rate' => 'weekly', 'max_capacity' => 2],
                'legendary_items' => ['spawn_rate' => 'monthly', 'max_capacity' => 1]
            ],
            'event_based' => [
                'treasure_chests' => ['trigger' => 'treasure_storm_event'],
                'magical_portals' => ['trigger' => 'magical_surge_event'],
                'cosmic_fragments' => ['trigger' => 'meteor_shower_event']
            ]
        ];
        
        foreach ($resourceTypes as $category => $resources) {
            $this->updateResourceCategory($category, $resources);
        }
    }
    
    /**
     * Interactive World Objects System
     */
    public function createInteractiveObjects() {
        $objects = [
            'musical_fountains' => [
                'description' => 'Fountains that play music when cats gather around',
                'interaction_types' => ['sit_and_listen', 'dance_around', 'harmonize'],
                'effects' => ['relaxation_boost', 'happiness_increase', 'social_bonding'],
                'spawn_locations' => ['social_areas', 'park_centers', 'plaza_squares']
            ],
            'wisdom_statues' => [
                'description' => 'Ancient statues that share daily wisdom',
                'interaction_types' => ['meditate_nearby', 'touch_paw_to_statue', 'circle_around'],
                'effects' => ['wisdom_points', 'calm_aura', 'insight_bonus'],
                'spawn_locations' => ['quiet_corners', 'mountain_peaks', 'sacred_groves']
            ],
            'playful_holograms' => [
                'description' => 'Interactive holographic toys and creatures',
                'interaction_types' => ['chase_hologram', 'play_with_projection', 'create_light_art'],
                'effects' => ['energy_boost', 'playfulness_increase', 'creativity_points'],
                'spawn_locations' => ['tech_areas', 'gaming_zones', 'innovation_labs']
            ],
            'comfort_clouds' => [
                'description' => 'Floating clouds that provide perfect napping spots',
                'interaction_types' => ['nap_on_cloud', 'cloud_jumping', 'sky_meditation'],
                'effects' => ['energy_restoration', 'stress_relief', 'dream_points'],
                'spawn_locations' => ['high_altitude_areas', 'peaceful_zones', 'sky_gardens']
            ]
        ];
        
        return $objects;
    }
    
    /**
     * Helper Functions
     */
    private function getCurrentSeason() {
        $month = (int)date('n');
        if ($month >= 3 && $month <= 5) return 'spring';
        if ($month >= 6 && $month <= 8) return 'summer';
        if ($month >= 9 && $month <= 11) return 'autumn';
        return 'winter';
    }
    
    private function getCurrentTimeSlot() {
        $hour = (int)date('G');
        if ($hour >= 5 && $hour < 7) return 'dawn';
        if ($hour >= 7 && $hour < 12) return 'morning';
        if ($hour >= 12 && $hour < 17) return 'afternoon';
        if ($hour >= 17 && $hour < 20) return 'evening';
        return 'night';
    }
    
    private function weightedRandomChoice($weights) {
        $totalWeight = array_sum($weights);
        $random = mt_rand(1, (int)($totalWeight * 1000)) / 1000;
        
        $currentWeight = 0;
        foreach ($weights as $choice => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $choice;
            }
        }
        
        return array_key_first($weights);
    }
    
    private function getActiveWorlds() {
        $stmt = $this->pdo->prepare("
            SELECT world_id, world_type, name 
            FROM metaverse_worlds 
            WHERE status = 'active'
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function getCurrentWeather($worldId) {
        $stmt = $this->pdo->prepare("
            SELECT weather_type 
            FROM world_weather_states 
            WHERE world_id = ?
            ORDER BY started_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$worldId]);
        $result = $stmt->fetchColumn();
        return $result ?: 'sunny'; // Default weather
    }
    
    private function notifyWeatherChange($worldId, $weather, $effects) {
        $notification = [
            'type' => 'weather_change',
            'world_id' => $worldId,
            'new_weather' => $weather,
            'effects' => $effects,
            'timestamp' => time()
        ];
        
        // This would integrate with the notification system
        $this->broadcastToWorldPlayers($worldId, $notification);
    }
    
    private function spawnRandomSpecialArea() {
        $templates = $this->getSpecialAreaTemplates();
        $template = $templates[array_rand($templates)];
        
        // Find suitable world for this special area
        $suitableWorlds = $this->findSuitableWorldsForArea($template);
        
        if (!empty($suitableWorlds)) {
            $selectedWorld = $suitableWorlds[array_rand($suitableWorlds)];
            $this->createSpecialAreaInstance($selectedWorld, $template);
        }
    }
    
    private function triggerWeatherEvents($worldId, $weather) {
        $weatherEvents = [
            'rainbow' => 'A beautiful rainbow appears! Perfect time for photos!',
            'aurora_borealis' => 'The northern lights dance across the sky!',
            'meteor_shower' => 'Shooting stars light up the night! Make a wish!',
            'magical_fog' => 'Mystical fog reveals hidden paths and secrets!',
            'tropical_storm' => 'Wild weather brings adventure and treasure!',
            'blizzard' => 'A fierce blizzard creates new snow formations!',
            'starry_night' => 'Perfect stargazing conditions with crystal clear skies!'
        ];
        
        if (isset($weatherEvents[$weather])) {
            $this->createWeatherEvent($worldId, $weather, $weatherEvents[$weather]);
        }
    }
}

/**
 * World Population Management
 */
class MetaversePopulationManager {
    private $pdo;
    
    public function __construct() {
        $this->pdo = get_db();
    }
    
    /**
     * Balance population across worlds
     */
    public function balanceWorldPopulation() {
        $worldStats = $this->getWorldPopulationStats();
        
        foreach ($worldStats as $world) {
            if ($world['population_ratio'] < 0.2) { // Less than 20% capacity
                $this->boostWorldAttractiveness($world['world_id']);
            } elseif ($world['population_ratio'] > 0.9) { // Over 90% capacity
                $this->createWorldInstance($world['world_id']);
            }
        }
    }
    
    /**
     * Create incentives for low-population worlds
     */
    private function boostWorldAttractiveness($worldId) {
        $boostStrategies = [
            'double_rewards' => 'All rewards doubled for the next hour!',
            'rare_item_spawn' => 'Rare items are more likely to appear!',
            'special_visitor' => 'A special celebrity cat NPC has arrived!',
            'mystery_quest' => 'An exclusive mystery quest has appeared!',
            'bonus_experience' => 'Experience gains increased by 50%!'
        ];
        
        $strategy = array_rand($boostStrategies);
        $message = $boostStrategies[$strategy];
        
        $this->applyWorldBoost($worldId, $strategy);
        $this->announceWorldBoost($worldId, $message);
    }
}

/**
 * Global instances
 */
$globalMetaverseWorldDynamics = new MetaverseWorldDynamics();
$globalMetaversePopulationManager = new MetaversePopulationManager();

/**
 * Wrapper functions
 */
function updateMetaverseWorldWeather() {
    global $globalMetaverseWorldDynamics;
    return $globalMetaverseWorldDynamics->updateWorldWeather();
}

function updateMetaverseSeasonalContent() {
    global $globalMetaverseWorldDynamics;
    return $globalMetaverseWorldDynamics->updateSeasonalContent();
}

function manageMetaverseSpecialAreas() {
    global $globalMetaverseWorldDynamics;
    return $globalMetaverseWorldDynamics->manageSpecialAreas();
}

function triggerMetaverseTimeCycleEvents() {
    global $globalMetaverseWorldDynamics;
    return $globalMetaverseWorldDynamics->createTimeCycleEvents();
}

function balanceMetaverseWorldPopulation() {
    global $globalMetaversePopulationManager;
    return $globalMetaversePopulationManager->balanceWorldPopulation();
}

function manageMetaverseWorldResources() {
    global $globalMetaverseWorldDynamics;
    return $globalMetaverseWorldDynamics->manageWorldResources();
}
?>
