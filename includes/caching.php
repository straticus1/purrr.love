<?php
/**
 * 🚀 Purrr.love Caching System
 * High-performance caching with Redis backend and multiple strategies
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Main caching class with Redis backend
 */
class PurrrCache {
    private $redis;
    private $config;
    private $prefix;
    private $defaultTTL;
    
    public function __construct() {
        $this->config = [
            'enabled' => getConfig('api.caching_enabled', true),
            'default_ttl' => getConfig('api.cache_ttl', 300),
            'prefix' => 'purrr:',
            'compression' => true,
            'serialization' => 'json'
        ];
        
        $this->prefix = $this->config['prefix'];
        $this->defaultTTL = $this->config['default_ttl'];
        
        $this->initializeRedis();
    }
    
    /**
     * Initialize Redis connection
     */
    private function initializeRedis() {
        try {
            $this->redis = new Redis();
            $this->redis->connect(
                getConfig('redis.host', 'localhost'),
                getConfig('redis.port', 6379),
                getConfig('redis.timeout', 5)
            );
            
            if (getConfig('redis.password')) {
                $this->redis->auth(getConfig('redis.password'));
            }
            
            $this->redis->select(getConfig('redis.database', 0));
            
            // Test connection
            $this->redis->ping();
            
        } catch (Exception $e) {
            error_log("Redis connection failed: " . $e->getMessage());
            $this->redis = null;
        }
    }
    
