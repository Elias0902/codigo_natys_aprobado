const CACHE_NAME = 'natys-cache-v1';
const urlsToCache = [
  '/',
  '/Assets/bootstrap/css/bootstrap.min.css',
  '/Assets/bootstrap/js/bootstrap.bundle.min.js',
  '/Assets/css/styles.css',
  '/Assets/custom.css',
  '/Assets/js/app.js',
  // Agrega aquí más rutas de recursos estáticos que desees cachear
  '/Assets/img/natys.png',
  '/Assets/img/crash.png',
  '/Assets/img/defaultAvatar.jpg'
];

// Instalación del Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache abierto');
        return cache.addAll(urlsToCache);
      })
  );
});

// Interceptar peticiones y servir desde caché
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Devuelve la respuesta de la caché si existe, o realiza la petición
        return response || fetch(event.request);
      })
  );
});

// Actualizar la caché cuando se actualiza el Service Worker
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
