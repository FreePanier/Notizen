const CACHE_NAME = 'notizen-app-v6'; // Auf v6 erhöht
const ASSETS = [
  'index.html',
  'tagebuch.html',
  'manifest.json',
  'icons/icon-192x192.png',
  'icons/icon-512x512.png'
];

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS);
    })
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    })
  );
  return self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // WICHTIGSTER FIX: Ignoriere ALLE Speicher- und Lösch-Befehle (POST)
  if (event.request.method !== 'GET') {
      return; // Der Service Worker mischt sich hier nicht ein!
  }

  const url = event.request.url;

  // PHP und JSON immer direkt vom Netzwerk laden
  if (url.includes('.php') || url.includes('.json')) {
    return; 
  }

  // HTML-Dateien: Erst Netzwerk, dann Cache (Network-First)
  if (event.request.mode === 'navigate' || url.endsWith('.html')) {
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          const copy = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
          return response;
        })
        .catch(() => caches.match(event.request))
    );
    return;
  }

  // Alles andere (Bilder, etc.): Erst Cache, dann Netzwerk (Cache-First)
  event.respondWith(
    caches.match(event.request).then((response) => {
      return response || fetch(event.request);
    })
  );
});