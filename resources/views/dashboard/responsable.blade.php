@extends('layouts.app')
@section('title', 'Tableau de bord - Responsable')
@section('page-title', 'Tableau de bord')

@section('content')
    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="header-sec mt-6">
            <div class="header-sec-title">
                <h1><i class="fas fa-user-shield mr-1"></i>Tableau de bord Responsable</h1>
                <p>Gérez vos ressources et supervisez les activités qui vous sont assignées</p>
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
                    <h3>{{ $stats['managedResources'] }}</h3>
                    <p>Ressources gérées</p>
                </div>
            </div>

            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['pending_reservations'] }}</h3>
                    <p>Réservations en attente</p>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-th-large"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['reportedMessagesCount'] }}</h3>
                    <p>Messages signalés</p>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['maintenance_count'] }}</h3>
                    <p>Maintenances planifiées</p>
                </div>
            </div>
        </div>

        <!-- Dashboard sections-->
        <div class="dashboard-sections">
            <!-- Actions rapides pour Responsable -->
            <div class="quick-actions-column">
                <div class="quick-actions-grid">
                    <a href="{{ route('profile') }}" class="quick-action">
                        <div class="action-icon"><i class="fas fa-user"></i></div>
                        <span>Mon profil</span>
                    </a>
                    <a href="{{ route('reservations.index', ['statut' => 'en attente']) }}" class="quick-action">
                        <div class="action-icon"><i class="fas fa-calendar-check"></i></div>
                        <span>Approuver réservations</span>
                    </a>

                    <a href="{{ route('reported-messages.index') }}" class="quick-action">
                        <div class="action-icon"><i class="fas fa-flag"></i></div>
                        <span>Messages signalés</span>
                        @if($stats['reportedMessagesCount'] > 0)
                            <span class="notification-badge unread-badge">{{ $stats['reportedMessagesCount'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('maintenance.index') }}" class="quick-action">
                        <div class="action-icon"><i class="fas fa-tools"></i></div>
                        <span>Planifier maintenance</span>
                        @if($stats['maintenance_count'] > 0)
                            <span class="notification-badge unread-badge">{{ $stats['maintenance_count'] }}</span>
                        @endif
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

            <!-- contenu principal -->
            <div class="dashboard-main-content">
                <!-- Ressources gérées -->
                <div class="dashboard-column">
                    <div class="section-card mb-3">
                        <div class="section-header">
                            <h3><i class="fas fa-th-large mr-1"></i>Ressources gérées</h3>
                            <a href="{{ route('responsable.resources') }}" class="btn btn-outline btn-sm">Voir toutes</a>
                        </div>

                        <div class="resources-list">
                            @forelse($managedResources->sortBy('nom') as $resource)
                                <div class="resource-item">
                                    <div class="resource-info">
                                        <div class="resource-name"><i class="fas fa-server mr-1"></i> {{ $resource->nom }}</div>
                                        <div class="resource-meta">
                                            <span
                                                class="resource-category">{{ $resource->category->nom ?? 'Non catégorisé' }}</span>
                                            <span class="resource-status status-{{ $resource->statut }}">
                                                {{ $resource->statut }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="resource-stats">
                                        <div class="stat-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>{{ $resource->reservations_count }} réservations</span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="fas fa-tools"></i>
                                            <span>{{ $resource->maintenance_schedules_count }} maintenances</span>
                                        </div>
                                    </div>
                                    <div class="resource-actions">
                                        <!-- Voir -->
                                        <a href="{{ route('resources.show', $resource->id) }}" class="btn btn-sm btn-primary"
                                            title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Modifier -->
                                        <a href="{{ route('resources.edit', $resource->id) }}" class="btn btn-sm btn-warning"
                                            title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Supprimer -->
                                        <form action="{{ route('resources.destroy', $resource->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette ressource ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-th-large"></i>
                                    <p>Aucune ressource assignée</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Réservations en attente -->
                    <div class="section-card">
                        <div class="section-header">
                            <h3><i class="fas fa-clock mr-1"></i>Réservations en attente</h3>
                            <a href="{{ route('reservations.index') }}?statut=en attente"
                                class="btn btn-outline btn-sm">Voir
                                toutes</a>
                        </div>

                        <div class="reservations-simple-list">
                            @forelse($pendingReservations as $reservation)
                                <div class="reservation-simple-item">
                                    <div class="reservation-simple-header">
                                        <div class="reservation-user">
                                            <div class="user-initials">
                                                {{ substr($reservation->user->prenom, 0, 1) }}{{ substr($reservation->user->nom, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="user-name">{{ $reservation->user->prenom }}
                                                    {{ $reservation->user->nom }}
                                                </div>
                                                <div class="reservation-time">{{ $reservation->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="reservation-simple-info">
                                        <div class="resource-name">
                                            <i class="fas fa-server"></i> {{ $reservation->resource->nom }}
                                        </div>
                                        <div class="reservation-dates">
                                            <i class="fas fa-calendar-alt"></i>
                                            {{ $reservation->date_debut->format('d/m H:i') }} →
                                            {{ $reservation->date_fin->format('d/m H:i') }}
                                            <span
                                                class="duration">({{ $reservation->date_debut->diffInHours($reservation->date_fin) }}h)</span>
                                        </div>
                                    </div>

                                    <div class="reservation-simple-actions">
                                        <form action="{{ route('reservations.approve', $reservation->id) }}" method="POST"
                                            class="action-form">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm" title="Approuver">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>

                                        <!-- Bouton pour ouvrir le modal de refus -->
                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="openRejectModalDashboard({{ $reservation->id }})" title="Refuser">
                                            <i class="fas fa-times"></i>
                                        </button>

                                        <a href="{{ route('reservations.show', $reservation->id) }}"
                                            class="btn btn-primary btn-sm" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="simple-empty-state">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Aucune réservation en attente</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Messages signalés récents -->
                    <div class="section-card mb-3">
                        <div class="section-header">
                            <h3><i class="fas fa-flag mr-1"></i>Messages signalés récents</h3>
                            <a href="{{ route('reported-messages.index') }}" class="btn btn-outline btn-sm">Voir tous</a>
                        </div>

                        <div class="resources-list">
                            @forelse($reportedMessages as $message)
                                <div class="message-item unread">
                                    <div class="message-icon">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                    <div class="message-content">
                                        <div class="message-header">
                                            <strong>{{ $message->user->prenom ?? 'Utilisateur' }}</strong>
                                            <span class="message-time">{{ $message->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="message-text">{{ Str::limit($message->message, 100) }}</div>
                                        <div class="message-resource">
                                            <i class="fas fa-server"></i>
                                            {{ $message->resource->nom ?? 'Ressource inconnue' }}
                                        </div>
                                    </div>
                                    <div class="message-actions mt-3 d-flex gap-2">
                                        @if(!$message->est_lu)
                                            <form action="{{ route('reported-messages.markAsRead', $message->id) }}" method="POST">
                                                @csrf
                                                <button title="Marque comme lu" type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> lu
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle"></i> Déjà lu
                                            </span>
                                        @endif
                                        <form action="{{ route('reported-messages.destroy', $message->id) }}" method="POST"
                                            onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button title="supprimer" type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Aucun message signalé</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

    <!-- Modals pour refus des réservations - EN DEHORS DU CONTENEUR PRINCIPAL -->
    @foreach($pendingReservations as $reservation)
        <div id="rejectModalDashboard-{{ $reservation->id }}" class="modal-overlay" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i class="fas fa-times-circle text-danger mr-2"></i>
                            Refus de réservation
                        </h3>
                        <button type="button" class="modal-close" onclick="closeRejectModalDashboard({{ $reservation->id }})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form action="{{ route('reservations.reject', $reservation->id) }}" method="POST"
                            onsubmit="return checkJustificationDashboard(this, {{ $reservation->id }})">
                            @csrf
                            @method('PATCH')

                            <div class="form-group mb-3">
                                <label for="commentaire-dashboard-{{ $reservation->id }}" class="form-label mb-2">
                                    Justification du refus (obligatoire)
                                </label>
                                <textarea id="commentaire-dashboard-{{ $reservation->id }}" name="commentaire"
                                    class="form-control w-80 h-80px"
                                    placeholder="Veuillez expliquer la raison du refus de cette réservation (minimum 10 caractères)..."
                                    required minlength="10"></textarea>
                                <small class="form-text text-gray">
                                    Cette justification sera visible par l'utilisateur.
                                </small>
                                <div id="justification-error-{{ $reservation->id }}" class="text-danger mt-1"
                                    style="display: none;">
                                    <i class="fas fa-exclamation-circle"></i>
                                    La justification doit contenir au moins 10 caractères.
                                </div>
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
                                    onclick="closeRejectModalDashboard({{ $reservation->id }})">
                                    <i class="fas fa-times mr-2"></i>
                                    Annuler
                                </button>
                                <button type="submit" class="btn btn-danger btn-submit" id="submit-btn-{{ $reservation->id }}">
                                    <i class="fas fa-ban mr-2"></i>
                                    Confirmer le refus
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        // Fonctions pour gérer les modals dans le dashboard
        function closeRejectModalDashboard(id) {
            const modal = document.getElementById(`rejectModalDashboard-${id}`);
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        function openRejectModalDashboard(id) {
            const modal = document.getElementById(`rejectModalDashboard-${id}`);
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);

            // Reset des erreurs
            const errorDiv = document.getElementById(`justification-error-${id}`);
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }

            // Reset du bouton de soumission
            const submitBtn = document.getElementById(`submit-btn-${id}`);
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-ban mr-2"></i> Confirmer le refus';
                submitBtn.disabled = false;
            }
        }

        function checkJustificationDashboard(form, id) {
            const justification = form.querySelector('textarea[name="commentaire"]').value.trim();
            const errorDiv = document.getElementById(`justification-error-${id}`);
            const submitBtn = document.getElementById(`submit-btn-${id}`);

            // Validation de la justification
            if (justification.length < 10) {
                if (errorDiv) {
                    errorDiv.style.display = 'block';
                }

                // Animation d'erreur
                form.querySelector('textarea').classList.add('bord-rad-lef2', 'c-dng');
                setTimeout(() => {
                    form.querySelector('textarea').classList.remove('bord-rad-lef2', 'c-dng');
                }, 1000);

                return false;
            }

            // Confirmation finale
            if (!confirm('Êtes-vous sûr de vouloir refuser cette réservation ? Cette action est irréversible.')) {
                return false;
            }

            // État de chargement du bouton
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Traitement...';
                submitBtn.disabled = true;
            }

            return true;
        }

        // Fermer le modal en cliquant à l'extérieur
        document.addEventListener('click', function (event) {
            if (event.target.classList.contains('modal-overlay')) {
                const id = event.target.id.replace('rejectModalDashboard-', '');
                if (id) {
                    closeRejectModalDashboard(id);
                }
            }
        });

        // Fermer le modal avec la touche Échap
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                const openModal = document.querySelector('.modal-overlay.active');
                if (openModal) {
                    const id = openModal.id.replace('rejectModalDashboard-', '');
                    if (id) {
                        closeRejectModalDashboard(id);
                    }
                }
            }
        });

        // Validation en temps réel
        document.addEventListener('DOMContentLoaded', function () {
            // Ajouter la validation en temps réel pour tous les textareas de justification
            document.querySelectorAll('textarea[name="commentaire"]').forEach(textarea => {
                textarea.addEventListener('input', function () {
                    const id = this.id.replace('commentaire-dashboard-', '');
                    const errorDiv = document.getElementById(`justification-error-${id}`);

                    if (this.value.trim().length >= 10) {
                        if (errorDiv) {
                            errorDiv.style.display = 'none';
                        }
                        this.classList.remove('bord-rad-lef2', 'c-dng');
                        this.classList.add('bord-rad-lef2');
                        this.style.borderLeftColor = '#27ae60';
                    } else {
                        this.classList.remove('bord-rad-lef2');
                        this.style.borderLeftColor = '';
                    }
                });

                // Validation à la perte de focus
                textarea.addEventListener('blur', function () {
                    const id = this.id.replace('commentaire-dashboard-', '');
                    const errorDiv = document.getElementById(`justification-error-${id}`);

                    if (this.value.trim().length > 0 && this.value.trim().length < 10) {
                        if (errorDiv) {
                            errorDiv.style.display = 'block';
                        }
                        this.classList.add('bord-rad-lef2', 'c-dng');
                    }
                });
            });
        });
    </script>
@endsection