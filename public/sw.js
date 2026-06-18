// Service Worker — приём Web Push даже при закрытом сайте/браузере.

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: 'Artgroups ERP', body: event.data ? event.data.text() : '' };
    }

    const title = data.title || '📋 Заполните KPI';
    const options = {
        body: data.body || 'Есть незаполненные показатели за сегодня.',
        icon: data.icon || '/images/artlogo.png',
        badge: data.icon || '/images/artlogo.png',
        tag: data.tag || 'kpi-reminder',
        renotify: true,
        data: { url: data.url || '/dashboard' },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/dashboard';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if ('focus' in client) {
                    client.navigate(url);
                    return client.focus();
                }
            }
            if (self.clients.openWindow) {
                return self.clients.openWindow(url);
            }
        })
    );
});
