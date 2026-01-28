@extends('layouts.app')

@section('title', 'Messages signalés')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="header-sec">
        <div class="header-sec-title">
            <h1><i class="fas fa-flag"></i> Messages signalés</h1>
            <p>Gérez les messages signalés par les utilisateurs pour vos ressources.</p>
        </div>
        <div class="header-sec-date">
            <i class="fas fa-calendar-alt"></i> {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="stats-grid mb-4">
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $messages->where('est_lu', false)->count() }}</h3>
                <p>Non lus</p>
            </div>
        </div>
        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $messages->where('est_lu', true)->count() }}</h3>
                <p>Lus</p>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-cube"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $messages->unique('resource_id')->count() }}</h3>
                <p>Ressources concernées</p>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="section-card mb-4">
        <div class="section-header">
            <h3><i class="fas fa-filter"></i> Filtres de recherche</h3>
            <span class="badge badge-primary">{{ $messages->total() }} message(s)</span>
        </div>
        <div class="section-body">
            <form action="{{ route('reported-messages.index') }}" method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label"><i class="fas fa-server"></i> Ressource</label>
                        <select name="resource_id" class="filter-select">
                            <option value="">Toutes les ressources</option>
                            @foreach(\App\Models\Resource::where('responsable_id', auth()->id())->get() as $resource)
                                <option value="{{ $resource->id }}" {{ request('resource_id') == $resource->id ? 'selected' : '' }}>
                                    {{ $resource->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label"><i class="fas fa-eye"></i> Statut</label>
                        <select name="est_lu" class="filter-select">
                            <option value="">Tous les statuts</option>
                            <option value="0" {{ request('est_lu') == '0' ? 'selected' : '' }}><i class="fas fa-circle text-danger"></i> Non lus</option>
                            <option value="1" {{ request('est_lu') == '1' ? 'selected' : '' }}><i class="fas fa-circle text-success"></i> Lus</option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label"><i class="fas fa-calendar-day"></i> Date de début</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-input">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label"><i class="fas fa-calendar-times"></i> Date de fin</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="filter-input">
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Appliquer les filtres
                    </button>
                    <a href="{{ route('reported-messages.index') }}" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages List -->
    @if($messages->count() > 0)
        <div class="reported-messages-container">
            @foreach($messages as $message)
                <div class="reported-message-card {{ $message->est_lu ? 'read' : 'unread' }}">
                    <div class="message-status-indicator">
                        <div class="status-dot {{ $message->est_lu ? 'read-dot' : 'unread-dot' }}"></div>
                        <span class="status-text">{{ $message->est_lu ? 'Lu' : 'Nouveau' }}</span>
                    </div>
                    
                    <div class="message-main-content">
                        <div class="message-header">
                            <div class="sender-info">
                                <div class="sender-avatar">
                                    {{ strtoupper(substr($message->user->prenom, 0, 1)) }}{{ strtoupper(substr($message->user->nom, 0, 1)) }}
                                </div>
                                <div class="sender-details">
                                    <h4 class="sender-name">
                                        {{ $message->user->prenom }} {{ $message->user->nom }}
                                        <span class="sender-email">({{ $message->user->email }})</span>
                                    </h4>
                                    <div class="message-meta">
                                        <span class="message-time">
                                            <i class="far fa-clock"></i> {{ $message->created_at->diffForHumans() }}
                                        </span>
                                        <span class="message-date">
                                            <i class="far fa-calendar"></i> {{ $message->created_at->format('d/m/Y à H:i') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="resource-badge">
                                @if($message->resource)
                                    <a href="{{ route('resources.show', $message->resource->id) }}" class="resource-link">
                                        <i class="fas fa-cube"></i>
                                        <span>{{ $message->resource->nom }}</span>
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @else
                                    <span class="resource-missing">
                                        <i class="fas fa-cube"></i>
                                        Ressource supprimée
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="message-content">
                            <div class="message-label">
                                <i class="fas fa-comment-alt"></i> Message signalé
                            </div>
                            <div class="message-text">
                                {{ $message->message }}
                            </div>
                        </div>
                        
                        <div class="message-actions">
                            @if(!$message->est_lu)
                                <form action="{{ route('reported-messages.markAsRead', $message->id) }}" method="POST" class="action-form">
                                    @csrf
                                    <button type="submit" class="btn btn-success action-btn">
                                        <i class="fas fa-check"></i>
                                        <span>Marquer comme lu</span>
                                    </button>
                                </form>
                            @else
                                <span class="status-confirmed">
                                    <i class="fas fa-check-circle"></i> Message lu
                                </span>
                            @endif
                            
                            <form action="{{ route('reported-messages.destroy', $message->id) }}" method="POST" class="action-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger action-btn" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">
                                    <i class="fas fa-trash"></i>
                                    <span>Supprimer</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($messages->hasPages())
            <div class="custom-pagination-wrapper mt-5">
                <div class="custom-pagination">
                    @if($messages->onFirstPage())
                        <span class="pagination-arrow disabled">
                            <i class="fas fa-chevron-left"></i> Précédent
                        </span>
                    @else
                        <a href="{{ $messages->previousPageUrl() }}" class="pagination-arrow">
                            <i class="fas fa-chevron-left"></i> Précédent
                        </a>
                    @endif

                    <div class="pagination-numbers">
                        @php
                            $current = $messages->currentPage();
                            $last = $messages->lastPage();
                            $window = 2;

                            if ($last <= 7) {
                                $pages = range(1, $last);
                            } else {
                                $pages = [];
                                $pages[] = 1;
                                $start = max(2, $current - $window);
                                $end = min($last - 1, $current + $window);
                                if ($start > 2) $pages[] = '...';
                                for ($i = $start; $i <= $end; $i++) $pages[] = $i;
                                if ($end < $last - 1) $pages[] = '...';
                                $pages[] = $last;
                            }
                        @endphp

                        @foreach($pages as $page)
                            @if($page == '...')
                                <span class="pagination-dots">...</span>
                            @elseif($page == $current)
                                <span class="pagination-number active">{{ $page }}</span>
                            @else
                                <a href="{{ $messages->url($page) }}" class="pagination-number">{{ $page }}</a>
                            @endif
                        @endforeach
                    </div>

                    @if($messages->hasMorePages())
                        <a href="{{ $messages->nextPageUrl() }}" class="pagination-arrow">
                            Suivant <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="pagination-arrow disabled">
                            Suivant <i class="fas fa-chevron-right"></i>
                        </span>
                    @endif
                </div>

                <div class="pagination-stats">
                    <i class="fas fa-list"></i>
                    Affichage de {{ $messages->firstItem() }} à {{ $messages->lastItem() }} sur {{ $messages->total() }} messages
                </div>
            </div>
        @endif
    @else
        <div class="empty-state-container">
            <div class="empty-state-icon">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <div class="empty-state-content">
                <h3>Aucun message signalé</h3>
                <p>Aucun message n'a été signalé pour vos ressources.</p>
                <div class="empty-state-actions">
                    <a href="{{ route('resources.index') }}" class="btn btn-primary">
                        <i class="fas fa-cube"></i> Voir mes ressources
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline">
                        <i class="fas fa-home"></i> Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    /* ===========================
       SECTION DES FILTRES
       =========================== */
    .filter-form {
        padding: 20px;
    }
    
    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .filter-label {
        font-weight: 600;
        color: var(--prim-color);
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }
    
    .filter-select, .filter-input {
        border: 2px solid var(--border-color);
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .filter-select:focus, .filter-input:focus {
        border-color: var(--sec-color);
        background: white;
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    
    .filter-actions {
        display: flex;
        gap: 15px;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
    }
    
    /* ===========================
       STATISTIQUES RAPIDES
       =========================== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }
    
    /* ===========================
       CARTES DES MESSAGES
       =========================== */
    .reported-messages-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin: 25px 0;
    }
    
    .reported-message-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        display: flex;
        position: relative;
    }
    
    .reported-message-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
        border-color: var(--sec-color);
    }
    
    .reported-message-card.unread {
        border-left: 4px solid var(--sec-color);
    }
    
    .reported-message-card.read {
        border-left: 4px solid var(--succ-color);
        opacity: 0.9;
    }
    
    .reported-message-card.read:hover {
        opacity: 1;
    }
    
    /* Indicateur de statut */
    .message-status-indicator {
        width: 80px;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 15px;
        border-right: 1px solid #e9ecef;
    }
    
    .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-bottom: 8px;
    }
    
    .unread-dot {
        background: var(--sec-color);
        box-shadow: 0 0 10px rgba(52, 152, 219, 0.5);
        animation: pulse 2s infinite;
    }
    
    .read-dot {
        background: var(--succ-color);
    }
    
    .status-text {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--dark-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        writing-mode: vertical-lr;
        transform: rotate(180deg);
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(52, 152, 219, 0); }
        100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
    }
    
    /* Contenu principal */
    .message-main-content {
        flex: 1;
        padding: 20px;
    }
    
    /* En-tête */
    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .sender-info {
        display: flex;
        align-items: center;
        gap: 15px;
        flex: 1;
    }
    
    .sender-avatar {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #9b59b6, #8e44ad);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    
    .sender-details {
        flex: 1;
    }
    
    .sender-name {
        font-size: 1.1rem;
        color: var(--dark-color);
        margin: 0 0 5px 0;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .sender-email {
        font-size: 0.85rem;
        color: var(--gray-color);
        font-weight: normal;
    }
    
    .message-meta {
        display: flex;
        gap: 15px;
        font-size: 0.85rem;
        color: var(--gray-color);
    }
    
    .message-meta i {
        margin-right: 5px;
    }
    
    /* Badge ressource */
    .resource-badge {
        flex-shrink: 0;
    }
    
    .resource-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 15px;
        background: var(--light-color);
        border-radius: 8px;
        color: var(--sec-color);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
    }
    
    .resource-link:hover {
        background: var(--sec-color);
        color: white;
        border-color: var(--sec-color);
        transform: translateY(-2px);
    }
    
    .resource-missing {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 15px;
        background: #f8f9fa;
        border-radius: 8px;
        color: var(--gray-color);
        font-size: 0.9rem;
        border: 1px dashed #dee2e6;
    }
    
    /* Contenu du message */
    .message-content {
        margin-bottom: 20px;
    }
    
    .message-label {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--prim-color);
        font-weight: 600;
        margin-bottom: 10px;
        font-size: 0.95rem;
    }
    
    .message-text {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 15px;
        line-height: 1.6;
        color: var(--dark-color);
        font-size: 0.95rem;
    }
    
    /* Actions */
    .message-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .action-form {
        margin: 0;
    }
    
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .status-confirmed {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(39, 174, 96, 0.1);
        color: var(--succ-color);
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    /* ===========================
       ÉTAT VIDE
       =========================== */
    .empty-state-container {
        text-align: center;
        padding: 60px 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
        border-radius: 15px;
        border: 2px dashed #dee2e6;
        margin: 30px 0;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: var(--sec-color);
        margin-bottom: 20px;
        opacity: 0.7;
    }
    
    .empty-state-content h3 {
        color: var(--prim-color);
        margin-bottom: 10px;
        font-size: 1.5rem;
    }
    
    .empty-state-content p {
        color: var(--gray-color);
        margin-bottom: 25px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .empty-state-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

</style>
@endsection