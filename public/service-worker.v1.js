// public/service-worker.v1.js

// Event push dari server
self.addEventListener("push", function(event) {
    let data = {};
    if (event.data) {
        data = event.data.json();
    }

    const title = data.title || "Notifikasi Baru";
    const options = {
        body: data.body || "Ada update dari aplikasi.",
        icon: data.icon || "/icons/icon-192.png", // pastikan file ada
        badge: data.badge || "/icons/badge-72.png", // opsional
        data: {
            url: data.url || "/", // link saat notif diklik
        },
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Event klik notifikasi → buka tab aplikasi
self.addEventListener("notificationclick", function(event) {
    event.notification.close();

    event.waitUntil(
        clients.matchAll({ type: "window", includeUncontrolled: true }).then(windowClients => {
            // Kalau tab aplikasi sudah ada → fokuskan
            for (let client of windowClients) {
                if (client.url.includes(self.location.origin) && "focus" in client) {
                    return client.focus();
                }
            }
            // Kalau belum ada → buka tab baru
            if (clients.openWindow) {
                return clients.openWindow(event.notification.data.url);
            }
        })
    );
});
