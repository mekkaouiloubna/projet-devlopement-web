@extends('layouts.app')
@section('title', 'Tableau de bord - Administration')
@section('page-title', 'Tableau de bord')

@section('content')
    <div class="admin-dashboard">
        <div class="header-sec">
            <div class="header-sec-title">
                <h1><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord</h1>
                <p>Suivez et administrez vos ressources efficacement, restez informé en temps réel</p>
            </div>
            <div class="header-sec-date">
                <i class="fas fa-calendar-alt"></i> {{ now()->format('d/m/Y') }}
            </div>
        </div>

        <!-- Statistiques administratives -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_users'] }}</h3>
                    <p>Utilisateurs totaux</p>
                    <small>{{ $stats['pending_users'] }} en attente</small>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_resources'] }}</h3>
                    <p>Ressources</p>
                    <small>{{ $stats['dispo_resources'] }} disponibles</small>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_reservations'] }}</h3>
                    <p>Réservations</p>
                    <small>{{ $stats['pending_reservations'] }} en attente</small>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total_maintenance'] }}</h3>
                    <p>Maintenances</p>
                    <small>{{ $stats['planifiee_maintenance'] }} planifiées</small>
                </div>
            </div>
        </div>

        <div class="dashboard-sections">
            <!-- Actions rapides pour Admin -->
            <div class="quick-actions-column">
                <div class="quick-actions-grid">

                    <a href="{{ route('categories.index') }}" class="quick-action warning">
                        <div class="action-icon"><i class="fas fa-tags"></i></div>
                        <span>gérer les catégories</span>
                    </a>

                    <a href="{{ route('reservations.index') }}" class="quick-action info">
                        <div class="action-icon"><i class="fas fa-calendar-alt"></i></div>
                        <span>Afficher les réservations</span>
                    </a>

                    <a href="{{ route('users.index') }}" class="quick-action danger">
                        <div class="action-icon"><i class="fas fa-users"></i></div>
                        <span>gérer les utilisateurs</span>
                        <span class="notification-badge">{{ $stats['pending_users'] }}</span>
                    </a>

                    <a href="{{ route('resources.index') }}" class="quick-action warning">
                        <div class="action-icon"><i class="fas fa-cogs"></i></div>
                        <span>gérer les ressources</span>
                    </a>

                </div>
            </div>

            <!-- Graphiques et statistiques avancées -->
            <div class="dashboard-charts">
                <!-- Taux d'occupation des ressources -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Taux d'occupation des ressources</h3>
                        <div class="chart-period-selector">
                            <button class="btn btn-primary btn-sm active" data-period="week">Semaine</button>
                            <button class="btn btn-primary btn-sm" data-period="month">Mois</button>
                            <button class="btn btn-primary btn-sm" data-period="year">Annee</button>
                        </div>
                    </div>
                    <div class="svg-chart" id="occupationChart">
                        <svg viewBox="0 0 800 300" class="line-chart-svg">
                            <!-- Axes -->
                            <line x1="50" y1="250" x2="750" y2="250" stroke="#ddd" stroke-width="2" />
                            <line x1="50" y1="50" x2="50" y2="250" stroke="#ddd" stroke-width="2" />

                            <!-- Y-axis labels -->
                            <text x="30" y="50" class="axis-label">100%</text>
                            <text x="30" y="150" class="axis-label">50%</text>
                            <text x="30" y="250" class="axis-label">0%</text>

                            <!-- Grid lines -->
                            <line x1="50" y1="150" x2="750" y2="150" stroke="#eee" stroke-dasharray="5,5" />

                            <!-- Data points and line (will be populated by JS) -->
                            <g class="data-line" fill="none" stroke="#3498db" stroke-width="3"></g>
                            <g class="data-points"></g>

                            <!-- X-axis labels (will be populated by JS) -->
                            <g class="x-axis-labels"></g>
                        </svg>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #3498db"></span>
                            <span>Taux d'occupation</span>
                        </div>
                    </div>
                </div>

                <!-- Réservations par statut -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Réservations par statut</h3>
                    </div>
                    <div class="svg-chart" id="reservationsChart">
                        <svg viewBox="0 0 400 400" class="pie-chart-svg">
                            <!-- Pie chart will be generated by JS -->
                            <g class="pie-slices"></g>

                            <!-- Center circle -->
                            <circle cx="200" cy="200" r="60" fill="white" />
                            <text x="200" y="200" text-anchor="middle" class="chart-center-text">
                                {{ $stats['total_reservations'] }}
                            </text>
                            <text x="200" y="220" text-anchor="middle" class="chart-center-subtext">
                                Total
                            </text>
                        </svg>
                        <div class="chart-legend" id="pieLegend">
                            <!-- Legend will be populated by JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertes importantes -->
        <div class="alerts-section">
            <div class="section-header">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#e74c3c">
                        <path
                            d="M12 9V11M12 15H12.01M5.07183 19H18.9282C20.4678 19 21.4301 17.3333 20.6603 16L13.7321 4C12.9623 2.66667 11.0378 2.66667 10.268 4L3.33978 16C2.56998 17.3333 3.53223 19 5.07183 19Z"
                            stroke="#e74c3c" stroke-width="2" />
                    </svg> Alertes importantes
                </h3>
            </div>

            <div class="alerts-container">
                @if($stats['pending_users'] > 0)
                    <div class="alert-item alert-warning">
                        <div class="alert-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="alert-content">
                            <h4>{{ $stats['pending_users'] }} demande(s) d'inscription en attente</h4>
                            <p>Des utilisateurs attendent l'activation de leur compte.</p>
                        </div>
                        <div class="alert-actions">
                            <a href="{{ route('users.index') }}" class="btn btn-sm btn-warning w-100">Voir</a>
                        </div>
                    </div>
                @endif

                @if($stats['pending_reservations'] > 0)
                    <div class="alert-item alert-info">
                        <div class="alert-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="alert-content">
                            <h4>{{ $stats['pending_reservations'] }} réservation(s) en attente d'approbation</h4>
                            <p>Des réservations nécessitent votre attention.</p>
                        </div>
                        <div class="alert-actions">
                            <a href="{{ route('reservations.index') }}?statut=en attente"
                                class="btn btn-sm btn-success">Voir</a>
                        </div>
                    </div>
                @endif

                @if($stats['resources_en_maintenance'] > 0)
                    <div class="alert-item alert-danger">
                        <div class="alert-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="alert-content">
                            <h4>{{ $stats['resources_en_maintenance'] }} ressource(s) en maintenance</h4>
                            <p>Vérifiez l'état des maintenances en cours.</p>
                        </div>
                        <div class="alert-actions">
                            <a href="{{ route('maintenance.index') }}" class="btn btn-sm btn-danger">Voir</a>
                        </div>
                    </div>
                @endif

                @if($stats['active_reservations'] > 0)
                    <div class="alert-item alert-warning">
                        <div class="alert-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="alert-content">
                            <h4>{{ $stats['active_reservations'] }} réservation(s) expirent bientôt</h4>
                            <p>Des réservations actives vont bientôt se terminer.</p>
                        </div>
                        <div class="alert-actions">
                            <a href="{{ route('reservations.index') }}?statut=active" class="btn btn-sm btn-warning">Voir</a>
                        </div>
                    </div>
                @endif
            </div>

            @if($stats['pending_users'] == 0 && $stats['pending_reservations'] == 0)
                <div class="no-alerts">
                    <p>Aucune alerte importante pour le moment.</p>
                </div>
            @endif
        </div>

        <!-- Activité récente -->
        <div class="recent-activity">
            <div class="section-header">
                <h3>Activité récente</h3>
                <a href="{{ route('history.index') }}" class="btn btn-outline btn-sm">Voir tout</a>
            </div>
            @foreach($history as $log)
                <div class="activity-content">
                    <div class="activity-title">
                        <strong>{{ $log->user->prenom ?? 'Système' }}</strong> {{ strtolower($log->action) }}
                        <strong>{{ $log->table_concernée }}</strong>
                    </div>
                    <div class="activity-description">{{ $log->description }}</div>
                    <div class="activity-time">{{ $log->created_at->diffForHumans() }}</div>
                </div>
            @endforeach
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialiser les graphiques SVG
            initOccupationChart();
            initPieChart();

            // Gestion des boutons de période
            document.querySelectorAll('.btn-primary').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.btn-primary').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    updateOccupationChart(this.dataset.period);
                });
            });

            // Animation des cartes de statistiques
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in');
            });
        });

        // Données pour les graphiques
        const occupationData = {
            week: {
                labels: @json($days),
                values: @json($occupationWeek)
            },
            month: {
                labels: @json($days),
                values: @json($occupationMonth)
            },
            year: {
                labels: @json($months),
                values: @json($occupationYear)
            },
        };

        // Initialiser le graphique d'occupation
        function initOccupationChart() {
            updateOccupationChart('week');
        }

        // Mettre à jour le graphique d'occupation
        function updateOccupationChart(period) {
            const data = occupationData[period];
            const svg = document.querySelector('.line-chart-svg');

            // Supprimer les anciens éléments
            svg.querySelector('.data-line').innerHTML = '';
            svg.querySelector('.data-points').innerHTML = '';
            svg.querySelector('.x-axis-labels').innerHTML = '';

            // Paramètres du graphique
            const width = 700; // 750 - 50
            const height = 200; // 250 - 50
            const paddingLeft = 50;
            const paddingBottom = 250;
            const pointCount = data.values.length;
            const spacing = width / (pointCount - 1);

            // Créer le chemin de la ligne
            let pathData = '';
            let points = [];

            data.values.forEach((value, index) => {
                const x = paddingLeft + (index * spacing);
                const y = paddingBottom - (value * 2); // 2px par pourcentage
                points.push({ x, y, value });

                if (index === 0) {
                    pathData = `M ${x} ${y}`;
                } else {
                    pathData += ` L ${x} ${y}`;
                }
            });

            // Ajouter la ligne
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            line.setAttribute('d', pathData);
            line.setAttribute('fill', 'none');
            line.setAttribute('stroke', '#3498db');
            line.setAttribute('stroke-width', '3');
            line.setAttribute('stroke-linecap', 'round');
            line.setAttribute('stroke-linejoin', 'round');
            svg.querySelector('.data-line').appendChild(line);

            // Ajouter les points et les labels
            points.forEach((point, index) => {
                // Point
                const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', point.x);
                circle.setAttribute('cy', point.y);
                circle.setAttribute('r', '4');
                circle.setAttribute('fill', '#3498db');
                circle.setAttribute('stroke', 'white');
                circle.setAttribute('stroke-width', '2');
                circle.classList.add('data-point');
                circle.dataset.value = point.value;
                circle.dataset.label = data.labels[index];

                // Animation au survol
                circle.addEventListener('mouseenter', function () {
                    this.setAttribute('r', '6');
                    showTooltip(this, point.value, data.labels[index]);
                });

                circle.addEventListener('mouseleave', function () {
                    this.setAttribute('r', '4');
                    hideTooltip();
                });

                svg.querySelector('.data-points').appendChild(circle);

                // Label de valeur
                const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', point.x);
                text.setAttribute('y', point.y - 10);
                text.setAttribute('text-anchor', 'middle');
                text.setAttribute('class', 'data-point-text');
                text.textContent = point.value + '%';
                svg.querySelector('.data-points').appendChild(text);

                // Label de l'axe X
                const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                label.setAttribute('x', point.x);
                label.setAttribute('y', paddingBottom + 20);
                label.setAttribute('text-anchor', 'middle');
                label.setAttribute('class', 'x-axis-label');
                label.textContent = data.labels[index];
                svg.querySelector('.x-axis-labels').appendChild(label);
            });
        }

        // Initialiser le graphique circulaire
        function initPieChart() {
            // Données des réservations par statut
            const reservationData = {
                labels: ['En attente', 'Approuvées', 'Actives', 'Terminées', 'Refusées'],
                values: [
                        {{ $stats['pending_reservations'] }},
                        {{ $stats['active_reservations'] }},
                        {{ $stats['reservation_approuvee'] }},
                        {{ $stats['reservation_terminee'] }},
                        {{ $stats['reservation_refusee'] }}
                ],
                colors: ['#f39c12', '#27ae60', '#3498db', '#95a5a6', '#e74c3c']
            };

            const svg = document.querySelector('.pie-chart-svg');
            const legendContainer = document.getElementById('pieLegend');

            // Vider les anciens éléments
            svg.querySelector('.pie-slices').innerHTML = '';
            legendContainer.innerHTML = '';

            const total = reservationData.values.reduce((a, b) => a + b, 0);
            const radius = 120;
            const centerX = 200;
            const centerY = 200;

            let currentAngle = -Math.PI / 2; // Commencer en haut

            // Créer les segments du camembert
            reservationData.values.forEach((value, index) => {
                if (value === 0) return;

                const percentage = value / total;
                const angle = percentage * 2 * Math.PI;
                const endAngle = currentAngle + angle;

                // Calculer les points pour le segment
                const x1 = centerX + radius * Math.cos(currentAngle);
                const y1 = centerY + radius * Math.sin(currentAngle);
                const x2 = centerX + radius * Math.cos(endAngle);
                const y2 = centerY + radius * Math.sin(endAngle);

                // Créer le chemin du segment
                const largeArcFlag = angle > Math.PI ? 1 : 0;
                const pathData = `
                    M ${centerX} ${centerY}
                    L ${x1} ${y1}
                    A ${radius} ${radius} 0 ${largeArcFlag} 1 ${x2} ${y2}
                    Z
                `;

                // Créer l'élément path
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', pathData);
                path.setAttribute('fill', reservationData.colors[index]);
                path.setAttribute('stroke', 'white');
                path.setAttribute('stroke-width', '2');
                path.classList.add('pie-slice');
                path.dataset.index = index;
                path.dataset.value = value;
                path.dataset.percentage = Math.round(percentage * 100);
                path.dataset.label = reservationData.labels[index];

                // Animation au survol
                path.addEventListener('mouseenter', function () {
                    this.style.transform = 'scale(1.05)';
                    this.style.transformOrigin = 'center';
                    this.style.transition = 'transform 0.3s ease';
                    showTooltip(this, value, reservationData.labels[index], percentage);
                });

                path.addEventListener('mouseleave', function () {
                    this.style.transform = 'scale(1)';
                    hideTooltip();
                });

                svg.querySelector('.pie-slices').appendChild(path);

                // Ajouter à la légende
                const legendItem = document.createElement('div');
                legendItem.className = 'pie-legend-item';
                legendItem.dataset.index = index;

                legendItem.innerHTML = `
                    <span class="pie-legend-color" style="background-color: ${reservationData.colors[index]}"></span>
                    <span class="pie-legend-text">
                        ${reservationData.labels[index]} 
                        <span class="pie-legend-value">(${value})</span>
                        <span style="color: #6c757d; font-size: 0.8rem;"> - ${Math.round(percentage * 100)}%</span>
                    </span>
                `;

                legendItem.addEventListener('mouseenter', function () {
                    const paths = document.querySelectorAll('.pie-slice');
                    paths.forEach(p => {
                        if (p.dataset.index !== index.toString()) {
                            p.style.opacity = '0.6';
                        }
                    });
                });

                legendItem.addEventListener('mouseleave', function () {
                    const paths = document.querySelectorAll('.pie-slice');
                    paths.forEach(p => p.style.opacity = '1');
                });

                legendContainer.appendChild(legendItem);

                currentAngle = endAngle;
            });
        }

        // Afficher le tooltip
        function showTooltip(element, value, label, percentage = null) {
            let tooltip = document.querySelector('.chart-tooltip');

            if (!tooltip) {
                tooltip = document.createElement('div');
                tooltip.className = 'chart-tooltip';
                document.body.appendChild(tooltip);
            }

            const rect = element.getBoundingClientRect();
            const svgRect = element.closest('svg').getBoundingClientRect();

            let content = `
                <div class="tooltip-header">${label}</div>
                <div class="tooltip-value">${value} ${percentage ? `(${Math.round(percentage * 100)}%)` : ''}</div>
            `;

            tooltip.innerHTML = content;
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = (svgRect.top - 10) + 'px';
            tooltip.classList.add('show');
        }

        // Cacher le tooltip
        function hideTooltip() {
            const tooltip = document.querySelector('.chart-tooltip');
            if (tooltip) {
                tooltip.classList.remove('show');
            }
        }

        // Fonction pour afficher les toasts
        function showAdminToast(message, type = 'info') {
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

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            const closeBtn = toast.querySelector('.toast-close');
            closeBtn.addEventListener('click', () => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            });

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        }

        function getToastIcon(type) {
            switch (type) {
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