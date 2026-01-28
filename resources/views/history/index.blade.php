@extends('layouts.app')

@section('title', 'Historique des activités')

@section('content')
    <div class="container">
        <!-- Header -->
        <div class="header-sec mb-4">
            <div class="header-sec-title">
                <h1>
                    <i class="fas fa-history me-2"></i>Historique des activités
                </h1>
                <p>Suivi de toutes les activités du système</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('history-logs.export') }}" class="btn btn-success trans-up">
                    <i class="fas fa-file-export me-1"></i>Exporter CSV
                </a>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="stats-grid mb-4">
            @php
                $stats = [
                    ['icon' => 'fa-plus-circle', 'color' => 'success', 'label' => 'Créations', 'count' => $historyLogs->where('action', 'création')->count()],
                    ['icon' => 'fa-edit', 'color' => 'warning', 'label' => 'Modifications', 'count' => $historyLogs->where('action', 'modification')->count()],
                    ['icon' => 'fa-ban', 'color' => 'danger', 'label' => 'Annulations', 'count' => $historyLogs->where('action', 'annulation')->count()],
                    ['icon' => 'fa-check-circle', 'color' => 'info', 'label' => 'Approbations', 'count' => $historyLogs->where('action', 'approbation')->count()],
                ];
            @endphp

            @foreach($stats as $stat)
                <div class="stat-card {{ $stat['color'] }}">
                    <div class="stat-icon">
                        <i class="fas {{ $stat['icon'] }}"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $stat['count'] }}</h3>
                        <p>{{ $stat['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Filtres horizontaux -->
        <div class="card p-3 mb-4">
            <form id="filterForm" method="GET" action="{{ route('history-logs.index') }}" class="w-100">
                <label for="action" class="font-wb">Action</label>
                <select name="action" id="action" class="form-control mr-2">
                    <option value="">Toutes les actions</option>
                    @foreach($actions as $actionValue)
                        <option value="{{ $actionValue }}" {{ request('action') == $actionValue ? 'selected' : '' }}>
                            {{ ucfirst($actionValue) }}
                        </option>
                    @endforeach
                </select>

                <label for="start_date" class="font-wb">Date début</label>
                <input type="date" name="start_date" id="start_date" class="form-control mr-2"
                    value="{{ request('start_date') }}">

                <label for="end_date" class="font-wb">Date fin</label>
                <input type="date" name="end_date" id="end_date" class="form-control mr-2"
                    value="{{ request('end_date') }}">

                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search me-1"></i>Appliquer
                    </button>
                    <a href="{{ route('history-logs.index') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-redo me-1"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Tableau des logs -->
        <div class="card">
            <div class="table-responsive max-h6">
                <table class="table">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Date/Heure</th>
                            <th>Action</th>
                            <th>Utilisateur</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historyLogs as $log)
                            <tr class="trans-up">
                                <td class="text-muted font-wb">#{{ $log->id }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="font-wb">
                                            {{ $log->created_at->format('d/m/Y') }}
                                        </span>
                                        <small class="text-muted">
                                            {{ $log->created_at->format('H:i:s') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $actionColors = [
                                            'création' => 'success',
                                            'modification' => 'warning',
                                            'suppression' => 'danger',
                                            'annulation' => 'secondary',
                                            'approbation' => 'info',
                                            'refus' => 'danger',
                                            'commentaire' => 'primary',
                                            'réclamation' => 'danger'
                                        ];
                                        $color = $actionColors[$log->action] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $color }} px-3 py-1">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->user)
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar-sm me-2">
                                                {{ substr($log->user->prenom, 0, 1) }}{{ substr($log->user->nom, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-wb">
                                                    {{ $log->user->prenom }} {{ $log->user->nom }}
                                                </div>
                                                <small class="text-muted d-block">
                                                    {{ $log->user->email }}
                                                </small>
                                                @if($log->user->role)
                                                    <span class="badge bg-light text-dark small mt-1">
                                                        {{ $log->user->role->nom }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-muted fst-italic small">
                                            <i class="fas fa-user-slash me-1"></i>
                                            Utilisateur supprimé
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="description-container w-80">
                                        <p class="mb-1">{{ $log->description }}</p>
                                        @if($log->created_at != $log->updated_at)
                                            <small class="text-muted">
                                                <i class="fas fa-pen fa-xs me-1"></i>
                                                Modifié le {{ $log->updated_at->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-history fa-4x mb-4"></i>
                                        <h4 class="mb-3">Aucune activité enregistrée</h4>
                                        <p class="mb-0">Les activités des utilisateurs apparaîtront ici.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($historyLogs->hasPages())
                <div class="custom-pagination-wrapper p-3">
                    <div class="custom-pagination">
                        @if($historyLogs->onFirstPage())
                            <span class="pagination-arrow disabled">
                                <i class="fas fa-chevron-left"></i>
                                Précédent
                            </span>
                        @else
                            <a href="{{ $historyLogs->previousPageUrl() }}" class="pagination-arrow">
                                <i class="fas fa-chevron-left"></i>
                                Précédent
                            </a>
                        @endif

                        <div class="pagination-numbers">
                            @foreach(range(1, $historyLogs->lastPage()) as $page)
                                @if($page == $historyLogs->currentPage())
                                    <span class="pagination-number active">{{ $page }}</span>
                                @elseif($page >= $historyLogs->currentPage() - 2 && $page <= $historyLogs->currentPage() + 2)
                                    <a href="{{ $historyLogs->url($page) }}" class="pagination-number">{{ $page }}</a>
                                @elseif($page == 1 || $page == $historyLogs->lastPage())
                                    <a href="{{ $historyLogs->url($page) }}" class="pagination-number">{{ $page }}</a>
                                @elseif($page == $historyLogs->currentPage() - 3 || $page == $historyLogs->currentPage() + 3)
                                    <span class="pagination-dots">...</span>
                                @endif
                            @endforeach
                        </div>

                        @if($historyLogs->hasMorePages())
                            <a href="{{ $historyLogs->nextPageUrl() }}" class="pagination-arrow">
                                Suivant
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="pagination-arrow disabled">
                                Suivant
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        @endif
                    </div>

                    <div class="pagination-stats">
                        Affichage de {{ $historyLogs->firstItem() }} à {{ $historyLogs->lastItem() }} sur
                        {{ $historyLogs->total() }} activités
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            .description-container {
                max-width: 400px;
                line-height: 1.5;
                word-wrap: break-word;
            }

            .preview-category {
                background: #f8f9fa;
                padding: 10px;
                border-radius: 8px;
                border-left: 3px solid var(--sec-color);
            }

            .preview-icon {
                width: 40px;
                height: 40px;
                background: linear-gradient(135deg, #e3f2fd, #bbdefb);
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                color: var(--sec-color);
                margin-right: 10px;
            }

            .preview-title {
                font-weight: 600;
                color: var(--prim-color);
                font-size: 0.9rem;
                margin-bottom: 5px;
            }

            .preview-description {
                font-size: 0.85rem;
                color: var(--gray-color);
                line-height: 1.4;
            }

            .form-control {
                border: 2px solid #e5e7eb;
                border-radius: 10px;
                padding: 8px 12px;
                font-size: 0.95rem;
                transition: all 0.3s ease;
                background: #f9fafb;
            }

            .form-control:focus {
                border-color: var(--sec-color);
                box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
                background: white;
                outline: none;
            }

            .form-label {
                font-weight: 600;
                color: var(--prim-color);
                margin-bottom: 8px;
                display: block;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Gestion des dates
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('end_date').max = today;

                const startDateInput = document.getElementById('start_date');
                const endDateInput = document.getElementById('end_date');

                if (startDateInput) {
                    startDateInput.addEventListener('change', function () {
                        if (this.value) {
                            endDateInput.min = this.value;
                        }
                    });
                }

                if (endDateInput) {
                    endDateInput.addEventListener('change', function () {
                        if (this.value && startDateInput.value) {
                            startDateInput.max = this.value;
                        }
                    });
                }

                // Auto-submit sur changement de filtre
                const filterForm = document.getElementById('filterForm');
                if (filterForm) {
                    const actionSelect = document.getElementById('action');
                    const userSelect = document.getElementById('user_id');

                    if (actionSelect) {
                        actionSelect.addEventListener('change', function () {
                            filterForm.submit();
                        });
                    }

                    if (userSelect) {
                        userSelect.addEventListener('change', function () {
                            filterForm.submit();
                        });
                    }
                }
            });
        </script>
    @endpush
@endsection