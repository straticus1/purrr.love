<?php
/**
 * 🏥 Purrr.love Cat Health Monitoring System
 * Integration with real pet health tracking devices and health analytics
 */

// Health monitoring configuration
define('HEALTH_CHECK_INTERVAL_HOURS', 6);
define('HEALTH_ALERT_THRESHOLDS', [
    'heart_rate' => ['min' => 140, 'max' => 220],
    'respiratory_rate' => ['min' => 15, 'max' => 40],
    'temperature' => ['min' => 100.5, 'max' => 102.5],
    'activity_level' => ['min' => 10, 'max' => 100],
    'sleep_quality' => ['min' => 60, 'max' => 100]
]);

// Device types
define('HEALTH_DEVICE_TYPES', [
    'smart_collar' => [
        'name' => 'Smart Collar',
        'capabilities' => ['gps', 'activity', 'heart_rate', 'temperature'],
        'battery_life_hours' => 72,
        'update_frequency_minutes' => 5
    ],
    'health_monitor' => [
        'name' => 'Health Monitor',
        'capabilities' => ['heart_rate', 'respiratory_rate', 'temperature', 'blood_pressure'],
        'battery_life_hours' => 48,
        'update_frequency_minutes' => 2
    ],
    'activity_tracker' => [
        'name' => 'Activity Tracker',
        'capabilities' => ['steps', 'distance', 'calories', 'sleep_quality'],
        'battery_life_hours' => 168,
        'update_frequency_minutes' => 10
    ],
    'smart_feeder' => [
        'name' => 'Smart Feeder',
        'capabilities' => ['food_intake', 'water_intake', 'feeding_schedule', 'weight'],
        'battery_life_hours' => 720,
        'update_frequency_minutes' => 60
    ],
    'litter_box_monitor' => [
        'name' => 'Litter Box Monitor',
        'capabilities' => ['usage_frequency', 'waste_analysis', 'health_indicators'],
        'battery_life_hours' => 1440,
        'update_frequency_minutes' => 120
    ]
]);

// Health metrics
define('HEALTH_METRICS', [
    'vital_signs' => ['heart_rate', 'respiratory_rate', 'temperature', 'blood_pressure'],
    'activity' => ['steps', 'distance', 'calories_burned', 'active_minutes'],
    'nutrition' => ['food_intake', 'water_intake', 'weight', 'body_condition'],
    'behavior' => ['sleep_quality', 'stress_level', 'mood_indicator', 'social_interaction'],
    'environmental' => ['room_temperature', 'humidity', 'air_quality', 'noise_level']
]);

/**
 * Register health monitoring device
 */
function registerHealthDevice($userId, $catId, $deviceData) {
    // Validate cat ownership
    if (!canMonitorCatHealth($userId, $catId)) {
        throw new Exception('Cannot monitor health for this cat', 403);
    }
    
    // Validate device data
    $validatedDevice = validateHealthDevice($deviceData);
    
    // Check if device is already registered
    if (isDeviceAlreadyRegistered($validatedDevice['device_id'])) {
        throw new Exception('Device already registered', 400);
    }
    
    // Register device
    $deviceRegistrationId = createDeviceRegistration($userId, $catId, $validatedDevice);
    
    // Initialize health monitoring profile
    $healthProfile = initializeCatHealthProfile($catId);
    
    // Set up monitoring schedule
    $monitoringSchedule = setupHealthMonitoringSchedule($catId, $validatedDevice);
    
    // Log device registration
    logHealthEvent('device_registered', $deviceRegistrationId, $userId, $catId);
    
    return [
        'registration_id' => $deviceRegistrationId,
        'device' => $validatedDevice,
        'health_profile' => $healthProfile,
        'monitoring_schedule' => $monitoringSchedule,
        'registered_at' => date('c')
    ];
}

/**
 * Process health data from device
 */
function processHealthData($deviceId, $healthData) {
    // Validate device
    $device = getRegisteredDevice($deviceId);
    if (!$device || !$device['active']) {
        throw new Exception('Invalid or inactive device', 400);
    }
    
    // Validate health data
    $validatedData = validateHealthData($healthData, $device['device_type']);
    
    // Process data based on device type
    $processedData = processHealthDataByDeviceType($device['device_type'], $validatedData);
    
    // Store health data
    $dataId = storeHealthData($device['cat_id'], $deviceId, $processedData);
    
    // Analyze health trends
    $healthAnalysis = analyzeHealthTrends($device['cat_id'], $processedData);
    
    // Check for health alerts
    $alerts = checkHealthAlerts($device['cat_id'], $processedData);
    
    // Update cat's health profile
    updateCatHealthProfile($device['cat_id'], $processedData, $healthAnalysis);
    
    // Notify owner if alerts found
    if (!empty($alerts)) {
        notifyHealthAlerts($device['cat_id'], $alerts);
    }
    
    // Log health data processing
    logHealthEvent('data_processed', $dataId, $device['user_id'], $device['cat_id']);
    
    return [
        'data_id' => $dataId,
        'processed' => true,
        'analysis' => $healthAnalysis,
        'alerts' => $alerts,
        'processed_at' => date('c')
    ];
}

