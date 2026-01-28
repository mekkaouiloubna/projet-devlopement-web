@extends('layouts.app')

@section('content')
    <div class="container">
        <!-- En-tête de page -->
        <div class="header-sec">
            <div class="header-sec-title">
                <h1>
                    <i class="fas fa-users"></i> Gestion des Utilisateurs
                </h1>
                <p>
                    Gérez les utilisateurs, leurs rôles et leurs statuts
                </p>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Utilisateurs Totaux</p>
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['active'] }}</h3>
                    <p>Utilisateurs Actifs</p>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['admin'] }}</h3>
                    <p>Administrateurs</p>
                </div>
            </div>
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['responsable'] }}</h3>
                    <p>Responsables</p>
                </div>
            </div>

            <div class="stat-card danger">
                <div class="stat-icon">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $pendingUsersCount }}</h3>
                    <p>En attente</p>
                </div>
            </div>
        </div>

        @if($pendingUsers && $pendingUsers->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h3><i class="fas fa-user-clock"></i> Demandes d'inscription en attente</h3>
                    <span class="badge badge-danger">{{ $pendingUsers->count() }} demande(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom & Prénom</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Date de demande</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingUsers as $user)
                                    <tr>
                                        <td>#{{ $user->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="user-avatar-sm">
                                                    {{ strtoupper(substr($user->prenom, 0, 1) . substr($user->nom, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $user->nom }} {{ $user->prenom }}</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $user->type ?? 'Non spécifié' }}
                                            </span>
                                        </td>
                                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <form action="{{ route('admin.users.approve-registration', $user->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success"
                                                        title="Accepter cette demande">
                                                        <i class="fas fa-check"></i> Accepter
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.users.reject-registration', $user->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        title="Refuser cette demande"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir refuser cette demande ?')">
                                                        <i class="fas fa-times"></i> Refuser
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-header">
                <h3><i class="fas fa-filter"></i> Filtres</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('users.index') }}" class="d-flex gap-3 flex-wrap">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user-tag"></i> Rôle</label>
                        <select name="role" class="form-control" onchange="this.form.submit()">
                            <option value="">Tous les rôles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->nom }}" {{ request('role') == $role->nom ? 'selected' : '' }}>
                                    {{ ucfirst($role->nom) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user-circle"></i> Statut</label>
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="">Tous les statuts</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-search"></i> Recherche</label>
                        <div class="search-box">
                            <input type="text" name="search" class="form-control" placeholder="Nom, prénom ou email..."
                                value="{{ request('search') }}">
                            <button type="submit" class="search-button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <a href="{{ route('users.index') }}" class="btn btn-outline btn-sm">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des utilisateurs -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3><i class="fas fa-list"></i> Liste des Utilisateurs</h3>
                <span class="badge badge-primary">{{ $users->count() }} utilisateurs</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom & Prénom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>#{{ $user->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="user-avatar-sm">
                                                {{ strtoupper(substr($user->prenom, 0, 1) . substr($user->nom, 0, 1)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $user->nom }} {{ $user->prenom }}</strong>
                                                @if($user->resourcesGerees->count() > 0)
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-server"></i> {{ $user->resourcesGerees->count() }}
                                                        ressource(s)
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge 
                                                        @if($user->role->nom == 'Admin') badge-danger
                                                        @elseif($user->role->nom == 'Responsable') badge-warning
                                                        @else badge-info @endif">
                                            {{ ucfirst($user->role->nom) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->account_status == 'pending')
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> En attente
                                            </span>
                                        @elseif($user->is_active)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle"></i> Actif
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times-circle"></i> Inactif
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.users.edit-role', $user->id) }}"
                                                class="btn btn-sm btn-outline">
                                                <i class="fas fa-user-edit"></i> Rôle
                                            </a>

                                            <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm 
                                                                @if($user->is_active && $user->account_status != 'pending') btn-warning @else btn-success @endif">
                                                    <i class="fas fa-power-off"></i>
                                                    @if($user->account_status == 'pending')
                                                        Activer
                                                    @elseif($user->is_active)
                                                        Désactiver
                                                    @else
                                                        Activer
                                                    @endif
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center p-5">
                                        <div class="notifications-empty-state">
                                            <i class="fas fa-users notifications-empty-icon"></i>
                                            <h4>Aucun utilisateur trouvé</h4>
                                            <p>Aucun utilisateur ne correspond à vos critères de recherche.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .user-avatar-sm {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .btn-group {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .stat-card.danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
        }

        .stat-card.danger .stat-icon {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
@endsection