<?php
/**
 * 🔗 Purrr.love Webhook System
 * Real-time notifications, integrations, and third-party connectivity
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Webhook event types
 */
define('WEBHOOK_EVENTS', [
    'cat.created' => 'Cat created',
    'cat.updated' => 'Cat updated',
    'cat.deleted' => 'Cat deleted',
    'user.registered' => 'User registered',
    'user.login' => 'User login',
    'game.played' => 'Game played',
    'transaction.completed' => 'Transaction completed',
    'breeding.started' => 'Breeding started',
    'breeding.completed' => 'Breeding completed',
    'quest.completed' => 'Quest completed',
    'achievement.unlocked' => 'Achievement unlocked',
    'night_watch.deployed' => 'Night watch deployed',
    'night_watch.encounter' => 'Night watch encounter',
    'api_key.created' => 'API key created',
    'api_key.revoked' => 'API key revoked',
    'rate_limit.exceeded' => 'Rate limit exceeded',
    'security.alert' => 'Security alert',
    'system.maintenance' => 'System maintenance'
]);

/**
 * Webhook delivery statuses
 */
define('WEBHOOK_STATUSES', [
    'pending' => 'Pending delivery',
    'delivered' => 'Successfully delivered',
    'failed' => 'Delivery failed',
    'retrying' => 'Retrying delivery',
    'cancelled' => 'Cancelled'
]);

/**
 * Webhook System Class
 */
class WebhookSystem {
    private $pdo;
    private $maxRetries = 3;
    private $retryDelay = 300; // 5 minutes
    
    public function __construct() {
        $this->pdo = get_db();
    }
    
