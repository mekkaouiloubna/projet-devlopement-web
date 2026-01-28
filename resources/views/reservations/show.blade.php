@extends('layouts.app')

@section('title', 'Détails de la réservation')

@section('content')
<div class="container">
    <!-- En-tête -->
    <div class="header-sec">
        <div class="header-sec-title">
            <h1><i class="fas fa-calendar-check me-2"></i>Détails de la réservation</h1>
            <p>Informations complètes sur la réservation #{{ $reservation->id }}</p>
        </div>
        <div class="header-sec-date">
            <i class="fas fa-calendar-alt"></i> {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <!-- Carte principale -->
    <div class="reservation-detail-card">
        <!-- Statut en bannière -->
        <div class="reservation-status-banner status-{{ $reservation->statut }}">
            <div class="status-content">
                <i class="fas fa-circle"></i>
                <span class="status-text">Statut: {{ ucfirst($reservation->statut) }}</span>
            </div>
            <div class="reservation-id">
                <i class="fas fa-hashtag"></i> ID: {{ $reservation->id }}
            </div>
        </div>

        <!-- Informations principales -->
        <div class="reservation-main-info">
            <div class="info-grid">
                <!-- Ressource -->
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="info-content">
                        <label>Ressource</label>
                        <h3>{{ $reservation->resource->nom }}</h3>
                        <div class="info-meta">
                            <span class="resource-category">
                                <i class="fas fa-tag"></i>
                                {{ $reservation->resource->category->nom ?? 'Non catégorisé' }}
                            </span>
                            <a href="{{ route('resources.show', $reservation->resource->id) }}" class="view-resource">
                                <i class="fas fa-external-link-alt"></i> Voir la ressource
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Utilisateur -->
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="info-content">
                        <label>Utilisateur</label>
                        <h3>{{ $reservation->user->prenom }} {{ $reservation->user->nom }}</h3>
                        <div class="info-meta">
                            <span class="user-email">
                                <i class="fas fa-envelope"></i>
                                {{ $reservation->user->email }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Période -->
                <div class="info-card timeline-card">
                    <div class="timeline-header">
                        <div class="info-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="info-content">
                            <label>Période de réservation</label>
                        </div>
                    </div>
                    
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date">
                                <div class="date-icon">
                                    <i class="fas fa-play-circle"></i>
                                </div>
                                <div class="date-content">
                                    <div class="date-label">Début</div>
                                    <div class="date-value">{{ \Carbon\Carbon::parse($reservation->date_debut)->format('d/m/Y') }}</div>
                                    <div class="time-value">{{ \Carbon\Carbon::parse($reservation->date_debut)->format('H:i') }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="timeline-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-date">
                                <div class="date-icon">
                                    <i class="fas fa-stop-circle"></i>
                                </div>
                                <div class="date-content">
                                    <div class="date-label">Fin</div>
                                    <div class="date-value">{{ \Carbon\Carbon::parse($reservation->date_fin)->format('d/m/Y') }}</div>
                                    <div class="time-value">{{ \Carbon\Carbon::parse($reservation->date_fin)->format('H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="duration-info">
                        <i class="fas fa-clock"></i>
                        @php
                            $start = \Carbon\Carbon::parse($reservation->date_debut);
                            $end = \Carbon\Carbon::parse($reservation->date_fin);
                            $duration = $start->diff($end);
                        @endphp
                        <span class="duration-text">
                            Durée: {{ $duration->days }} jours, {{ $duration->h }} heures
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Justifications -->
        <div class="reservation-justifications">
            <div class="justification-section">
                <div class="section-header">
                    <h3><i class="fas fa-file-alt"></i> Justification de la réservation</h3>
                </div>
                <div class="justification-content">
                    <p>{{ $reservation->justification }}</p>
                </div>
            </div>
            
            @if($reservation->commentaire_responsable)
                <div class="justification-section rejection-section">
                    <div class="section-header">
                        <h3><i class="fas fa-times-circle"></i> Justification du refus</h3>
                    </div>
                    <div class="justification-content">
                        <p>{{ $reservation->commentaire_responsable }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Conversation -->
        <div class="reservation-conversation">
            <div class="conversation-header">
                <h3>
                    <i class="fas fa-comments"></i> Conversation
                    <span class="message-count">{{ $reservation->conversations->count() }}</span>
                </h3>
                <div class="conversation-info">
                    <i class="fas fa-info-circle"></i>
                    Cette conversation est visible par le demandeur et les responsables
                </div>
            </div>
            
            <div class="conversation-body">
                <!-- Formulaire d'ajout de message -->
                @if(auth()->check() && (auth()->user()->isAdminOrRespoResource($reservation->resource) || auth()->user()->id === $reservation->user_id))
                    <div class="message-form-container">
                        <form action="{{ route('reservations.addComment', $reservation->id) }}" method="POST" class="message-form">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Ajouter un message</label>
                                <div class="input-group">
                                    <textarea name="message" class="message-input" 
                                        placeholder="Écrivez votre message ici..." 
                                        rows="2" required></textarea>
                                    <button type="submit" class="send-button">
                                        <i class="fas fa-paper-plane"></i>
                                        <span>Envoyer</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
                
                <!-- Liste des messages -->
                <div class="messages-container">
                    @if($reservation->conversations->count())
                        @foreach($reservation->conversations->sortByDesc('created_at') as $msg)
                            <div class="message-item {{ $msg->user_id === auth()->id() ? 'sent' : 'received' }}">
                                <div class="message-avatar">
                                    {{ strtoupper(substr($msg->user->prenom, 0, 1)) }}{{ strtoupper(substr($msg->user->nom, 0, 1)) }}
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <strong>{{ $msg->user->prenom }} {{ $msg->user->nom }}</strong>
                                        <span class="message-time">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="message-text">{{ $msg->message }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-conversation">
                            <div class="empty-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h4>Aucun message</h4>
                            <p>Soyez le premier à commenter cette réservation</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="reservation-actions">
            <div class="actions-header">
                <h3><i class="fas fa-cogs"></i> Actions</h3>
            </div>
            
            <div class="actions-buttons">
                @if($reservation->statut == 'en attente' && auth()->check() && auth()->user()->isAdminOrRespoResource($reservation->resource))
                    <form class="btn btn-success btn-sm" action="{{ route('reservations.approve', $reservation->id) }}" method="POST" class="action-form">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-check"></i> Approuver
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-danger btn-sm" onclick="openRejectModal({{ $reservation->id }})">
                        <i class="fas fa-times"></i>Refuser
                    </button>
                @endif

                @if(in_array($reservation->statut, ['en attente', 'approuvée']) && $reservation->user_id == Auth::id())
                    <a href="{{ route('reservations.edit', $reservation->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i>Modifier
                    </a>
                    
                    <form class="btn btn-warning btn-sm" action="{{ route('reservations.cancel', $reservation->id) }}" method="POST" class="action-form">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-warning btn-sm"
                                onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                            <i class="fas fa-ban"></i>Annuler la réservation
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('reservations.index') }}" class="btn btn-outline btn-sm">
                    <i class="fas fa-arrow-left"></i>
                    <span>Retour aux réservations</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour refus -->
@include('components.reject-modal', ['reservation' => $reservation])

<style>
    /* ===========================
       CARTE PRINCIPALE
       =========================== */
    .reservation-detail-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin: 25px 0;
    }

    /* Bannière de statut */
    .reservation-status-banner {
        padding: 15px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
        font-weight: 600;
    }

    .status-en_attente { background: linear-gradient(135deg, #f39c12, #e67e22); }
    .status-approuvée { background: linear-gradient(135deg, #27ae60, #219653); }
    .status-refusée { background: linear-gradient(135deg, #e74c3c, #c0392b); }
    .status-active { background: linear-gradient(135deg, #3498db, #2980b9); }
    .status-terminée { background: linear-gradient(135deg, #9b59b6, #8e44ad); }

    .status-content {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.1rem;
    }

    .reservation-id {
        background: rgba(255, 255, 255, 0.2);
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
    }

    /* Informations principales */
    .reservation-main-info {
        padding: 30px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        display: flex;
        gap: 15px;
        align-items: flex-start;
    }

    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-color: var(--sec-color);
    }

    .timeline-card {
        grid-column: span 2;
        flex-direction: column;
    }

    @media (max-width: 992px) {
        .timeline-card {
            grid-column: span 1;
        }
    }

    .info-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--sec-color), var(--prim-color));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .info-content {
        flex: 1;
    }

    .info-content label {
        font-size: 0.9rem;
        color: var(--gray-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        display: block;
    }

    .info-content h3 {
        margin: 0 0 10px 0;
        color: var(--prim-color);
        font-size: 1.3rem;
    }

    .info-meta {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .resource-category, .user-email {
        font-size: 0.85rem;
        color: var(--gray-color);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .view-resource {
        font-size: 0.85rem;
        color: var(--sec-color);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: color 0.3s ease;
    }

    .view-resource:hover {
        color: var(--prim-color);
        text-decoration: underline;
    }

    /* Timeline */
    .timeline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 20px 0;
        gap: 20px;
    }

    .timeline-item {
        flex: 1;
    }

    .timeline-date {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: white;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }

    .date-icon {
        width: 40px;
        height: 40px;
        background: var(--light-color);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--sec-color);
        font-size: 1.1rem;
    }

    .date-content {
        flex: 1;
    }

    .date-label {
        font-size: 0.8rem;
        color: var(--gray-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .date-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--prim-color);
        margin-bottom: 2px;
    }

    .time-value {
        font-size: 0.9rem;
        color: var(--gray-color);
        background: #f8f9fa;
        padding: 2px 8px;
        border-radius: 12px;
        display: inline-block;
    }

    .timeline-arrow {
        color: var(--gray-color);
        font-size: 1.2rem;
    }

    .duration-info {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        background: white;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-weight: 500;
        color: var(--prim-color);
        margin-top: 10px;
    }

    /* Justifications */
    .reservation-justifications {
        padding: 0 30px 30px;
    }

    .justification-section {
        margin-bottom: 25px;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .section-header h3 {
        margin: 0;
        font-size: 1.1rem;
        color: var(--prim-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .justification-content {
        background: #f8fafc;
        border-radius: 10px;
        padding: 20px;
        border: 1px solid #e2e8f0;
    }

    .justification-content p {
        margin: 0;
        line-height: 1.6;
        color: var(--dark-color);
    }

    .rejection-section .section-header h3 {
        color: #e74c3c;
    }

    /* Conversation */
    .reservation-conversation {
        padding: 0 30px 30px;
    }

    .conversation-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .conversation-header h3 {
        margin: 0;
        font-size: 1.1rem;
        color: var(--prim-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .message-count {
        background: var(--sec-color);
        color: white;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .conversation-info {
        font-size: 0.85rem;
        color: var(--gray-color);
        display: flex;
        align-items: center;
        gap: 5px;
        background: #f8f9fa;
        padding: 5px 10px;
        border-radius: 6px;
    }

    .message-form-container {
        margin-bottom: 25px;
    }

    .message-form .form-group {
        margin-bottom: 0;
    }

    .message-input {
        width: 100%;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 0.95rem;
        resize: vertical;
        min-height: 60px;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .message-input:focus {
        border-color: var(--sec-color);
        background: white;
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .input-group {
        display: flex;
        gap: 10px;
    }

    .send-button {
        background: var(--sec-color);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 0 20px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .send-button:hover {
        background: #2980b9;
        transform: translateY(-2px);
    }

    /* Messages */
    .messages-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
        max-height: 400px;
        overflow-y: auto;
        padding-right: 10px;
    }

    .messages-container::-webkit-scrollbar {
        width: 6px;
    }

    .messages-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .messages-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .message-item {
        display: flex;
        gap: 12px;
        max-width: 80%;
    }

    .message-item.sent {
        margin-left: auto;
        flex-direction: row-reverse;
    }

    .message-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #9b59b6, #8e44ad);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .message-item.sent .message-avatar {
        background: linear-gradient(135deg, var(--sec-color), #2980b9);
    }

    .message-content {
        flex: 1;
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }

    .message-item.sent .message-header {
        flex-direction: row-reverse;
    }

    .message-header strong {
        font-size: 0.9rem;
        color: var(--prim-color);
    }

    .message-time {
        font-size: 0.8rem;
        color: var(--gray-color);
    }

    .message-text {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 15px;
        font-size: 0.9rem;
        line-height: 1.5;
        color: var(--dark-color);
    }

    .message-item.sent .message-text {
        background: #e3f2fd;
        border-color: #bbdefb;
    }

    /* Conversation vide */
    .empty-conversation {
        text-align: center;
        padding: 40px 20px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 2px dashed #dee2e6;
    }

    .empty-icon {
        font-size: 3rem;
        color: var(--sec-color);
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-conversation h4 {
        color: var(--prim-color);
        margin-bottom: 10px;
    }

    .empty-conversation p {
        color: var(--gray-color);
        margin: 0;
    }

    /* Actions */
    .reservation-actions {
        padding: 30px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .actions-header {
        margin-bottom: 20px;
    }

    .actions-header h3 {
        margin: 0 0 5px 0;
        color: var(--prim-color);
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .actions-info {
        font-size: 0.85rem;
        color: var(--gray-color);
    }

    .actions-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .action-form {
        margin: 0;
    }
</style>

<script src="{{ asset('js/reject-modal.js') }}"></script>
@endsection