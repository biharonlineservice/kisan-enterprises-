/* Kisan Enterprises ERP — Progressive Web App Service Worker */
const CACHE_NAME = 'kisan-erp-v1.1';
const PRECACHE_ASSETS = [
    '/offline',
    '/manifest.json',
    '/css/app.css',
    '/js/app.js',
    '/vendor/bootstrap/bootstrap.min.css',
    '/vendor/bootstrap/bootstrap.bundle.min.js',
    '/vendor/bootstrap-icons/bootstrap-icons.min.css',
    '/icons/icon-192.png',
    '/icons/icon-512.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                console.warn('Some pre-cache assets failed to load:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    // NEVER intercept non-GET requests (POST, PUT, DELETE) - must go to live server!
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Navigation requests (HTML pages) -> Network-First with Offline fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match('/offline').then((response) => {
                    return response || new Response('Offline', { status: 503, statusText: 'Offline' });
                });
            })
        );
        return;
    }

    // Static assets (CSS, JS, Fonts, App Icons) -> Cache-First with Network fallback
    if (
        url.pathname.startsWith('/css/') ||
        url.pathname.startsWith('/js/') ||
        url.pathname.startsWith('/vendor/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/fonts/') ||
        url.pathname === '/manifest.json'
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return networkResponse;
                });
            })
        );
        return;
    }

    // All other requests -> Network Only (never cache sensitive ERP data in unprotected cache)
});
