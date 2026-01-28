@extends('layouts.app')

@section('content')

<div class="container">
    <!-- En-tête de page -->
     <div class="header-sec">
        <div class="header-sec-title">
            <h1><i class="fas fa-bell"></i> Notifications</h1>
            <p>Gérez vos notifications et restez informé</p>
        </div>
        <div class="page-actions">
            <form method="POST" action="{{ route('notifications.clearRead') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger trans-up">
                    <i class="fas fa-trash"></i> Supprimer les notifications lues
                </button>
            </form>
            
            <form method="POST" action="{{ route('notifications.read-all') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success trans-up">
                    <i class="fas fa-check-double"></i> Tout marquer comme lu
                </button>
            </form>
        </div>
    </div>

    <!-- Filtres -->
    <div class="notifications-controls mb-4">
        <div class="notifications-filter">
            <button class="filter-btn active" data-filter="all">Toutes</button>
            <button class="filter-btn" data-filter="unread">Non lues</button>
            <button class="filter-btn" data-filter="read">Lues</button>
        </div>
        
        <div class="notifications-count-badge">
            <span class="badge badge-primary">
                {{ $notifications->total() }} notification(s)
            </span>
        </div>
    </div>

    <!-- Liste des notifications -->
    <div class="notifications-list-container">
        <div class="notifications-list-header">
            <h3>Vos notifications</h3>
            <span class="notifications-count">
                {{ $notifications->where('est_lu', false)->count() }} non lues
            </span>
        </div>
        
        <div class="notifications-list-content">
            @if($notifications->count())
                @foreach($notifications as $notification)
                    <div class="notification-item-enhanced {{ $notification->est_lu ? 'read' : 'unread' }} 
                         {{ $notification->type ?? 'info' }}">
                        <div class="notification-icon">
                            @switch($notification->type ?? 'info')
                                @case('warning')
                                    <i class="fas fa-exclamation-triangle"></i>
                                    @break
                                @case('success')
                                    <i class="fas fa-check-circle"></i>
                                    @break
                                @case('danger')
                                    <i class="fas fa-times-circle"></i>
                                    @break
                                @default
                                    <i class="fas fa-info-circle"></i>
                            @endswitch
                        </div>
                        
                        <div class="notification-content">
                            <div class="notification-header">
                                <h4 class="notification-title">{{ $notification->titre }}</h4>
                                <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            
                            <p class="notification-message">{{ $notification->message }}</p>
                            
                            <div class="notification-actions">
                                @if(!$notification->est_lu)
                                    <form method="POST" action="{{ route('notifications.read', $notification) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="notification-mark-btn">
                                            <i class="fas fa-check"></i> Marquer comme lu
                                        </button>
                                    </form>
                                @endif
                                
                                <form method="POST" action="{{ route('notifications.destroy', $notification) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="notification-delete-btn" onclick="return confirm('Supprimer cette notification ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="notification-status">
                            @if(!$notification->est_lu)
                                <span class="notification-badge unread-badge">Nouveau</span>
                            @else
                                <span class="notification-badge read-badge">Lu</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="notifications-empty-state">
                    <div class="notifications-empty-icon">
                        <i class="far fa-bell-slash"></i>
                    </div>
                    <h4>Aucune notification</h4>
                    <p>Vous n'avez aucune notification pour le moment. Nous vous informerons ici des nouvelles activités.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="notifications-pagination-wrapper">
            {{ $notifications->links('vendor.pagination.custom') }}
        </div>
    @endif
</div>

<!-- Script pour les filtres -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const notificationItems = document.querySelectorAll('.notification-item-enhanced');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
            // Filtrer les notifications
            notificationItems.forEach(item => {
                switch(filter) {
                    case 'all':
                        item.style.display = 'flex';
                        break;
                    case 'unread':
                        item.style.display = item.classList.contains('unread') ? 'flex' : 'none';
                        break;
                    case 'read':
                        item.style.display = item.classList.contains('read') ? 'flex' : 'none';
                        break;
                }
            });
        });
    });
});
</script>

@endsection