    /**
     * Create webhook subscription
     */
    public function createWebhook($userId, $url, $events, $secret = null, $headers = []) {
        try {
            // Validate URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new Exception('Invalid webhook URL');
            }
            
            // Validate events
            $validEvents = array_keys(WEBHOOK_EVENTS);
            foreach ($events as $event) {
                if (!in_array($event, $validEvents)) {
                    throw new Exception("Invalid event type: $event");
                }
            }
            
            // Generate secret if not provided
            if (!$secret) {
                $secret = bin2hex(random_bytes(32));
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO webhook_subscriptions 
                (user_id, url, events, secret, headers, active, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, true, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $url,
                json_encode($events),
                $secret,
                json_encode($headers),
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $webhookId = $this->pdo->lastInsertId();
            
            // Log webhook creation
            logSecurityEvent('webhook_created', [
                'user_id' => $userId,
                'webhook_id' => $webhookId,
                'url' => $url,
                'events' => $events
            ]);
            
            return [
                'id' => $webhookId,
                'url' => $url,
                'events' => $events,
                'secret' => $secret,
                'headers' => $headers
            ];
            
        } catch (Exception $e) {
            error_log("Error creating webhook: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update webhook subscription
     */
    public function updateWebhook($webhookId, $userId, $data) {
        try {
            // Verify ownership
            $stmt = $this->pdo->prepare("
                SELECT id FROM webhook_subscriptions 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$webhookId, $userId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Webhook not found or access denied');
            }
            
            $updates = [];
            $params = [];
            
            if (isset($data['url'])) {
                if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
                    throw new Exception('Invalid webhook URL');
                }
                $updates[] = 'url = ?';
                $params[] = $data['url'];
            }
            
            if (isset($data['events'])) {
                $validEvents = array_keys(WEBHOOK_EVENTS);
                foreach ($data['events'] as $event) {
                    if (!in_array($event, $validEvents)) {
                        throw new Exception("Invalid event type: $event");
                    }
                }
                $updates[] = 'events = ?';
                $params[] = json_encode($data['events']);
            }
            
            if (isset($data['headers'])) {
                $updates[] = 'headers = ?';
                $params[] = json_encode($data['headers']);
            }
            
            if (isset($data['active'])) {
                $updates[] = 'active = ?';
                $params[] = $data['active'];
            }
            
            if (empty($updates)) {
                throw new Exception('No valid updates provided');
            }
            
            $updates[] = 'updated_at = ?';
            $params[] = date('Y-m-d H:i:s');
            $params[] = $webhookId;
            
            $sql = "UPDATE webhook_subscriptions SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            // Log webhook update
            logSecurityEvent('webhook_updated', [
                'user_id' => $userId,
                'webhook_id' => $webhookId,
                'updates' => $data
            ]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error updating webhook: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Delete webhook subscription
     */
    public function deleteWebhook($webhookId, $userId) {
        try {
            // Verify ownership
            $stmt = $this->pdo->prepare("
                SELECT id FROM webhook_subscriptions 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$webhookId, $userId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Webhook not found or access denied');
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM webhook_subscriptions WHERE id = ?");
            $stmt->execute([$webhookId]);
            
            // Log webhook deletion
            logSecurityEvent('webhook_deleted', [
                'user_id' => $userId,
                'webhook_id' => $webhookId
            ]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error deleting webhook: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get user's webhook subscriptions
     */
    public function getUserWebhooks($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, url, events, headers, active, created_at, updated_at
                FROM webhook_subscriptions 
                WHERE user_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            
            $webhooks = [];
            while ($row = $stmt->fetch()) {
                $webhooks[] = [
                    'id' => $row['id'],
                    'url' => $row['url'],
                    'events' => json_decode($row['events'], true),
                    'headers' => json_decode($row['headers'], true),
                    'active' => (bool)$row['active'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at']
                ];
            }
            
            return $webhooks;
            
        } catch (Exception $e) {
            error_log("Error getting user webhooks: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Trigger webhook event
     */
    public function triggerEvent($eventType, $data, $userId = null) {
        try {
            if (!isset(WEBHOOK_EVENTS[$eventType])) {
                throw new Exception("Invalid event type: $eventType");
            }
            
            // Get active webhooks for this event
            $stmt = $this->pdo->prepare("
                SELECT id, url, secret, headers, user_id
                FROM webhook_subscriptions 
                WHERE active = true AND events::jsonb ? ?
            ");
            $stmt->execute([$eventType]);
            
            $webhooks = $stmt->fetchAll();
            
            foreach ($webhooks as $webhook) {
                // Skip if user-specific and doesn't match
                if ($userId && $webhook['user_id'] != $userId) {
                    continue;
                }
                
                $this->queueWebhookDelivery($webhook['id'], $eventType, $data);
            }
            
            // Log event trigger
            logSecurityEvent('webhook_event_triggered', [
                'event_type' => $eventType,
                'user_id' => $userId,
                'webhooks_queued' => count($webhooks)
            ]);
            
            return count($webhooks);
            
        } catch (Exception $e) {
            error_log("Error triggering webhook event: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Queue webhook delivery
     */
    private function queueWebhookDelivery($webhookId, $eventType, $data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO webhook_deliveries 
                (webhook_id, event_type, payload, status, attempts, created_at)
                VALUES (?, ?, ?, 'pending', 0, ?)
            ");
            
            $stmt->execute([
                $webhookId,
                $eventType,
                json_encode($data),
                date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Error queuing webhook delivery: " . $e->getMessage());
        }
    }
    
    /**
     * Process webhook delivery queue
     */
    public function processDeliveryQueue() {
        try {
            // Get pending deliveries
            $stmt = $this->pdo->prepare("
                SELECT 
                    wd.id,
                    wd.webhook_id,
                    wd.event_type,
                    wd.payload,
                    wd.attempts,
                    ws.url,
                    ws.secret,
                    ws.headers
                FROM webhook_deliveries wd
                JOIN webhook_subscriptions ws ON wd.webhook_id = ws.id
                WHERE wd.status IN ('pending', 'retrying')
                AND wd.attempts < ?
                ORDER BY wd.created_at ASC
                LIMIT 100
            ");
            $stmt->execute([$this->maxRetries]);
            
            $deliveries = $stmt->fetchAll();
            $processed = 0;
            
            foreach ($deliveries as $delivery) {
                $success = $this->deliverWebhook($delivery);
                
                if ($success) {
                    $this->updateDeliveryStatus($delivery['id'], 'delivered');
                } else {
                    $attempts = $delivery['attempts'] + 1;
                    $status = $attempts >= $this->maxRetries ? 'failed' : 'retrying';
                    $this->updateDeliveryStatus($delivery['id'], $status, $attempts);
                }
                
                $processed++;
                
                // Small delay to prevent overwhelming external services
                usleep(100000); // 0.1 seconds
            }
            
            return $processed;
            
        } catch (Exception $e) {
            error_log("Error processing webhook delivery queue: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Deliver webhook to external URL
     */
    private function deliverWebhook($delivery) {
        try {
            $payload = json_decode($delivery['payload'], true);
            $headers = json_decode($delivery['headers'], true) ?: [];
            
            // Add webhook signature
            $timestamp = time();
            $signature = $this->generateSignature($delivery['secret'], $payload, $timestamp);
            
            $headers['Content-Type'] = 'application/json';
            $headers['X-Webhook-Signature'] = $signature;
            $headers['X-Webhook-Timestamp'] = $timestamp;
            $headers['X-Webhook-Event'] = $delivery['event_type'];
            $headers['User-Agent'] = 'Purrr.love-Webhook/1.0';
            
            // Prepare cURL request
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $delivery['url'],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_MAXREDIRS => 0
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                error_log("cURL error for webhook delivery {$delivery['id']}: $error");
                return false;
            }
            
            // Consider 2xx status codes as success
            $success = $httpCode >= 200 && $httpCode < 300;
            
            // Log delivery attempt
            $this->logDeliveryAttempt($delivery['id'], $httpCode, $response, $error);
            
            return $success;
            
        } catch (Exception $e) {
            error_log("Error delivering webhook {$delivery['id']}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate webhook signature
     */
    private function generateSignature($secret, $payload, $timestamp) {
        $data = $timestamp . '.' . json_encode($payload);
        return hash_hmac('sha256', $data, $secret);
    }
    
    /**
     * Format headers for cURL
     */
    private function formatHeaders($headers) {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = "$key: $value";
        }
        return $formatted;
    }
    
    /**
     * Update delivery status
     */
    private function updateDeliveryStatus($deliveryId, $status, $attempts = null) {
        try {
            $sql = "UPDATE webhook_deliveries SET status = ?, updated_at = ?";
            $params = [$status, date('Y-m-d H:i:s')];
            
            if ($attempts !== null) {
                $sql .= ", attempts = ?";
                $params[] = $attempts;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $deliveryId;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
        } catch (Exception $e) {
            error_log("Error updating delivery status: " . $e->getMessage());
        }
    }
    
    /**
     * Log delivery attempt
     */
    private function logDeliveryAttempt($deliveryId, $httpCode, $response, $error) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE webhook_deliveries 
                SET last_response_code = ?, last_response_body = ?, last_error = ?, updated_at = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $httpCode,
                substr($response, 0, 1000), // Limit response body
                $error,
                date('Y-m-d H:i:s'),
                $deliveryId
            ]);
            
        } catch (Exception $e) {
            error_log("Error logging delivery attempt: " . $e->getMessage());
        }
    }
    
    /**
     * Get webhook delivery statistics
     */
    public function getDeliveryStats($webhookId = null) {
        try {
            $sql = "
                SELECT 
                    status,
                    COUNT(*) as count,
                    AVG(attempts) as avg_attempts
                FROM webhook_deliveries
            ";
            $params = [];
            
            if ($webhookId) {
                $sql .= " WHERE webhook_id = ?";
                $params[] = $webhookId;
            }
            
            $sql .= " GROUP BY status";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $stats = [];
            while ($row = $stmt->fetch()) {
                $stats[$row['status']] = [
                    'count' => (int)$row['count'],
                    'avg_attempts' => round($row['avg_attempts'], 2)
                ];
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error getting delivery stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Retry failed deliveries
     */
    public function retryFailedDeliveries($webhookId = null) {
        try {
            $sql = "
                UPDATE webhook_deliveries 
                SET status = 'retrying', updated_at = ?
                WHERE status = 'failed'
            ";
            $params = [date('Y-m-d H:i:s')];
            
            if ($webhookId) {
                $sql .= " AND webhook_id = ?";
                $params[] = $webhookId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();
            
        } catch (Exception $e) {
            error_log("Error retrying failed deliveries: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Clean up old webhook deliveries
     */
    public function cleanupOldDeliveries($days = 30) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM webhook_deliveries 
                WHERE created_at < NOW() - INTERVAL ? DAY
                AND status IN ('delivered', 'failed')
            ");
            
            $stmt->execute([$days]);
            
            return $stmt->rowCount();
            
        } catch (Exception $e) {
            error_log("Error cleaning up old deliveries: " . $e->getMessage());
            return 0;
        }
    }
}

/**
 * Global webhook system instance
 */
$globalWebhookSystem = new WebhookSystem();

/**
 * Webhook wrapper functions
 */
function createWebhook($userId, $url, $events, $secret = null, $headers = []) {
    global $globalWebhookSystem;
    return $globalWebhookSystem->createWebhook($userId, $url, $events, $secret, $headers);
}

function updateWebhook($webhookId, $userId, $data) {
    global $globalWebhookSystem;
    return $globalWebhookSystem->updateWebhook($webhookId, $userId, $data);
}

function deleteWebhook($webhookId, $userId) {
    global $globalWebhookSystem;
    return $globalWebhookSystem->deleteWebhook($webhookId, $userId);
}

function getUserWebhooks($userId) {
    global $globalWebhookSystem;
    return $globalWebhookSystem->getUserWebhooks($userId);
}

function triggerWebhookEvent($eventType, $data, $userId = null) {
    global $globalWebhookSystem;
    return $globalWebhookSystem->triggerEvent($eventType, $data, $userId);
}

function processWebhookQueue() {
    global $globalWebhookSystem;
    return $globalWebhookSystem->processDeliveryQueue();
}

function getWebhookStats($webhookId = null) {
    global $globalWebhookSystem;
    return $globalWebhookSystem->getDeliveryStats($webhookId);
}

function retryFailedWebhooks($webhookId = null) {
    global $globalWebhookSystem;
    return $globalWebhookSystem->retryFailedDeliveries($webhookId);
}

function cleanupWebhooks($days = 30) {
    global $globalWebhookSystem;
    return $globalWebhookSystem->cleanupOldDeliveries($days);
}
?>
