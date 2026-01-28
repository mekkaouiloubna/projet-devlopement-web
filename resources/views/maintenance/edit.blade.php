@extends('layouts.app')

@section('content')
<div class="container">
    <!-- En-tête de page -->
    <div class="header-sec">
        <div class="header-sec-title">
            <h1><i class="fas fa-edit"></i> Modifier la Maintenance</h1>
            <p>Modifiez les informations de la maintenance</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-tools"></i> Modifier la maintenance</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('maintenance.update', $maintenance->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Information sur la ressource (non modifiable) -->
                        <div class="form-group mb-4">
                            <label class="form-label">
                                <i class="fas fa-server"></i> Ressource
                            </label>
                            <div class="resource-info p-3 bg-light rounded">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="resource-icon">
                                        <i class="fas fa-server"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-1">{{ $maintenance->resource->nom }}</h4>
                                        <p class="mb-1 text-muted">{{ $maintenance->resource->category->nom }}</p>
                                        <small class="text-muted">
                                            Statut actuel: 
                                            <span class="badge 
                                                @if($maintenance->resource->statut == 'disponible') badge-success
                                                @elseif($maintenance->resource->statut == 'réservé') badge-warning
                                                @elseif($maintenance->resource->statut == 'maintenance') badge-info
                                                @else badge-danger @endif">
                                                {{ $maintenance->resource->statut }}
                                            </span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                La ressource ne peut pas être modifiée après création
                            </small>
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
                                           value="{{ \Carbon\Carbon::parse($maintenance->date_debut)->format('Y-m-d\TH:i') }}">
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
                                           value="{{ \Carbon\Carbon::parse($maintenance->date_fin)->format('Y-m-d\TH:i') }}">
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
                                      required>{{ $maintenance->raison }}</textarea>
                            <br><small class="form-text text-muted">
                                Décrivez brièvement pourquoi cette maintenance est nécessaire
                            </small>
                        </div>
                        
                        <!-- Statut -->
                        <div class="form-group mb-4">
                            <label class="form-label">
                                <i class="fas fa-tag"></i> Statut * <span class="text-danger">*</span>
                            </label>
                            <select name="statut" class="form-control" required>
                                <option value="planifiée" {{ $maintenance->statut == 'planifiée' ? 'selected' : '' }}>Planifiée</option>
                                <option value="en cours" {{ $maintenance->statut == 'en cours' ? 'selected' : '' }}>En cours</option>
                                <option value="terminée" {{ $maintenance->statut == 'terminée' ? 'selected' : '' }}>Terminée</option>
                            </select>
                           <br> <small class="form-text text-muted">
                                Statut actuel de la maintenance
                            </small>
                        </div>
                        
                        <!-- Boutons -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                            <a href="{{ route('maintenance.show', $maintenance->id) }}" class="btn btn-outline ml-2">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Colonne droite : Informations et historique -->
        <div class="col-md-4">
            <!-- Carte informations actuelles -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Informations actuelles</h3>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <i class="fas fa-calendar-plus"></i>
                            <div>
                                <strong>Créée le</strong>
                                <p>{{ $maintenance->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-user"></i>
                            <div>
                                <strong>Créée par</strong>
                                <p>
                                    @if($maintenance->createdBy)
                                        {{ $maintenance->createdBy->nom }} {{ $maintenance->createdBy->prenom }}
                                    @else
                                        Système
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-history"></i>
                            <div>
                                <strong>Dernière modification</strong>
                                <p>{{ $maintenance->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.resource-info {
    border-left: 4px solid #3498db;
}

.resource-icon {
    width: 50px;
    height: 50px;
    background: #e3f2fd;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3498db;
    font-size: 1.2rem;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-item i {
    font-size: 1.2rem;
    color: #3498db;
    width: 24px;
    margin-top: 3px;
}

.info-item div {
    flex: 1;
}

.info-item strong {
    display: block;
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 2px;
}

.info-item p {
    font-size: 1rem;
    margin: 0;
    font-weight: 500;
}

.alert ul {
    margin-bottom: 0;
    padding-left: 20px;
}

.alert ul li {
    margin-bottom: 5px;
}
</style>

<script>
// Valider que la date de fin est après la date de début
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const startDateInput = document.querySelector('input[name="date_debut"]');
    const endDateInput = document.querySelector('input[name="date_fin"]');
    
    form.addEventListener('submit', function(e) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (endDate <= startDate) {
            e.preventDefault();
            alert('La date de fin doit être après la date de début.');
            endDateInput.focus();
        }
        
        // Avertir si la maintenance est planifiée dans le passé
        if (startDate < new Date()) {
            if (!confirm('La maintenance est planifiée dans le passé. Continuer ?')) {
                e.preventDefault();
            }
        }
    });
});
</script>

@endsection