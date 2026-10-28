/*
 * DeepskyLog service worker: the observing lists and the page at the eyepiece
 * (resources/views/observing-lists/eyepiece.blade.php) without a connection.
 *
 * - The pages of an observing list (/observing-list/{slug}), at the
 *   eyepiece (/observing-list/{slug}/eyepiece?item=…) and the lists to take
 *   to the telescope (/observing-lists/eyepiece) are fetched from the
 *   network and kept in PAGES; without a connection the kept copy is shown.
 *   The page at the eyepiece puts all its pages in PAGES with "Save for
 *   offline use".
 * - "Log it" (/observing-list/{slug}/eyepiece/{item}/log) without a
 *   connection goes back to the page of the object with #log, which opens
 *   the quick note; the page keeps that observation until it can be sent.
 * - The scripts, styles and icons the pages use are kept in ASSETS and
 *   refreshed in the background.
 * - Logging out deletes the kept pages and ends the push notifications of
 *   this browser, which would show the observer's alerts to the next one.
 * - Push notifications (App\Services\PushNotifications, turned on on the
 *   profile page): shown with their title and text; tapping one opens its
 *   url, in a DeepskyLog window already open if there is one. A subscription
 *   the browser renews by itself is handed to /push-subscriptions/renew.
 *
 * Every other request goes to the network as usual. Change VERSION when this
 * file changes the way pages are kept; the page at the eyepiece writes to
 * PAGES itself (its CACHE).
 */
const VERSION = 'v1';
const PAGES = 'dsl-offline-' + VERSION;
const ASSETS = 'dsl-assets-' + VERSION;

const PAGE = /^\/observing-list\/[^/]+(\/eyepiece)?$|^\/observing-lists\/eyepiece$/;
const LOG = /^\/observing-list\/([^/]+)\/eyepiece\/(\d+)\/log$/;
const ASSET = /^\/(build\/|js\/|images\/icons\/|wireui\/assets\/|livewire\/livewire(\.min)?\.js$)/;

self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys
                .filter((key) => key.startsWith('dsl-') && key !== PAGES && key !== ASSETS)
                .map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

function offlinePage() {
    return new Response(
        '<!DOCTYPE html><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        + '<title>DeepskyLog</title><body style="background:#000;color:#d1d5db;font-family:system-ui,sans-serif;padding:1rem">'
        + '<h1>No connection</h1><p>This page was not saved for offline use. Open the page at the eyepiece of your list and use "Save for offline use" while you have a connection.</p>'
        + '<p><a style="color:#e5e7eb" href="javascript:history.back()">Back</a></p></body>',
        { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
    );
}

function networkFirst(request) {
    return fetch(request)
        .then((response) => {
            if (response.ok && !response.redirected && response.type === 'basic') {
                const copy = response.clone();
                caches.open(PAGES).then((cache) => cache.put(request, copy));
            }
            return response;
        })
        .catch(() => caches.open(PAGES)
            .then((cache) => cache.match(request, { ignoreVary: true }))
            .then((cached) => cached || offlinePage()));
}

function staleWhileRevalidate(request) {
    return caches.open(ASSETS).then((cache) => cache.match(request, { ignoreVary: true }).then((cached) => {
        const fresh = fetch(request)
            .then((response) => {
                if (response.ok && response.type === 'basic') {
                    cache.put(request, response.clone());
                }
                return response;
            })
            .catch(() => cached);
        return cached || fresh;
    }));
}

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    // The kept pages hold the observer's data: gone when they log out
    if (request.method === 'POST' && url.pathname === '/logout') {
        event.waitUntil(Promise.all([
            caches.delete(PAGES),
            self.registration.pushManager.getSubscription()
                .then((subscription) => subscription && subscription.unsubscribe())
                .catch(() => {}),
        ]));
        return;
    }
    if (request.method !== 'GET') {
        return;
    }

    const log = url.pathname.match(LOG);
    if (log && request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => Response.redirect(
            '/observing-list/' + log[1] + '/eyepiece?item=' + log[2] + '#log', 302
        )));
        return;
    }

    if (PAGE.test(url.pathname) && request.mode === 'navigate') {
        event.respondWith(networkFirst(request));
        return;
    }

    if (ASSET.test(url.pathname) || url.pathname === '/manifest.webmanifest') {
        event.respondWith(staleWhileRevalidate(request));
    }
});

self.addEventListener('push', (event) => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { body: event.data ? event.data.text() : '' };
    }
    const options = {
        body: data.body || '',
        icon: data.icon || '/images/icons/icon-192.png',
        badge: '/images/icons/icon-192.png',
        data: { url: data.url || '/' },
    };
    if (data.tag) {
        options.tag = data.tag;
        options.renotify = true;
    }
    event.waitUntil(self.registration.showNotification(data.title || 'DeepskyLog', options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const target = new URL((event.notification.data && event.notification.data.url) || '/', self.location.origin);
    if (target.origin !== self.location.origin) {
        return;
    }
    event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
        const open = windows.find((client) => new URL(client.url).origin === target.origin && 'navigate' in client);
        if (open) {
            return open.navigate(target.href).then((client) => (client || open).focus()).catch(() => self.clients.openWindow(target.href));
        }
        return self.clients.openWindow(target.href);
    }));
});

self.addEventListener('pushsubscriptionchange', (event) => {
    const old = event.oldSubscription;
    if (!old) {
        return;
    }
    const renewed = event.newSubscription
        ? Promise.resolve(event.newSubscription)
        : self.registration.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: old.options.applicationServerKey });
    event.waitUntil(renewed.then((subscription) => {
        const body = subscription.toJSON();
        body.old_endpoint = old.endpoint;
        return fetch('/push-subscriptions/renew', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(body),
        });
    }).catch(() => {}));
});
