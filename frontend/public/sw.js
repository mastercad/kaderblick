// Service Worker für Push Notifications
const CACHE_NAME = 'kaderblick-v1';

// Installation
self.addEventListener('install', (event) => {
  // console.log('Service Worker installed');
  self.skipWaiting();
});

// Aktivierung
self.addEventListener('activate', (event) => {
  // console.log('Service Worker activated');
  event.waitUntil(self.clients.claim());
});

// Push Notification empfangen
self.addEventListener('push', (event) => {
//  console.log('Push notification received:', event);
  
  let notificationData = {
    title: 'Kaderblick',
    body: 'Neue Benachrichtigung',
    icon: '/favicon.ico',
    badge: '/favicon.ico',
    data: {}
  };

  try {
    if (event.data) {
      const data = event.data.json();
      notificationData = {
        title: data.title || notificationData.title,
        body: data.body || data.message || notificationData.body,
        icon: data.icon || notificationData.icon,
        badge: data.badge || notificationData.badge,
        data: data.data || {},
        actions: data.actions || []
      };
    }
  } catch (error) {
    console.error('Error parsing push data:', error);
  }

  const notificationPromise = self.registration.showNotification(
    notificationData.title,
    {
      body: notificationData.body,
      icon: notificationData.icon,
      badge: notificationData.badge,
      data: notificationData.data,
      actions: notificationData.actions,
      requireInteraction: false,
      tag: 'kaderblick-notification'
    }
  );

  event.waitUntil(notificationPromise);
});

// Notification Click Handler
self.addEventListener('notificationclick', (event) => {
  // console.log('Notification clicked:', event);
  event.notification.close();

  // App öffnen oder fokussieren
  const urlToOpen = event.notification.data?.url || '/';
  
  event.waitUntil(
    self.clients.matchAll({
      type: 'window',
      includeUncontrolled: true
    }).then((clientList) => {
      // Prüfen ob App bereits offen ist
      for (const client of clientList) {
        if (client.url.includes(self.location.origin) && 'focus' in client) {
          client.focus();
          if (urlToOpen !== '/') {
            client.navigate(urlToOpen);
          }
          return;
        }
      }
      
      // Neue Tab/Window öffnen
      if (self.clients.openWindow) {
        return self.clients.openWindow(self.location.origin + urlToOpen);
      }
    })
  );
});

// Background Sync (optional für später)
self.addEventListener('sync', (event) => {
  if (event.tag === 'background-sync') {
    console.log('Background sync triggered');
    // Hier könnten wir später Background-Updates implementieren
  }
});
