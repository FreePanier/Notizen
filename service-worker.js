const CACHE_NAME = 'Notizen-v2'; // Ändere diese Version (v2, v3...), wenn du ein Update erzwingen willst
const ASSETS = [
  'index.html',
  'manifest.json',
  'icons/icon-192x192.png',
  'icons/icon-512x512.png'
];

// Installieren & Cachen
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS);
    })
  );
  self.skipWaiting(); // Erzwingt, dass der neue SW sofort aktiv wird
});

// Alten Cache löschen bei Update
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    })
  );
});

// Netzwerk-First Strategie (Immer prüfen ob online was neues da ist)
self.addEventListener('fetch', (event) => {
  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request);
    })
  );
});