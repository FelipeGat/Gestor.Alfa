// Service Worker para Portal do Funcionário
const CACHE_NAME = 'portal-funcionario-v1';
const OFFLINE_URL = '/offline';

// URLs para cache estático
const STATIC_URLS = [
  '/',
  '/portal-funcionario',
  '/build/manifest.json'
];

// Instalação do Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[SW] Cache aberto:', CACHE_NAME);
        return cache.addAll(STATIC_URLS);
      })
      .then(() => self.skipWaiting())
  );
});

// Ativação e limpeza de caches antigos
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames
            .filter(name => name !== CACHE_NAME)
            .map(name => caches.delete(name))
        );
      })
      .then(() => self.clients.claim())
  );
});

// Interceptação de requisições
self.addEventListener('fetch', event => {
  // Ignorar requisições que não são GET
  if (event.request.method !== 'GET') return;

  // Ignorar URLs externas e admin
  const url = new URL(event.request.url);
  if (url.origin !== location.origin) return;

  event.respondWith(
    caches.match(event.request)
      .then(cachedResponse => {
        if (cachedResponse) {
          // Retorna cache e atualiza em background
          event.waitUntil(updateCache(event.request));
          return cachedResponse;
        }

        // Não está em cache, faz fetch
        return fetchAndCache(event.request);
      })
      .catch(() => {
        // Fallback para offline
        if (event.request.destination === 'document') {
          return caches.match(OFFLINE_URL);
        }
      })
  );
});

// Fetch e cache
async function fetchAndCache(request) {
  const response = await fetch(request);
  
  // Cache apenas respostas válidas
  if (response.ok) {
    const cache = await caches.open(CACHE_NAME);
    cache.put(request, response.clone());
  }
  
  return response;
}

// Atualização de cache em background
async function updateCache(request) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(CACHE_NAME);
      await cache.put(request, response);
    }
  } catch (error) {
    // Network error - ignora silently
  }
}
