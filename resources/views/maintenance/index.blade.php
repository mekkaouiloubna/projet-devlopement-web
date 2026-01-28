@extends('layouts.app')

@section('content')
    <div class="container">
        <!-- En-tête de page -->
        <div class="header-sec">
            <div class="header-sec-title">
                <h1><i class="fas fa-tools"></i> Gestion des Maintenances</h1>
                <p> Planifiez et gérez les maintenances des ressources</p>
            </div>
            <a href="{{ route('maintenance.create') }}" class="mt-2 btn btn-primary trans-up">Cree Maintenance</a>
        </div>

        <!-- Formulaire de filtres avancés - Version horizontale -->
        <div class="card mb-4">
            <div class="card-header">
                <h3><i class="fas fa-filter"></i> Filtres de recherche</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('maintenance.index') }}" method="GET" id="filterForm">
                    <!-- Dates et Tri -->
                    <div class="d-flex gap-3 flex-wrap align-items-end">
                        <!-- Recherche textuelle -->
                        <label class="form-label">Recherche</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Raison ou ressource..."
                                value="{{ request('search') }}">
                        </div>

                        <!-- Date de début -->
                        <label class="form-label">Date de début</label>
                        <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">

                        <!-- Date de fin -->
                        <label class="form-label">Date de fin</label>
                        <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">

                        <!-- Boutons d'action -->
                        <button type="submit" class="btn btn-primary" style="height: 42px;">
                            <i class="fas fa-search"></i> Appliquer
                        </button>
                        <a href="{{ route('maintenance.index') }}" class="btn btn-outline" style="height: 42px;">
                            <i class="fas fa-times"></i> Effacer
                        </a>
                    </div>

                    <!-- Filtres rapides -->
                    <div class="mt-2">
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="form-label mb-0 me-2">Filtres rapides:</span>
                            <a href="{{ route('maintenance.index') }}"
                                class="btn btn-sm {{ !request()->hasAny(['statut', 'search', 'resource_id', 'date_debut', 'date_fin']) ? 'btn-primary' : 'btn-outline' }}">
                                Toutes ({{ $counts['all'] ?? 0 }})
                            </a>
                            <a href="{{ route('maintenance.index') }}?statut=planifiée"
                                class="btn btn-sm {{ request('statut') == 'planifiée' ? 'btn-primary' : 'btn-outline' }}">
                                Planifiées ({{ $counts['planifiée'] ?? 0 }})
                            </a>
                            <a href="{{ route('maintenance.index') }}?statut=en cours"
                                class="btn btn-sm {{ request('statut') == 'en cours' ? 'btn-primary' : 'btn-outline' }}">
                                En cours ({{ $counts['en cours'] ?? 0 }})
                            </a>
                            <a href="{{ route('maintenance.index') }}?statut=terminée"
                                class="btn btn-sm {{ request('statut') == 'terminée' ? 'btn-primary' : 'btn-outline' }}">
                                Terminées ({{ $counts['terminée'] ?? 0 }})
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des maintenances -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><i class="fas fa-list"></i> Liste des Maintenances</h3>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge badge-primary">{{ $maintenances->count() }} maintenance(s)</span>
                    <div class="dropdown">
                        <button class="btn btn-outline btn-sm" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Exporter
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#"><i class="fas fa-file-excel"></i> Excel</a>
                            <a class="dropdown-item" href="#"><i class="fas fa-file-pdf"></i> PDF</a>
                            <a class="dropdown-item" href="#"><i class="fas fa-file-csv"></i> CSV</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @if($maintenances->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th class="w-35">Ressource</th>
                                    <th class="w-30">Période</th>
                                    <th class="w-15">Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($maintenances as $maintenance)
                                    <tr class="transition">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="resource-icon-sm">
                                                    <i class="fas fa-server"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $maintenance->resource->nom }}</strong>
                                                    <small class="d-block text-muted">
                                                        {{ $maintenance->resource->category->nom }}
                                                        @if($maintenance->resource->responsable)
                                                            • {{ $maintenance->resource->responsable->nom }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-primary mr-2">
                                                    <i class="fas fa-play-circle c-info"></i>
                                                    {{ \Carbon\Carbon::parse($maintenance->date_debut)->format('d/m/Y H:i') }}
                                                </span>
                                                <span class="text-muted">
                                                    <i class="fas fa-stop-circle"></i>
                                                    {{ \Carbon\Carbon::parse($maintenance->date_fin)->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge 
                                                @if($maintenance->statut == 'planifiée') badge-warning
                                                @elseif($maintenance->statut == 'en cours') badge-info
                                                @else badge-success @endif ">
                                                {{ ucfirst($maintenance->statut) }}
                                            </span>
                                        </td>
                                        <td class="d-flex gap-3 font-s1">
                                            <a href="{{ route('maintenance.show', $maintenance->id) }}" class="btn btn-sm btn-primary"
                                                title="Voir détails" data-bs-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if($maintenance->statut != 'terminée')
                                                <a href="{{ route('maintenance.edit', $maintenance->id) }}"
                                                    class="btn btn-sm btn-warning" title="Modifier" data-bs-toggle="tooltip">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif

                                            <form action="{{ route('maintenance.destroy', $maintenance->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette maintenance ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer"
                                                    data-bs-toggle="tooltip">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Affichage du nombre total -->
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-database"></i>
                                <strong>Total:</strong> {{ $maintenances->count() }} enregistrement(s)
                            </small>
                            <div class="d-flex align-items-center gap-3">
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> {{ now()->format('d/m/Y H:i') }}
                                </small>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i> Affichage
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#"><i class="fas fa-th-list"></i> Tableau</a>
                                        <a class="dropdown-item" href="#"><i class="fas fa-th-large"></i> Cartes</a>
                                        <a class="dropdown-item" href="#"><i class="fas fa-calendar-alt"></i> Calendrier</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center p-5">
                        <div class="empty-state">
                            <div class="resource-icon-lg mx-auto mb-3">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h4 class="mb-2">Aucune maintenance trouvée</h4>
                            <p class="text-muted mb-4">
                                @if(request()->hasAny(['statut', 'search', 'resource_id', 'date_debut', 'date_fin']))
                                    Aucune maintenance ne correspond à vos critères de recherche.
                                @else
                                    Aucune maintenance n'a été planifiée pour le moment.
                                @endif
                            </p>
                            <div class="d-flex gap-2 justify-content-center">
                                @if(request()->hasAny(['statut', 'search', 'resource_id', 'date_debut', 'date_fin']))
                                    <a href="{{ route('maintenance.index') }}" class="btn btn-outline">
                                        <i class="fas fa-redo"></i> Réinitialiser
                                    </a>
                                @endif
                                <a href="{{ route('maintenance.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Planifier une maintenance
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Styles spécifiques pour la page maintenance */
        .resource-icon-sm {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sec-color);
            font-size: 1rem;
        }

        .resource-icon-lg {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sec-color);
            font-size: 2rem;
        }

        .btn-group {
            display: flex;
            gap: 5px;
        }

        .btn-group .btn {
            padding: 6px 12px;
            min-width: 40px;
        }

        .table th {
            font-weight: 600;
            color: var(--prim-color);
            border-top: none;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateX(2px);
        }

        .empty-state {
            padding: 40px 20px;
        }

        .badge {
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .badge-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }

        .badge-info {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }

        .badge-success {
            background: linear-gradient(135deg, #27ae60, #219653);
        }

        /* Amélioration des filtres */
        .form-control,
        .input-group-text {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            transition: var(--transition);
        }

        .form-control:focus,
        .input-group:focus-within .input-group-text {
            border-color: var(--sec-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .input-group-text {
            background-color: #f8fafc;
            border-right: none;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: #4a5568;
        }

        /* Boutons de filtres rapides */
        .btn-sm {
            padding: 5px 12px;
            font-size: 0.85rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .d-flex.flex-wrap {
                flex-direction: column;
            }

            .flex-grow-1,
            .d-flex>div {
                min-width: 100% !important;
                width: 100%;
            }

            .btn-group {
                flex-wrap: wrap;
                justify-content: center;
            }

            .table-responsive {
                font-size: 0.9rem;
            }

            .resource-icon-sm {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .card-header .d-flex {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start !important;
            }

            .header-sec-title h1 {
                font-size: 1.5rem;
            }

            .header-sec-title p {
                font-size: 0.9rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sauvegarder les filtres dans localStorage
            const filterForm = document.getElementById('filterForm');
            const formInputs = filterForm.querySelectorAll('input, select');

            // Charger les filtres sauvegardés
            formInputs.forEach(input => {
                const savedValue = localStorage.getItem(`maintenance_filter_${input.name}`);
                if (savedValue !== null && input.value === '') {
                    input.value = savedValue;
                }
            });

            // Sauvegarder les filtres lors de la modification
            formInputs.forEach(input => {
                input.addEventListener('change', function () {
                    localStorage.setItem(`maintenance_filter_${this.name}`, this.value);
                });
            });

            // Bouton pour effacer tous les filtres
            const clearButton = document.querySelector('a.btn-outline[href*="maintenance.index"]');
            if (clearButton && clearButton.textContent.includes('Effacer')) {
                clearButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    // Effacer le localStorage pour cette page
                    formInputs.forEach(input => {
                        localStorage.removeItem(`maintenance_filter_${input.name}`);
                    });
                    window.location.href = this.href;
                });
            }

            // Auto-submit pour certains filtres
            const autoSubmitInputs = document.querySelectorAll('select[name="statut"], select[name="sort"]');
            autoSubmitInputs.forEach(input => {
                input.addEventListener('change', function () {
                    filterForm.submit();
                });
            });

            // Initialiser les tooltips Bootstrap
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Animation pour les lignes du tableau
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
                row.classList.add('fade-in');
            });

            // Exporter les données
            const exportButtons = document.querySelectorAll('.dropdown-item');
            exportButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const format = this.textContent.toLowerCase().trim();
                    alert(`Exportation des données au format ${format}... (fonctionnalité à implémenter)`);
                });
            });
        });
    </script>

@endsection