@extends('layouts.app')

@section('content')
<div class="container">
    <!-- En-tête de page -->
    <div class="header-sec">
        <div class="header-sec-title">
            <h1>
                <i class="fas fa-tools"></i> Détails de la Maintenance
            </h1>
            <p>
                Informations détaillées sur la maintenance
            </p>
        </div>
        <div class="header-sec-actions">
            <a href="{{ route('maintenance.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
            @if($maintenance->statut != 'terminée')
                <a href="{{ route('maintenance.edit', $maintenance->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Modifier
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Colonne gauche : Informations principales -->
        <div class="col-md-8">
            <!-- Carte informations -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Informations de la maintenance</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label">Ressource concernée</label>
                                <div class="resource-card p-3 border rounded">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="resource-icon-lg">
                                            <i class="fas fa-server"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-1">{{ $maintenance->resource->nom }}</h4>
                                            <p class="mb-1 text-muted">{{ $maintenance->resource->category->nom }}</p>
                                            <small class="text-muted">
                                                Responsable: 
                                                @if($maintenance->resource->responsable)
                                                    {{ $maintenance->resource->responsable->nom }} {{ $maintenance->resource->responsable->prenom }}
                                                @else
                                                    Non assigné
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label">Statut</label>
                                <div class="status-display">
                                    <span class="badge 
                                        @if($maintenance->statut == 'planifiée') badge-warning
                                        @elseif($maintenance->statut == 'en cours') badge-info
                                        @else badge-success @endif
                                        p-3 fs-6">
                                        <i class="fas 
                                            @if($maintenance->statut == 'planifiée') fa-clock
                                            @elseif($maintenance->statut == 'en cours') fa-spinner fa-spin
                                            @else fa-check-circle @endif
                                        "></i>
                                        {{ ucfirst($maintenance->statut) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label">Date de début</label>
                                <div class="date-display bg-light p-3 rounded">
                                    <i class="fas fa-calendar-alt text-primary"></i>
                                    <strong>{{ \Carbon\Carbon::parse($maintenance->date_debut)->format('d/m/Y H:i') }}</strong>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label">Date de fin</label>
                                <div class="date-display bg-light p-3 rounded">
                                    <i class="fas fa-calendar-alt text-primary"></i>
                                    <strong>{{ \Carbon\Carbon::parse($maintenance->date_fin)->format('d/m/Y H:i') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-group mt-4">
                        <label class="form-label">Raison de la maintenance</label>
                        <div class="reason-display p-3 bg-light rounded">
                            <p class="mb-0">{{ $maintenance->raison }}</p>
                        </div>
                    </div>
                    
                    <!-- Progression temporelle -->
                    <div class="info-group mt-4">
                        <label class="form-label">Progression</label>
                        <div class="timeline-progress">
                            @php
                                $now = now();
                                $start = \Carbon\Carbon::parse($maintenance->date_debut);
                                $end = \Carbon\Carbon::parse($maintenance->date_fin);
                                $totalDuration = $start->diffInSeconds($end);
                                $elapsedDuration = $now->diffInSeconds($start);
                                
                                if ($totalDuration > 0) {
                                    $percentage = min(100, max(0, ($elapsedDuration / $totalDuration) * 100));
                                } else {
                                    $percentage = 0;
                                }
                                
                                if ($now->lt($start)) {
                                    $statusText = 'Pas encore commencé';
                                    $statusClass = 'bg-secondary';
                                } elseif ($now->gt($end)) {
                                    $statusText = 'Terminé';
                                    $statusClass = 'bg-success';
                                    $percentage = 100;
                                } else {
                                    $statusText = 'En cours';
                                    $statusClass = 'bg-info';
                                }
                            @endphp
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ $start->format('d/m H:i') }}</span>
                                <span class="text-muted">{{ $statusText }}</span>
                                <span>{{ $end->format('d/m H:i') }}</span>
                            </div>
                            
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar {{ $statusClass }}" 
                                     role="progressbar" 
                                     style="width: {{ $percentage }}%"
                                     aria-valuenow="{{ $percentage }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Carte impact -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-exclamation-triangle"></i> Impact sur les réservations</h3>
                </div>
                <div class="card-body">
                    @php
                        $affectedReservations = \App\Models\Reservation::where('resource_id', $maintenance->resource_id)
                            ->where(function($q) use ($maintenance) {
                                $q->whereBetween('date_debut', [$maintenance->date_debut, $maintenance->date_fin])
                                  ->orWhereBetween('date_fin', [$maintenance->date_debut, $maintenance->date_fin])
                                  ->orWhere(function($q2) use ($maintenance) {
                                      $q2->where('date_debut', '<=', $maintenance->date_debut)
                                         ->where('date_fin', '>=', $maintenance->date_fin);
                                  });
                            })
                            ->with('user')
                            ->get();
                    @endphp
                    
                    @if($affectedReservations->count() > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle"></i>
                            Cette maintenance affecte {{ $affectedReservations->count() }} réservation(s)
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Date début</th>
                                        <th>Date fin</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($affectedReservations as $reservation)
                                        <tr>
                                            <td>{{ $reservation->user->nom }} {{ $reservation->user->prenom }}</td>
                                            <td>{{ \Carbon\Carbon::parse($reservation->date_debut)->format('d/m/Y H:i') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($reservation->date_fin)->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($reservation->statut == 'approuvée') badge-success
                                                    @elseif($reservation->statut == 'en attente') badge-warning
                                                    @elseif($reservation->statut == 'active') badge-info
                                                    @else badge-secondary @endif">
                                                    {{ ucfirst($reservation->statut) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            Aucune réservation n'est affectée par cette maintenance
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Colonne droite : Actions et métadonnées -->
        <div class="col-md-4">
            <!-- Carte actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-cogs"></i> Actions</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($maintenance->statut == 'planifiée')
                            <form action="{{ route('maintenance.update', $maintenance->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="statut" value="en cours">
                                <button type="submit" class="btn btn-info w-100 mb-2">
                                    <i class="fas fa-play"></i> Démarrer la maintenance
                                </button>
                            </form>
                        @endif
                        
                        @if($maintenance->statut == 'en cours')
                            <form action="{{ route('maintenance.update', $maintenance->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="statut" value="terminée">
                                <button type="submit" class="btn btn-success w-100 mb-2"
                                        onclick="return confirm('Marquer cette maintenance comme terminée ?')">
                                    <i class="fas fa-check"></i> Terminer la maintenance
                                </button>
                            </form>
                        @endif
                        
                        @if($maintenance->statut != 'terminée')
                            <a href="{{ route('maintenance.edit', $maintenance->id) }}" 
                               class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                        @endif
                        
                        <form action="{{ route('maintenance.destroy', $maintenance->id) }}" 
                              method="POST" class="d-grid"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette maintenance ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Carte métadonnées -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Historique</h3>
                </div>
                <div class="card-body">
                    <div class="metadata-list">
                        <div class="metadata-item">
                            <i class="fas fa-calendar-plus"></i>
                            <div>
                                <strong>Créée le</strong>
                                <p class="mb-0">{{ $maintenance->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        
                        <div class="metadata-item">
                            <i class="fas fa-calendar-edit"></i>
                            <div>
                                <strong>Dernière modification</strong>
                                <p class="mb-0">{{ $maintenance->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        
                        <div class="metadata-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Durée totale</strong>
                                <p class="mb-0">
                                    {{ \Carbon\Carbon::parse($maintenance->date_debut)->diffInHours($maintenance->date_fin) }} heures
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.resource-icon-lg {
    width: 60px;
    height: 60px;
    background: #e3f2fd;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3498db;
    font-size: 1.5rem;
}

.resource-card {
    border-left: 4px solid #3498db;
}

.info-group {
    margin-bottom: 1.5rem;
}

.info-group:last-child {
    margin-bottom: 0;
}

.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.date-display {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
}

.reason-display {
    font-size: 1rem;
    line-height: 1.6;
}

.status-display .badge {
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
}

.metadata-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.metadata-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.metadata-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.metadata-item i {
    font-size: 1.2rem;
    color: #3498db;
    width: 24px;
}

.metadata-item div {
    flex: 1;
}

.metadata-item strong {
    display: block;
    font-size: 0.9rem;
    color: #666;
}

.metadata-item p {
    font-size: 1rem;
    margin: 5px 0 0;
}

.timeline-progress .progress {
    border-radius: 5px;
    overflow: hidden;
}

.d-grid {
    display: grid;
}
</style>

@endsection