@extends('layouts.app')

@section('title', 'Mon Profil')

@section('content')
    <div class="container">
        <!-- En-tête -->
        <div class="header-sec">
            <div class="header-sec-title">
                <h1><i class="fas fa-user-circle me-2"></i>Mon Profil</h1>
                <p>Gérez vos informations personnelles et vos paramètres</p>
            </div>
            <div class="header-sec-date">
                <i class="fas fa-calendar-alt"></i> {{ now()->format('d/m/Y') }}
            </div>
        </div>

        <div class="grid mb-4" style="grid-template-columns: 30% 70%; gap: 30px;">
            <!-- Colonne gauche: Informations personnelles -->
            <div>
                <!-- Carte profil -->
                <div class="section-card mb-4">
                    <div class="section-icon" style="width: 100px; height: 100px; margin-bottom: 20px;">
                        <i class="fas fa-user fa-2x"></i>
                    </div>
                    
                    <h3 class="mb-3">{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</h3>
                    
                    <div class="section-list" style="text-align: left;">
                        <li>
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email</strong>
                                <p class="mb-0 text-muted">{{ auth()->user()->email }}</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-user-tag"></i>
                            <div>
                                <strong>Rôle</strong>
                                <p class="mb-0">
                                    <span class="badge badge-primary">
                                        {{ auth()->user()->role_id == 3 ? 'admin' : '' }}
                                        {{ auth()->user()->role_id == 2 ? 'responsable' : '' }}
                                        {{ auth()->user()->role_id == 1 ? 'utilisateur' : '' }}
                                    </span>
                                </p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-calendar-plus"></i>
                            <div>
                                <strong>Membre depuis</strong>
                                <p class="mb-0 text-muted">{{ auth()->user()->created_at->format('d/m/Y') }}</p>
                            </div>
                        </li>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="section-card">
                    <h3 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Statistiques</h3>
                    
                    <div class="section-list" style="text-align: left;">
                        <li>
                            <i class="fas fa-calendar-check" style="color: var(--succ-color);"></i>
                            <div>
                                <strong>Réservations approuvées</strong>
                                <p class="mb-0">{{ auth()->user()->reservations()->where('statut', 'approuvée')->count() }}</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-clock" style="color: var(--war-color);"></i>
                            <div>
                                <strong>En attente</strong>
                                <p class="mb-0">{{ auth()->user()->reservations()->where('statut', 'en attente')->count() }}</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-bell" style="color: var(--info-color);"></i>
                            <div>
                                <strong>Notifications non lues</strong>
                                <p class="mb-0">{{ auth()->user()->notifications()->where('est_lu', false)->count() }}</p>
                            </div>
                        </li>
                    </div>
                </div>
            </div>

            <!-- Colonne droite: Formulaire de modification -->
            <div>
                <!-- Formulaire d'édition -->
                <div class="card mb-2">
                    <div class="card-header">
                        <h3 class="page-title">
                            <i class="fas fa-edit"></i>Modifier mes informations
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger mb-4">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <div class="mb-3">
                                        <label for="prenom" class="form-label">Prénom</label>
                                        <input type="text" class="form-control" id="prenom" name="prenom" 
                                               value="{{ old('prenom', auth()->user()->prenom) }}" required>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="mb-3">
                                        <label for="nom" class="form-label">Nom</label>
                                        <input type="text" class="form-control" id="nom" name="nom" 
                                               value="{{ old('nom', auth()->user()->nom) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-flex align-items-center">
                                    <i class="fas fa-key me-2"></i>Changer le mot de passe
                                    <small class="text-muted ms-2">(Laisser vide pour ne pas changer)</small>
                                </label>
                                
                                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                                    <div>
                                        <label for="password" class="form-label">Nouveau mot de passe</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                    </div>
                                    
                                    <div>
                                        <label for="password_confirmation" class="form-label">Confirmation</label>
                                        <input type="password" class="form-control" id="password_confirmation" 
                                               name="password_confirmation">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="reset" class="btn btn-outline">
                                    <i class="fas fa-undo me-2"></i>Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Section paramètres avancés -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="page-title">
                            <i class="fas fa-cogs"></i>Paramètres
                        </h3>
                    </div>
                    <div class="card-body mb-4">
                        <div class="section-list" style="text-align: left;">    
                            <!-- Thème -->
                            <li>
                                <i class="fas fa-palette"></i>
                                <div style="flex: 1;">
                                    <strong>Thème</strong>
                                    <p class="mb-2 text-muted">Choisir le thème de l'interface</p>
                                    
                                    <div class="d-flex gap-3">
                                        <button class="btn btn-sm btn-outline active">
                                            <i class="fas fa-sun me-1"></i> Clair
                                        </button>
                                        <button class="btn btn-sm btn-outline">
                                            <i class="fas fa-moon me-1"></i> Sombre
                                        </button>
                                    </div>
                                </div>
                            </li>
                            
                            <!-- Actions de compte -->
                            <li>
                                <i class="fas fa-user-cog"></i>
                                <div style="flex: 1;">
                                    <strong>Actions du compte</strong>
                                    <p class="mb-2 text-muted">Gérer votre compte</p>
                                    
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('profile.delete') }}" method="POST" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash me-1"></i> Supprimer le compte
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline">
                                                <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Réservations récentes -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="page-title mb-0">
                    <i class="fas fa-history me-2"></i>Historique de reservation
                </h3>
            </div>
            <div class="card-body">
                @if(auth()->user()->reservations()->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr >
                                    <th>Ressource</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(auth()->user()->reservations()->get() as $reservation)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-cube me-2 text-primary"> </i> 
                                                {{ $reservation->resource->nom }}
                                            </div>
                                        </td>
                                        <td>{{ $reservation->date_debut->format('d/m/Y H:i') }}</td>
                                        <td>{{ $reservation->date_fin->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="w-70 ta-center badge 
                                                {{ $reservation->statut == 'en attente' ? 'badge-warning' : '' }}
                                                {{ $reservation->statut == 'approuvée' ? 'badge-success' : '' }}
                                                {{ $reservation->statut == 'refusée' ? 'badge-danger' : '' }}
                                                {{ $reservation->statut == 'active' ? 'badge-info' : '' }}
                                                {{ $reservation->statut == 'terminée' ? 'badge-primary' : '' }}">
                                                {{ $reservation->statut }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('reservations.show', $reservation->id) }}" 
                                               class="btn btn-sm btn-outline">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Aucune réservation</h4>
                        <p class="text-muted">Vous n'avez pas encore fait de réservation.</p>
                        <a href="{{ route('reservations.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Faire une réservation
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Historique pour admin-->
         @if(auth()->user()->role == 'admin')
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="page-title mb-0">
                    <i class="fas fa-history me-2"></i>Mon historique d'activités
                    <span class="badge badge-primary">{{ $historyLogs->where('user_id', auth()->id())->count() }}</span>
                </h3>
                
                <!-- Filtres -->
                <div class="d-flex gap-2">
                    <select id="filterAction" class="form-control form-control-sm w-auto">
                        <option value="">Toutes les actions</option>
                        <option value="création">Créations</option>
                        <option value="modification">Modifications</option>
                        <option value="annulation">Annulations</option>
                        <option value="commentaire">Commentaires</option>
                        <option value="approbation">Approbations</option>
                        <option value="refus">Refus</option>
                        <option value="réclamation">Réclamations</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                @php
                    $userHistory = $historyLogs->where('user_id', auth()->id())->sortByDesc('created_at');
                @endphp
                
                @if($userHistory->count() > 0)
                    <div class="history-timeline">
                        @foreach($userHistory as $log)
                            <div class="history-item" data-action="{{ $log->action }}">
                                <div class="history-item-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <!-- Icône et couleur selon l'action -->
                                            @php
                                                $icon = 'fa-history';
                                                $bgColor = 'bg-info';
                                                $badgeColor = 'badge-info';
                                                
                                                switch($log->action) {
                                                    case 'création':
                                                        $icon = 'fa-plus';
                                                        $bgColor = 'bg-success';
                                                        $badgeColor = 'badge-success';
                                                        break;
                                                    case 'modification':
                                                        $icon = 'fa-edit';
                                                        $bgColor = 'bg-warning';
                                                        $badgeColor = 'badge-warning';
                                                        break;
                                                    case 'annulation':
                                                        $icon = 'fa-times';
                                                        $bgColor = 'bg-danger';
                                                        $badgeColor = 'badge-danger';
                                                        break;
                                                    case 'commentaire':
                                                        $icon = 'fa-comment';
                                                        $bgColor = 'bg-info';
                                                        $badgeColor = 'badge-info';
                                                        break;
                                                    case 'approbation':
                                                        $icon = 'fa-check';
                                                        $bgColor = 'bg-success';
                                                        $badgeColor = 'badge-success';
                                                        break;
                                                    case 'refus':
                                                        $icon = 'fa-ban';
                                                        $bgColor = 'bg-danger';
                                                        $badgeColor = 'badge-danger';
                                                        break;
                                                    case 'réclamation':
                                                        $icon = 'fa-exclamation-triangle';
                                                        $bgColor = 'bg-warning';
                                                        $badgeColor = 'badge-warning';
                                                        break;
                                                }
                                            @endphp
                                            
                                            <div class="history-icon {{ $bgColor }}">
                                                <i class="fas {{ $icon }}"></i>
                                            </div>
                                            
                                            <div>
                                                <h5 class="mb-1">
                                                    @switch($log->action)
                                                        @case('création')
                                                            Création
                                                            @break
                                                        @case('modification')
                                                            Modification
                                                            @break
                                                        @case('annulation')
                                                            Annulation
                                                            @break
                                                        @case('commentaire')
                                                            Commentaire
                                                            @break
                                                        @case('approbation')
                                                            Approbation
                                                            @break
                                                        @case('refus')
                                                            Refus
                                                            @break
                                                        @case('réclamation')
                                                            Réclamation
                                                            @break
                                                        @default
                                                            {{ ucfirst($log->action) }}
                                                    @endswitch
                                                </h5>
                                                <p class="mb-0 text-muted">
                                                    {!! str_replace('**', '<strong>', str_replace('**', '</strong>', $log->description)) !!}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right">
                                            <small class="text-muted d-block">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $log->created_at->format('d/m/Y H:i') }}
                                            </small>
                                            <span class="badge {{ $badgeColor }}">
                                                {{ $log->action }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Détails des changements -->
                                @if($log->anciennes_valeurs || $log->nouvelles_valeurs)
                                    <div class="history-item-details mt-3">
                                        <button class="btn btn-sm btn-outline mb-2 toggle-details-btn" 
                                                data-target="details-{{ $loop->index }}">
                                            <i class="fas fa-chevron-down me-1"></i>
                                            Afficher les détails
                                        </button>
                                        
                                        <div class="details-content" id="details-{{ $loop->index }}" style="display: none;">
                                            <div class="row">
                                                @if($log->anciennes_valeurs)
                                                    <div class="col-md-6">
                                                        <div class="card border">
                                                            <div class="card-header bg-light">
                                                                <strong><i class="fas fa-arrow-left me-1"></i>Anciennes valeurs</strong>
                                                            </div>
                                                            <div class="card-body">
                                                                @php
                                                                    $oldValues = $log->anciennes_valeurs;
                                                                @endphp
                                                                @if(is_array($oldValues) && count($oldValues) > 0)
                                                                    <div class="values-container">
                                                                        @foreach($oldValues as $key => $value)
                                                                            <div class="value-item mb-2">
                                                                                <div class="value-key">
                                                                                    <strong>{{ $key }}:</strong>
                                                                                </div>
                                                                                <div class="value-content">
                                                                                    @if(is_array($value))
                                                                                        <span class="badge badge-info">{{ json_encode($value) }}</span>
                                                                                    @else
                                                                                        {{ $value }}
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <p class="mb-0 text-muted">Aucune ancienne valeur</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                @if($log->nouvelles_valeurs)
                                                    <div class="col-md-6">
                                                        <div class="card border">
                                                            <div class="card-header bg-light">
                                                                <strong><i class="fas fa-arrow-right me-1"></i>Nouvelles valeurs</strong>
                                                            </div>
                                                            <div class="card-body">
                                                                @php
                                                                    $newValues = $log->nouvelles_valeurs;
                                                                @endphp
                                                                @if(is_array($newValues) && count($newValues) > 0)
                                                                    <div class="values-container">
                                                                        @foreach($newValues as $key => $value)
                                                                            <div class="value-item mb-2">
                                                                                <div class="value-key">
                                                                                    <strong>{{ $key }}:</strong>
                                                                                </div>
                                                                                <div class="value-content">
                                                                                    @if(is_array($value))
                                                                                        <span class="badge badge-info">{{ json_encode($value) }}</span>
                                                                                    @else
                                                                                        {{ $value }}
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <p class="mb-0 text-muted">Aucune nouvelle valeur</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            @if(!$loop->last)
                                <hr class="my-3">
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-history fa-3x text-muted"></i>
                        </div>
                        <h4 class="text-muted">Aucune activité enregistrée</h4>
                        <p class="text-muted">Votre historique d'activités apparaîtra ici.</p>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <style>
        .section-list li {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .section-list li:last-child {
            border-bottom: none;
        }

        .section-list li i {
            width: 24px;
            color: var(--sec-color);
            font-size: 1.1rem;
            margin-top: 2px;
        }

        .section-list li div {
            flex: 1;
        }

        .section-list li strong {
            display: block;
            color: var(--prim-color);
            margin-bottom: 4px;
        }

        .section-list li p {
            margin: 0;
        }

        .text-muted {
            color: var(--gray-color) !important;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .form-check-label {
            cursor: pointer;
            user-select: none;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--prim-color);
        }

        .table td {
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Style pour les boutons actifs */
        .btn-outline.active {
            background-color: var(--sec-color);
            color: white;
            border-color: var(--sec-color);
        }

        /* Styles pour l'historique */
        .history-item {
            padding: 20px;
            border-radius: var(--radius-sm);
            background: white;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .history-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
            border-color: var(--sec-color);
        }

        .history-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            flex-shrink: 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .bg-success {
            background: linear-gradient(135deg, var(--succ-color), #27ae60) !important;
        }

        .bg-warning {
            background: linear-gradient(135deg, var(--war-color), #f39c12) !important;
        }

        .bg-danger {
            background: linear-gradient(135deg, var(--dng-color), #e74c3c) !important;
        }

        .bg-info {
            background: linear-gradient(135deg, var(--info-color), #1abc9c) !important;
        }

        .history-item-header {
            margin-bottom: 10px;
        }

        .history-item-details {
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            margin-top: 15px;
        }

        .details-content {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { 
                opacity: 0;
                transform: translateY(-10px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Styles pour les boutons */
        .toggle-details-btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid var(--border-color);
        }

        .toggle-details-btn:hover {
            background-color: var(--sec-color);
            color: white;
            border-color: var(--sec-color);
            transform: translateY(-2px);
        }

        .toggle-details-btn.active {
            background-color: var(--sec-color);
            color: white;
            border-color: var(--sec-color);
        }

        .toggle-details-btn.active i {
            transform: rotate(180deg);
        }

        .toggle-details-btn i {
            transition: transform 0.3s ease;
        }

        /* Styles pour les valeurs */
        .values-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .value-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 3px solid var(--sec-color);
        }

        .value-key {
            min-width: 120px;
            font-weight: 600;
            color: var(--prim-color);
        }

        .value-content {
            flex: 1;
            word-break: break-word;
        }

        /* Séparateur */
        hr {
            border: none;
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
            margin: 20px 0;
        }

        /* Filtres */
        .form-control-sm {
            padding: 8px 12px;
            font-size: 0.9rem;
            height: auto;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control-sm:focus {
            border-color: var(--sec-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .w-auto {
            width: auto !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr !important;
            }
            
            .grid[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
            
            .history-item-header .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 15px;
            }
            
            .text-right {
                text-align: left !important;
                width: 100%;
            }
            
            .card-header .d-flex {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start !important;
            }
            
            .d-flex.gap-2 {
                width: 100%;
            }
            
            .form-control-sm.w-auto {
                width: 100% !important;
            }
            
            .history-item-details .row {
                flex-direction: column;
            }
            
            .history-item-details .col-md-6 {
                width: 100%;
                margin-bottom: 15px;
            }
            
            .history-icon {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
            }
            
            .value-item {
                flex-direction: column;
                gap: 5px;
            }
            
            .value-key {
                min-width: auto;
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion du thème
            const themeButtons = document.querySelectorAll('.btn-outline[class*="btn-sm"]');
            themeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    themeButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    const theme = this.textContent.includes('Clair') ? 'light' : 'dark';
                    localStorage.setItem('theme', theme);
                    
                    // Appliquer le thème
                    if (theme === 'dark') {
                        document.body.classList.add('dark-theme');
                    } else {
                        document.body.classList.remove('dark-theme');
                    }
                });
            });
            
            // Charger le thème sauvegardé
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-theme');
                themeButtons.forEach(btn => {
                    if (btn.textContent.includes('Sombre')) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }
            
            // Toggle des notifications
            const notificationCheckboxes = document.querySelectorAll('.form-check-input');
            notificationCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const type = this.id.includes('email') ? 'email' : 'site';
                    const enabled = this.checked;
                    
                    // Sauvegarder la préférence
                    fetch('/api/notification-settings', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ type, enabled })
                    });
                });
            });
            
            // Fonction pour afficher/masquer les détails de l'historique
            const toggleButtons = document.querySelectorAll('.toggle-details-btn');
            
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const detailsContent = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    if (detailsContent.style.display === 'none') {
                        detailsContent.style.display = 'block';
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                        this.innerHTML = '<i class="fas fa-chevron-up me-1"></i> Masquer les détails';
                        this.classList.add('active');
                    } else {
                        detailsContent.style.display = 'none';
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                        this.innerHTML = '<i class="fas fa-chevron-down me-1"></i> Afficher les détails';
                        this.classList.remove('active');
                    }
                });
            });
            
            // Filtrage des actions de l'historique
            const filterAction = document.getElementById('filterAction');
            if (filterAction) {
                const historyItems = document.querySelectorAll('.history-item');
                
                function filterHistory() {
                    const actionValue = filterAction.value;
                    
                    historyItems.forEach(item => {
                        const itemAction = item.getAttribute('data-action');
                        const shouldShow = actionValue === '' || itemAction === actionValue;
                        
                        if (shouldShow) {
                            item.style.display = 'block';
                            if (item.nextElementSibling && item.nextElementSibling.tagName === 'HR') {
                                item.nextElementSibling.style.display = 'block';
                            }
                        } else {
                            item.style.display = 'none';
                            if (item.nextElementSibling && item.nextElementSibling.tagName === 'HR') {
                                item.nextElementSibling.style.display = 'none';
                            }
                        }
                    });
                }
                
                filterAction.addEventListener('change', filterHistory);
                
                // Charger le filtre sauvegardé
                const savedAction = localStorage.getItem('historyFilterAction');
                if (savedAction) {
                    filterAction.value = savedAction;
                    filterHistory();
                }
                
                // Sauvegarder le filtre
                filterAction.addEventListener('change', function() {
                    localStorage.setItem('historyFilterAction', this.value);
                });
            }
            
            // Confirmation pour la suppression de compte
            const deleteForm = document.querySelector('form[action*="profile.delete"]');
            if (deleteForm) {
                deleteForm.addEventListener('submit', function(e) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Toutes vos données seront perdues.')) {
                        e.preventDefault();
                    }
                });
            }
            
            // Animation pour les éléments de l'historique
            const historyItems = document.querySelectorAll('.history-item');
            if (historyItems.length > 0) {
                const historyItemsArray = Array.from(historyItems);
                historyItemsArray.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(20px)';
                        item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, 100);
                    }, index * 50);
                });
            }
        });
    </script>
@endsection