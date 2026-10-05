// This file stores only static files (CSS, JS, fonts, icons) and the offline page.
// It never stores logged-in pages or lead data.
// Change the cache name when you replace the logo or CSS, so phones pick up the new files.

var STATIC_CACHE = 'argil-static-v6';

var PRECACHE_URLS = [
    '/offline.html',
    '/css/app.css',
    '/js/argil.js',
    '/images/argil-logo.svg',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-192.png',
    '/icons/icon-maskable-512.png'
];

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(STATIC_CACHE).then(function (cache) {
            return cache.addAll(PRECACHE_URLS);
        })
    );

    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (names) {
            return Promise.all(names.map(function (name) {
                if (name !== STATIC_CACHE) {
                    return caches.delete(name);
                }
            }));
        })
    );

    self.clients.claim();
});

self.addEventListener('fetch', function (event) {
    var request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    // Opening a page is never saved. If the network is down, show the offline page.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(function () {
                return caches.match('/offline.html');
            })
        );
        return;
    }

    var isStatic = request.destination === 'style'
        || request.destination === 'script'
        || request.destination === 'font'
        || request.destination === 'image';

    if (!isStatic) {
        return;
    }

    event.respondWith(
        caches.match(request).then(function (cached) {
            if (cached) {
                return cached;
            }

            return fetch(request).then(function (response) {
                if (!response || response.status !== 200 || response.type === 'opaque') {
                    return response;
                }

                var copy = response.clone();

                caches.open(STATIC_CACHE).then(function (cache) {
                    cache.put(request, copy);
                });

                return response;
            });
        })
    );
});
