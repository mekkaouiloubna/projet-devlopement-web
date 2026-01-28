@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="header-sec">
            <div class="header-sec-title">
                <h1><i class="fas fa-tachometer-alt me-2"></i>Liste des réservations</h1>
                <p>Informations complètes sur les réservations.</p>
            </div>
            <div class="header-sec-date">
                <i class="fas fa-calendar-alt"></i> {{ now()->format('d/m/Y') }}
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Utilisateur</th>
                    <th>Ressource</th>
                    <th>Date début</th>
                    <th>Date fin</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $reservation)
                    <tr>
                        <td>{{ $reservation->id }}</td>
                        <td>{{ $reservation->user->prenom }} {{ $reservation->user->nom }}</td>
                        <td>{{ $reservation->resource->nom }}</td>
                        <td>{{ $reservation->date_debut }}</td>
                        <td>{{ $reservation->date_fin }}</td>
                        <td>
                            <span class="w-100 ta-center badge 
                                {{ $reservation->statut == 'en attente' ? 'badge-warning' : '' }}
                                {{ $reservation->statut == 'approuvée' ? 'badge-success' : '' }}
                                {{ $reservation->statut == 'refusée' ? 'badge-danger' : '' }}
                                {{ $reservation->statut == 'active' ? 'badge-info' : '' }}
                                {{ $reservation->statut == 'terminée' ? 'badge-primary' : '' }}">
                                {{ $reservation->statut }}
                            </span>
                        </td>
                        <td>
                            @if($reservation->statut === 'en attente' && auth()->user()->isAdminOrRespoResource($reservation->resource))
                                <form action="{{ route('reservations.approve', $reservation->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm">Approuver</button>
                                </form>

                                <!-- Bouton Refuser -->
                                <button type="button" class="btn btn-danger btn-sm"
                                    onclick="openRejectModal({{ $reservation->id }})">
                                    Refuser
                                </button>

                                <!-- Modal -->
                                <div id="rejectModal-{{ $reservation->id }}" class="modal fade-in">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h3 class="modal-title">
                                                    <i class="fas fa-times-circle text-danger mr-2"></i>
                                                    Refus de réservation
                                                </h3>
                                                <button type="button" class="modal-close"
                                                    onclick="closeRejectModal({{ $reservation->id }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>

                                            <div class="modal-body">
                                                <form action="{{ route('reservations.reject', $reservation->id) }}" method="POST"
                                                    onsubmit="return checkJustification(this)">
                                                    @csrf
                                                    @method('PATCH')

                                                    <div class="form-group mb-3">
                                                        <label for="commentaire-{{ $reservation->id }}" class="form-label mb-2">
                                                            Justification du refus 
                                                        </label>
                                                        <input type="text" id="commentaire-{{ $reservation->id }}" name="commentaire"
                                                            class="form-control w-80 h-80px"
                                                            placeholder="Veuillez expliquer la raison du refus de cette réservation..."
                                                            required></input> <br>
                                                        <small class="form-text text-gray">
                                                            Cette justification sera visible par l'utilisateur.
                                                        </small>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label class="form-label mb-2">
                                                            Détails de la réservation
                                                        </label>
                                                        <div class="reservation-info bg-light p-3 rounded">
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span><strong>Ressource:</strong></span>
                                                                <span class="text-primary">{{ $reservation->resource->nom }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span><strong>Utilisateur:</strong></span>
                                                                <span>{{ $reservation->user->prenom }}
                                                                    {{ $reservation->user->nom }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                <span><strong>Période:</strong></span>
                                                                <span>{{ \Carbon\Carbon::parse($reservation->date_debut)->format('d/m/Y H:i') }}
                                                                    →
                                                                    {{ \Carbon\Carbon::parse($reservation->date_fin)->format('d/m/Y H:i') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline"
                                                            onclick="closeRejectModal({{ $reservation->id }})">
                                                            <i class="fas fa-times mr-2"></i>
                                                            Annuler
                                                        </button>
                                                        <button type="submit" class="btn btn-danger btn-submit">
                                                            <i class="fas fa-ban mr-2"></i>
                                                            Confirmer le refus
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <a href="{{ route('reservations.show', $reservation->id) }}"
                                class="btn btn-outline btn-s">Détails</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Aucune réservation trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($reservations->count() > 0)
            @if($reservations->hasPages())
                <!-- Custom Pagination -->
                <div class="custom-pagination-wrapper">
                    <div class="custom-pagination">
                        {{-- Previous Button --}}
                        @if($reservations->onFirstPage())
                            <span class="pagination-arrow disabled">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </span>
                        @else
                            <a href="{{ $reservations->previousPageUrl() }}" class="pagination-arrow">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        <div class="pagination-numbers">
                            @php
                                $current = $reservations->currentPage();
                                $last = $reservations->lastPage();
                                $window = 2;

                                if ($last <= 7) {
                                    $pages = range(1, $last);
                                } else {
                                    $pages = [];
                                    $pages[] = 1;
                                    $start = max(2, $current - $window);
                                    $end = min($last - 1, $current + $window);
                                    if ($start > 2)
                                        $pages[] = '...';
                                    for ($i = $start; $i <= $end; $i++)
                                        $pages[] = $i;
                                    if ($end < $last - 1)
                                        $pages[] = '...';
                                    $pages[] = $last;
                                }
                            @endphp

                            @foreach($pages as $page)
                                @if($page == '...')
                                    <span class="pagination-dots">...</span>
                                @elseif($page == $current)
                                    <span class="pagination-number active">{{ $page }}</span>
                                @else
                                    <a href="{{ $reservations->url($page) }}" class="pagination-number">{{ $page }}</a>
                                @endif
                            @endforeach
                        </div>

                        {{-- Next Button --}}
                        @if($reservations->hasMorePages())
                            <a href="{{ $reservations->nextPageUrl() }}" class="pagination-arrow">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="pagination-arrow disabled">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </span>
                        @endif
                    </div>

                    <div class="pagination-stats">
                        Affichage de {{ $reservations->firstItem() }} à {{ $reservations->lastItem() }} sur
                        {{ $reservations->total() }}
                        reservation
                    </div>
                </div>
            @endif
        @else
            <div class="section-card ta-center p-5">
                <div class="section-icon mb-3" style="width: 80px; height: 80px; background: var(--light-color);">
                    <i class="fas fa-envelope-open-text" style="font-size: 2rem; color: var(--sec-color)"></i>
                </div>
                <h3 class="mb-2">Aucun reservation trouvée</h3>
                <p class="text-gray">Vous n'avez aucune reservation pour le moment.</p>
            </div>
        @endif
    </div>
    <script>
        function closeRejectModal(id) {
            const modal = document.getElementById(`rejectModal-${id}`);
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        function openRejectModal(id) {
            const modal = document.getElementById(`rejectModal-${id}`);
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }

        function checkJustification(form) {
            const justification = form.querySelector('textarea[name="commentaire"]').value.trim();

            if (justification.length < 10) {
                alert('Veuillez fournir une justification détaillée (au moins 10 caractères).');
                return false;
            }

            if (!confirm('Êtes-vous sûr de vouloir refuser cette réservation ? Cette action est irréversible.')) {
                return false;
            }

            // Add loading state to button
            const submitBtn = form.querySelector('.btn-submit');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Traitement...';
            submitBtn.disabled = true;

            return true;
        }

        // Close modal when clicking outside
        document.addEventListener('click', function (event) {
            if (event.target.classList.contains('modal')) {
                const id = event.target.id.replace('rejectModal-', '');
                closeRejectModal(id);
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    const id = openModal.id.replace('rejectModal-', '');
                    closeRejectModal(id);
                }
            }
        });
    </script>
@endsection