    /**
     * Set cache value
     */
    public function set($key, $value, $ttl = null, $tags = []) {
        if (!$this->redis || !$this->config['enabled']) {
            return false;
        }
        
        $ttl = $ttl ?: $this->defaultTTL;
        $fullKey = $this->prefix . $key;
        
        try {
            // Serialize and compress value
            $serializedValue = $this->serializeValue($value);
            
            // Store value
            $result = $this->redis->setex($fullKey, $ttl, $serializedValue);
            
            // Store tags if provided
            if (!empty($tags)) {
                $this->storeTags($fullKey, $tags);
            }
            
            // Log cache operation
            logSecurityEvent('cache_set', [
                'key' => $key,
                'ttl' => $ttl,
                'tags' => $tags,
                'size' => strlen($serializedValue)
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Cache set error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cache value
     */
    public function get($key) {
        if (!$this->redis || !$this->config['enabled']) {
            return null;
        }
        
        $fullKey = $this->prefix . $key;
        
        try {
            $value = $this->redis->get($fullKey);
            
            if ($value === false) {
                return null;
            }
            
            // Deserialize value
            $deserializedValue = $this->deserializeValue($value);
            
            // Log cache hit
            logSecurityEvent('cache_hit', [
                'key' => $key,
                'size' => strlen($value)
            ]);
            
            return $deserializedValue;
            
        } catch (Exception $e) {
            error_log("Cache get error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Delete cache value
     */
    public function delete($key) {
        if (!$this->redis || !$this->config['enabled']) {
            return false;
        }
        
        $fullKey = $this->prefix . $key;
        
        try {
            $result = $this->redis->del($fullKey);
            
            // Remove from tags
            $this->removeFromTags($fullKey);
            
            // Log cache deletion
            logSecurityEvent('cache_delete', ['key' => $key]);
            
            return $result > 0;
            
        } catch (Exception $e) {
            error_log("Cache delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if key exists
     */
    public function exists($key) {
        if (!$this->redis || !$this->config['enabled']) {
            return false;
        }
        
        $fullKey = $this->prefix . $key;
        
        try {
            return $this->redis->exists($fullKey);
        } catch (Exception $e) {
            error_log("Cache exists error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set multiple values
     */
    public function setMultiple($items, $ttl = null) {
        if (!$this->redis || !$this->config['enabled']) {
            return false;
        }
        
        $ttl = $ttl ?: $this->defaultTTL;
        $pipeline = $this->redis->multi(Redis::PIPELINE);
        
        try {
            foreach ($items as $key => $value) {
                $fullKey = $this->prefix . $key;
                $serializedValue = $this->serializeValue($value);
                $pipeline->setex($fullKey, $ttl, $serializedValue);
            }
            
            $results = $pipeline->exec();
            
            // Log bulk operation
            logSecurityEvent('cache_set_multiple', [
                'count' => count($items),
                'ttl' => $ttl
            ]);
            
            return !in_array(false, $results);
            
        } catch (Exception $e) {
            error_log("Cache set multiple error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get multiple values
     */
    public function getMultiple($keys) {
        if (!$this->redis || !$this->config['enabled']) {
            return array_fill_keys($keys, null);
        }
        
        $fullKeys = array_map(function($key) {
            return $this->prefix . $key;
        }, $keys);
        
        try {
            $values = $this->redis->mget($fullKeys);
            $result = [];
            
            foreach ($keys as $index => $key) {
                $value = $values[$index];
                $result[$key] = $value !== false ? $this->deserializeValue($value) : null;
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Cache get multiple error: " . $e->getMessage());
            return array_fill_keys($keys, null);
        }
    }
    
    /**
     * Delete multiple values
     */
    public function deleteMultiple($keys) {
        if (!$this->redis || !$this->config['enabled']) {
            return false;
        }
        
        $fullKeys = array_map(function($key) {
            return $this->prefix . $key;
        }, $keys);
        
        try {
            $result = $this->redis->del($fullKeys);
            
            // Remove from tags
            foreach ($fullKeys as $fullKey) {
                $this->removeFromTags($fullKey);
            }
            
            // Log bulk deletion
            logSecurityEvent('cache_delete_multiple', ['count' => count($keys)]);
            
            return $result > 0;
            
        } catch (Exception $e) {
            error_log("Cache delete multiple error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Increment counter
     */
    public function increment($key, $value = 1, $ttl = null) {
        if (!$this->redis || !$this->config['enabled']) {
            return false;
        }
        
        $fullKey = $this->prefix . $key;
        
        try {
            $result = $this->redis->incrBy($fullKey, $value);
            
            // Set TTL if provided
            if ($ttl) {
                $this->redis->expire($fullKey, $ttl);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Cache increment error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Decrement counter
     */
    public function decrement($key, $value = 1, $ttl = null) {
        if (!$this->redis || !$this->config['enabled']) {
            return false;
        }
        
        $fullKey = $this->prefix . $key;
        
        try {
            $result = $this->redis->decrBy($fullKey, $value);
            
            // Set TTL if provided
            if ($ttl) {
                $this->redis->expire($fullKey, $ttl);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Cache decrement error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set with tags
     */
    public function setWithTags($key, $value, $tags, $ttl = null) {
        return $this->set($key, $value, $ttl, $tags);
    }
    
    /**
     * Delete by tags
     */
    public function deleteByTags($tags) {
        if (!$this->redis || !$this->config['enabled']) {
            return false;
        }
        
        try {
            $keysToDelete = [];
            
            foreach ($tags as $tag) {
                $tagKey = $this->prefix . "tag:{$tag}";
                $keys = $this->redis->smembers($tagKey);
                
                foreach ($keys as $key) {
                    $keysToDelete[] = $key;
                }
            }
            
            if (!empty($keysToDelete)) {
                $this->redis->del($keysToDelete);
                
                // Clean up tag sets
                foreach ($tags as $tag) {
                    $tagKey = $this->prefix . "tag:{$tag}";
                    $this->redis->del($tagKey);
                }
            }
            
            // Log tag-based deletion
            logSecurityEvent('cache_delete_by_tags', [
                'tags' => $tags,
                'keys_deleted' => count($keysToDelete)
            ]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Cache delete by tags error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear all cache
     */
    public function clear() {
        if (!$this->redis || !$this->config['enabled']) {
            return false;
        }
        
        try {
            $keys = $this->redis->keys($this->prefix . "*");
            
            if (!empty($keys)) {
                $this->redis->del($keys);
            }
            
            // Log cache clear
            logSecurityEvent('cache_clear', ['keys_cleared' => count($keys)]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Cache clear error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cache statistics
     */
    public function getStats() {
        if (!$this->redis || !$this->config['enabled']) {
            return [];
        }
        
        try {
            $info = $this->redis->info();
            $keys = $this->redis->keys($this->prefix . "*");
            
            return [
                'redis_version' => $info['redis_version'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory' => $info['used_memory'] ?? 0,
                'used_memory_human' => $info['used_memory_human'] ?? '0B',
                'total_keys' => count($keys),
                'cache_prefix' => $this->prefix,
                'default_ttl' => $this->defaultTTL,
                'compression_enabled' => $this->config['compression'],
                'serialization' => $this->config['serialization']
            ];
            
        } catch (Exception $e) {
            error_log("Cache stats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Store tags for a key
     */
    private function storeTags($fullKey, $tags) {
        try {
            foreach ($tags as $tag) {
                $tagKey = $this->prefix . "tag:{$tag}";
                $this->redis->sadd($tagKey, $fullKey);
                $this->redis->expire($tagKey, $this->defaultTTL + 3600); // Tag TTL + 1 hour
            }
        } catch (Exception $e) {
            error_log("Failed to store cache tags: " . $e->getMessage());
        }
    }
    
    /**
     * Remove key from tags
     */
    private function removeFromTags($fullKey) {
        try {
            $pattern = $this->prefix . "tag:*";
            $tagKeys = $this->redis->keys($pattern);
            
            foreach ($tagKeys as $tagKey) {
                $this->redis->srem($tagKey, $fullKey);
            }
        } catch (Exception $e) {
            error_log("Failed to remove key from tags: " . $e->getMessage());
        }
    }
    
    /**
     * Serialize value
     */
    private function serializeValue($value) {
        if ($this->config['serialization'] === 'json') {
            $serialized = json_encode($value);
        } else {
            $serialized = serialize($value);
        }
        
        if ($this->config['compression'] && strlen($serialized) > 1024) {
            $serialized = gzcompress($serialized, 6);
        }
        
        return $serialized;
    }
    
    /**
     * Deserialize value
     */
    private function deserializeValue($value) {
        if ($this->config['compression'] && $this->isCompressed($value)) {
            $value = gzuncompress($value);
        }
        
        if ($this->config['serialization'] === 'json') {
            return json_decode($value, true);
        } else {
            return unserialize($value);
        }
    }
    
    /**
     * Check if value is compressed
     */
    private function isCompressed($value) {
        return substr($value, 0, 2) === "\x1f\x8b";
    }
}

/**
 * Global cache instance
 */
$globalCache = new PurrrCache();

/**
 * Cache wrapper functions
 */
function cacheSet($key, $value, $ttl = null, $tags = []) {
    global $globalCache;
    return $globalCache->set($key, $value, $ttl, $tags);
}

function cacheGet($key) {
    global $globalCache;
    return $globalCache->get($key);
}

function cacheDelete($key) {
    global $globalCache;
    return $globalCache->delete($key);
}

function cacheExists($key) {
    global $globalCache;
    return $globalCache->exists($key);
}

function cacheSetMultiple($items, $ttl = null) {
    global $globalCache;
    return $globalCache->setMultiple($items, $ttl);
}

function cacheGetMultiple($keys) {
    global $globalCache;
    return $globalCache->getMultiple($keys);
}

function cacheDeleteMultiple($keys) {
    global $globalCache;
    return $globalCache->deleteMultiple($keys);
}

function cacheIncrement($key, $value = 1, $ttl = null) {
    global $globalCache;
    return $globalCache->increment($key, $value, $ttl);
}

function cacheDecrement($key, $value = 1, $ttl = null) {
    global $globalCache;
    return $globalCache->decrement($key, $value, $ttl);
}

function cacheSetWithTags($key, $value, $tags, $ttl = null) {
    global $globalCache;
    return $globalCache->setWithTags($key, $value, $tags, $ttl);
}

function cacheDeleteByTags($tags) {
    global $globalCache;
    return $globalCache->deleteByTags($tags);
}

function cacheClear() {
    global $globalCache;
    return $globalCache->clear();
}

function cacheGetStats() {
    global $globalCache;
    return $globalCache->getStats();
}

/**
 * Cache decorator for functions
 */
function cacheDecorator($key, $ttl = null, $tags = []) {
    return function($callback) use ($key, $ttl, $tags) {
        $cached = cacheGet($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $result = $callback();
        cacheSet($key, $result, $ttl, $tags);
        
        return $result;
    };
}

/**
 * Cache key generator
 */
function generateCacheKey($prefix, $params = []) {
    $key = $prefix;
    
    if (!empty($params)) {
        $key .= ':' . md5(serialize($params));
    }
    
    return $key;
}

    /**
     * Cache invalidation patterns
     */
     function invalidateCachePattern($pattern) {
         global $globalCache;
         
         try {
             // Use reflection to access private property
             $reflection = new ReflectionClass($globalCache);
             $redisProperty = $reflection->getProperty('redis');
             $redisProperty->setAccessible(true);
             $redis = $redisProperty->getValue($globalCache);
             
             $keys = $redis->keys($pattern);
             
             if (!empty($keys)) {
                 $redis->del($keys);
                 
                 logSecurityEvent('cache_pattern_invalidated', [
                     'pattern' => $pattern,
                     'keys_deleted' => count($keys)
                 ]);
             }
             
             return true;
             
         } catch (Exception $e) {
             error_log("Cache pattern invalidation error: " . $e->getMessage());
             return false;
         }
     }
?>
