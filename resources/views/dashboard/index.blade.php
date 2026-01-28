@extends('layouts.app')

@section('title', 'Tableau de bord - Utilisateur')

@section('page-title', 'Tableau de bord')

@section('breadcrumbs')
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item">Tableau de bord</span>
@endsection

@section('content')
<div class="dashboard-container">
    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="4" width="18" height="18" rx="2" stroke="white" stroke-width="2"/>
                    <path d="M16 2V6" stroke="white" stroke-width="2"/>
                    <path d="M8 2V6" stroke="white" stroke-width="2"/>
                    <path d="M3 10H21" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>{{ auth()->user()->reservations()->where('statut', 'approuvée')->count() }}</h3>
                <p>Réservations approuvées</p>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="white" stroke-width="2"/>
                    <path d="M12 8V12" stroke="white" stroke-width="2"/>
                    <path d="M12 16H12.01" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>{{ auth()->user()->reservations()->where('statut', 'en_attente')->count() }}</h3>
                <p>En attente</p>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="white" stroke-width="2"/>
                    <path d="M3 9H21" stroke="white" stroke-width="2"/>
                    <path d="M9 21V9" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>{{ \App\Models\Resource::where('est_actif', true)->where('statut', 'disponible')->count() }}</h3>
                <p>Ressources disponibles</p>
            </div>
        </div>

        <div class="stat-card primary">
            <div class="stat-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="white" stroke-width="2"/>
                    <path d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>{{ auth()->user()->notifications()->where('est_lu', false)->count() }}</h3>
                <p>Notifications non lues</p>
            </div>
        </div>
    </div>

    <!-- Sections principales -->
    <div class="dashboard-sections">
        <!-- Section des réservations récentes -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Mes réservations récentes</h2>
                <a href="{{ route('reservations.index') }}" class="btn-view-all">Voir tout</a>
            </div>
            
            <div class="section-content">
                @if(auth()->user()->reservations()->count() > 0)
                    <div class="reservations-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Ressource</th>
                                    <th>Période</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(auth()->user()->reservations()->latest()->take(5)->get() as $reservation)
                                    <tr>
                                        <td>
                                            <div class="resource-info">
                                                <div class="resource-name">{{ $reservation->resource->nom }}</div>
                                                <div class="resource-category">{{ $reservation->resource->category->nom }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="date-info">
                                                <div class="date-start">{{ $reservation->date_debut->format('d/m/Y H:i') }}</div>
                                                <div class="date-end">{{ $reservation->date_fin->format('d/m/Y H:i') }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'en_attente' => 'warning',
                                                    'approuvée' => 'success',
                                                    'refusée' => 'danger',
                                                    'active' => 'info',
                                                    'terminée' => 'secondary'
                                                ];
                                            @endphp
                                            <span class="badge badge-{{ $statusColors[$reservation->statut] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $reservation->statut)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('reservations.show', $reservation->id) }}" class="btn-action view" title="Voir détails">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                        <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2"/>
                                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                                    </svg>
                                                </a>
                                                @if($reservation->statut == 'en_attente' || $reservation->statut == 'approuvée')
                                                    <button onclick="cancelReservation({{ $reservation->id }})" class="btn-action cancel" title="Annuler">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                            <path d="M18 6L6 18" stroke="currentColor" stroke-width="2"/>
                                                            <path d="M6 6L18 18" stroke="currentColor" stroke-width="2"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none">
                                <rect x="3" y="4" width="18" height="18" rx="2" stroke="#95a5a6" stroke-width="2"/>
                                <path d="M16 2V6" stroke="#95a5a6" stroke-width="2"/>
                                <path d="M8 2V6" stroke="#95a5a6" stroke-width="2"/>
                                <path d="M3 10H21" stroke="#95a5a6" stroke-width="2"/>
                            </svg>
                        </div>
                        <h3>Aucune réservation</h3>
                        <p>Vous n'avez pas encore de réservation.</p>
                        <a href="{{ route('reservations.create') }}" class="btn btn-primary">
                            Faire une réservation
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Section des ressources populaires -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Ressources populaires</h2>
                <a href="{{ route('resources.index') }}" class="btn-view-all">Explorer</a>
            </div>
            
            <div class="section-content">
                <div class="resources-grid">
                    @foreach(\App\Models\Resource::where('est_actif', true)->orderByDesc('id')->take(3)->get() as $resource)
                        <div class="resource-card">
                            <div class="resource-header">
                                <div class="resource-status status-{{ $resource->statut }}">
                                    {{ ucfirst($resource->statut) }}
                                </div>
                                <h3>{{ $resource->nom }}</h3>
                                <p class="resource-category">{{ $resource->category->nom }}</p>
                            </div>
                            
                            <div class="resource-specs">
                                @php
                                    $specs = json_decode($resource->specifications, true);
                                @endphp
                                @if($specs)
                                    <div class="spec-item">
                                        <span class="spec-label">CPU:</span>
                                        <span class="spec-value">{{ $specs['cpu'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="spec-item">
                                        <span class="spec-label">RAM:</span>
                                        <span class="spec-value">{{ $specs['ram'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="spec-item">
                                        <span class="spec-label">Stockage:</span>
                                        <span class="spec-value">{{ $specs['stockage'] ?? $specs['capacite'] ?? 'N/A' }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="resource-footer">
                                <a href="{{ route('resources.show', $resource->id) }}" class="btn btn-outline btn-sm">
                                    Voir détails
                                </a>
                                <a href="{{ route('reservations.create', ['resource_id' => $resource->id]) }}" 
                                   class="btn btn-primary btn-sm" 
                                   {{ $resource->statut != 'disponible' ? 'disabled' : '' }}>
                                    Réserver
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Section des actions rapides et notifications -->
    <div class="dashboard-bottom">
        <!-- Actions rapides -->
        <div class="quick-actions-section">
            <h3>Actions rapides</h3>
            <div class="quick-actions-grid">
                <a href="{{ route('reservations.create') }}" class="quick-action">
                    <div class="action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="4" width="18" height="18" rx="2" stroke="#3498db" stroke-width="2"/>
                            <path d="M16 2V6" stroke="#3498db" stroke-width="2"/>
                            <path d="M8 2V6" stroke="#3498db" stroke-width="2"/>
                            <path d="M3 10H21" stroke="#3498db" stroke-width="2"/>
                            <path d="M9 16H15" stroke="#3498db" stroke-width="2"/>
                        </svg>
                    </div>
                    <span>Nouvelle réservation</span>
                </a>
                
                <a href="{{ route('resources.index') }}" class="quick-action">
                    <div class="action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="#27ae60" stroke-width="2"/>
                            <path d="M3 9H21" stroke="#27ae60" stroke-width="2"/>
                            <path d="M9 21V9" stroke="#27ae60" stroke-width="2"/>
                        </svg>
                    </div>
                    <span>Parcourir ressources</span>
                </a>
                
                <a href="{{ route('notifications.index') }}" class="quick-action">
                    <div class="action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="#f39c12" stroke-width="2"/>
                            <path d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21" stroke="#f39c12" stroke-width="2"/>
                        </svg>
                    </div>
                    <span>Notifications</span>
                    @if(auth()->user()->notifications()->where('est_lu', false)->count() > 0)
                        <span class="notification-badge">{{ auth()->user()->notifications()->where('est_lu', false)->count() }}</span>
                    @endif
                </a>
                
                <a href="{{ route('profile') }}" class="quick-action">
                    <div class="action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z" stroke="#9b59b6" stroke-width="2"/>
                            <path d="M6 20C6 17.7909 7.79086 16 10 16H14C16.2091 16 18 17.7909 18 20" stroke="#9b59b6" stroke-width="2"/>
                        </svg>
                    </div>
                    <span>Mon profil</span>
                </a>
            </div>
        </div>

        <!-- Notifications récentes -->
        <div class="notifications-section">
            <div class="section-header">
                <h3>Notifications récentes</h3>
                <a href="{{ route('notifications.index') }}" class="btn-mark-all">Marquer comme lues</a>
            </div>
            
            <div class="notifications-list">
                @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notification)
                    <div class="notification-item {{ !$notification->est_lu ? 'unread' : '' }}">
                        <div class="notification-icon">
                            @switch($notification->type)
                                @case('réservation')
                                    <div class="icon-reservation">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#3498db">
                                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                                        </svg>
                                    </div>
                                    @break
                                @case('maintenance')
                                    <div class="icon-maintenance">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#f39c12">
                                            <path d="M14 10L12 11.5L14 13M10 10L12 11.5L10 13M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"/>
                                        </svg>
                                    </div>
                                    @break
                                @default
                                    <div class="icon-system">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#1abc9c">
                                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"/>
                                        </svg>
                                    </div>
                            @endswitch
                        </div>
                        <div class="notification-content">
                            <h4>{{ $notification->titre }}</h4>
                            <p>{{ Str::limit($notification->message, 60) }}</p>
                            <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        @if(!$notification->est_lu)
                            <div class="notification-indicator"></div>
                        @endif
                    </div>
                @empty
                    <div class="empty-notifications">
                        <p>Aucune notification</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    .dashboard-container {
        padding: 20px 0;
    }

    /* Statistiques */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: linear-gradient(135deg, var(--card-color, #3498db) 0%, var(--card-color-dark, #2980b9) 100%);
        border-radius: 15px;
        padding: 25px;
        color: white;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .stat-card.success {
        --card-color: #27ae60;
        --card-color-dark: #219653;
    }

    .stat-card.warning {
        --card-color: #f39c12;
        --card-color-dark: #e67e22;
    }

    .stat-card.info {
        --card-color: #3498db;
        --card-color-dark: #2980b9;
    }

    .stat-card.primary {
        --card-color: #9b59b6;
        --card-color-dark: #8e44ad;
    }

    .stat-icon svg {
        width: 40px;
        height: 40px;
    }

    .stat-content h3 {
        font-size: 2.5rem;
        margin: 0 0 5px 0;
        color: white;
        font-weight: 700;
    }

    .stat-content p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    /* Sections du tableau de bord */
    .dashboard-sections {
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
        margin-bottom: 40px;
    }

    @media (min-width: 1200px) {
        .dashboard-sections {
            grid-template-columns: 2fr 1fr;
        }
    }

    .dashboard-section {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .section-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-header h2 {
        margin: 0;
        font-size: 1.5rem;
        color: #2c3e50;
    }

    .btn-view-all {
        color: #3498db;
        font-weight: 500;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .btn-view-all:hover {
        color: #2980b9;
        text-decoration: underline;
    }

    .section-content {
        padding: 20px;
    }

    /* Table des réservations */
    .reservations-table {
        overflow-x: auto;
    }

    .reservations-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .reservations-table th {
        background-color: #f8f9fa;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 2px solid #eee;
    }

    .reservations-table td {
        padding: 15px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }

    .reservations-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .resource-info .resource-name {
        font-weight: 500;
        color: #2c3e50;
    }

    .resource-info .resource-category {
        font-size: 0.85rem;
        color: #95a5a6;
        margin-top: 3px;
    }

    .date-info .date-start,
    .date-info .date-end {
        font-size: 0.9rem;
    }

    .date-info .date-end {
        color: #95a5a6;
        margin-top: 2px;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: none;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-action:hover {
        background-color: #f8f9fa;
        border-color: #adb5bd;
    }

    .btn-action.view {
        color: #3498db;
    }

    .btn-action.cancel {
        color: #e74c3c;
    }

    /* État vide */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }

    .empty-icon svg {
        width: 60px;
        height: 60px;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #95a5a6;
        margin-bottom: 20px;
    }

    /* Grille des ressources */
    .resources-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .resource-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        transition: box-shadow 0.3s ease;
    }

    .resource-card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .resource-header {
        margin-bottom: 15px;
    }

    .resource-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .status-disponible {
        background-color: #d4edda;
        color: #155724;
    }

    .status-réservé {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-maintenance {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .resource-header h3 {
        font-size: 1.2rem;
        margin: 0 0 5px 0;
        color: #2c3e50;
    }

    .resource-category {
        color: #95a5a6;
        font-size: 0.9rem;
        margin: 0;
    }

    .resource-specs {
        margin: 15px 0;
        padding: 15px 0;
        border-top: 1px solid #e9ecef;
        border-bottom: 1px solid #e9ecef;
    }

    .spec-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .spec-item:last-child {
        margin-bottom: 0;
    }

    .spec-label {
        color: #6c757d;
        font-weight: 500;
    }

    .spec-value {
        color: #2c3e50;
        font-weight: 600;
    }

    .resource-footer {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .resource-footer .btn {
        flex: 1;
        padding: 8px 12px;
        font-size: 0.85rem;
    }

    /* Section inférieure */
    .dashboard-bottom {
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
    }

    @media (min-width: 992px) {
        .dashboard-bottom {
            grid-template-columns: 1fr 1fr;
        }
    }

    /* Actions rapides */
    .quick-actions-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .quick-actions-section h3 {
        margin: 0 0 20px 0;
        color: #2c3e50;
        font-size: 1.3rem;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }

    .quick-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 20px;
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        text-decoration: none;
        color: #495057;
        transition: all 0.3s ease;
        position: relative;
    }

    .quick-action:hover {
        border-color: #3498db;
        background-color: #f8f9fa;
        color: #3498db;
        transform: translateY(-3px);
    }

    .action-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        transition: background-color 0.3s ease;
    }

    .quick-action:hover .action-icon {
        background-color: #e9ecef;
    }

    .action-icon svg {
        width: 24px;
        height: 24px;
    }

    .quick-action span {
        font-weight: 500;
        font-size: 0.95rem;
    }

    .notification-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #e74c3c;
        color: white;
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 600;
    }

    /* Notifications */
    .notifications-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .notifications-section .section-header {
        padding: 0;
        border: none;
        margin-bottom: 20px;
    }

    .notifications-section h3 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.3rem;
    }

    .btn-mark-all {
        color: #95a5a6;
        font-size: 0.9rem;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .btn-mark-all:hover {
        color: #3498db;
    }

    .notifications-list {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 10px;
    }

    .notification-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 10px;
        transition: background-color 0.3s ease;
        position: relative;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
    }

    .notification-item.unread {
        background-color: #f0f9ff;
    }

    .notification-icon {
        flex-shrink: 0;
    }

    .notification-content {
        flex: 1;
    }

    .notification-content h4 {
        margin: 0 0 5px 0;
        font-size: 0.95rem;
        color: #2c3e50;
    }

    .notification-content p {
        margin: 0 0 5px 0;
        font-size: 0.85rem;
        color: #6c757d;
        line-height: 1.4;
    }

    .notification-time {
        font-size: 0.75rem;
        color: #95a5a6;
    }

    .notification-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #3498db;
        position: absolute;
        top: 20px;
        right: 15px;
    }

    .empty-notifications {
        text-align: center;
        padding: 30px;
        color: #95a5a6;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    .badge-info {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .badge-secondary {
        background-color: #e9ecef;
        color: #495057;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .resources-grid {
            grid-template-columns: 1fr;
        }
        
        .quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .reservations-table table {
            font-size: 0.9rem;
        }
        
        .reservations-table th,
        .reservations-table td {
            padding: 10px;
        }
    }

    @media (max-width: 576px) {
        .quick-actions-grid {
            grid-template-columns: 1fr;
        }
        
        .resource-footer {
            flex-direction: column;
        }
        
        .action-buttons {
            justify-content: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation des cartes de statistiques
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('fade-in');
        });

        // Gestion de l'annulation des réservations
        window.cancelReservation = function(reservationId) {
            if (confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
                fetch(`/reservations/${reservationId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Afficher un message de succès
                        showToast('Réservation annulée avec succès', 'success');
                        
                        // Recharger la page après 1.5 secondes
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast(data.message || 'Erreur lors de l\'annulation', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Erreur de connexion', 'error');
                });
            }
        };

        // Fonction pour afficher les toasts
        function showToast(message, type = 'info') {
            // Supprimer les anciens toasts
            const oldToasts = document.querySelectorAll('.custom-toast');
            oldToasts.forEach(toast => toast.remove());

            // Créer le toast
            const toast = document.createElement('div');
            toast.className = `custom-toast toast-${type}`;
            toast.innerHTML = `
                <div class="toast-content">
                    <div class="toast-message">${message}</div>
                    <button class="toast-close">&times;</button>
                </div>
            `;

            // Ajouter au body
            document.body.appendChild(toast);

            // Animation d'entrée
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            // Fermer le toast
            const closeBtn = toast.querySelector('.toast-close');
            closeBtn.addEventListener('click', () => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            });

            // Fermeture automatique après 5 secondes
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        }

        // Styles pour les toasts
        const toastStyles = document.createElement('style');
        toastStyles.textContent = `
            .custom-toast {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
                padding: 15px 20px;
                min-width: 300px;
                max-width: 400px;
                z-index: 9999;
                transform: translateY(100px);
                opacity: 0;
                transition: transform 0.3s ease, opacity 0.3s ease;
            }

            .custom-toast.show {
                transform: translateY(0);
                opacity: 1;
            }

            .toast-success {
                border-left: 4px solid #27ae60;
            }

            .toast-error {
                border-left: 4px solid #e74c3c;
            }

            .toast-info {
                border-left: 4px solid #3498db;
            }

            .toast-warning {
                border-left: 4px solid #f39c12;
            }

            .toast-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 15px;
            }

            .toast-message {
                flex: 1;
                font-weight: 500;
                color: #2c3e50;
            }

            .toast-close {
                background: none;
                border: none;
                font-size: 1.2rem;
                color: #95a5a6;
                cursor: pointer;
                padding: 0;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
                transition: background-color 0.2s ease;
            }

            .toast-close:hover {
                background-color: #f8f9fa;
                color: #e74c3c;
            }

            @media (max-width: 576px) {
                .custom-toast {
                    left: 20px;
                    right: 20px;
                    min-width: auto;
                    max-width: none;
                }
            }
        `;
        document.head.appendChild(toastStyles);

        // Mise à jour en temps réel des notifications
        function updateNotificationCount() {
            fetch('/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    // Mettre à jour le badge dans la carte de statistiques
                    const statBadge = document.querySelector('.stat-card.primary .stat-content h3');
                    if (statBadge) {
                        statBadge.textContent = data.count;
                    }

                    // Mettre à jour le badge dans les actions rapides
                    const quickBadge = document.querySelector('.quick-action .notification-badge');
                    if (quickBadge) {
                        if (data.count > 0) {
                            quickBadge.textContent = data.count;
                            quickBadge.style.display = 'inline-block';
                        } else {
                            quickBadge.style.display = 'none';
                        }
                    } else if (data.count > 0) {
                        // Ajouter le badge s'il n'existe pas
                        const quickAction = document.querySelector('.quick-action[href*="notifications"]');
                        if (quickAction) {
                            const badge = document.createElement('span');
                            badge.className = 'notification-badge';
                            badge.textContent = data.count;
                            quickAction.appendChild(badge);
                        }
                    }
                })
                .catch(error => console.error('Error updating notification count:', error));
        }

        // Mettre à jour toutes les minutes
        setInterval(updateNotificationCount, 60000);

        // Animation d'entrée pour les éléments
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        // Observer les sections
        document.querySelectorAll('.dashboard-section, .quick-actions-section, .notifications-section').forEach(section => {
            observer.observe(section);
        });
    });

    // CSS pour les animations
    const animationStyles = document.createElement('style');
    animationStyles.textContent = `
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
            opacity: 0;
        }

        .animate-in {
            animation: fadeIn 0.6s ease forwards;
        }
    `;
    document.head.appendChild(animationStyles);
</script>
@endsection