<!-- Modal pour refus de réservation - Component réutilisable -->
<div class="modal fade-in" id="rejectModal-{{ $reservation->id }}">
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
                    onsubmit="return checkJustification(this, {{ $reservation->id }})">
                    @csrf
                    @method('PATCH')

                    <div class="form-group mb-3">
                        <label for="commentaire-{{ $reservation->id }}" class="form-label mb-2">
                            Justification du refus (obligatoire)
                        </label>
                        <textarea id="commentaire-{{ $reservation->id }}" name="commentaire"
                            class="form-control w-80 h-80px"
                            placeholder="Veuillez expliquer la raison du refus de cette réservation (minimum 10 caractères)..."
                            required minlength="10" rows="3"></textarea>
                        <small class="form-text text-gray">
                            Cette justification sera visible par l'utilisateur.
                        </small>
                        <div id="justification-error-{{ $reservation->id }}" 
                             class="text-danger mt-1" style="display: none;">
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
                            onclick="closeRejectModal({{ $reservation->id }})">
                            <i class="fas fa-times mr-2"></i>
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-danger btn-submit" 
                                id="submit-btn-{{ $reservation->id }}">
                            <i class="fas fa-ban mr-2"></i>
                            Confirmer le refus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>