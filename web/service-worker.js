/**
 * 🚀 Purrr.love Service Worker
 * Progressive Web App functionality with offline support
 */

const CACHE_NAME = 'purrr-love-v2.2.0';
const OFFLINE_CACHE = 'purrr-love-offline-v1.0';
const DATA_CACHE = 'purrr-love-data-v1.0';

// Files to cache for offline functionality
const FILES_TO_CACHE = [
  '/web/dashboard.php',
  '/web/realtime-dashboard.php',
  '/web/cat-needs.php',
  '/web/offline.html',
  '/web/assets/css/app.css',
  '/web/assets/js/app.js',
  '/web/assets/icons/icon-192x192.png',
  '/web/assets/icons/icon-512x512.png',
  'https://cdn.tailwindcss.com',
  'https://cdn.jsdelivr.net/npm/chart.js',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
];

// API endpoints to cache
const API_ENDPOINTS = [
  '/web/api/',
  '/includes/',
  '/uploads/cat_photos/'
];

/**
 * Service Worker Installation
 */
self.addEventListener('install', (event) => {
  console.log('🐱 Purrr.love Service Worker installing...');
  
  event.waitUntil(
    Promise.all([
      // Cache core app files
      caches.open(CACHE_NAME).then((cache) => {
        console.log('📦 Caching app shell files');
        return cache.addAll(FILES_TO_CACHE);
      }),
      
      // Cache offline page
      caches.open(OFFLINE_CACHE).then((cache) => {
        return cache.add('/web/offline.html');
      })
    ])
  );
  
  // Skip waiting to activate immediately
  self.skipWaiting();
});

/**
 * Service Worker Activation
 */
self.addEventListener('activate', (event) => {
  console.log('🚀 Purrr.love Service Worker activating...');
  
  event.waitUntil(
    Promise.all([
      // Clean up old caches
      caches.keys().then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== CACHE_NAME && 
                cacheName !== OFFLINE_CACHE && 
                cacheName !== DATA_CACHE) {
              console.log('🗑️ Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      }),
      
      // Take control of all pages immediately
      self.clients.claim()
    ])
  );
});

/**
 * Fetch Event Handler - Network First with Cache Fallback
 */
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Handle different types of requests
  if (url.pathname.startsWith('/web/api/')) {
    // API requests - Network first, then cache
    event.respondWith(handleAPIRequest(request));
  } else if (url.pathname.endsWith('.php')) {
    // PHP pages - Network first with offline fallback
    event.respondWith(handlePageRequest(request));
  } else if (isStaticAsset(request)) {
    // Static assets - Cache first
    event.respondWith(handleStaticAsset(request));
  } else {
    // Default handling
    event.respondWith(handleDefaultRequest(request));
  }
});

/**
 * Handle API Requests
 */