/**
 * Get cat health summary
 */
function getCatHealthSummary($catId, $timeframe = '7d') {
    // Get cat's health profile
    $healthProfile = getCatHealthProfile($catId);
    if (!$healthProfile) {
        throw new Exception('Health profile not found', 404);
    }
    
    // Get recent health data
    $recentData = getRecentHealthData($catId, $timeframe);
    
    // Calculate health trends
    $healthTrends = calculateHealthTrends($catId, $timeframe);
    
    // Get health recommendations
    $recommendations = generateHealthRecommendations($catId, $healthProfile, $recentData);
    
    // Get upcoming health checks
    $upcomingChecks = getUpcomingHealthChecks($catId);
    
    return [
        'health_profile' => $healthProfile,
        'recent_data' => $recentData,
        'health_trends' => $healthTrends,
        'recommendations' => $recommendations,
        'upcoming_checks' => $upcomingChecks,
        'last_updated' => date('c')
    ];
}

/**
 * Set up health monitoring schedule
 */
function setupHealthMonitoringSchedule($catId, $device) {
    $schedule = [];
    
    // Get device capabilities
    $deviceCapabilities = HEALTH_DEVICE_TYPES[$device['device_type']]['capabilities'];
    
    // Set up monitoring intervals based on capabilities
    foreach ($deviceCapabilities as $capability) {
        switch ($capability) {
            case 'heart_rate':
            case 'respiratory_rate':
                $schedule[$capability] = [
                    'frequency' => '2m', // Every 2 minutes
                    'alert_thresholds' => HEALTH_ALERT_THRESHOLDS[$capability] ?? null
                ];
                break;
                
            case 'temperature':
                $schedule[$capability] = [
                    'frequency' => '10m', // Every 10 minutes
                    'alert_thresholds' => HEALTH_ALERT_THRESHOLDS[$capability] ?? null
                ];
                break;
                
            case 'activity':
            case 'steps':
                $schedule[$capability] = [
                    'frequency' => '5m', // Every 5 minutes
                    'alert_thresholds' => HEALTH_ALERT_THRESHOLDS['activity_level'] ?? null
                ];
                break;
                
            case 'sleep_quality':
                $schedule[$capability] = [
                    'frequency' => '1h', // Every hour
                    'alert_thresholds' => HEALTH_ALERT_THRESHOLDS['sleep_quality'] ?? null
                ];
                break;
                
            default:
                $schedule[$capability] = [
                    'frequency' => '15m', // Default: every 15 minutes
                    'alert_thresholds' => null
                ];
        }
    }
    
    return $schedule;
}

/**
 * Process health data by device type
 */
function processHealthDataByDeviceType($deviceType, $healthData) {
    $processed = [];
    
    switch ($deviceType) {
        case 'smart_collar':
            $processed = processSmartCollarData($healthData);
            break;
            
        case 'health_monitor':
            $processed = processHealthMonitorData($healthData);
            break;
            
        case 'activity_tracker':
            $processed = processActivityTrackerData($healthData);
            break;
            
        case 'smart_feeder':
            $processed = processSmartFeederData($healthData);
            break;
            
        case 'litter_box_monitor':
            $processed = processLitterBoxData($healthData);
            break;
            
        default:
            $processed = $healthData;
    }
    
    // Add timestamp and device type
    $processed['timestamp'] = time();
    $processed['device_type'] = $deviceType;
    
    return $processed;
}

/**
 * Process smart collar data
 */
function processSmartCollarData($data) {
    $processed = [];
    
    // Process GPS data
    if (isset($data['gps'])) {
        $processed['location'] = [
            'latitude' => $data['gps']['lat'] ?? 0,
            'longitude' => $data['gps']['lng'] ?? 0,
            'accuracy' => $data['gps']['accuracy'] ?? 0
        ];
    }
    
    // Process activity data
    if (isset($data['activity'])) {
        $processed['activity'] = [
            'steps' => $data['activity']['steps'] ?? 0,
            'distance' => $data['activity']['distance'] ?? 0,
            'calories' => $data['activity']['calories'] ?? 0
        ];
    }
    
    // Process vital signs
    if (isset($data['vitals'])) {
        $processed['vitals'] = [
            'heart_rate' => $data['vitals']['heart_rate'] ?? 0,
            'temperature' => $data['vitals']['temperature'] ?? 0
        ];
    }
    
    return $processed;
}

