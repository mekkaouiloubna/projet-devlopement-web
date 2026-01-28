@extends('layouts.app')

@section('title', 'Tableau de bord - Administration')

@section('page-title', 'Tableau de bord')

@section('breadcrumbs')
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item">Administration</span>
@endsection

@section('content')
<div class="admin-dashboard">
    <!-- Statistiques administratives -->
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="stat-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="white" stroke-width="2"/>
                    <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>{{ \App\Models\User::count() }}</h3>
                <p>Utilisateurs totaux</p>
                <div class="stat-trend">
                    <span class="trend-up">{{ \App\Models\User::where('account_status', 'pending')->count() }} en attente</span>
                </div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="stat-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="white" stroke-width="2"/>
                    <path d="M3 9H21" stroke="white" stroke-width="2"/>
                    <path d="M9 21V9" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>{{ \App\Models\Resource::count() }}</h3>
                <p>Ressources</p>
                <div class="stat-trend">
                    <span class="trend-info">{{ \App\Models\Resource::where('statut', 'disponible')->count() }} disponibles</span>
                </div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="stat-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="4" width="18" height="18" rx="2" stroke="white" stroke-width="2"/>
                    <path d="M16 2V6" stroke="white" stroke-width="2"/>
                    <path d="M8 2V6" stroke="white" stroke-width="2"/>
                    <path d="M3 10H21" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>{{ \App\Models\Reservation::count() }}</h3>
                <p>Réservations</p>
                <div class="stat-trend">
                    <span class="trend-warning">{{ \App\Models\Reservation::where('statut', 'en_attente')->count() }} en attente</span>
                </div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="stat-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <path d="M14 10L12 11.5L14 13M10 10L12 11.5L10 13M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>{{ \App\Models\MaintenanceSchedule::count() }}</h3>
                <p>Maintenances</p>
                <div class="stat-trend">
                    <span class="trend-info">{{ \App\Models\MaintenanceSchedule::where('statut', 'planifiée')->count() }} planifiées</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et statistiques avancées -->
    <div class="dashboard-charts">
        <div class="chart-card">
            <div class="chart-header">
                <h3>Taux d'occupation des ressources</h3>
                <select class="chart-period" id="occupationPeriod">
                    <option value="week">Cette semaine</option>
                    <option value="month" selected>Ce mois</option>
                    <option value="quarter">Ce trimestre</option>
                </select>
            </div>
            <div class="chart-container">
                <canvas id="occupationChart" height="250"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Réservations par statut</h3>
            </div>
            <div class="chart-container">
                <canvas id="reservationsChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Alertes importantes -->
    <div class="alerts-section">
        <div class="section-header">
            <h3><svg width="20" height="20" viewBox="0 0 24 24" fill="#e74c3c">
                <path d="M12 9V11M12 15H12.01M5.07183 19H18.9282C20.4678 19 21.4301 17.3333 20.6603 16L13.7321 4C12.9623 2.66667 11.0378 2.66667 10.268 4L3.33978 16C2.56998 17.3333 3.53223 19 5.07183 19Z" stroke="#e74c3c" stroke-width="2"/>
            </svg> Alertes importantes</h3>
        </div>
        
        <div class="alerts-container">
            @if(\App\Models\User::where('account_status', 'pending')->count() > 0)
                <div class="alert-item alert-warning">
                    <div class="alert-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="alert-content">
                        <h4>{{ \App\Models\User::where('account_status', 'pending')->count() }} demande(s) d'inscription en attente</h4>
                        <p>Des utilisateurs attendent l'activation de leur compte.</p>
                    </div>
                    <div class="alert-actions">
                        <a href="{{ route('admin.users') }}?status=pending" class="btn btn-sm btn-warning">Vérifier</a>
                    </div>
                </div>
            @endif

            @if(\App\Models\Reservation::where('statut', 'en_attente')->count() > 0)
                <div class="alert-item alert-info">
                    <div class="alert-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                            <path d="M16 2V6" stroke="currentColor" stroke-width="2"/>
                            <path d="M8 2V6" stroke="currentColor" stroke-width="2"/>
                            <path d="M3 10H21" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="alert-content">
                        <h4>{{ \App\Models\Reservation::where('statut', 'en_attente')->count() }} réservation(s) en attente d'approbation</h4>
                        <p>Des réservations nécessitent votre attention.</p>
                    </div>
                    <div class="alert-actions">
                        <a href="{{ route('reservations.index') }}?statut=en_attente" class="btn btn-sm btn-info">Approuver</a>
                    </div>
                </div>
            @endif

            @if(\App\Models\Resource::where('statut', 'maintenance')->count() > 0)
                <div class="alert-item alert-danger">
                    <div class="alert-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M14 10L12 11.5L14 13M10 10L12 11.5L10 13M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="alert-content">
                        <h4>{{ \App\Models\Resource::where('statut', 'maintenance')->count() }} ressource(s) en maintenance</h4>
                        <p>Vérifiez l'état des maintenances en cours.</p>
                    </div>
                    <div class="alert-actions">
                        <a href="{{ route('maintenance.index') }}" class="btn btn-sm btn-danger">Vérifier</a>
                    </div>
                </div>
            @endif

            @php
                $expiringReservations = \App\Models\Reservation::where('statut', 'active')
                    ->where('date_fin', '<=', now()->addDays(2))
                    ->count();
            @endphp
            @if($expiringReservations > 0)
                <div class="alert-item alert-warning">
                    <div class="alert-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M12 8V12L15 15" stroke="currentColor" stroke-width="2"/>
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="alert-content">
                        <h4>{{ $expiringReservations }} réservation(s) expirent bientôt</h4>
                        <p>Des réservations actives vont bientôt se terminer.</p>
                    </div>
                    <div class="alert-actions">
                        <a href="{{ route('reservations.index') }}?statut=active" class="btn btn-sm btn-warning">Voir</a>
                    </div>
                </div>
            @endif
        </div>

        @if(\App\Models\User::where('account_status', 'pending')->count() == 0 && 
            \App\Models\Reservation::where('statut', 'en_attente')->count() == 0)
            <div class="no-alerts">
                <p>Aucune alerte importante pour le moment.</p>
            </div>
        @endif
    </div>

    <!-- Activité récente -->
    <div class="recent-activity">
        <div class="section-header">
            <h3>Activité récente</h3>
            <a href="{{ route('history.index') }}" class="btn-view-all">Voir tout l'historique</a>
        </div>
        
        <div class="activity-timeline">
            @foreach(\App\Models\HistoryLog::with('user')->latest()->take(8)->get() as $log)
                <div class="activity-item">
                    <div class="activity-icon">
                        @switch($log->action)
                            @case('Connexion')
                                <div class="icon-success">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#27ae60">
                                        <path d="M11 16L7 12L8.41 10.59L11 13.17L15.59 8.58L17 10L11 16Z"/>
                                    </svg>
                                </div>
                                @break
                            @case('Création')
                                <div class="icon-info">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#3498db">
                                        <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                    </svg>
                                </div>
                                @break
                            @case('Modification')
                                <div class="icon-warning">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#f39c12">
                                        <path d="M3 17.25V21H6.75L17.81 9.94L14.06 6.19L3 17.25ZM20.71 7.04C21.1 6.65 21.1 6.02 20.71 5.63L18.37 3.29C17.98 2.9 17.35 2.9 16.96 3.29L15.13 5.12L18.88 8.87L20.71 7.04Z"/>
                                    </svg>
                                </div>
                                @break
                            @default
                                <div class="icon-secondary">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#95a5a6">
                                        <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2Z"/>
                                    </svg>
                                </div>
                        @endswitch
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">
                            <strong>{{ $log->user->prenom ?? 'Système' }}</strong> {{ strtolower($log->action) }}
                            <strong>{{ $log->table_concernée }}</strong>
                        </div>
                        <div class="activity-description">{{ $log->description }}</div>
                        <div class="activity-time">{{ $log->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Rapports rapides -->
    <div class="quick-reports">
        <div class="section-header">
            <h3>Rapports rapides</h3>
        </div>
        
        <div class="reports-grid">
            <a href="{{ route('reports.generate') }}?type=reservations&start_date={{ now()->subMonth()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" 
               class="report-card" target="_blank">
                <div class="report-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="4" width="18" height="18" rx="2" stroke="#3498db" stroke-width="2"/>
                        <path d="M16 2V6" stroke="#3498db" stroke-width="2"/>
                        <path d="M8 2V6" stroke="#3498db" stroke-width="2"/>
                        <path d="M3 10H21" stroke="#3498db" stroke-width="2"/>
                    </svg>
                </div>
                <div class="report-content">
                    <h4>Réservations du mois</h4>
                    <p>Statistiques des réservations du dernier mois</p>
                </div>
            </a>
            
            <a href="{{ route('reports.generate') }}?type=users&start_date={{ now()->subMonth()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" 
               class="report-card" target="_blank">
                <div class="report-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                        <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#27ae60" stroke-width="2"/>
                        <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="#27ae60" stroke-width="2"/>
                    </svg>
                </div>
                <div class="report-content">
                    <h4>Utilisateurs actifs</h4>
                    <p>Activité des utilisateurs du dernier mois</p>
                </div>
            </a>
            
            <a href="{{ route('reports.generate') }}?type=resources&start_date={{ now()->subMonth()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" 
               class="report-card" target="_blank">
                <div class="report-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="#9b59b6" stroke-width="2"/>
                        <path d="M3 9H21" stroke="#9b59b6" stroke-width="2"/>
                        <path d="M9 21V9" stroke="#9b59b6" stroke-width="2"/>
                    </svg>
                </div>
                <div class="report-content">
                    <h4>Utilisation des ressources</h4>
                    <p>Taux d'occupation des ressources</p>
                </div>
            </a>
            
            <a href="{{ route('admin.overview') }}" class="report-card">
                <div class="report-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                        <path d="M9 17L9 11" stroke="#f39c12" stroke-width="2"/>
                        <path d="M12 17L12 7" stroke="#f39c12" stroke-width="2"/>
                        <path d="M15 17L15 13" stroke="#f39c12" stroke-width="2"/>
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="#f39c12" stroke-width="2"/>
                    </svg>
                </div>
                <div class="report-content">
                    <h4>Vue d'ensemble</h4>
                    <p>Statistiques globales du système</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Include Chart.js for graphs -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .admin-dashboard {
        padding: 20px 0;
    }

    /* Statistiques administratives */
    .admin-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .admin-stat-card {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        border-radius: 15px;
        padding: 25px;
        color: white;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .admin-stat-card:nth-child(2) {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    }

    .admin-stat-card:nth-child(3) {
        background: linear-gradient(135deg, #27ae60 0%, #219653 100%);
    }

    .admin-stat-card:nth-child(4) {
        background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
    }

    .admin-stat-card:hover {
        transform: translateY(-5px);
    }

    .admin-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: rgba(255, 255, 255, 0.3);
    }

    .stat-icon {
        margin-bottom: 20px;
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
        margin: 0 0 10px 0;
        opacity: 0.9;
        font-size: 0.95rem;
    }

    .stat-trend {
        font-size: 0.85rem;
        padding-top: 10px;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    .trend-up {
        color: #2ecc71;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .trend-info {
        color: #3498db;
    }

    .trend-warning {
        color: #f39c12;
    }

    /* Graphiques */
    .dashboard-charts {
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
        margin-bottom: 40px;
    }

    @media (min-width: 1200px) {
        .dashboard-charts {
            grid-template-columns: 1fr 1fr;
        }
    }

    .chart-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .chart-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chart-header h3 {
        margin: 0;
        font-size: 1.3rem;
        color: #2c3e50;
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

    .chart-container {
        padding: 20px;
        min-height: 300px;
    }

    /* Alertes */
    .alerts-section {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
        overflow: hidden;
    }

    .alerts-section .section-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alerts-section h3 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alerts-container {
        padding: 20px;
    }

    .alert-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 15px;
        transition: transform 0.3s ease;
    }

    .alert-item:hover {
        transform: translateX(5px);
    }

    .alert-item.alert-warning {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
    }

    .alert-item.alert-info {
        background-color: #d1ecf1;
        border-left: 4px solid #17a2b8;
    }

    .alert-item.alert-danger {
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
    }

    .alert-icon svg {
        width: 24px;
        height: 24px;
    }

    .alert-content {
        flex: 1;
    }

    .alert-content h4 {
        margin: 0 0 5px 0;
        font-size: 1rem;
        color: #2c3e50;
    }

    .alert-content p {
        margin: 0;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .alert-actions .btn {
        white-space: nowrap;
    }

    .no-alerts {
        text-align: center;
        padding: 30px;
        color: #95a5a6;
    }

    /* Activité récente */
    .recent-activity {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
        overflow: hidden;
    }

    .recent-activity .section-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .recent-activity h3 {
        margin: 0;
        font-size: 1.3rem;
        color: #2c3e50;
    }

    .activity-timeline {
        padding: 20px;
        position: relative;
    }

    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 35px;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
    }

    .activity-item {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
        position: relative;
    }

    .activity-item:last-child {
        margin-bottom: 0;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    .activity-icon .icon-success {
        color: #27ae60;
    }

    .activity-icon .icon-info {
        color: #3498db;
    }

    .activity-icon .icon-warning {
        color: #f39c12;
    }

    .activity-icon .icon-secondary {
        color: #95a5a6;
    }

    .activity-content {
        flex: 1;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid #3498db;
    }

    .activity-title {
        font-size: 0.95rem;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .activity-description {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .activity-time {
        font-size: 0.75rem;
        color: #95a5a6;
    }

    /* Rapports rapides */
    .quick-reports {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .quick-reports .section-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
    }

    .quick-reports h3 {
        margin: 0;
        font-size: 1.3rem;
        color: #2c3e50;
    }

    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .report-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 25px;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        text-decoration: none;
        color: #495057;
        transition: all 0.3s ease;
    }

    .report-card:hover {
        border-color: #3498db;
        background-color: #f8f9fa;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .report-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        transition: background-color 0.3s ease;
    }

    .report-card:hover .report-icon {
        background-color: #e9ecef;
    }

    .report-icon svg {
        width: 32px;
        height: 32px;
    }

    .report-content h4 {
        margin: 0 0 8px 0;
        font-size: 1.1rem;
        color: #2c3e50;
    }

    .report-content p {
        margin: 0;
        font-size: 0.9rem;
        color: #6c757d;
        line-height: 1.4;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .admin-stats-grid {
            grid-template-columns: 1fr;
        }
        
        .activity-timeline::before {
            left: 25px;
        }
        
        .activity-icon {
            width: 30px;
            height: 30px;
        }
        
        .reports-grid {
            grid-template-columns: 1fr;
        }
        
        .alert-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .alert-actions {
            align-self: flex-end;
        }
    }

    @media (max-width: 576px) {
        .chart-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .chart-period {
            align-self: flex-start;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les graphiques
        initOccupationChart();
        initReservationsChart();
        
        // Gestion du changement de période pour le graphique d'occupation
        document.getElementById('occupationPeriod').addEventListener('change', function() {
            updateOccupationChart(this.value);
        });

        // Animation des cartes de statistiques
        const statCards = document.querySelectorAll('.admin-stat-card');
        statCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('fade-in');
        });
    });

    // Variables pour les graphiques
    let occupationChart = null;
    let reservationsChart = null;

    // Initialiser le graphique d'occupation
    function initOccupationChart() {
        const ctx = document.getElementById('occupationChart').getContext('2d');
        
        // Données d'exemple (à remplacer par des données réelles)
        const data = {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Taux d\'occupation (%)',
                data: [65, 70, 80, 75, 85, 60, 55],
                backgroundColor: 'rgba(52, 152, 219, 0.2)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        };

        occupationChart = new Chart(ctx, config);
    }

    // Mettre à jour le graphique d'occupation
    function updateOccupationChart(period) {
        // Simuler des données différentes selon la période
        let data;
        switch(period) {
            case 'week':
                data = [70, 75, 80, 65, 90, 85, 70];
                break;
            case 'month':
                data = [65, 70, 80, 75, 85, 60, 55, 70, 75, 65, 80, 85, 90, 75, 70, 65, 80, 85, 90, 95, 85, 80, 75, 70, 65, 60, 55, 50, 65, 70];
                break;
            case 'quarter':
                data = [60, 65, 70, 75, 80, 85, 90, 85, 80, 75, 70, 65];
                break;
        }

        occupationChart.data.datasets[0].data = data;
        occupationChart.update();
    }

    // Initialiser le graphique des réservations par statut
    function initReservationsChart() {
        const ctx = document.getElementById('reservationsChart').getContext('2d');
        
        // Données réelles depuis la base de données
        const statusData = {
            labels: ['En attente', 'Approuvées', 'Actives', 'Terminées', 'Refusées'],
            datasets: [{
                data: [
                    {{ \App\Models\Reservation::where('statut', 'en_attente')->count() }},
                    {{ \App\Models\Reservation::where('statut', 'approuvée')->count() }},
                    {{ \App\Models\Reservation::where('statut', 'active')->count() }},
                    {{ \App\Models\Reservation::where('statut', 'terminée')->count() }},
                    {{ \App\Models\Reservation::where('statut', 'refusée')->count() }}
                ],
                backgroundColor: [
                    'rgba(243, 156, 18, 0.8)',
                    'rgba(39, 174, 96, 0.8)',
                    'rgba(52, 152, 219, 0.8)',
                    'rgba(149, 165, 166, 0.8)',
                    'rgba(231, 76, 60, 0.8)'
                ],
                borderColor: [
                    'rgba(243, 156, 18, 1)',
                    'rgba(39, 174, 96, 1)',
                    'rgba(52, 152, 219, 1)',
                    'rgba(149, 165, 166, 1)',
                    'rgba(231, 76, 60, 1)'
                ],
                borderWidth: 1
            }]
        };

        const config = {
            type: 'doughnut',
            data: statusData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        };

        reservationsChart = new Chart(ctx, config);
    }

    // Fonction pour afficher les toasts
    function showAdminToast(message, type = 'info') {
        // Créer le toast
        const toast = document.createElement('div');
        toast.className = `admin-toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <div class="toast-icon">
                    ${getToastIcon(type)}
                </div>
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

    function getToastIcon(type) {
        switch(type) {
            case 'success':
                return '<svg width="20" height="20" viewBox="0 0 24 24" fill="#27ae60"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>';
            case 'error':
                return '<svg width="20" height="20" viewBox="0 0 24 24" fill="#e74c3c"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';
            case 'warning':
                return '<svg width="20" height="20" viewBox="0 0 24 24" fill="#f39c12"><path d="M12 9V11M12 15H12.01M5.07183 19H18.9282C20.4678 19 21.4301 17.3333 20.6603 16L13.7321 4C12.9623 2.66667 11.0378 2.66667 10.268 4L3.33978 16C2.56998 17.3333 3.53223 19 5.07183 19Z" stroke="#f39c12" stroke-width="2"/></svg>';
            default:
                return '<svg width="20" height="20" viewBox="0 0 24 24" fill="#3498db"><path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"/></svg>';
        }
    }

    // Styles pour les toasts admin
    const adminToastStyles = document.createElement('style');
    adminToastStyles.textContent = `
        .admin-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            padding: 15px;
            min-width: 300px;
            max-width: 400px;
            z-index: 9999;
            transform: translateX(100%);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
            border-left: 4px solid;
        }

        .admin-toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast-success {
            border-left-color: #27ae60;
        }

        .toast-error {
            border-left-color: #e74c3c;
        }

        .toast-info {
            border-left-color: #3498db;
        }

        .toast-warning {
            border-left-color: #f39c12;
        }

        .toast-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toast-icon {
            flex-shrink: 0;
        }

        .toast-message {
            flex: 1;
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.95rem;
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
            .admin-toast {
                left: 20px;
                right: 20px;
                min-width: auto;
                max-width: none;
            }
        }
    `;
    document.head.appendChild(adminToastStyles);

    // CSS pour les animations
    const adminAnimationStyles = document.createElement('style');
    adminAnimationStyles.textContent = `
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

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-item {
            animation: slideIn 0.3s ease forwards;
            opacity: 0;
        }

        .alert-item:nth-child(1) { animation-delay: 0.1s; }
        .alert-item:nth-child(2) { animation-delay: 0.2s; }
        .alert-item:nth-child(3) { animation-delay: 0.3s; }
        .alert-item:nth-child(4) { animation-delay: 0.4s; }
    `;
    document.head.appendChild(adminAnimationStyles);
</script>
@endsection