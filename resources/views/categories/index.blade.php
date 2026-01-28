@extends('layouts.app')
@section('title', 'Gestion des catégories')
@section('page-title', 'Catégories de ressources')

@section('content')
    <div class="categories-page">
        <!-- En-tête -->
        <div class="header-sec">
            <div class="header-sec-title">
                <h1><i class="fas fa-tags me-2"></i>Gestion des catégories</h1>
                <p>Organisez vos ressources en catégories pour une meilleure gestion</p>
            </div>
            <div class="header-sec-date">
                <i class="fas fa-layer-group"></i> {{ $categories->count() }} catégorie(s)
            </div>
        </div>

        <!-- Barre d'actions -->
        <div class="page-actions">
            <a href="{{ route('categories.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nouvelle catégorie
            </a>

            <form action="{{ route('categories.index') }}" method="GET" class="d-flex gap-2 flex-grow-1">
                <div class="search-box w-100">
                    <input type="text" name="search" class="search-input" placeholder="Rechercher une catégorie..."
                        value="{{ request('search') }}">
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                @if(request('search'))
                    <a href="{{ route('categories.index') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-times"></i> Effacer
                    </a>
                @endif
            </form>
        </div>

        <!-- Grille des catégories -->
        @if($categories->count() > 0)
            <div class="grid gap-3 mt-4">
                @foreach($categories as $category)
                    <div class="section-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="section-icon">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <div>
                                    <h3 class="mb-1">{{ $category->nom }}</h3>
                                    <small class="text-muted">
                                        <i class="fas fa-box me-1"></i>
                                        {{ $category->resources_count }} ressource(s)
                                    </small>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline dropdown-toggle" type="button" onclick="toggleDropdown(this)">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary btn-s mb-1">
                                        <i class="fas fa-edit me-2"></i> Modifier
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="zero">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-s mb-1"
                                            onclick="return confirm('Supprimer cette catégorie ?')">
                                            <i class="fas fa-trash me-2"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted mb-3">
                            {{ $category->description ?: 'Aucune description' }}
                        </p>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <a href="{{ route('resources.index') }}?category_id={{ $category->id }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-list me-1"></i> Voir les ressources
                            </a>
                            <small class="text-muted">
                                Créée {{ $category->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>

        @else
            <!-- État vide -->
            <div class="section-card ta-center mt-4">
                <div class="notifications-empty-icon mb-3">
                    <i class="fas fa-folder-open fa-4x text-muted"></i>
                </div>
                <h4 class="mb-2">Aucune catégorie trouvée</h4>
                <p class="text-muted mb-4">
                    {{ request('search')
                ? 'Aucune catégorie ne correspond à votre recherche.'
                : 'Commencez par créer votre première catégorie.' }}
                </p>
                <a href="{{ route('categories.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i> Créer une catégorie
                </a>
            </div>
        @endif
    </div>

    <style>
        .categories-page {
            padding: 20px 0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .section-card {
            background: var(--white-color);
            border-radius: var(--radius-sm);
            padding: 25px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--sec-color);
        }

        .section-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #f0f7ff, #e3f2fd);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--sec-color);
        }
    </style>

    <script>
        function toggleDropdown(button) {
            const menu = button.nextElementSibling;
            const isVisible = menu.style.display === 'block';

            // Fermer tous les autres menus
            document.querySelectorAll('.dropdown-menu').forEach(m => {
                m.style.display = 'none';
            });

            if (!isVisible) {
                menu.style.display = 'block';
                // Fermer en cliquant ailleurs
                setTimeout(() => {
                    document.addEventListener('click', function closeMenu(e) {
                        if (!button.contains(e.target) && !menu.contains(e.target)) {
                            menu.style.display = 'none';
                            document.removeEventListener('click', closeMenu);
                        }
                    });
                });
            }
        }
    </script>
@endsection