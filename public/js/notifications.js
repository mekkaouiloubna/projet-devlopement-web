/* ===========================================
   Gestion des notifications
   =========================================== */

const NotificationManager = {
    // Mettre à jour les notifications
    updateNotifications: function() {
        fetch('/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                this.updateBadge(data.count);
            })
            .catch(error => console.error('Error:', error));
    },
    
    // Mettre à jour le compteur
    updateBadge: function(count) {
        const badges = document.querySelectorAll('.notification-count');
        badges.forEach(badge => {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        });
    },
    
    // Marquer comme lu
    markAsRead: function(notificationId) {
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notification = document.querySelector(`.notification-${notificationId}`);
                if (notification) {
                    notification.classList.remove('unread');
                    this.updateNotifications();
                }
            }
        })
        .catch(error => console.error('Error:', error));
    },
    
    // Marquer tous comme lus
    markAllAsRead: function() {
        fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(() => {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            this.updateNotifications();
            App.showToast('Toutes les notifications ont été marquées comme lues', 'success');
        })
        .catch(error => console.error('Error:', error));
    },
    
    // Supprimer la notification
    deleteNotification: function(notificationId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) return;
        
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(() => {
            const notification = document.querySelector(`.notification-${notificationId}`);
            if (notification) {
                notification.remove();
                this.updateNotifications();
                App.showToast('La notification a été supprimée', 'success');
            }
        })
        .catch(error => console.error('Error:', error));
    },
    
    // Supprimer toutes les notifications lues
    clearReadNotifications: function() {
        if (!confirm('Êtes-vous sûr de vouloir supprimer toutes les notifications lues ?')) return;
        
        fetch('/notifications/clear-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(() => {
            document.querySelectorAll('.notification-item:not(.unread)').forEach(item => {
                item.remove();
            });
            App.showToast('Toutes les notifications lues ont été supprimées', 'success');
        })
        .catch(error => console.error('Error:', error));
    },
    
    // Afficher la liste des notifications
    showNotifications: function() {
        const notificationsPanel = document.getElementById('notifications-panel');
        if (notificationsPanel) {
            notificationsPanel.classList.toggle('active');
        }
    }
};

// Ajouter les événements pour les notifications
document.addEventListener('DOMContentLoaded', function() {
    // Mettre à jour les notifications chaque minute
    setInterval(() => {
        NotificationManager.updateNotifications();
    }, 60000);
    
    // Mise à jour immédiate au chargement de la page
    NotificationManager.updateNotifications();
});
