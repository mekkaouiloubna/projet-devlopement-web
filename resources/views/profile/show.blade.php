@extends('layouts.app')

@section('content')
<div class="container">
    <!-- En-tête de page -->
    <div class="header-sec">
        <div class="header-sec-title">
            <h1>
                <i class="fas fa-user-circle"></i> Profil Utilisateur
            </h1>
            <p>
                Informations détaillées et activités de l'utilisateur
            </p>
        </div>
        <div class="header-sec-actions">
            @if(Auth::user()->isAdmin() || Auth::id() == $user->id)
                <a href="{{ route('profile.edit', $user->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Modifier le profil
                </a>
            @endif
            @if(Auth::user()->isAdmin())
                <a href="{{ route('users.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Colonne gauche : Informations utilisateur -->
        <div class="col-md-4">
            <!-- Carte profil -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-id-card"></i> Informations personnelles</h3>
                </div>
                <div class="card-body text-center">
                    <!-- Avatar -->
                    <div class="user-avatar-xl mb-3">
                        {{ strtoupper(substr($user->prenom, 0, 1) . substr($user->nom, 0, 1)) }}
                    </div>
                    
                    <!-- Nom et prénom -->
                    <h3 class="mb-1">{{ $user->nom }} {{ $user->prenom }}</h3>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    
                    <!-- Badges statut et rôle -->
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge 
                            @if($user->role->nom == 'Admin') badge-danger
                            @elseif($user->role->nom == 'Responsable') badge-warning
                            @else badge-info @endif">
                            <i class="fas fa-user-tag"></i> {{ ucfirst($user->role->nom) }}
                        </span>
                        
                        <span class="badge 
                            @if($user->is_active && $user->account_status == 'active') badge-success
                            @elseif($user->account_status == 'pending') badge-warning
                            @else badge-danger @endif">
                            @if($user->account_status == 'pending')
                                <i class="fas fa-clock"></i> En attente
                            @elseif($user->is_active)
                                <i class="fas fa-check-circle"></i> Actif
                            @else
                                <i class="fas fa-times-circle"></i> Inactif
                            @endif
                            {{ ucfirst($user->account_status) }}
                        </span>
                    </div>
                    
                    <!-- Informations supplémentaires -->
                    <div class="user-info-list">
                        <div class="info-item">
                            <i class="fas fa-id-badge"></i>
                            <span>ID: <strong>#{{ $user->id }}</strong></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Inscrit le: <strong>{{ $user->created_at->format('d/m/Y') }}</strong></span>
                        </div>
                        @if($user->type)
                        <div class="info-item">
                            <i class="fas fa-user-tag"></i>
                            <span>Type: <strong>{{ $user->type }}</strong></span>
                        </div>
                        @endif
                        @if($user->email_verified_at)
                        <div class="info-item">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Email vérifié</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Carte ressources gérées (si responsable) -->
            @if($user->resourcesGerees->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-server"></i> Ressources gérées</h3>
                </div>
                <div class="card-body">
                    <div class="resources-managed-list">
                        @foreach($user->resourcesGerees as $resource)
                            <div class="resource-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $resource->nom }}</strong>
                                        <small class="d-block text-muted">{{ $resource->category->nom }}</small>
                                    </div>
                                    <span class="badge 
                                        @if($resource->statut == 'disponible') badge-success
                                        @elseif($resource->statut == 'réservé') badge-warning
                                        @elseif($resource->statut == 'maintenance') badge-info
                                        @else badge-danger @endif">
                                        {{ $resource->statut }}
                                    </span>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <hr class="my-2">
                            @endif
                        @endforeach
                    </div>
                    <p class="text-center mt-3 mb-0">
                        <small class="text-muted">{{ $user->resourcesGerees->count() }} ressource(s) gérée(s)</small>
                    </p>
                </div>
            </div>
            @endif
        </div>

        <!-- Colonne droite : Activités et statistiques -->
        <div class="col-md-8">
            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-card info">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $reservationsCount }}</h3>
                            <p>Réservations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $conversationsCount }}</h3>
                            <p>Messages</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Réservations récentes -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3><i class="fas fa-history"></i> Réservations récentes</h3>
                    @if($user->reservations->count() > 0)
                        <a href="{{ route('reservations.index') }}?user_id={{ $user->id }}" class="btn btn-sm btn-outline">
                            Voir toutes
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($user->reservations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Ressource</th>
                                        <th>Date début</th>
                                        <th>Date fin</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->reservations->sortByDesc('created_at')->take(5) as $reservation)
                                        <tr>
                                            <td>
                                                <strong>{{ $reservation->resource->nom }}</strong>
                                                <small class="d-block text-muted">{{ $reservation->resource->category->nom }}</small>
                                            </td>
                                            <td>{{ $reservation->date_debut->format('d/m/Y H:i') }}</td>
                                            <td>{{ $reservation->date_fin->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($reservation->statut == 'approuvée') badge-success
                                                    @elseif($reservation->statut == 'en attente') badge-warning
                                                    @elseif($reservation->statut == 'active') badge-info
                                                    @else badge-secondary @endif">
                                                    {{ ucfirst($reservation->statut) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('reservations.show', $reservation->id) }}" 
                                                   class="btn btn-sm btn-outline">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune réservation</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Activités récentes -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Activités récentes</h3>
                </div>
                <div class="card-body">
                    @if($user->historyLogs->count() > 0)
                        <div class="activity-timeline">
                            @foreach($user->historyLogs->sortByDesc('created_at')->take(10) as $log)
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        @if($log->action == 'création')
                                            <i class="fas fa-plus-circle text-success"></i>
                                        @elseif($log->action == 'modification')
                                            <i class="fas fa-edit text-warning"></i>
                                        @elseif($log->action == 'annulation' || $log->action == 'suppression')
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        @elseif($log->action == 'approbation')
                                            <i class="fas fa-check-circle text-success"></i>
                                        @else
                                            <i class="fas fa-info-circle text-info"></i>
                                        @endif
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-description">
                                            {{ $log->description }}
                                        </div>
                                        <div class="activity-meta">
                                            <small class="text-muted">
                                                {{ $log->created_at->diffForHumans() }}
                                                @if($log->user && $log->user->id != $user->id)
                                                    • Par {{ $log->user->prenom }} {{ $log->user->nom }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @if(!$loop->last)
                                    <hr class="my-2">
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune activité récente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles CSS -->
<style>
/* Avatar utilisateur */
.user-avatar-xl {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 2rem;
    margin: 0 auto;
}

/* Liste d'informations */
.user-info-list {
    text-align: left;
    margin-top: 20px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.info-item:last-child {
    border-bottom: none;
}

.info-item i {
    width: 20px;
    color: #3498db;
}

/* Ressources gérées */
.resource-item {
    padding: 10px 0;
}

.resources-managed-list {
    max-height: 300px;
    overflow-y: auto;
}

/* Timeline des activités */
.activity-timeline {
    position: relative;
    padding-left: 30px;
}

.activity-item {
    position: relative;
    margin-bottom: 15px;
}

.activity-item:last-child {
    margin-bottom: 0;
}

.activity-icon {
    position: absolute;
    left: -30px;
    top: 0;
    width: 24px;
    height: 24px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #dee2e6;
}

.activity-content {
    margin-left: 10px;
}

.activity-description {
    font-size: 0.95rem;
    color: #333;
}

.activity-meta {
    font-size: 0.85rem;
    color: #6c757d;
}

/* Cards de statistiques */
.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
    height: 100%;
}

.stat-card.info {
    border-left: 4px solid #3498db;
}

.stat-card.warning {
    border-left: 4px solid #f39c12;
}

.stat-card .stat-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-card.warning .stat-icon {
    background: linear-gradient(135deg, #f39c12, #e67e22);
}

.stat-content h3 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: bold;
}

.stat-content p {
    margin: 5px 0 0;
    color: #6c757d;
}

/* Header */
.header-sec {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.header-sec-title h1 {
    margin: 0;
    font-size: 1.8rem;
    color: #333;
}

.header-sec-title p {
    margin: 5px 0 0;
    color: #6c757d;
}

.header-sec-actions {
    display: flex;
    gap: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .header-sec {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .stat-card {
        margin-bottom: 15px;
    }
    
    .user-avatar-xl {
        width: 80px;
        height: 80px;
        font-size: 1.5rem;
    }
}
</style>
@endsection