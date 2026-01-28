// Fonctions pour gérer les modals de refus de réservations
function closeRejectModal(id) {
    const modal = document.getElementById(`rejectModal-${id}`);
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

function openRejectModal(id) {
    const modal = document.getElementById(`rejectModal-${id}`);
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
    
    // Reset des erreurs
    const errorDiv = document.getElementById(`justification-error-${id}`);
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
    
    // Reset du bouton de soumission
    const submitBtn = document.getElementById(`submit-btn-${id}`);
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-ban mr-2"></i> Confirmer le refus';
        submitBtn.disabled = false;
    }
}

function checkJustification(form, id) {
    const justification = form.querySelector('textarea[name="commentaire"]').value.trim();
    const errorDiv = document.getElementById(`justification-error-${id}`);
    const submitBtn = document.getElementById(`submit-btn-${id}`);

    // Validation de la justification
    if (justification.length < 10) {
        if (errorDiv) {
            errorDiv.style.display = 'block';
        }
        
        // Animation d'erreur
        const textarea = form.querySelector('textarea[name="commentaire"]');
        textarea.classList.add('bord-rad-lef2', 'c-dng');
        setTimeout(() => {
            textarea.classList.remove('bord-rad-lef2', 'c-dng');
        }, 1000);
        
        return false;
    }

    // Confirmation finale
    if (!confirm('Êtes-vous sûr de vouloir refuser cette réservation ? Cette action est irréversible.')) {
        return false;
    }

    // État de chargement du bouton
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Traitement...';
        submitBtn.disabled = true;
    }

    return true;
}

// Initialisation des événements
document.addEventListener('DOMContentLoaded', function() {
    // Fermer le modal en cliquant à l'extérieur
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            const id = event.target.id.replace('rejectModal-', '');
            if (id) {
                closeRejectModal(id);
            }
        }
    });

    // Fermer le modal avec la touche Échap
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                const id = openModal.id.replace('rejectModal-', '');
                if (id) {
                    closeRejectModal(id);
                }
            }
        }
    });

    // Validation en temps réel pour tous les textareas de justification
    document.querySelectorAll('textarea[name="commentaire"]').forEach(textarea => {
        textarea.addEventListener('input', function() {
            const id = this.id.replace('commentaire-', '');
            const errorDiv = document.getElementById(`justification-error-${id}`);
            
            if (this.value.trim().length >= 10) {
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                }
                this.classList.remove('bord-rad-lef2', 'c-dng');
                this.classList.add('bord-rad-lef2');
                this.style.borderLeftColor = '#27ae60';
            } else {
                this.classList.remove('bord-rad-lef2');
                this.style.borderLeftColor = '';
            }
        });
        
        // Validation à la perte de focus
        textarea.addEventListener('blur', function() {
            const id = this.id.replace('commentaire-', '');
            const errorDiv = document.getElementById(`justification-error-${id}`);
            
            if (this.value.trim().length > 0 && this.value.trim().length < 10) {
                if (errorDiv) {
                    errorDiv.style.display = 'block';
                }
                this.classList.add('bord-rad-lef2', 'c-dng');
            }
        });
    });
});