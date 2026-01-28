{{-- resources/views/admin/overview.blade.php --}}
@extends('layouts.app')

@section('title', 'Vue d\'ensemble - Administration')
@section('page-title', 'Vue d\'ensemble du système')

@section('content')
<div class="container">
    <div class="header-sec">
        <div class="header-sec-title">
            <h1><i class="fas fa-chart-pie me-2"></i>Vue d'ensemble du système</h1>
            <p>Statistiques globales et indicateurs de performance</p>
        </div>
        <div class="header-sec-date">
            <i class="fas fa-sync-alt me-2"></i>Mis à jour {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>{{ \App\Models\User::count() }}</h3>
                <p>Utilisateurs totaux</p>
            </div>
        </div>
        
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-content">
                <h3>{{ \App\Models\Resource::count() }}</h3>
                <p>Ressources</p>
            </div>
        </div>
        
        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <h3>{{ \App\Models\Reservation::count() }}</h3>
                <p>Réservations totales</p>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $occupationRate ?? 0 }}%</h3>
                <p>Taux d'occupation</p>
            </div>
        </div>
    </div>

    <!-- Graphiques et répartitions -->
    <div class="dashboard-charts">
        <!-- Utilisateurs par rôle -->
        <div class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-user-tag me-2"></i>Utilisateurs par rôle</h3>
            </div>
            <div class="chart-container">
                <canvas id="usersByRoleChart" height="250"></canvas>
            </div>
        </div>

        <!-- Statut des ressources -->
        <div class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-server me-2"></i>Statut des ressources</h3>
            </div>
            <div class="chart-container">
                <canvas id="resourcesByStatusChart" height="250"></canvas>
            </div>
        </div>

        <!-- Réservations par statut -->
        <div class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-calendar-alt me-2"></i>Réservations par statut</h3>
            </div>
            <div class="chart-container">
                <canvas id="reservationsByStatusChart" height="250"></canvas>
            </div>
        </div>

        <!-- Évolution des inscriptions -->
        <div class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-chart-line me-2"></i>Évolution mensuelle</h3>
                <select class="chart-period" id="evolutionPeriod">
                    <option value="users">Utilisateurs</option>
                    <option value="reservations" selected>Réservations</option>
                    <option value="resources">Ressources</option>
                </select>
            </div>
            <div class="chart-container">
                <canvas id="evolutionChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Tableaux détaillés -->
    <div class="detailed-tables">
        <!-- Top 5 ressources les plus utilisées -->
        <div class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-trophy me-2"></i>Top 5 ressources les plus utilisées</h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ressource</th>
                            <th>Catégorie</th>
                            <th>Responsable</th>
                            <th>Réservations</th>
                            <th>Taux d'utilisation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $topResources = \App\Models\Resource::withCount(['reservations' => function($query) {
                                $query->where('statut', '!=', 'refusée');
                            }])
                            ->with(['category', 'responsable'])
                            ->orderBy('reservations_count', 'desc')
                            ->limit(5)
                            ->get();
                        @endphp
                        
                        @forelse($topResources as $resource)
                            <tr>
                                <td>{{ $resource->nom }}</td>
                                <td>{{ $resource->category->nom ?? 'N/A' }}</td>
                                <td>{{ $resource->responsable->prenom ?? '' }} {{ $resource->responsable->nom ?? 'N/A' }}</td>
                                <td>{{ $resource->reservations_count }}</td>
                                <td>
                                    @php
                                        $totalReservations = \App\Models\Reservation::where('statut', '!=', 'refusée')->count();
                                        $usageRate = $totalReservations > 0 ? round(($resource->reservations_count / $totalReservations) * 100, 1) : 0;
                                    @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $usageRate }}%;"
                                             aria-valuenow="{{ $usageRate }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ $usageRate }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucune donnée disponible</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top 5 utilisateurs les plus actifs -->
        <div class="section-card">
            <div class="section-header">
                <h3><i class="fas fa-user-check me-2"></i>Top 5 utilisateurs les plus actifs</h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Réservations</th>
                            <th>Dernière activité</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $topUsers = \App\Models\User::withCount(['reservations' => function($query) {
                                $query->where('statut', '!=', 'refusée');
                            }])
                            ->with('role')
                            ->orderBy('reservations_count', 'desc')
                            ->limit(5)
                            ->get();
                        @endphp
                        
                        @forelse($topUsers as $user)
                            <tr>
                                <td>{{ $user->prenom }} {{ $user->nom }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role->nom ?? 'N/A' }}</td>
                                <td>{{ $user->reservations_count }}</td>
                                <td>
                                    @php
                                        $lastActivity = \App\Models\HistoryLog::where('user_id', $user->id)
                                            ->latest()
                                            ->first();
                                    @endphp
                                    {{ $lastActivity->created_at->diffForHumans() ?? 'Jamais' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucune donnée disponible</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.dashboard-charts {
    display: grid;
    grid-template-columns: 1fr;
    gap: 25px;
    margin: 30px 0;
}

@media (min-width: 992px) {
    .dashboard-charts {
        grid-template-columns: 1fr 1fr;
    }
}

.detailed-tables {
    display: grid;
    grid-template-columns: 1fr;
    gap: 25px;
    margin: 30px 0;
}

@media (min-width: 1200px) {
    .detailed-tables {
        grid-template-columns: 1fr 1fr;
    }
}

.chart-container {
    padding: 20px;
    min-height: 300px;
}

.progress {
    background-color: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(90deg, #3498db, #2980b9);
    transition: width 0.6s ease;
}

.chart-period {
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: white;
    color: #495057;
    font-size: 0.9rem;
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données pour les graphiques
    const usersByRoleData = {
        labels: {!! json_encode($usersByRole->pluck('nom') ?? []) !!},
        datasets: [{
            data: {!! json_encode($usersByRole->pluck('count') ?? []) !!},
            backgroundColor: [
                '#3498db', '#27ae60', '#f39c12', '#9b59b6', '#e74c3c'
            ]
        }]
    };

    const resourcesByStatusData = {
        labels: {!! json_encode($resourcesByStatus->pluck('statut') ?? []) !!},
        datasets: [{
            data: {!! json_encode($resourcesByStatus->pluck('count') ?? []) !!},
            backgroundColor: [
                '#27ae60', // disponible
                '#f39c12', // réservé
                '#3498db', // maintenance
                '#e74c3c'  // hors_service
            ]
        }]
    };

    const reservationsByStatusData = {
        labels: {!! json_encode($reservationsByStatus->pluck('statut') ?? []) !!},
        datasets: [{
            data: {!! json_encode($reservationsByStatus->pluck('count') ?? []) !!},
            backgroundColor: [
                '#f39c12', // en attente
                '#27ae60', // approuvée
                '#3498db', // active
                '#95a5a6', // terminée
                '#e74c3c'  // refusée
            ]
        }]
    };

    // Initialiser les graphiques
    initChart('usersByRoleChart', 'doughnut', usersByRoleData);
    initChart('resourcesByStatusChart', 'pie', resourcesByStatusData);
    initChart('reservationsByStatusChart', 'doughnut', reservationsByStatusData);
    initEvolutionChart();

    // Gestion du changement de période
    document.getElementById('evolutionPeriod').addEventListener('change', function() {
        updateEvolutionChart(this.value);
    });
});

function initChart(canvasId, type, data) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    new Chart(ctx, {
        type: type,
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
}

function initEvolutionChart() {
    const ctx = document.getElementById('evolutionChart').getContext('2d');
    // Données d'exemple - À remplacer par des données réelles
    const data = {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [{
            label: 'Réservations',
            data: [65, 59, 80, 81, 56, 55, 40, 70, 85, 90, 75, 60],
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            fill: true,
            tension: 0.4
        }]
    };

    new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function updateEvolutionChart(type) {
    // Logique pour mettre à jour les données selon le type
    console.log('Mise à jour du graphique pour:', type);
    // Implémenter l'ajax call ici pour récupérer les données réelles
}
</script>
@endsection