async function handleAPIRequest(request) {
  try {
    // Try network first
    const networkResponse = await fetch(request);
    
    // Cache successful responses
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(DATA_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
    
  } catch (error) {
    console.log('📡 Network request failed, trying cache:', request.url);
    
    // Fallback to cache
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline data if available
    return createOfflineResponse(request);
  }
}

/**
 * Handle Page Requests
 */
async function handlePageRequest(request) {
  try {
    // Try network first
    const networkResponse = await fetch(request);
    
    // Cache the response
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
    
  } catch (error) {
    console.log('📱 Page request failed, trying cache:', request.url);
    
    // Try cache first
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline page
    return caches.match('/web/offline.html');
  }
}

/**
 * Handle Static Assets
 */
async function handleStaticAsset(request) {
  // Cache first strategy for static assets
  const cachedResponse = await caches.match(request);
  if (cachedResponse) {
    return cachedResponse;
  }
  
  try {
    const networkResponse = await fetch(request);
    
    // Cache the asset
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
    
  } catch (error) {
    console.log('🎨 Static asset failed to load:', request.url);
    return new Response('Asset not available offline', { status: 404 });
  }
}

/**
 * Default Request Handler
 */
async function handleDefaultRequest(request) {
  try {
    return await fetch(request);
  } catch (error) {
    const cachedResponse = await caches.match(request);
    return cachedResponse || new Response('Not available offline', { status: 404 });
  }
}

/**
 * Create Offline Response
 */
function createOfflineResponse(request) {
  const url = new URL(request.url);
  
  // Return cached cat data if available
  if (url.pathname.includes('/cats/')) {
    return new Response(JSON.stringify({
      success: false,
      offline: true,
      message: 'Data not available offline',
      cached_data: null
    }), {
      headers: { 'Content-Type': 'application/json' }
    });
  }
  
  return new Response('Service not available offline', { status: 503 });
}

/**
 * Check if request is for static asset
 */
function isStaticAsset(request) {
  const url = new URL(request.url);
  const staticExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2'];
  
  return staticExtensions.some(ext => url.pathname.endsWith(ext)) ||
         url.hostname === 'cdn.tailwindcss.com' ||
         url.hostname === 'cdn.jsdelivr.net' ||
         url.hostname === 'cdnjs.cloudflare.com';
}

/**
 * Background Sync for offline data
 */
self.addEventListener('sync', (event) => {
  console.log('🔄 Background sync triggered:', event.tag);
  
  if (event.tag === 'cat-data-sync') {
    event.waitUntil(syncCatData());
  } else if (event.tag === 'photo-upload-sync') {
    event.waitUntil(syncPhotoUploads());
  } else if (event.tag === 'activity-log-sync') {
    event.waitUntil(syncActivityLogs());
  }
});

/**
 * Sync Cat Data
 */
async function syncCatData() {
  try {
    console.log('📊 Syncing cat data...');
    
    // Get pending data from IndexedDB
    const pendingData = await getPendingDataFromIDB('cat-data');
    
    for (const data of pendingData) {
      try {
        const response = await fetch('/web/api/sync-cat-data.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data)
        });
        
        if (response.ok) {
          // Remove from pending queue
          await removeFromPendingQueue('cat-data', data.id);
          console.log('✅ Cat data synced successfully');
        }
      } catch (error) {
        console.error('❌ Failed to sync cat data:', error);
      }
    }
  } catch (error) {
    console.error('🔄 Background sync failed:', error);
  }
}

/**
 * Sync Photo Uploads
 */
async function syncPhotoUploads() {
  try {
    console.log('📸 Syncing photo uploads...');
    
    const pendingUploads = await getPendingDataFromIDB('photo-uploads');
    
    for (const upload of pendingUploads) {
      try {
        const formData = new FormData();
        formData.append('photo', upload.file);
        formData.append('cat_id', upload.cat_id);
        formData.append('analysis_types', JSON.stringify(upload.analysis_types));
        
        const response = await fetch('/web/api/analyze-photo.php', {
          method: 'POST',
          body: formData
        });
        
        if (response.ok) {
          await removeFromPendingQueue('photo-uploads', upload.id);
          console.log('✅ Photo uploaded and analyzed successfully');
          
          // Notify user
          showNotification('📸 Photo Analysis Complete', {
            body: 'Your cat photo has been analyzed successfully!',
            icon: '/web/assets/icons/icon-192x192.png',
            badge: '/web/assets/icons/badge-72x72.png',
            tag: 'photo-analysis-complete'
          });
        }
      } catch (error) {
        console.error('❌ Failed to sync photo upload:', error);
      }
    }
  } catch (error) {
    console.error('🔄 Photo sync failed:', error);
  }
}

/**
 * Sync Activity Logs
 */
async function syncActivityLogs() {
  try {
    console.log('🎯 Syncing activity logs...');
    
    const pendingLogs = await getPendingDataFromIDB('activity-logs');
    
    for (const log of pendingLogs) {
      try {
        const response = await fetch('/web/api/log-activity.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(log)
        });
        
        if (response.ok) {
          await removeFromPendingQueue('activity-logs', log.id);
          console.log('✅ Activity log synced successfully');
        }
      } catch (error) {
        console.error('❌ Failed to sync activity log:', error);
      }
    }
  } catch (error) {
    console.error('🔄 Activity sync failed:', error);
  }
}

/**
 * Push Notification Handler
 */
self.addEventListener('push', (event) => {
  console.log('🔔 Push notification received');
  
  let notificationData = {
    title: '🐱 Purrr.love Notification',
    body: 'You have a new update!',
    icon: '/web/assets/icons/icon-192x192.png',
    badge: '/web/assets/icons/badge-72x72.png',
    tag: 'general',
    requireInteraction: false,
    actions: [
      {
        action: 'open',
        title: 'Open App',
        icon: '/web/assets/icons/open-96x96.png'
      },
      {
        action: 'dismiss',
        title: 'Dismiss',
        icon: '/web/assets/icons/dismiss-96x96.png'
      }
    ]
  };
  
  if (event.data) {
    try {
      const data = event.data.json();
      notificationData = { ...notificationData, ...data };
    } catch (error) {
      console.error('Failed to parse push data:', error);
    }
  }
  
  event.waitUntil(
    showNotification(notificationData.title, notificationData)
  );
});

