@extends('layouts.app')

@section('title', 'Ressources disponibles')
@section('page-title', 'Ressources disponibles')
@section('page-icon', 'fas fa-server')

@section('content')
    <div class="header-sec" style="margin:60px 0 0 0;">
        <div class="header-sec-title">
            <h1>
                <i class="fas fa-server"></i>
                Liste des ressources
            </h1>
            <p>Parcourir les ressources disponibles et leurs caractéristiques</p>
        </div>
        <div class="header-sec-date">
            <i class="fas fa-calendar-alt"></i>
            {{ now()->format('d/m/Y') }}
        </div>
    </div>
    @auth
        <div class="page-actions">@if(auth()->user()->isAdmin())
            <a href="{{ route('resources.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle ressource
            </a>
        @endif
        </div>
    @endauth

    <!-- Filtres -->
    <div class="mt-3 mb-3">
        <h5><i class="fas fa-filter"></i> Filtres</h5>
        <form method="GET" action="{{ route('resources.index') }}" class="filtre-col">
            <!-- Catégorie -->
            <div class="col-md-3 form-group">
                <label class="form-label">Catégorie</label>
                <select name="category_id" class="form-control">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Statut -->
            <div class="col-md-3 form-group">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-control">
                    <option value="">Tous les statuts</option>
                    <option value="disponible" {{ request('statut') == 'disponible' ? 'selected' : '' }}>
                        Disponible</option>
                    <option value="réservé" {{ request('statut') == 'réservé' ? 'selected' : '' }}>Réservé
                    </option>
                    <option value="maintenance" {{ request('statut') == 'maintenance' ? 'selected' : '' }}>
                        Maintenance</option>
                    <option value="hors_service" {{ request('statut') == 'hors_service' ? 'selected' : '' }}>Hors
                        service</option>
                </select>
            </div>

            <!-- Recherche -->
            <div class="col-md-3 form-group">
                <label class="form-label">Recherche</label>
                <input type="text" name="search" class="form-control" placeholder="Nom, description..."
                    value="{{ request('search') }}">
            </div>

            <!-- Actions -->
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filtrer
            </button>

            @if(request()->hasAny(['category_id', 'statut', 'search']))
                <a href="{{ route('resources.index') }}" class="btn btn-sm btn-outline">
                    <i class="fas fa-times"></i> Réinitialiser
                </a>
            @endif
        </form>
    </div>

    @auth
        @if(auth()->user()->isAdminOrRespo())
            <!-- Statistiques -->
            <div class="stats-grid mb-4">
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $totalResources }}</h3>
                        <p>Total ressources</p>
                    </div>
                </div>

                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $availableResources }}</h3>
                        <p>Disponibles</p>
                    </div>
                </div>

                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $reservedResources }}</h3>
                        <p>Réservées</p>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $maintenanceResources }}</h3>
                        <p>En maintenance</p>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    <!-- Liste des ressources -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list"></i> Liste des ressources</h5>
            <div class="text-muted">
                {{ $resources->total() }} ressource(s) trouvée(s)
            </div>
        </div>

        <div class="card-body">
            @if($resources->count() > 0)
                <div class="element-grid">
                    @foreach($resources as $resource)
                        <div class="resource-card">
                            <div class="resource-header">
                                <div class="resource-category">
                                    <i class="fas fa-folder"></i> {{ $resource->category->nom }}
                                </div>
                                <div class="resource-status {{ $resource->statut }}">
                                    @if($resource->statut == 'disponible')
                                        <span class="badge badge-success">Disponible</span>
                                    @elseif($resource->statut == 'réservé')
                                        <span class="badge badge-warning">Réservé</span>
                                    @elseif($resource->statut == 'maintenance')
                                        <span class="badge badge-info">Maintenance</span>
                                    @else
                                        <span class="badge badge-danger">Hors service</span>
                                    @endif
                                </div>
                            </div>

                            <h4 class="resource-title">
                                <i class="fas fa-server"></i> {{ $resource->nom }}
                            </h4>

                            <p class="resource-description">
                                {{ Str::limit($resource->description, 100) }}
                            </p>

                            <div class="resource-specs">
                                @if($resource->specifications)
                                    @php
                                        $specs = json_decode($resource->specifications, true);
                                    @endphp
                                    @if($specs)
                                        @foreach(array_slice($specs, 0, 3) as $key => $value)
                                            <div class="spec-item">
                                                <span class="spec-label">{{ $key }}:</span>
                                                <span class="spec-value">{{ $value }}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                @endif
                            </div>

                            <div class="resource-responsable">
                                <small class="text-muted">
                                    @if($resource->responsable)
                                        Responsable: {{ $resource->responsable->prenom }} {{ $resource->responsable->nom }}
                                    @else
                                        Pas de responsable assigné
                                    @endif
                                </small>
                            </div>

                            <div class="resource-actions">
                                 <a href="{{ route('resources.show', $resource->id) }}" class="btn btn-sm btn-primary"
                                            title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                @auth
                                    @if($resource->isDisponible() && (auth()->user()->isUtilisateur()))
                                        <a href="{{ route('reservations.create', ['resource_id' => $resource->id]) }}"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-calendar-plus"></i> Réserver
                                        </a>
                                    @endif
                                    @if(auth()->user()->isAdminOrRespoResource($resource))
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
                                    @endif
                                @endauth
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($resources->hasPages())
                    <div class="mt-4">
                        {{ $resources->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-server fa-3x text-muted mb-3"></i>
                    <h5>Aucune ressource trouvée</h5>
                    <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                </div>
            @endif
        </div>
    </div>

    <style>
        .filtre-col {
            display: flex;
            justify-content: space-around;
            align-items: center;
            border-radius: var(--radius-sm);
            padding: 15px;
            background: #ffffff;
            box-shadow: var(--shadow-sm);
        }

        .element-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .resource-card {
            background: white;
            border: 1.5px solid #000000;
            border-radius: var(--radius-sm);
            padding: 20px;
            transition: var(--transition);
        }

        .resource-card:hover {
            border-color: var(--sec-color);
            box-shadow: var(--shadow-md);
        }

        .resource-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .resource-category {
            font-size: 0.85rem;
            color: var(--gray-color);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .resource-title {
            font-size: 1.2rem;
            color: var(--prim-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .resource-description {
            color: var(--gray-color);
            font-size: 0.95rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .resource-specs {
            margin: 15px 0;
            padding: 15px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }

        .spec-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .spec-label {
            color: var(--gray-color);
        }

        .spec-value {
            color: var(--dark-color);
            font-weight: 500;
        }

        .resource-responsable {
            margin: 15px 0;
            font-size: 0.85rem;
        }

        .resource-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
    </style>
@endsection