/**
 * Process health monitor data
 */
function processHealthMonitorData($data) {
    $processed = [];
    
    // Process vital signs
    if (isset($data['vitals'])) {
        $processed['vitals'] = [
            'heart_rate' => $data['vitals']['heart_rate'] ?? 0,
            'respiratory_rate' => $data['vitals']['respiratory_rate'] ?? 0,
            'temperature' => $data['vitals']['temperature'] ?? 0,
            'blood_pressure' => [
                'systolic' => $data['vitals']['blood_pressure']['systolic'] ?? 0,
                'diastolic' => $data['vitals']['blood_pressure']['diastolic'] ?? 0
            ]
        ];
    }
    
    // Process additional metrics
    if (isset($data['additional'])) {
        $processed['additional'] = $data['additional'];
    }
    
    return $processed;
}

/**
 * Process activity tracker data
 */
function processActivityTrackerData($data) {
    $processed = [];
    
    // Process activity metrics
    if (isset($data['activity'])) {
        $processed['activity'] = [
            'steps' => $data['activity']['steps'] ?? 0,
            'distance' => $data['activity']['distance'] ?? 0,
            'calories_burned' => $data['activity']['calories_burned'] ?? 0,
            'active_minutes' => $data['activity']['active_minutes'] ?? 0
        ];
    }
    
    // Process sleep data
    if (isset($data['sleep'])) {
        $processed['sleep'] = [
            'duration' => $data['sleep']['duration'] ?? 0,
            'quality' => $data['sleep']['quality'] ?? 0,
            'deep_sleep_percentage' => $data['sleep']['deep_sleep_percentage'] ?? 0,
            'rem_sleep_percentage' => $data['sleep']['rem_sleep_percentage'] ?? 0
        ];
    }
    
    return $processed;
}

/**
 * Process smart feeder data
 */
function processSmartFeederData($data) {
    $processed = [];
    
    // Process nutrition data
    if (isset($data['nutrition'])) {
        $processed['nutrition'] = [
            'food_intake' => $data['nutrition']['food_intake'] ?? 0,
            'water_intake' => $data['nutrition']['water_intake'] ?? 0,
            'feeding_time' => $data['nutrition']['feeding_time'] ?? time(),
            'meal_type' => $data['nutrition']['meal_type'] ?? 'regular'
        ];
    }
    
    // Process weight data
    if (isset($data['weight'])) {
        $processed['weight'] = [
            'current_weight' => $data['weight']['current'] ?? 0,
            'weight_change' => $data['weight']['change'] ?? 0,
            'measurement_time' => $data['weight']['timestamp'] ?? time()
        ];
    }
    
    return $processed;
}

/**
 * Process litter box data
 */
function processLitterBoxData($data) {
    $processed = [];
    
    // Process usage data
    if (isset($data['usage'])) {
        $processed['usage'] = [
            'frequency' => $data['usage']['frequency'] ?? 0,
            'duration' => $data['usage']['duration'] ?? 0,
            'last_used' => $data['usage']['last_used'] ?? time()
        ];
    }
    
    // Process waste analysis
    if (isset($data['waste'])) {
        $processed['waste'] = [
            'consistency' => $data['waste']['consistency'] ?? 'normal',
            'color' => $data['waste']['color'] ?? 'normal',
            'health_indicators' => $data['waste']['health_indicators'] ?? []
        ];
    }
    
    return $processed;
}

/**
 * Analyze health trends
 */
function analyzeHealthTrends($catId, $timeframe) {
    $trends = [];
    
    // Get historical data
    $historicalData = getHistoricalHealthData($catId, $timeframe);
    
    // Analyze vital signs trends
    $trends['vital_signs'] = analyzeVitalSignsTrends($historicalData);
    
    // Analyze activity trends
    $trends['activity'] = analyzeActivityTrends($historicalData);
    
    // Analyze nutrition trends
    $trends['nutrition'] = analyzeNutritionTrends($historicalData);
    
    // Analyze behavior trends
    $trends['behavior'] = analyzeBehaviorTrends($historicalData);
    
    // Calculate overall health score
    $trends['overall_health_score'] = calculateOverallHealthScore($trends);
    
    return $trends;
}

/**
 * Check health alerts
 */
