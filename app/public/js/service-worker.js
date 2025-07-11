self.addEventListener('push', function(event) {
    const data = event.data.json();
    
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/images/logo.png',
            badge: '/images/badge.png',
            data: data.url
        })
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data)
    );
});
