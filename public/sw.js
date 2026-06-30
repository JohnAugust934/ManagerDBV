/*
 * Service Worker do Desbravadores Manager (PWA).
 *
 * Princípio: este é um app AUTENTICADO e multi-tenant. Páginas HTML NUNCA são
 * servidas do cache (evita CSRF token velho e dados de outro clube). A rede é
 * sempre tentada primeiro; só quando ela falha mostramos a página offline.
 * Apenas assets imutáveis (build com hash, ícones, manifest) ficam em cache.
 *
 * Para forçar atualização do SW, suba o número da versão abaixo.
 */
const VERSION = 'v2';
const CACHE = `dbv-${VERSION}`;
const OFFLINE_URL = '/offline.html';

const PRECACHE = [OFFLINE_URL, '/manifest.webmanifest', '/icons/icon-192.png', '/icons/icon-512.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Só lidamos com GET de mesma origem. POST/PUT/etc. e terceiros passam direto.
    if (request.method !== 'GET') return;
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Navegações (HTML): network-first, fallback para a página offline.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    // Assets imutáveis (build com hash + ícones): cache-first, popula em background.
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/')) {
        event.respondWith(
            caches.match(request).then(
                (cached) =>
                    cached ||
                    fetch(request).then((response) => {
                        const copy = response.clone();
                        caches.open(CACHE).then((cache) => cache.put(request, copy));
                        return response;
                    })
            )
        );
    }
});
