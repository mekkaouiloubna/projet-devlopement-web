@extends('layouts.app')

@section('title', 'Tableau de bord - Responsable')

@section('page-title', 'Tableau de bord Responsable')

@section('breadcrumbs')
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item">Responsable</span>
@endsection

@section('content')
<div class="responsable-dashboard">
    <!-- Statistiques du responsable -->
    <div class="responsable-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="white" stroke-width="2"/>
                    <path d="M3 9H21" stroke="white" stroke-width="2"/>
                    <path d="M9 21V9" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>{{ auth()->user()->resourcesGerees()->count() }}</h3>
                <p>Ressources gérées</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="4" width="18" height="18" rx="2" stroke="white" stroke-width="2"/>
                    <path d="M16 2V6" stroke="white" stroke-width="2"/>
                    <path d="M8 2V6" stroke="white" stroke-width="2"/>
                    <path d="M3 10H21" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>{{ auth()->user()->resourcesGerees()->withCount(['reservations' => function($q) {
                    $q->where('statut', 'en_attente');
                }])->get()->sum('reservations_count') }}</h3>
                <p>Réservations en attente</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>{{ \App\Models\Conversation::whereHas('reservation.resource', function($q) {
                    $q->where('responsable_id', auth()->id());
                })->where('est_signalé', true)->count() }}</h3>
                <p>Messages signalés</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <path d="M14 10L12 11.5L14 13M10 10L12 11.5L10 13M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z" stroke="white" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>{{ \App\Models\MaintenanceSchedule::whereHas('resource', function($q) {
                    $q->where('responsable_id', auth()->id());
                })->where('statut', 'planifiée')->count() }}</h3>
                <p>Maintenances planifiées</p>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="dashboard-content">
        <!-- Réservations en attente -->
        <div class="pending-reservations">
            <div class="section-header">
                <h3>Réservations en attente</h3>
                <a href="{{ route('responsable.reservations') }}" class="btn-view-all">Gérer toutes</a>
            </div>

            <div class="reservations-list">
                @php
                    $pendingReservations = \App\Models\Reservation::whereHas('resource', function($q) {
                        $q->where('responsable_id', auth()->id());
                    })->where('statut', 'en_attente')->latest()->take(5)->get();
                @endphp

                @if($pendingReservations->count() > 0)
                    @foreach($pendingReservations as $reservation)
                        <div class="reservation-item">
                            <div class="reservation-info">
                                <div class="resource-name">
                                    <strong>{{ $reservation->resource->nom }}</strong>
                                    <span class="resource-category">{{ $reservation->resource->category->nom }}</span>
                                </div>
                                <div class="user-info">
                                    <span class="user-name">{{ $reservation->user->prenom }} {{ $reservation->user->nom }}</span>
                                    <span class="user-type">{{ $reservation->user->type }}</span>
                                </div>
                                <div class="date-info">
                                    <span class="date-start">{{ $reservation->date_debut->format('d/m/Y H:i') }}</span>
                                    <span class="date-end">{{ $reservation->date_fin->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="justification">
                                    <strong>Justification:</strong>
                                    <p>{{ Str::limit($reservation->justification, 100) }}</p>
                                </div>
                            </div>
                            <div class="reservation-actions">
                                <button onclick="approveReservation({{ $reservation->id }})" class="btn btn-success btn-sm">
                                    Approuver
                                </button>
                                <button onclick="showRejectModal({{ $reservation->id }})" class="btn btn-danger btn-sm">
                                    Refuser
                                </button>
                                <a href="{{ route('reservations.show', $reservation->id) }}" class="btn btn-outline btn-sm">
                                    Détails
                                </a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <p>Aucune réservation en attente.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Ressources gérées -->
        <div class="managed-resources">
            <div class="section-header">
                <h3>Mes ressources</h3>
                <a href="{{ route('responsable.resources') }}" class="btn-view-all">Toutes les ressources</a>
            </div>

            <div class="resources-grid">
                @foreach(auth()->user()->resourcesGerees()->with('category')->take(4)->get() as $resource)
                    <div class="resource-card">
                        <div class="resource-header">
                            <div class="resource-status status-{{ $resource->statut }}">
                                {{ ucfirst($resource->statut) }}
                            </div>
                            <h4>{{ $resource->nom }}</h4>
                            <p class="resource-category">{{ $resource->category->nom }}</p>
                        </div>
                        
                        <div class="resource-stats">
                            <div class="stat">
                                <span class="stat-label">Réservations actives:</span>
                                <span class="stat-value">{{ $resource->reservations()->where('statut', 'active')->count() }}</span>
                            </div>
                            <div class="stat">
                                <span class="stat-label">En attente:</span>
                                <span class="stat-value">{{ $resource->reservations()->where('statut', 'en_attente')->count() }}</span>
                            </div>
                        </div>
                        
                        <div class="resource-actions">
                            <a href="{{ route('resources.show', $resource->id) }}" class="btn btn-outline btn-sm">
                                Voir
                            </a>
                            <a href="{{ route('resources.edit', $resource->id) }}" class="btn btn-primary btn-sm">
                                Modifier
                            </a>
                            <a href="{{ route('responsable.usage-report', $resource->id) }}" class="btn btn-info btn-sm">
                                Rapport
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Messages signalés -->
    <div class="reported-messages">
        <div class="section-header">
            <h3>Messages signalés récents</h3>
            <a href="{{ route('responsable.reported-messages') }}" class="btn-view-all">Voir tout</a>
        </div>

        <div class="messages-list">
            @php
                $reportedMessages = \App\Models\Conversation::whereHas('reservation.resource', function($q) {
                    $q->where('responsable_id', auth()->id());
                })->where('est_signalé', true)->with(['user', 'reservation.resource'])->latest()->take(3)->get();
            @endphp

            @if($reportedMessages->count() > 0)
                @foreach($reportedMessages as $message)
                    <div class="message-item">
                        <div class="message-header">
                            <div class="user-info">
                                <div class="user-avatar">
                                    {{ strtoupper(substr($message->user->prenom, 0, 1)) }}{{ strtoupper(substr($message->user->nom, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="user-name">{{ $message->user->prenom }} {{ $message->user->nom }}</div>
                                    <div class="message-time">{{ $message->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <div class="resource-info">
                                <span class="resource-name">{{ $message->reservation->resource->nom }}</span>
                            </div>
                        </div>
                        <div class="message-content">
                            <p>{{ $message->message }}</p>
                        </div>
                        <div class="message-actions">
                            <button onclick="deleteMessage({{ $message->id }})" class="btn btn-danger btn-sm">
                                Supprimer
                            </button>
                            <button onclick="unreportMessage({{ $message->id }})" class="btn btn-outline btn-sm">
                                Désignaler
                            </button>
                            <a href="{{ route('reservations.show', $message->reservation_id) }}" class="btn btn-info btn-sm">
                                Voir réservation
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <p>Aucun message signalé.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal pour refuser une réservation -->
<div class="modal" id="rejectModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Refuser la réservation</h3>
            <button class="modal-close" onclick="closeRejectModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="rejectForm">
                @csrf
                <input type="hidden" name="reservation_id" id="rejectReservationId">
                <div class="form-group">
                    <label for="rejectReason" class="form-label">Raison du refus *</label>
                    <textarea id="rejectReason" name="reason" class="form-control" rows="4" 
                              placeholder="Expliquez la raison du refus..." required></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeRejectModal()">Annuler</button>
            <button type="button" class="btn btn-danger" onclick="submitReject()">Confirmer le refus</button>
        </div>
    </div>
</div>

<style>
    .responsable-dashboard {
        padding: 20px 0;
    }

    /* Statistiques du responsable */
    .responsable-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .responsable-stats .stat-card {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        border-radius: 15px;
        padding: 25px;
        color: white;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .responsable-stats .stat-card:nth-child(2) {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    }

    .responsable-stats .stat-card:nth-child(3) {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    }

    .responsable-stats .stat-card:nth-child(4) {
        background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
    }

    .responsable-stats .stat-card:hover {
        transform: translateY(-5px);
    }

    .responsable-stats .stat-icon svg {
        width: 40px;
        height: 40px;
    }

    .responsable-stats .stat-content h3 {
        font-size: 2.5rem;
        margin: 0 0 5px 0;
        color: white;
        font-weight: 700;
    }

    .responsable-stats .stat-content p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    /* Contenu principal */
    .dashboard-content {
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
        margin-bottom: 40px;
    }

    @media (min-width: 1200px) {
        .dashboard-content {
            grid-template-columns: 2fr 1fr;
        }
    }

    /* Réservations en attente */
    .pending-reservations {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .pending-reservations .section-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pending-reservations h3 {
        margin: 0;
        font-size: 1.3rem;
        color: #2c3e50;
    }

    .reservations-list {
        padding: 20px;
    }

    .reservation-item {
        padding: 20px;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        margin-bottom: 15px;
        transition: border-color 0.3s ease;
    }

    .reservation-item:hover {
        border-color: #3498db;
    }

    .reservation-item:last-child {
        margin-bottom: 0;
    }

    .reservation-info {
        margin-bottom: 15px;
    }

    .resource-name {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .resource-name strong {
        font-size: 1.1rem;
        color: #2c3e50;
    }

    .resource-category {
        font-size: 0.85rem;
        background-color: #f8f9fa;
        padding: 3px 8px;
        border-radius: 4px;
        color: #6c757d;
    }

    .user-info {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }

    .user-name {
        font-weight: 500;
        color: #2c3e50;
    }

    .user-type {
        color: #95a5a6;
    }

    .date-info {
        display: flex;
        gap: 20px;
        margin-bottom: 10px;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .justification {
        margin-top: 10px;
    }

    .justification strong {
        color: #2c3e50;
        font-size: 0.9rem;
    }

    .justification p {
        margin: 5px 0 0 0;
        color: #6c757d;
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .reservation-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .reservation-actions .btn {
        min-width: 100px;
    }

    /* Ressources gérées */
    .managed-resources {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .managed-resources .section-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .managed-resources h3 {
        margin: 0;
        font-size: 1.3rem;
        color: #2c3e50;
    }

    .resources-grid {
        padding: 20px;
        display: grid;
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .resource-card {
        padding: 20px;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .resource-card:hover {
        border-color: #3498db;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .resource-header {
        margin-bottom: 15px;
    }

    .resource-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .status-disponible {
        background-color: #d4edda;
        color: #155724;
    }

    .status-réservé {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-maintenance {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .resource-header h4 {
        margin: 0 0 5px 0;
        font-size: 1.1rem;
        color: #2c3e50;
    }

    .resource-category {
        color: #95a5a6;
        font-size: 0.9rem;
        margin: 0;
    }

    .resource-stats {
        margin: 15px 0;
        padding: 15px 0;
        border-top: 1px solid #e9ecef;
        border-bottom: 1px solid #e9ecef;
    }

    .stat {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .stat:last-child {
        margin-bottom: 0;
    }

    .stat-label {
        color: #6c757d;
    }

    .stat-value {
        font-weight: 600;
        color: #2c3e50;
    }

    .resource-actions {
        display: flex;
        gap: 10px;
    }

    .resource-actions .btn {
        flex: 1;
        padding: 8px 12px;
        font-size: 0.85rem;
    }

    /* Messages signalés */
    .reported-messages {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .reported-messages .section-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .reported-messages h3 {
        margin: 0;
        font-size: 1.3rem;
        color: #2c3e50;
    }

    .messages-list {
        padding: 20px;
    }

    .message-item {
        padding: 20px;
        border: 1px solid #f8d7da;
        border-radius: 10px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
    }

    .message-item:last-child {
        margin-bottom: 0;
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #3498db;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1rem;
    }

    .user-name {
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 3px;
    }

    .message-time {
        font-size: 0.8rem;
        color: #95a5a6;
    }

    .resource-name {
        font-size: 0.9rem;
        color: #6c757d;
        background-color: #e9ecef;
        padding: 3px 8px;
        border-radius: 4px;
    }

    .message-content {
        margin: 15px 0;
        padding: 15px;
        background: white;
        border-radius: 8px;
        border-left: 4px solid #e74c3c;
    }

    .message-content p {
        margin: 0;
        color: #2c3e50;
        line-height: 1.5;
    }

    .message-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    /* État vide */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #95a5a6;
    }

    .empty-state p {
        margin: 0;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background-color: white;
        border-radius: 15px;
        width: 100%;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
        animation: modalFadeIn 0.3s ease;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        color: #2c3e50;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6c757d;
        transition: color 0.3s ease;
    }

    .modal-close:hover {
        color: #e74c3c;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        padding: 20px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .responsable-stats {
            grid-template-columns: 1fr;
        }
        
        .dashboard-content {
            grid-template-columns: 1fr;
        }
        
        .reservation-actions {
            flex-direction: column;
        }
        
        .reservation-actions .btn {
            width: 100%;
        }
        
        .message-actions {
            flex-direction: column;
        }
        
        .message-actions .btn {
            width: 100%;
        }
        
        .resource-actions {
            flex-direction: column;
        }
        
        .resource-actions .btn {
            width: 100%;
        }
        
        .message-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }

    @media (max-width: 576px) {
        .user-info {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .date-info {
            flex-direction: column;
            gap: 5px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation des cartes de statistiques
        const statCards = document.querySelectorAll('.responsable-stats .stat-card');
        statCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('fade-in');
        });
    });

    // Variables globales
    let currentReservationId = null;

    // Approuver une réservation
    function approveReservation(reservationId) {
        if (confirm('Êtes-vous sûr de vouloir approuver cette réservation ?')) {
            fetch(`/reservations/${reservationId}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    comment: 'Réservation approuvée'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Réservation approuvée avec succès', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(data.message || 'Erreur lors de l\'approbation', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Erreur de connexion', 'error');
            });
        }
    }

    // Afficher le modal de refus
    function showRejectModal(reservationId) {
        currentReservationId = reservationId;
        document.getElementById('rejectReservationId').value = reservationId;
        document.getElementById('rejectModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Fermer le modal de refus
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.remove('active');
        document.body.style.overflow = '';
        document.getElementById('rejectForm').reset();
        currentReservationId = null;
    }

    // Soumettre le refus
    function submitReject() {
        const reason = document.getElementById('rejectReason').value.trim();
        
        if (!reason) {
            showToast('Veuillez saisir une raison pour le refus', 'warning');
            return;
        }

        fetch(`/reservations/${currentReservationId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                comment: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Réservation refusée avec succès', 'success');
                closeRejectModal();
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(data.message || 'Erreur lors du refus', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erreur de connexion', 'error');
        });
    }

    // Supprimer un message signalé
    function deleteMessage(messageId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce message ? Cette action est irréversible.')) {
            fetch(`/conversations/${messageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Message supprimé avec succès', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(data.message || 'Erreur lors de la suppression', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Erreur de connexion', 'error');
            });
        }
    }

    // Désignaler un message
    function unreportMessage(messageId) {
        fetch(`/conversations/${messageId}/unreport`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Message désignalé avec succès', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(data.message || 'Erreur lors du désignalement', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erreur de connexion', 'error');
        });
    }

    // Fonction pour afficher les toasts
    function showToast(message, type = 'info') {
        // Supprimer les anciens toasts
        const oldToasts = document.querySelectorAll('.responsable-toast');
        oldToasts.forEach(toast => toast.remove());

        // Créer le toast
        const toast = document.createElement('div');
        toast.className = `responsable-toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
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

    // Styles pour les toasts
    const toastStyles = document.createElement('style');
    toastStyles.textContent = `
        .responsable-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            padding: 15px 20px;
            min-width: 300px;
            max-width: 400px;
            z-index: 9999;
            transform: translateY(100px);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .responsable-toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-success {
            border-left: 4px solid #27ae60;
        }

        .toast-error {
            border-left: 4px solid #e74c3c;
        }

        .toast-info {
            border-left: 4px solid #3498db;
        }

        .toast-warning {
            border-left: 4px solid #f39c12;
        }

        .toast-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .toast-message {
            flex: 1;
            font-weight: 500;
            color: #2c3e50;
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
            .responsable-toast {
                left: 20px;
                right: 20px;
                min-width: auto;
                max-width: none;
            }
        }
    `;
    document.head.appendChild(toastStyles);

    // CSS pour les animations
    const animationStyles = document.createElement('style');
    animationStyles.textContent = `
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

        .reservation-item, .resource-card, .message-item {
            animation: slideIn 0.3s ease forwards;
            opacity: 0;
        }

        .reservation-item:nth-child(1) { animation-delay: 0.1s; }
        .reservation-item:nth-child(2) { animation-delay: 0.2s; }
        .reservation-item:nth-child(3) { animation-delay: 0.3s; }
        .reservation-item:nth-child(4) { animation-delay: 0.4s; }
        .reservation-item:nth-child(5) { animation-delay: 0.5s; }

        .resource-card:nth-child(1) { animation-delay: 0.1s; }
        .resource-card:nth-child(2) { animation-delay: 0.2s; }
        .resource-card:nth-child(3) { animation-delay: 0.3s; }
        .resource-card:nth-child(4) { animation-delay: 0.4s; }

        .message-item:nth-child(1) { animation-delay: 0.1s; }
        .message-item:nth-child(2) { animation-delay: 0.2s; }
        .message-item:nth-child(3) { animation-delay: 0.3s; }
    `;
    document.head.appendChild(animationStyles);

    // Empêcher la fermeture du modal en cliquant dans le contenu
    document.querySelector('.modal-content').addEventListener('click', function(event) {
        event.stopPropagation();
    });

    // Fermer le modal en cliquant à l'extérieur
    document.querySelector('.modal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeRejectModal();
        }
    });
</script>
@endsection