@extends('layouts.app')
@section('title', 'Tableau de bord - Utilisateur')
@section('page-title', 'Tableau de bord')

@section('content')
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header-sec mt-6">
            <div class="header-sec-title">
                <h1><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord</h1>
                <p>Gérez vos réservations en temps réel</p>
            </div>
            <div class="header-sec-date">
                <i class="fas fa-calendar-alt"></i> {{ now()->format('d/m/Y') }}
            </div>
        </div>

        <!-- Statistiques principales -->
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ auth()->user()->reservations()->where('statut', 'approuvée')->count() }}</h3>
                    <p>Réservations approuvées</p>
                </div>
            </div>

            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ auth()->user()->reservations()->where('statut', 'en attente')->count() }}</h3>
                    <p>En attente</p>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-th-large"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ \App\Models\Resource::where('est_actif', true)->where('statut', 'disponible')->count() }}</h3>
                    <p>Ressources disponibles</p>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ auth()->user()->notifications()->where('est_lu', false)->count() }}</h3>
                    <p>Notifications non lues</p>
                </div>
            </div>
        </div>

        <!-- Dashboard sections-->
        <div class="dashboard-sections">
            <div class="quick-actions-column">
                <div class="quick-actions-grid">
                    <a href="{{ route('profile') }}" class="quick-action">
                        <div class="action-icon"><i class="fas fa-user"></i></div>
                        <span>Mon profil</span>
                    </a>
                    <a href="{{ route('reservations.create') }}" class="quick-action">
                        <div class="action-icon"><i class="fas fa-calendar-alt"></i></div>
                        <span>Nouvelle réservation</span>
                    </a>
                    <a href="{{ route('resources.index') }}" class="quick-action">
                        <div class="action-icon"><i class="fas fa-th-large"></i></div>
                        <span>Parcourir ressources</span>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="quick-action">
                        <div class="action-icon"><i class="fas fa-bell"></i></div>
                        <span>Notifications</span>
                        @if(auth()->user()->notifications()->where('est_lu', false)->count() > 0)
                            <span
                                class="notification-badge unread-badge">{{ auth()->user()->notifications()->where('est_lu', false)->count() }}</span>
                        @endif
                    </a>
                </div>
            </div>

            <div class="recent-reservations-column">
                <!-- Réservations récentes -->
                <div class="section-content">
                    <div class="section-header">
                        <h2>Mes réservations récentes</h2>
                        <a href="{{ route('reservations.index') }}" class="btn btn-outline btn-sm">Voir tout</a>
                    </div>
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
                                                    <div class="date-start">{{ $reservation->date_debut->format('d/m/Y H:i') }}
                                                    </div>
                                                    <div class="date-end">{{ $reservation->date_fin->format('d/m/Y H:i') }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'en attente' => 'warning',
                                                        'approuvée' => 'success',
                                                        'refusée' => 'danger',
                                                        'active' => 'info',
                                                        'terminée' => 'primary',
                                                    ];
                                                @endphp
                                                <span class="w-70 ta-center badge badge-{{ $statusColors[$reservation->statut]}}">
                                                    {{ ucfirst(str_replace('_', ' ', $reservation->statut)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a title="Voir detaille" href="{{ route('reservations.show', $reservation->id) }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($reservation->statut == 'en attente' || $reservation->statut == 'approuvée')
                                                        <form action="{{ route('reservations.cancel', $reservation->id) }}" method="POST" class="d-inline">
                                                            @csrf @method('PATCH')
                                                            <button title="Annuler" type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                                                <i class="fas fa-ban me-2"></i>
                                                            </button>
                                                        </form>
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
                            <div class="empty-icon"><i class="fas fa-calendar-alt"></i></div>
                            <h3>Aucune réservation</h3>
                            <p>Vous n'avez pas encore de réservation.</p>
                            <a href="{{ route('reservations.create') }}" class="btn btn-primary">Faire une réservation</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .recent-reservations-column {
            display: flex;
            flex-direction: column;
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

        /* État vide */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-icon {
            font-size: 3rem;
            color: #e2e8f0;
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
    </style>
@endsection