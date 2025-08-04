// Simple service worker for Hi-Time notifications
// This handles basic caching and can be extended for offline functionality

const CACHE_NAME = 'hi-time-v1';
const urlsToCache = [
  '/',
  '/favicon.ico'
];

self.addEventListener('install', function(event) {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        if (response) {
          return response;
        }
        return fetch(event.request);
      }
    )
  );
});

// Handle notifications (currently using browser notifications instead)
self.addEventListener('notificationclick', function(event) {
  event.notification.close();

  event.waitUntil(
    clients.openWindow('/')
  );
});
