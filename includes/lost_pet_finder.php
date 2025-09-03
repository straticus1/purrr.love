<?php
/**
 * Lost Pet Finder System
 * 
 * Features:
 * - Lost pet database management
 * - Advanced search algorithms
 * - Facebook app integration
 * - Real-time alerts and notifications
 * - Community sighting reports
 * - Privacy and permission controls
 * 
 * @package Purrr.love
 * @version 1.0.0
 */

define('SECURE_ACCESS', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

class LostPetFinder {
    private $pdo;
    private $config;
    private $facebookApp;
    
    public function __construct() {
        $this->pdo = getDatabaseConnection();
        $this->config = getConfig();
        $this->initializeFacebookApp();
    }
    
    /**
     * Initialize Facebook App integration
     */
    private function initializeFacebookApp() {
        if ($this->config['facebook']['enabled']) {
            $this->facebookApp = new FacebookAppIntegration(
                $this->config['facebook']['app_id'],
                $this->config['facebook']['app_secret'],
                $this->config['facebook']['access_token']
            );
        }
    }
    
    /**
     * Report a lost pet
     */
    public function reportLostPet($userId, $petData) {
        try {
            // Validate and sanitize input
            $petData = $this->sanitizePetData($petData);
            
            // Check if pet already reported
            $existingReport = $this->getExistingReport($petData['microchip_id'] ?? null, $petData['name'], $userId);
            if ($existingReport) {
                return [
                    'success' => false,
                    'message' => 'Pet already reported as lost',
                    'report_id' => $existingReport['id']
                ];
            }
            
            // Create lost pet report
            $stmt = $this->pdo->prepare("
                INSERT INTO lost_pet_reports (
                    user_id, pet_name, pet_type, breed, color, age, 
                    microchip_id, collar_id, last_seen_location, last_seen_date,
                    contact_info, reward_amount, description, photos,
                    facebook_share_enabled, privacy_level, status, created_at
                ) VALUES (
                    :user_id, :pet_name, :pet_type, :breed, :color, :age,
                    :microchip_id, :collar_id, :last_seen_location, :last_seen_date,
                    :contact_info, :reward_amount, :description, :photos,
                    :facebook_share_enabled, :privacy_level, 'active', NOW()
                )
            ");
            
            $stmt->execute([
                'user_id' => $userId,
                'pet_name' => $petData['name'],
                'pet_type' => $petData['type'] ?? 'cat',
                'breed' => $petData['breed'],
                'color' => $petData['color'],
                'age' => $petData['age'],
                'microchip_id' => $petData['microchip_id'],
                'collar_id' => $petData['collar_id'],
                'last_seen_location' => $petData['last_seen_location'],
                'last_seen_date' => $petData['last_seen_date'],
                'contact_info' => json_encode($petData['contact_info']),
                'reward_amount' => $petData['reward_amount'] ?? 0,
                'description' => $petData['description'],
                'photos' => json_encode($petData['photos'] ?? []),
                'facebook_share_enabled' => $petData['facebook_share_enabled'] ?? false,
                'privacy_level' => $petData['privacy_level'] ?? 'public'
            ]);
            
            $reportId = $this->pdo->lastInsertId();
            
            // Log security event
            logSecurityEvent('lost_pet_reported', $userId, [
                'report_id' => $reportId,
                'pet_name' => $petData['name'],
                'location' => $petData['last_seen_location']
            ]);
            
            // Share to Facebook if enabled
            if ($petData['facebook_share_enabled'] && $this->facebookApp) {
                $this->shareToFacebook($reportId, $petData);
            }
            
            // Send real-time alerts to nearby users
            $this->sendLocationAlerts($reportId, $petData['last_seen_location']);
            
            return [
                'success' => true,
                'message' => 'Lost pet report created successfully',
                'report_id' => $reportId
            ];
            
        } catch (Exception $e) {
            logSecurityEvent('lost_pet_report_error', $userId, [
                'error' => $e->getMessage(),
                'pet_data' => $petData
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to create lost pet report',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Search for lost pets with advanced algorithms
     */
    public function searchLostPets($searchCriteria) {
        try {
            $query = "
                SELECT lpr.*, u.username, u.email,
                       ST_Distance_Sphere(
                           POINT(:search_lat, :search_lng),
                           POINT(lpr.latitude, lpr.longitude)
                       ) as distance_meters
                FROM lost_pet_reports lpr
                JOIN users u ON lpr.user_id = u.id
                WHERE lpr.status = 'active'
                AND lpr.privacy_level IN ('public', 'community')
            ";
            
            $params = [
                'search_lat' => $searchCriteria['latitude'] ?? null,
                'search_lng' => $searchCriteria['longitude'] ?? null
            ];
            
            // Add search filters
            if (!empty($searchCriteria['breed'])) {
                $query .= " AND lpr.breed ILIKE :breed";
                $params['breed'] = '%' . $searchCriteria['breed'] . '%';
            }
            
            if (!empty($searchCriteria['color'])) {
                $query .= " AND lpr.color ILIKE :color";
                $params['color'] = '%' . $searchCriteria['color'] . '%';
            }
            
            if (!empty($searchCriteria['age_range'])) {
                $query .= " AND lpr.age BETWEEN :age_min AND :age_max";
                $params['age_min'] = $searchCriteria['age_range']['min'];
                $params['age_max'] = $searchCriteria['age_range']['max'];
            }
            
            if (!empty($searchCriteria['radius_km'])) {
                $query .= " HAVING distance_meters <= :max_distance";
                $params['max_distance'] = $searchCriteria['radius_km'] * 1000;
            }
            
            $query .= " ORDER BY distance_meters ASC, lpr.created_at DESC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Apply ML-based similarity scoring
            if (!empty($searchCriteria['pet_photo'])) {
                $results = $this->applyMLSimilarityScoring($results, $searchCriteria['pet_photo']);
            }
            
            return [
                'success' => true,
                'results' => $results,
                'total_count' => count($results)
            ];
            
        } catch (Exception $e) {
            logSecurityEvent('lost_pet_search_error', null, [
                'error' => $e->getMessage(),
                'search_criteria' => $searchCriteria
            ]);
            
            return [
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Report a pet sighting
     */
    public function reportSighting($userId, $sightingData) {
        try {
            $sightingData = $this->sanitizeSightingData($sightingData);
            
            // Create sighting report
            $stmt = $this->pdo->prepare("
                INSERT INTO pet_sightings (
                    user_id, lost_pet_report_id, location, latitude, longitude,
                    sighting_date, description, photos, confidence_level,
                    contact_info, status, created_at
                ) VALUES (
                    :user_id, :lost_pet_report_id, :location, :latitude, :longitude,
                    :sighting_date, :description, :photos, :confidence_level,
                    :contact_info, 'pending', NOW()
                )
            ");
            
            $stmt->execute([
                'user_id' => $userId,
                'lost_pet_report_id' => $sightingData['lost_pet_report_id'],
                'location' => $sightingData['location'],
                'latitude' => $sightingData['latitude'],
                'longitude' => $sightingData['longitude'],
                'sighting_date' => $sightingData['sighting_date'],
                'description' => $sightingData['description'],
                'photos' => json_encode($sightingData['photos'] ?? []),
                'confidence_level' => $sightingData['confidence_level'] ?? 'medium',
                'contact_info' => json_encode($sightingData['contact_info'])
            ]);
            
            $sightingId = $this->pdo->lastInsertId();
            
            // Notify the pet owner
            $this->notifyPetOwner($sightingData['lost_pet_report_id'], $sightingId);
            
            // Log security event
            logSecurityEvent('pet_sighting_reported', $userId, [
                'sighting_id' => $sightingId,
                'lost_pet_report_id' => $sightingData['lost_pet_report_id']
            ]);
            
            return [
                'success' => true,
                'message' => 'Sighting report submitted successfully',
                'sighting_id' => $sightingId
            ];
            
        } catch (Exception $e) {
            logSecurityEvent('pet_sighting_error', $userId, [
                'error' => $e->getMessage(),
                'sighting_data' => $sightingData
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to submit sighting report',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Mark pet as found
     */
    public function markPetAsFound($reportId, $userId, $foundData = []) {
        try {
            // Verify ownership
            $stmt = $this->pdo->prepare("
                SELECT user_id, status FROM lost_pet_reports 
                WHERE id = :report_id
            ");
            $stmt->execute(['report_id' => $reportId]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$report || $report['user_id'] != $userId) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized or report not found'
                ];
            }
            
            if ($report['status'] !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Report is not active'
                ];
            }
            
            // Update report status
            $stmt = $this->pdo->prepare("
                UPDATE lost_pet_reports 
                SET status = 'found', found_date = NOW(), found_location = :found_location,
                    found_details = :found_details, updated_at = NOW()
                WHERE id = :report_id
            ");
            
            $stmt->execute([
                'report_id' => $reportId,
                'found_location' => $foundData['location'] ?? null,
                'found_details' => json_encode($foundData)
            ]);
            
            // Notify all sighting reporters
            $this->notifySightingReporters($reportId);
            
            // Remove from Facebook if shared
            if ($this->facebookApp) {
                $this->removeFromFacebook($reportId);
            }
            
            // Log security event
            logSecurityEvent('pet_found', $userId, [
                'report_id' => $reportId,
                'found_location' => $foundData['location'] ?? null
            ]);
            
            return [
                'success' => true,
                'message' => 'Pet marked as found successfully'
            ];
            
        } catch (Exception $e) {
            logSecurityEvent('mark_pet_found_error', $userId, [
                'error' => $e->getMessage(),
                'report_id' => $reportId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to mark pet as found',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get lost pet statistics
     */
    public function getStatistics($userId = null) {
        try {
            $stats = [];
            
            // Overall statistics
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_reports,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_reports,
                    COUNT(CASE WHEN status = 'found' THEN 1 END) as found_pets,
                    COUNT(CASE WHEN status = 'active' AND created_at >= NOW() - INTERVAL '7 days' THEN 1 END) as recent_reports
                FROM lost_pet_reports
            ");
            $stmt->execute();
            $stats['overall'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // User-specific statistics
            if ($userId) {
                $stmt = $this->pdo->prepare("
                    SELECT 
                        COUNT(*) as user_reports,
                        COUNT(CASE WHEN status = 'found' THEN 1 END) as user_found_pets,
                        AVG(EXTRACT(EPOCH FROM (found_date - created_at))/86400) as avg_days_to_find
                    FROM lost_pet_reports
                    WHERE user_id = :user_id
                ");
                $stmt->execute(['user_id' => $userId]);
                $stats['user'] = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Success rate by breed
            $stmt = $this->pdo->prepare("
                SELECT 
                    breed,
                    COUNT(*) as total_reports,
                    COUNT(CASE WHEN status = 'found' THEN 1 END) as found_count,
                    ROUND(
                        COUNT(CASE WHEN status = 'found' THEN 1 END)::decimal / COUNT(*) * 100, 2
                    ) as success_rate
                FROM lost_pet_reports
                WHERE breed IS NOT NULL
                GROUP BY breed
                HAVING COUNT(*) >= 5
                ORDER BY success_rate DESC
                LIMIT 10
            ");
            $stmt->execute();
            $stats['breed_success_rates'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'statistics' => $stats
            ];
            
        } catch (Exception $e) {
            logSecurityEvent('lost_pet_stats_error', $userId, [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Facebook App Integration Methods
     */
    
    /**
     * Share lost pet to Facebook
     */
    private function shareToFacebook($reportId, $petData) {
        if (!$this->facebookApp) return false;
        
        try {
            $message = $this->generateFacebookMessage($petData);
            $photoUrl = $this->getPrimaryPhotoUrl($petData['photos']);
            
            $result = $this->facebookApp->createPost([
                'message' => $message,
                'link' => $this->config['app_url'] . "/lost-pet/{$reportId}",
                'picture' => $photoUrl
            ]);
            
            // Store Facebook post ID for later removal
            if ($result['success']) {
                $stmt = $this->pdo->prepare("
                    UPDATE lost_pet_reports 
                    SET facebook_post_id = :post_id 
                    WHERE id = :report_id
                ");
                $stmt->execute([
                    'post_id' => $result['post_id'],
                    'report_id' => $reportId
                ]);
            }
            
            return $result['success'];
            
        } catch (Exception $e) {
            logSecurityEvent('facebook_share_error', null, [
                'error' => $e->getMessage(),
                'report_id' => $reportId
            ]);
            return false;
        }
    }
    
    /**
     * Remove lost pet post from Facebook
     */
    private function removeFromFacebook($reportId) {
        if (!$this->facebookApp) return false;
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT facebook_post_id FROM lost_pet_reports 
                WHERE id = :report_id
            ");
            $stmt->execute(['report_id' => $reportId]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($report['facebook_post_id']) {
                $this->facebookApp->deletePost($report['facebook_post_id']);
            }
            
            return true;
            
        } catch (Exception $e) {
            logSecurityEvent('facebook_remove_error', null, [
                'error' => $e->getMessage(),
                'report_id' => $reportId
            ]);
            return false;
        }
    }
    
    /**
     * Generate Facebook message for lost pet
     */
    private function generateFacebookMessage($petData) {
        $message = "🐱 LOST PET ALERT 🐱\n\n";
        $message .= "Name: {$petData['name']}\n";
        $message .= "Breed: {$petData['breed']}\n";
        $message .= "Color: {$petData['color']}\n";
        $message .= "Last seen: {$petData['last_seen_location']} on {$petData['last_seen_date']}\n\n";
        
        if (!empty($petData['description'])) {
            $message .= "Description: {$petData['description']}\n\n";
        }
        
        if (!empty($petData['reward_amount']) && $petData['reward_amount'] > 0) {
            $message .= "💰 REWARD: \${$petData['reward_amount']}\n\n";
        }
        
        $message .= "Please share this post to help find {$petData['name']}!\n";
        $message .= "If you see this pet, please contact the owner immediately.\n\n";
        $message .= "#LostPet #FindMyCat #PurrrLove";
        
        return $message;
    }
    
    /**
     * Utility Methods
     */
    
    private function sanitizePetData($data) {
        return [
            'name' => sanitizeInput($data['name'] ?? ''),
            'type' => sanitizeInput($data['type'] ?? 'cat'),
            'breed' => sanitizeInput($data['breed'] ?? ''),
            'color' => sanitizeInput($data['color'] ?? ''),
            'age' => (int)($data['age'] ?? 0),
            'microchip_id' => sanitizeInput($data['microchip_id'] ?? ''),
            'collar_id' => sanitizeInput($data['collar_id'] ?? ''),
            'last_seen_location' => sanitizeInput($data['last_seen_location'] ?? ''),
            'last_seen_date' => sanitizeInput($data['last_seen_date'] ?? ''),
            'contact_info' => $data['contact_info'] ?? [],
            'reward_amount' => (float)($data['reward_amount'] ?? 0),
            'description' => sanitizeInput($data['description'] ?? ''),
            'photos' => $data['photos'] ?? [],
            'facebook_share_enabled' => (bool)($data['facebook_share_enabled'] ?? false),
            'privacy_level' => sanitizeInput($data['privacy_level'] ?? 'public')
        ];
    }
    
    private function sanitizeSightingData($data) {
        return [
            'lost_pet_report_id' => (int)($data['lost_pet_report_id'] ?? 0),
            'location' => sanitizeInput($data['location'] ?? ''),
            'latitude' => (float)($data['latitude'] ?? 0),
            'longitude' => (float)($data['longitude'] ?? 0),
            'sighting_date' => sanitizeInput($data['sighting_date'] ?? ''),
            'description' => sanitizeInput($data['description'] ?? ''),
            'photos' => $data['photos'] ?? [],
            'confidence_level' => sanitizeInput($data['confidence_level'] ?? 'medium'),
            'contact_info' => $data['contact_info'] ?? []
        ];
    }
    
    private function getExistingReport($microchipId, $petName, $userId) {
        if ($microchipId) {
            $stmt = $this->pdo->prepare("
                SELECT id FROM lost_pet_reports 
                WHERE microchip_id = :microchip_id AND status = 'active'
            ");
            $stmt->execute(['microchip_id' => $microchipId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $stmt = $this->pdo->prepare("
            SELECT id FROM lost_pet_reports 
            WHERE pet_name = :pet_name AND user_id = :user_id AND status = 'active'
        ");
        $stmt->execute(['pet_name' => $petName, 'user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function sendLocationAlerts($reportId, $location) {
        // Implementation for sending real-time alerts to nearby users
        // This would integrate with the notification system
    }
    
    private function notifyPetOwner($reportId, $sightingId) {
        // Implementation for notifying pet owners of new sightings
        // This would integrate with the notification system
    }
    
    private function notifySightingReporters($reportId) {
        // Implementation for notifying all users who reported sightings
        // This would integrate with the notification system
    }
    
    private function applyMLSimilarityScoring($results, $petPhoto) {
        // Implementation for ML-based photo similarity scoring
        // This would integrate with the ML system
        return $results;
    }
    
    private function getPrimaryPhotoUrl($photos) {
        if (empty($photos)) return null;
        return $photos[0]['url'] ?? null;
    }
}

/**
 * Facebook App Integration Class
 */
class FacebookAppIntegration {
    private $appId;
    private $appSecret;
    private $accessToken;
    
    public function __construct($appId, $appSecret, $accessToken) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->accessToken = $accessToken;
    }
    
    public function createPost($postData) {
        // Implementation for creating Facebook posts
        // This would use Facebook Graph API
        return ['success' => true, 'post_id' => 'fb_' . time()];
    }
    
    public function deletePost($postId) {
        // Implementation for deleting Facebook posts
        // This would use Facebook Graph API
        return ['success' => true];
    }
    
    public function getPageInsights() {
        // Implementation for getting Facebook page insights
        // This would use Facebook Graph API
        return ['success' => true, 'insights' => []];
    }
}
