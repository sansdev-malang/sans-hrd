const CACHE_NAME = 'sans-hrd-v1.0.0';
const OFFLINE_URL = '/offline';

const PRECACHE_ASSETS = [
    '/offline',
    '/manifest.json',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/icons/icon.svg'
];

// Install Event - Precache essential assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// Activate Event - Clean up old cache versions
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle HTTP/HTTPS requests
    if (!request.url.startsWith('http')) return;

    // Ignore non-GET requests (POST, PUT, DELETE, etc.)
    if (request.method !== 'GET') return;

    // Strategy 1: HTML Navigation - Network First, fallback to offline cache/page
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    return networkResponse;
                })
                .catch(async () => {
                    const cache = await caches.open(CACHE_NAME);
                    const cachedResponse = await cache.match(request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    const offlinePage = await cache.match(OFFLINE_URL);
                    return offlinePage || new Response('Anda sedang offline.', {
                        headers: { 'Content-Type': 'text/plain; charset=utf-8' }
                    });
                })
        );
        return;
    }

    // Strategy 2: Static Assets (Images, Fonts, CSS, JS) - Stale While Revalidate
    if (
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'image' ||
        request.destination === 'font' ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/build/') ||
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.js') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.svg') ||
        url.pathname.endsWith('.woff2')
    ) {
        event.respondWith(
            caches.open(CACHE_NAME).then(async (cache) => {
                const cachedResponse = await cache.match(request);
                const fetchPromise = fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        cache.put(request, networkResponse.clone());
                    }
                    return networkResponse;
                }).catch(() => null);

                return cachedResponse || fetchPromise;
            })
        );
        return;
    }

    // Default: Network with basic cache fallback
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});
