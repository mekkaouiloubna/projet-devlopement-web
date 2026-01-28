{{-- resources/views/reservations/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nouvelle Réservation')

@section('content')
<div class="container">
    <!-- En-tête de la page -->
    <div class="header-sec mb-4">
        <div class="header-sec-title">
            <h1><i class="fas fa-calendar-plus"></i> Nouvelle Réservation</h1>
            <p>Réservez une ressource pour une période spécifique</p>
        </div>
        <div class="header-sec-date">
            {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <!-- Carte du formulaire -->
    <div class="card p-4 mb-5">
        <div class="mb-4">
            <h2 class="page-title">
                <i class="fas fa-file-alt"></i> Formulaire de réservation
            </h2>
            <p class="page-subtitle">Remplissez tous les champs requis pour soumettre votre demande</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-4 fade-in flash-message">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Veuillez corriger les erreurs suivantes :</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('reservations.store') }}" method="POST" id="reservationForm">
            @csrf
            <!-- Sélection de la ressource -->
            <div class="mb-4">
                <label for="resource_id" class="form-label">
                    <i class="fas fa-server mr-1"></i>  Ressource à réserver 
                </label>
                <select name="resource_id" id="resource_id" class="form-control" required
                        data-selected="{{ old('resource_id', $resource_id) }}">
                    <option value="">Sélectionnez une ressource</option>
                    @foreach($resources as $resource)
                        <option value="{{ $resource->id }}"
                            {{ old('resource_id', $resource_id) == $resource->id ? 'selected' : '' }}
                            data-disponible="{{ $resource->isDisponible() ? '1' : '0' }}">
                            {{ $resource->nom }} ({{ $resource->category->nom ?? 'Non catégorisé' }})
                            - {{ $resource->statut }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text fade-in flash-message" id="resourceInfo">
                    <!-- Les informations sur la ressource seront chargées ici -->
                </div>
            </div>

            <!-- Période de réservation -->
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div>
                    <label for="date_debut" class="form-label">
                        <i class="fas fa-calendar-day mr-1"></i> Date de début *
                    </label>
                    <input type="datetime-local" 
                           name="date_debut" 
                           id="date_debut" 
                           class="form-control" 
                           value="{{ old('date_debut') }}"
                           min="{{ now()->format('Y-m-d\TH:i') }}"
                           required>
                    <div class="form-text">La réservation ne peut pas commencer dans le passé</div>
                </div>

                <div>
                    <label for="date_fin" class="form-label">
                        <i class="fas fa-calendar-times mr-1"></i> Date de fin *
                    </label>
                    <input type="datetime-local" 
                           name="date_fin" 
                           id="date_fin" 
                           class="form-control" 
                           value="{{ old('date_fin') }}"
                           required>
                    <div class="form-text">Doit être postérieure à la date de début</div>
                </div>
            </div>

            <!-- Durée calculée -->
            <div class="mb-4">
                <div class="reservation-info p-3 bord-rad-lef2">
                    <strong><i class="fas fa-clock"></i> Durée estimée :</strong>
                    <span id="dureeCalcul">--</span>
                </div>
            </div>

            <!-- Justification -->
            <div class="mb-4">
                <label for="justification" class="form-label">
                    <i class="fas fa-comment-dots mr-1"></i> Justification de la réservation *
                </label>
                <input name="justification" type="text"
                          id="justification" 
                          class="form-control w-80 h-80px" 
                          placeholder="Décrivez en détail l'objectif de cette réservation..." 
                          required>{{ old('justification') }}</input>
                <div class="form-text">Minimum 10 caractères, maximum 1000 caractères</div>
                <div class="text-right mt-2">
                    <span id="charCount">0</span> / 1000 caractères
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="mb-2">
                <h4 class="mb-2">
                    <i class="fas fa-info-circle"></i> Informations supplémentaires
                </h4>
                <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                    <div class="section-card">
                        <div class="section-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4>Demandeur</h4>
                        <p>{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</p>
                    </div>
                    <div class="section-card">
                        <div class="section-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email</h4>
                        <p>{{ auth()->user()->email }}</p>
                    </div>
                    <div class="section-card">
                        <div class="section-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h4>Statut initial</h4>
                        <p><span class="badge badge-warning">En attente</span></p>
                    </div>
                </div>
            </div>

            <!-- Actions du formulaire -->
            <div class="page-actions mt-5">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-paper-plane"></i> Soumettre la demande
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline btn-lg">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const resourceSelect = document.getElementById('resource_id');
    const dateDebutInput = document.getElementById('date_debut');
    const dateFinInput = document.getElementById('date_fin');
    const justificationTextarea = document.getElementById('justification');
    const charCountSpan = document.getElementById('charCount');
    const dureeSpan = document.getElementById('dureeCalcul');
    const previewBtn = document.getElementById('previewBtn');
    const previewModal = document.getElementById('previewModal');
    const submitFromPreviewBtn = document.getElementById('submitFromPreview');
    const resourceInfoDiv = document.getElementById('resourceInfo');
    const reservationForm = document.getElementById('reservationForm');

    // Initialiser la date de début avec l'heure actuelle
    const now = new Date();
    now.setMinutes(now.getMinutes() + 30); // Début dans 30 minutes
    const formattedNow = now.toISOString().slice(0, 16);
    if (!dateDebutInput.value) {
        dateDebutInput.value = formattedNow;
    }

    // Mettre à jour le compteur de caractères
    justificationTextarea.addEventListener('input', function() {
        const length = this.value.length;
        charCountSpan.textContent = length;
        if (length < 10) {
            this.style.borderColor = '#e74c3c';
            charCountSpan.style.color = '#e74c3c';
        } else {
            this.style.borderColor = '#27ae60';
            charCountSpan.style.color = '#27ae60';
        }
    });

    // Calculer la durée
    function calculerDuree() {
        const debut = new Date(dateDebutInput.value);
        const fin = new Date(dateFinInput.value);
        
        if (debut && fin && fin > debut) {
            const diffMs = fin - debut;
            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
            const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
            
            let duree = '';
            if (diffHours > 0) {
                duree += `${diffHours} heure${diffHours > 1 ? 's' : ''}`;
            }
            if (diffMinutes > 0) {
                duree += `${diffHours > 0 ? ' et ' : ''}${diffMinutes} minute${diffMinutes > 1 ? 's' : ''}`;
            }
            if (!duree) duree = '0 minute';
            
            dureeSpan.textContent = duree;
        } else {
            dureeSpan.textContent = '--';
        }
    }

    // Mettre à jour les informations de la ressource
    function updateResourceInfo() {
        const selectedOption = resourceSelect.options[resourceSelect.selectedIndex];
        if (selectedOption.value) {
            const isDisponible = selectedOption.getAttribute('data-disponible') === '1';
            
            if (isDisponible) {
                resourceInfoDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Ressource disponible</strong>
                        <p class="mb-0 mt-1">Cette ressource peut être réservée.</p>
                    </div>
                `;
            } else {
                resourceInfoDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Ressource non disponible</strong>
                        <p class="mb-0 mt-1">Cette ressource n'est pas actuellement disponible pour réservation.</p>
                    </div>
                `;
            }
        } else {
            resourceInfoDiv.innerHTML = '';
        }
    }

    // Générer l'aperçu
    function genererAperçu() {
        const resourceName = resourceSelect.options[resourceSelect.selectedIndex]?.text || 'Non sélectionnée';
        const dateDebut = dateDebutInput.value ? new Date(dateDebutInput.value).toLocaleString('fr-FR') : 'Non définie';
        const dateFin = dateFinInput.value ? new Date(dateFinInput.value).toLocaleString('fr-FR') : 'Non définie';
        const justification = justificationTextarea.value || 'Aucune justification fournie';
        
        return `
            <div class="reservation-info p-3 mb-3">
                <h4 class="mb-3"><i class="fas fa-server"></i> Ressource</h4>
                <p class="font-wb">${resourceName}</p>
            </div>
            
            <div class="reservation-info p-3 mb-3">
                <h4 class="mb-3"><i class="fas fa-calendar-alt"></i> Période</h4>
                <p><strong>Début :</strong> ${dateDebut}</p>
                <p><strong>Fin :</strong> ${dateFin}</p>
                <p><strong>Durée :</strong> ${dureeSpan.textContent}</p>
            </div>
            
            <div class="reservation-info p-3 mb-3">
                <h4 class="mb-3"><i class="fas fa-comment"></i> Justification</h4>
                <p>${justification}</p>
            </div>
            
            <div class="reservation-info p-3">
                <h4 class="mb-3"><i class="fas fa-user"></i> Demandeur</h4>
                <p>${document.querySelector('.section-card:nth-child(1) p').textContent}</p>
                <p>${document.querySelector('.section-card:nth-child(2) p').textContent}</p>
            </div>
        `;
    }

    // Événements
    dateDebutInput.addEventListener('change', function() {
        if (dateFinInput.value && new Date(dateFinInput.value) <= new Date(this.value)) {
            dateFinInput.value = '';
            dateFinInput.min = this.value;
        }
        calculerDuree();
    });

    dateFinInput.addEventListener('change', calculerDuree);
    resourceSelect.addEventListener('change', updateResourceInfo);

    previewBtn.addEventListener('click', function() {
        const aperçuContent = genererAperçu();
        document.getElementById('previewContent').innerHTML = aperçuContent;
        previewModal.classList.add('show', 'fade-in');
    });

    submitFromPreviewBtn.addEventListener('click', function() {
        reservationForm.submit();
    });

    // Fermer la modal
    previewModal.addEventListener('click', function(e) {
        if (e.target === this || e.target.closest('.modal-close')) {
            this.classList.remove('show');
        }
    });

    // Initialiser
    updateResourceInfo();
    justificationTextarea.dispatchEvent(new Event('input'));
    calculerDuree();
});
</script>
@endpush

@push('styles')
<style>
.reservation-info {
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #3498db;
}

#charCount {
    font-weight: bold;
}

.modal {
    display: none;
}

.modal.show {
    display: flex;
}

#previewContent p {
    margin-bottom: 10px;
}
</style>
@endpush