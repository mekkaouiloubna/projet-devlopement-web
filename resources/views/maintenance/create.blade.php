@extends('layouts.app')

@section('content')
<div class="container">
    <!-- En-tête de page -->
    <div class="header-sec">
        <div class="header-sec-title">
            <h1>
                <i class="fas fa-plus-circle"></i> Planifier une Maintenance
            </h1>
            <p>
                Planifiez une nouvelle maintenance pour une ressource
            </p>
        </div>
            <a href="{{ route('maintenance.index') }}" class="mt-2 btn btn-outline trans-up">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-tools"></i> Informations de la maintenance</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('maintenance.store') }}" method="POST">
                        @csrf
                        
                        <!-- Sélection de la ressource -->
                        <div class="form-group mb-4">
                            <label class="form-label">
                                <i class="fas fa-server"></i> Ressource * <span class="text-danger">*</span>
                            </label>
                            <select name="resource_id" class="form-control" required
                                    onchange="checkResourceAvailability(this)">
                                <option value="">Sélectionnez une ressource...</option>
                                @foreach($resources as $resource)
                                    <option value="{{ $resource->id }}" 
                                            {{ $resource_id == $resource->id ? 'selected' : '' }}>
                                        {{ $resource->nom }} 
                                        ({{ $resource->category->nom }})
                                        - Statut: 
                                        <span class="badge 
                                            @if($resource->statut == 'disponible') badge-success
                                            @elseif($resource->statut == 'réservé') badge-warning
                                            @elseif($resource->statut == 'maintenance') badge-info
                                            @else badge-danger @endif">
                                            {{ $resource->statut }}
                                        </span>
                                    </option>
                                @endforeach
                            </select>
                            <br><small class="form-text text-muted">
                                Sélectionnez la ressource à mettre en maintenance
                            </small>
                            <div id="resourceAvailability" class="mt-2" style="display: none;"></div>
                        </div>
                        
                        <div class="row">
                            <!-- Date de début -->
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt"></i> Date de début * <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" 
                                           name="date_debut" 
                                           class="form-control" 
                                           required
                                           min="{{ date('Y-m-d\TH:i') }}"
                                           onchange="updateMinEndDate(this)">
                                    <br><small class="form-text text-muted">
                                        Date et heure du début de la maintenance
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Date de fin -->
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt"></i> Date de fin * <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" 
                                           name="date_fin" 
                                           class="form-control" 
                                           required
                                           id="date_fin">
                                    <br><small class="form-text text-muted">
                                        Date et heure de fin de la maintenance
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Raison -->
                        <div class="form-group mb-4">
                            <label class="form-label">
                                <i class="fas fa-comment"></i> Raison de la maintenance * <span class="text-danger">*</span>
                            </label>
                            <textarea name="raison" 
                                      class="form-control" 
                                      rows="4" 
                                      required 
                                      placeholder="Décrivez la raison de cette maintenance..."></textarea>
                            <br><small class="form-text text-muted">
                                Décrivez brièvement pourquoi cette maintenance est nécessaire (10-500 caractères)
                            </small>
                        </div>
                        
                        <!-- Statut -->
                        <div class="form-group mb-4">
                            <label class="form-label">
                                <i class="fas fa-tag"></i> Statut initial * <span class="text-danger">*</span>
                            </label>
                            <select name="statut" class="form-control" required>
                                <option value="planifiée" selected>Planifiée</option>
                                <option value="en cours">En cours</option>
                            </select>
                            <br><small class="form-text text-muted">
                                Le statut initial de la maintenance
                            </small>
                        </div>
                        
                        <!-- Boutons -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Planifier la maintenance
                            </button>
                            <button type="reset" class="btn btn-outline ml-2">
                                <i class="fas fa-redo"></i> Réinitialiser
                            </button>
                            <a href="{{ route('maintenance.index') }}" class="btn btn-outline ml-2">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-label .text-danger {
    color: #dc3545;
}

.form-control {
    border-radius: 8px;
    border: 2px solid #dee2e6;
    transition: border-color 0.3s;
}

.form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}
</style>

<script>
// Mettre à jour la date minimale de fin
function updateMinEndDate(startDateInput) {
    const endDateInput = document.getElementById('date_fin');
    endDateInput.min = startDateInput.value;
    
    // Si la date de fin est antérieure à la nouvelle date de début, la réinitialiser
    if (endDateInput.value && endDateInput.value < startDateInput.value) {
        endDateInput.value = '';
    }
}

// Vérifier la disponibilité de la ressource
function checkResourceAvailability(select) {
    const resourceId = select.value;
    const availabilityDiv = document.getElementById('resourceAvailability');
    const conflictsDiv = document.getElementById('conflictsPreview');
    const conflictsList = document.getElementById('conflictsList');
    
    if (!resourceId) {
        availabilityDiv.style.display = 'none';
        conflictsDiv.style.display = 'none';
        return;
    }
    
    // Simuler une requête AJAX (dans un cas réel, utiliser fetch/axios)
    // Ici on va simplement afficher un message
    const selectedOption = select.options[select.selectedIndex];
    const resourceName = selectedOption.text.split('(')[0].trim();
    
    // Pour l'exemple, on simule une vérification
    setTimeout(() => {
        availabilityDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>${resourceName}</strong> - Vérification en cours...
                <br>
                <small>Veuillez sélectionner les dates pour vérifier les conflits</small>
            </div>
        `;
        availabilityDiv.style.display = 'block';
        
        // Simuler la détection de conflits (dans un cas réel, faire une requête AJAX)
        simulateConflictsCheck(resourceId);
    }, 500);
}

// Simuler la vérification des conflits
function simulateConflictsCheck(resourceId) {
    const conflictsDiv = document.getElementById('conflictsPreview');
    const conflictsList = document.getElementById('conflictsList');
    
    // Dans un cas réel, faire une requête AJAX vers le serveur
    // Exemple de structure pour les conflits
    const fakeConflicts = [
        { user: 'Jean Dupont', date_debut: '2024-02-10 09:00', date_fin: '2024-02-10 12:00' },
        { user: 'Marie Martin', date_debut: '2024-02-11 14:00', date_fin: '2024-02-11 16:00' }
    ];
    
    if (fakeConflicts.length > 0) {
        conflictsList.innerHTML = `
            <p>Cette ressource a ${fakeConflicts.length} réservation(s) potentiellement conflictuelles :</p>
            <ul>
                ${fakeConflicts.map(conflict => 
                    `<li>${conflict.user} (${conflict.date_debut} - ${conflict.date_fin})</li>`
                ).join('')}
            </ul>
            <p class="mb-0"><small>Vous devrez peut-être contacter ces utilisateurs.</small></p>
        `;
        conflictsDiv.style.display = 'block';
    } else {
        conflictsDiv.style.display = 'none';
    }
}

// Initialiser le formulaire
document.addEventListener('DOMContentLoaded', function() {
    const resourceSelect = document.querySelector('select[name="resource_id"]');
    const startDateInput = document.querySelector('input[name="date_debut"]');
    
    // Définir la date minimale de début à maintenant
    const now = new Date();
    const timezoneOffset = now.getTimezoneOffset() * 60000;
    const localISOTime = new Date(now - timezoneOffset).toISOString().slice(0, 16);
    startDateInput.min = localISOTime;
    
    // Si une ressource est présélectionnée, vérifier sa disponibilité
    if (resourceSelect.value) {
        checkResourceAvailability(resourceSelect);
    }
    
    // Mettre à jour la date de fin quand la date de début change
    startDateInput.addEventListener('change', function() {
        updateMinEndDate(this);
    });
});
</script>

@endsection