function checkHealthAlerts($catId, $healthData) {
    $alerts = [];
    
    // Check vital signs
    if (isset($healthData['vitals'])) {
        $vitals = $healthData['vitals'];
        
        // Heart rate alerts
        if (isset($vitals['heart_rate'])) {
            $heartRate = $vitals['heart_rate'];
            if ($heartRate < HEALTH_ALERT_THRESHOLDS['heart_rate']['min'] || 
                $heartRate > HEALTH_ALERT_THRESHOLDS['heart_rate']['max']) {
                $alerts[] = [
                    'type' => 'vital_sign_alert',
                    'metric' => 'heart_rate',
                    'value' => $heartRate,
                    'threshold' => HEALTH_ALERT_THRESHOLDS['heart_rate'],
                    'severity' => 'high',
                    'message' => "Heart rate outside normal range: {$heartRate} bpm"
                ];
            }
        }
        
        // Temperature alerts
        if (isset($vitals['temperature'])) {
            $temperature = $vitals['temperature'];
            if ($temperature < HEALTH_ALERT_THRESHOLDS['temperature']['min'] || 
                $temperature > HEALTH_ALERT_THRESHOLDS['temperature']['max']) {
                $alerts[] = [
                    'type' => 'vital_sign_alert',
                    'metric' => 'temperature',
                    'value' => $temperature,
                    'threshold' => HEALTH_ALERT_THRESHOLDS['temperature'],
                    'severity' => 'high',
                    'message' => "Temperature outside normal range: {$temperature}°F"
                ];
            }
        }
    }
    
    // Check activity alerts
    if (isset($healthData['activity'])) {
        $activity = $healthData['activity'];
        
        if (isset($activity['steps'])) {
            $steps = $activity['steps'];
            if ($steps < HEALTH_ALERT_THRESHOLDS['activity_level']['min']) {
                $alerts[] = [
                    'type' => 'activity_alert',
                    'metric' => 'steps',
                    'value' => $steps,
                    'threshold' => HEALTH_ALERT_THRESHOLDS['activity_level']['min'],
                    'severity' => 'medium',
                    'message' => "Low activity level: {$steps} steps"
                ];
            }
        }
    }
    
    // Check sleep quality alerts
    if (isset($healthData['sleep'])) {
        $sleep = $healthData['sleep'];
        
        if (isset($sleep['quality'])) {
            $quality = $sleep['quality'];
            if ($quality < HEALTH_ALERT_THRESHOLDS['sleep_quality']['min']) {
                $alerts[] = [
                    'type' => 'sleep_alert',
                    'metric' => 'sleep_quality',
                    'value' => $quality,
                    'threshold' => HEALTH_ALERT_THRESHOLDS['sleep_quality']['min'],
                    'severity' => 'medium',
                    'message' => "Poor sleep quality: {$quality}%"
                ];
            }
        }
    }
    
    return $alerts;
}

/**
 * Generate health recommendations
 */
function generateHealthRecommendations($catId, $healthProfile, $recentData) {
    $recommendations = [];
    
    // Analyze recent data for patterns
    $patterns = analyzeHealthPatterns($recentData);
    
    // Generate recommendations based on patterns
    foreach ($patterns as $pattern) {
        switch ($pattern['type']) {
            case 'low_activity':
                $recommendations[] = [
                    'type' => 'activity',
                    'priority' => 'medium',
                    'title' => 'Increase Activity Level',
                    'description' => 'Your cat has been less active than usual. Consider increasing playtime and exercise.',
                    'suggestions' => [
                        'Schedule 15-20 minutes of playtime twice daily',
                        'Introduce new toys or interactive games',
                        'Create climbing opportunities with cat trees'
                    ]
                ];
                break;
                
            case 'poor_sleep':
                $recommendations[] = [
                    'type' => 'sleep',
                    'priority' => 'medium',
                    'title' => 'Improve Sleep Quality',
                    'description' => 'Your cat\'s sleep quality has decreased. Consider environmental improvements.',
                    'suggestions' => [
                        'Ensure quiet, dark sleeping environment',
                        'Provide comfortable, elevated sleeping spots',
                        'Maintain consistent daily routine'
                    ]
                ];
                break;
                
            case 'weight_gain':
                $recommendations[] = [
                    'type' => 'nutrition',
                    'priority' => 'high',
                    'title' => 'Monitor Weight Management',
                    'description' => 'Your cat has gained weight. Consider dietary adjustments.',
                    'suggestions' => [
                        'Consult with veterinarian about diet',
                        'Measure food portions carefully',
                        'Increase physical activity'
                    ]
                ];
                break;
                
            case 'stress_indicator':
                $recommendations[] = [
                    'type' => 'behavior',
                    'priority' => 'medium',
                    'title' => 'Reduce Stress Levels',
                    'description' => 'Your cat shows signs of stress. Consider environmental enrichment.',
                    'suggestions' => [
                        'Provide hiding spots and safe spaces',
                        'Use pheromone diffusers',
                        'Maintain consistent routine'
                    ]
                ];
                break;
        }
    }
    
    return $recommendations;
}

