"use strict";

/* Bump à chaque fix chrome iOS pour invalider les vieux CSS en cache. */
const CACHE_NAME = "offline-cache-v3-splash-chrome";
const OFFLINE_URL = "/offline.html";

const filesToCache = [OFFLINE_URL];

self.addEventListener("install", (event) => {
    self.skipWaiting();
    event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(filesToCache)));
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) =>
            Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener("fetch", (event) => {
    const req = event.request;

    if (req.mode === "navigate") {
        event.respondWith(fetch(req).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    // CSS / JS / fonts : toujours réseau d’abord (évite vieux splash/auth après deploy).
    const dest = req.destination;
    if (dest === "style" || dest === "script" || dest === "worker" || dest === "font") {
        event.respondWith(
            fetch(req)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(req, copy)).catch(() => {});
                    return res;
                })
                .catch(() => caches.match(req))
        );
        return;
    }

    event.respondWith(
        fetch(req)
            .then((res) => res)
            .catch(() => caches.match(req))
    );
});