/**
 * Notification Click Handler
 */
self.addEventListener('notificationclick', (event) => {
  console.log('🔔 Notification clicked:', event.action);
  
  event.notification.close();
  
  if (event.action === 'dismiss') {
    return;
  }
  
  let urlToOpen = '/web/dashboard.php';
  
  // Handle different notification types
  const tag = event.notification.tag;
  switch (tag) {
    case 'care-reminder':
      urlToOpen = '/web/cat-needs.php';
      break;
    case 'health-alert':
      urlToOpen = '/web/realtime-dashboard.php';
      break;
    case 'photo-analysis-complete':
      urlToOpen = '/web/photo-analysis.php';
      break;
  }
  
  event.waitUntil(
    clients.matchAll({ type: 'window' }).then((clientList) => {
      // Check if app is already open
      for (const client of clientList) {
        if (client.url.includes('/web/') && 'focus' in client) {
          client.focus();
          client.postMessage({ action: 'navigate', url: urlToOpen });
          return;
        }
      }
      
      // Open new window
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen);
      }
    })
  );
});

/**
 * Message Handler for communication with main app
 */
self.addEventListener('message', (event) => {
  const { type, data } = event.data;
  
  switch (type) {
    case 'SKIP_WAITING':
      self.skipWaiting();
      break;
      
    case 'CACHE_CAT_DATA':
      cacheCatData(data);
      break;
      
    case 'QUEUE_OFFLINE_DATA':
      queueOfflineData(data);
      break;
      
    case 'REQUEST_SYNC':
      requestBackgroundSync(data.tag);
      break;
  }
});

/**
 * Utility Functions
 */

async function showNotification(title, options = {}) {
  const registration = await self.registration;
  return registration.showNotification(title, {
    icon: '/web/assets/icons/icon-192x192.png',
    badge: '/web/assets/icons/badge-72x72.png',
    ...options
  });
}

async function getPendingDataFromIDB(storeName) {
  // In a real implementation, this would use IndexedDB
  // For now, return mock data
  return [];
}

async function removeFromPendingQueue(storeName, id) {
  // Remove item from IndexedDB queue
  console.log(`Removing ${id} from ${storeName} queue`);
}

async function cacheCatData(data) {
  const cache = await caches.open(DATA_CACHE);
  const response = new Response(JSON.stringify(data));
  await cache.put('/api/cat-data', response);
}

async function queueOfflineData(data) {
  // Queue data for later sync
  console.log('Queueing offline data:', data);
  
  // Register for background sync
  if (self.registration.sync) {
    try {
      await self.registration.sync.register('cat-data-sync');
      console.log('📅 Background sync registered');
    } catch (error) {
      console.error('Failed to register background sync:', error);
    }
  }
}

async function requestBackgroundSync(tag) {
  if (self.registration.sync) {
    try {
      await self.registration.sync.register(tag);
      console.log(`📅 Background sync registered: ${tag}`);
    } catch (error) {
      console.error(`Failed to register background sync: ${tag}`, error);
    }
  }
}

/**
 * Periodic Background Sync (if supported)
 */
self.addEventListener('periodicsync', (event) => {
  console.log('⏰ Periodic sync triggered:', event.tag);
  
  if (event.tag === 'cat-wellness-check') {
    event.waitUntil(performWellnessCheck());
  }
});

async function performWellnessCheck() {
  try {
    console.log('🏥 Performing periodic wellness check...');
    
    // Check for urgent care reminders
    const response = await fetch('/web/api/wellness-check.php');
    const data = await response.json();
    
    if (data.urgent_alerts && data.urgent_alerts.length > 0) {
      for (const alert of data.urgent_alerts) {
        await showNotification('🚨 Urgent Cat Care Alert', {
          body: alert.message,
          tag: 'urgent-care',
          requireInteraction: true,
          actions: [
            { action: 'view', title: 'View Details' },
            { action: 'snooze', title: 'Remind Later' }
          ]
        });
      }
    }
  } catch (error) {
    console.error('❌ Wellness check failed:', error);
  }
}

console.log('🐱 Purrr.love Service Worker loaded successfully!');