/**
 * Validate health device
 */
function validateHealthDevice($deviceData) {
    $validated = [];
    
    // Required fields
    if (empty($deviceData['device_id'])) {
        throw new Exception('Device ID is required', 400);
    }
    
    if (empty($deviceData['device_type'])) {
        throw new Exception('Device type is required', 400);
    }
    
    if (!array_key_exists($deviceData['device_type'], HEALTH_DEVICE_TYPES)) {
        throw new Exception('Invalid device type', 400);
    }
    
    // Optional fields with defaults
    $validated['device_id'] = $deviceData['device_id'];
    $validated['device_type'] = $deviceData['device_type'];
    $validated['device_name'] = $deviceData['device_name'] ?? 'Health Monitor';
    $validated['manufacturer'] = $deviceData['manufacturer'] ?? 'Unknown';
    $validated['model'] = $deviceData['model'] ?? 'Unknown';
    $validated['firmware_version'] = $deviceData['firmware_version'] ?? '1.0.0';
    $validated['battery_level'] = $deviceData['battery_level'] ?? 100;
    $validated['last_sync'] = $deviceData['last_sync'] ?? time();
    
    return $validated;
}

/**
 * Validate health data
 */
function validateHealthData($healthData, $deviceType) {
    // Basic validation
    if (!is_array($healthData)) {
        throw new Exception('Health data must be an array', 400);
    }
    
    if (empty($healthData)) {
        throw new Exception('Health data cannot be empty', 400);
    }
    
    // Device-specific validation
    $deviceCapabilities = HEALTH_DEVICE_TYPES[$deviceType]['capabilities'];
    
    foreach ($healthData as $key => $value) {
        // Check if the metric is supported by the device
        if (!in_array($key, $deviceCapabilities) && !in_array($key, ['timestamp', 'device_id'])) {
            throw new Exception("Metric '$key' not supported by device type '$deviceType'", 400);
        }
    }
    
    return $healthData;
}

/**
 * Check if user can monitor cat health
 */
function canMonitorCatHealth($userId, $catId) {
    // Check cat ownership
    $cat = getCatById($catId);
    if (!$cat || $cat['owner_id'] !== $userId) {
        return false;
    }
    
    // Check if user has health monitoring permissions
    $user = getUserById($userId);
    if ($user['level'] < 5) {
        return false;
    }
    
    return true;
}

/**
 * Check if device is already registered
 */
function isDeviceAlreadyRegistered($deviceId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM health_devices 
        WHERE device_id = ? AND active = 1
    ");
    
    $stmt->execute([$deviceId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] > 0;
}

/**
 * Create device registration
 */
function createDeviceRegistration($userId, $catId, $deviceData) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO health_devices 
        (user_id, cat_id, device_id, device_type, device_name, manufacturer, 
         model, firmware_version, battery_level, last_sync, active, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
    ");
    
    $stmt->execute([
        $userId,
        $catId,
        $deviceData['device_id'],
        $deviceData['device_type'],
        $deviceData['device_name'],
        $deviceData['manufacturer'],
        $deviceData['model'],
        $deviceData['firmware_version'],
        $deviceData['battery_level'],
        $deviceData['last_sync'],
        date('Y-m-d H:i:s')
    ]);
    
    return $pdo->lastInsertId();
}

/**
 * Get registered device
 */
function getRegisteredDevice($deviceId) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM health_devices WHERE device_id = ?
    ");
    
    $stmt->execute([$deviceId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Store health data
 */
function storeHealthData($catId, $deviceId, $healthData) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO cat_health_data 
        (cat_id, device_id, data_type, data_value, raw_data, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $catId,
        $deviceId,
        json_encode(array_keys($healthData)),
        json_encode($healthData),
        json_encode($healthData),
        date('Y-m-d H:i:s')
    ]);
    
    return $pdo->lastInsertId();
}

/**
 * Log health event
 */
function logHealthEvent($eventType, $eventId, $userId, $catId, $details = null) {
    $pdo = get_db();
    
    $stmt = $pdo->prepare("
        INSERT INTO health_events 
        (event_type, event_id, user_id, cat_id, details, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $eventType,
        $eventId,
        $userId,
        $catId,
        $details ? json_encode($details) : null,
        date('Y-m-d H:i:s')
    ]);
}
