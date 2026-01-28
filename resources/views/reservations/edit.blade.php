{{-- resources/views/reservations/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Modifier la Réservation')

@section('content')
    <div class="container">
        <!-- En-tête de la page -->
        <div class="header-sec mb-4">
            <div class="header-sec-title">
                <h1><i class="fas fa-edit"></i> Modifier la Réservation #{{ $reservation->id }}</h1>
                <p>Modifiez les détails de votre réservation</p>
            </div>
            <div class="header-sec-date">
                Dernière modification : {{ $reservation->updated_at->format('d/m/Y H:i') }}
            </div>
        </div>

        <!-- Carte du formulaire -->
        <div class="card p-3 mb-2">
            <div class="mb-2">
                <h2 class="page-title">
                    <i class="fas fa-edit"></i> Modifier la réservation
                </h2>
                <p class="page-subtitle">Vous ne pouvez modifier que certains champs selon le statut</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Veuillez corriger les erreurs suivantes :</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success mb-4">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('reservations.update', $reservation->id) }}" method="POST" id="editReservationForm">
                @csrf
                @method('PUT')

                <!-- Informations non modifiables -->
                <div class="mb-4">
                    <h4 class="mb-3">
                        <i class="fas fa-lock"></i> Informations verrouillées
                    </h4>
                    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                        <div class="section-card">
                            <div class="section-icon">
                                <i class="fas fa-server"></i>
                            </div>
                            <h4>Ressource</h4>
                            <p class="font-wb">{{ $reservation->resource->nom }}</p>
                            <small class="text-muted">Catégorie :
                                {{ $reservation->resource->category->nom ?? 'Non catégorisé' }}</small>
                        </div>
                        <div class="section-card">
                            <div class="section-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h4>Demandeur</h4>
                            <p class="font-wb">{{ $reservation->user->prenom }} {{ $reservation->user->nom }}</p>
                            <small class="text-muted">{{ $reservation->user->email }}</small>
                        </div>
                        <div class="section-card">
                            <div class="section-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h4>Période actuelle</h4>
                            <p><strong>Début :</strong> {{ $reservation->date_debut->format('d/m/Y H:i') }}</p>
                            <p><strong>Fin :</strong> {{ $reservation->date_fin->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Champs modifiables selon le statut -->
                <div class="mb-4">
                    <h4 class="mb-3">
                        <i class="fas fa-edit"></i> Champs modifiables
                    </h4>

                    <!-- Date de début (modifiable seulement si en attente) -->
                    @if($reservation->statut === 'en attente')
                        <div class="mb-4">
                            <label for="date_debut" class="form-label">
                                <i class="fas fa-calendar-day"></i> Nouvelle date de début
                            </label>
                            <input type="datetime-local" name="date_debut" id="date_debut" class="form-control"
                                value="{{ old('date_debut', $reservation->date_debut->format('Y-m-d\TH:i')) }}"
                                min="{{ now()->format('Y-m-d\TH:i') }}">
                            <div class="form-text">Modifiez la date de début si nécessaire</div>
                        </div>

                        <!-- Date de fin -->
                        <div class="mb-4">
                            <label for="date_fin" class="form-label">
                                <i class="fas fa-calendar-times"></i> Nouvelle date de fin
                            </label>
                            <input type="datetime-local" name="date_fin" id="date_fin" class="form-control"
                                value="{{ old('date_fin', $reservation->date_fin->format('Y-m-d\TH:i')) }}">
                            <div class="form-text">Doit être postérieure à la date de début</div>
                        </div>

                        <!-- Durée calculée -->
                        <div class="mb-4">
                            <div class="reservation-info p-3 bord-rad-lef2">
                                <strong><i class="fas fa-clock"></i> Nouvelle durée estimée :</strong>
                                <span id="dureeCalcul">--</span>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info p-3">
                            <i class="fas fa-info-circle"></i>
                            Les dates ne peuvent pas être modifiées car la réservation n'est plus en statut "en attente".
                        </div>
                    @endif

                    <!-- Justification (toujours modifiable) -->
                    <div class="mb-4">
                        <label for="justification" class="form-label">
                            <i class="fas fa-comment-dots"></i> Justification *
                        </label>
                        <input name="justification" type="text"
                          id="justification" 
                          class="form-control w-80 h-80px" 
                          placeholder="Décrivez en détail l'objectif de cette réservation..." 
                          required>{{ old('justification') }}</input>
                        <div class="form-text">Minimum 10 caractères, maximum 1000 caractères</div>
                        <div class="text-right mt-2">
                            <span id="charCount">{{ strlen($reservation->justification) }}</span> / 1000 caractères
                        </div>
                    </div>

                    <!-- Commentaire du responsable (visible seulement) -->
                    @if($reservation->commentaire_responsable)
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-comment-medical"></i> Commentaire du responsable
                            </label>
                            <div class="reservation-info p-3" style="background: #f0f7ff;">
                                <p class="mb-0">{{ $reservation->commentaire_responsable }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Informations sur les conflits potentiels -->
                <div class="alert alert-danger fade-in flash-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Attention :</strong> La modification de cette réservation peut entraîner des conflits avec
                    d'autres réservations.
                    Le système vérifiera automatiquement la disponibilité lors de la soumission.
                </div>

                <!-- Actions du formulaire -->
                <div class="page-actions mt-5">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                    <a href="{{ route('reservations.show', $reservation->id) }}" class="btn btn-outline btn-lg">
                        <i class="fas fa-times"></i> Annuler
                    </a>

                    @if($reservation->statut === 'en attente')
                        <button type="button" class="btn btn-danger btn-lg" id="cancelBtn">
                            <i class="fas fa-ban"></i> Annuler la réservation
                        </button>
                    @endif

                    <a href="{{ route('reservations.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-list"></i> Retour à la liste
                    </a>
                </div>
            </form>
        </div>

        <!-- Modal d'annulation -->
        <div class="modal" id="cancelModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i class="fas fa-exclamation-triangle"></i> Confirmer l'annulation
                        </h3>
                        <button type="button" class="modal-close" data-dismiss="modal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger p-3">
                            <i class="fas fa-ban"></i>
                            <strong>Attention !</strong> Vous êtes sur le point d'annuler cette réservation.
                        </div>
                        <p>Êtes-vous sûr de vouloir annuler la réservation <strong>#{{ $reservation->id }}</strong> ?</p>
                        <p>Cette action ne peut pas être annulée.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-dismiss="modal">
                            <i class="fas fa-times"></i> Non, garder
                        </button>
                        <form action="{{ route('reservations.cancel', $reservation->id) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-check"></i> Oui, annuler
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Éléments DOM
            const dateDebutInput = document.getElementById('date_debut');
            const dateFinInput = document.getElementById('date_fin');
            const justificationTextarea = document.getElementById('justification');
            const charCountSpan = document.getElementById('charCount');
            const dureeSpan = document.getElementById('dureeCalcul');
            const cancelBtn = document.getElementById('cancelBtn');
            const cancelModal = document.getElementById('cancelModal');

            // Mettre à jour le compteur de caractères
            justificationTextarea.addEventListener('input', function () {
                const length = this.value.length;
                charCountSpan.textContent = length;
                if (length < 10) {
                    this.style.borderColor = '#e74c3c';
                    charCountSpan.style.color = '#e74c3c';
                } else {
                    this.style.borderColor = '#27ae60';
                    charCountSpan.style.color = '#27ae60';
                }
            });

            // Calculer la durée
            function calculerDuree() {
                if (!dateDebutInput || !dateFinInput) return;

                const debut = new Date(dateDebutInput.value);
                const fin = new Date(dateFinInput.value);

                if (debut && fin && fin > debut) {
                    const diffMs = fin - debut;
                    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                    const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));

                    let duree = '';
                    if (diffHours > 0) {
                        duree += `${diffHours} heure${diffHours > 1 ? 's' : ''}`;
                    }
                    if (diffMinutes > 0) {
                        duree += `${diffHours > 0 ? ' et ' : ''}${diffMinutes} minute${diffMinutes > 1 ? 's' : ''}`;
                    }
                    if (!duree) duree = '0 minute';

                    dureeSpan.textContent = duree;
                } else {
                    dureeSpan.textContent = '--';
                }
            }

            // Événements pour les dates
            if (dateDebutInput && dateFinInput) {
                dateDebutInput.addEventListener('change', function () {
                    if (dateFinInput.value && new Date(dateFinInput.value) <= new Date(this.value)) {
                        dateFinInput.min = this.value;
                    }
                    calculerDuree();
                });

                dateFinInput.addEventListener('change', calculerDuree);
                calculerDuree(); // Calcul initial
            }

            // Gestion de l'annulation
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function () {
                    cancelModal.classList.add('show', 'fade-in');
                });
            }

            // Fermer les modals
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function (e) {
                    if (e.target === this || e.target.closest('.modal-close')) {
                        this.classList.remove('show');
                    }
                });
            });

            // Initialiser le compteur de caractères
            justificationTextarea.dispatchEvent(new Event('input'));
        });
    </script>
@endpush

@push('styles')
    <style>
        .reservation-info {
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #3498db;
        }

        #charCount {
            font-weight: bold;
        }

        .modal {
            display: none;
        }

        .modal.show {
            display: flex;
        }

        .section-card {
            transition: transform 0.3s ease;
        }

        .section-card:hover {
            transform: translateY(-5px);
        }
    </style>
@endpush