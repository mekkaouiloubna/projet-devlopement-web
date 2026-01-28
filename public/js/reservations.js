/* ===========================================
   Gestion des réservations et des interactions
   =========================================== */

const ReservationManager = {
    // Validation des dates
    validateDates: function(startDateId, endDateId) {
        const startDate = document.getElementById(startDateId);
        const endDate = document.getElementById(endDateId);
        
        if (!startDate || !endDate) return true;
        
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        
        if (end <= start) {
            App.showToast('La date de fin doit être après la date de début', 'error');
            endDate.focus();
            return false;
        }
        
        // Vérifier que la date n’est pas passée
        const now = new Date();
        if (start < now) {
            App.showToast('Impossible de réserver une date passée', 'error');
            startDate.focus();
            return false;
        }
        
        return true;
    },
    
    // Vérifier la disponibilité des ressources
    checkResourceAvailability: function(resourceId, startDate, endDate, callback) {
        const data = {
            resource_id: resourceId,
            start_date: startDate,
            end_date: endDate,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };
        
        fetch('/api/check-availability', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => { if (callback) callback(data.available, data.message); })
        .catch(error => { console.error('Error:', error); callback(false, 'Erreur lors de la vérification de disponibilité'); });
    },
    
    // Soumettre une réservation
    submitReservation: function(form) {
        if (!this.validateDates('date_debut', 'date_fin')) return false;
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Envoi en cours...';
        submitBtn.disabled = true;
        
        App.submitForm(form, function(response) {
            if (response.success) {
                App.showToast(response.message || 'Demande de réservation envoyée avec succès', 'success');
                setTimeout(() => {
                    window.location.href = response.redirect || '/reservations';
                }, 1500);
            } else {
                App.showToast(response.message || 'Erreur lors de l’envoi', 'error');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
        
        return false;
    },
    
    // Annuler une réservation
    cancelReservation: function(reservationId) {
        if (!confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) return;
        
        fetch(`/reservations/${reservationId}/cancel`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showToast(data.message || 'Réservation annulée avec succès', 'success');
                
                const statusElement = document.querySelector(`.reservation-status-${reservationId}`);
                if (statusElement) {
                    statusElement.textContent = 'Annulée';
                    statusElement.className = `badge badge-danger reservation-status-${reservationId}`;
                }
                
                document.querySelectorAll(`.reservation-action-${reservationId}`).forEach(btn => btn.disabled = true);
            } else {
                App.showToast(data.message || 'Erreur lors de l’annulation', 'error');
            }
        })
        .catch(error => { console.error('Error:', error); App.showToast('Erreur de connexion', 'error'); });
    },
    
    // Approuver une réservation
    approveReservation: function(reservationId) {
        const comment = prompt('Entrez un commentaire (optionnel) :');
        if (comment === null) return;
        
        const data = { comment: comment, _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content') };
        
        fetch(`/reservations/${reservationId}/approve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showToast(data.message || 'Réservation approuvée', 'success');
                this.updateReservationStatus(reservationId, 'approuvée', 'Approuvée');
            } else {
                App.showToast(data.message || 'Erreur', 'error');
            }
        })
        .catch(error => { console.error('Error:', error); App.showToast('Erreur de connexion', 'error'); });
    },
    
    // Rejeter une réservation
    rejectReservation: function(reservationId) {
        const comment = prompt('Entrez le motif du refus (obligatoire) :');
        if (!comment) { App.showToast('Motif requis', 'warning'); return; }
        
        const data = { comment: comment, _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content') };
        
        fetch(`/reservations/${reservationId}/reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showToast(data.message || 'Réservation refusée', 'success');
                this.updateReservationStatus(reservationId, 'refusée', 'Refusée');
            } else {
                App.showToast(data.message || 'Erreur', 'error');
            }
        })
        .catch(error => { console.error('Error:', error); App.showToast('Erreur de connexion', 'error'); });
    },
    
    // Mettre à jour le statut dans l’interface
    updateReservationStatus: function(reservationId, status, displayText) {
        const statusElement = document.querySelector(`.reservation-status-${reservationId}`);
        if (statusElement) {
            statusElement.textContent = displayText;
            const statusClasses = {
                'approuvée': 'badge-success',
                'refusée': 'badge-danger',
                'en attente': 'badge-warning',
                'active': 'badge-info',
                'terminée': 'badge-secondary'
            };
            statusElement.className = `badge ${statusClasses[status] || 'badge-secondary'} reservation-status-${reservationId}`;
        }
        
        if (status !== 'en attente') {
            document.querySelectorAll(`.reservation-action-${reservationId}`).forEach(btn => {
                if (btn.classList.contains('btn-approve') || btn.classList.contains('btn-reject')) btn.style.display = 'none';
            });
        }
    },
    
    // Initialiser le calendrier des réservations
    initReservationCalendar: function() {
        const calendarEl = document.getElementById('reservation-calendar');
        if (!calendarEl) return;
        
        const today = new Date();
        this.loadReservationsForMonth(today.getFullYear(), today.getMonth() + 1);
    },
    
    // Charger les réservations d’un mois
    loadReservationsForMonth: function(year, month) {
        fetch(`/api/reservations/month/${year}/${month}`)
            .then(response => response.json())
            .then(reservations => this.renderCalendar(reservations))
            .catch(error => console.error('Error:', error));
    },
    
    // Afficher le calendrier
    renderCalendar: function(reservations) {
        console.log('Affichage du calendrier avec les réservations :', reservations);
    }
};

// Événements DOM
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#date_debut, #date_fin').forEach(input => {
        input.addEventListener('change', function() {
            ReservationManager.validateDates('date_debut', 'date_fin');
        });
    });
    
    const reservationForm = document.getElementById('reservation-form');
    if (reservationForm) {
        reservationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            ReservationManager.submitReservation(this);
        });
    }
    
    ReservationManager.initReservationCalendar();
});
