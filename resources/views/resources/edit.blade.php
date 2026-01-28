@extends('layouts.app')

@section('title', 'Modifier la ressource')

@section('content')
<div class="container">
    <!-- En-tête de la page -->
    <div class="header-sec mt-4">
        <div class="header-sec-title">
            <h1><i class="fas fa-edit"></i> Modifier la ressource</h1>
            <p>Modifiez les informations de la ressource <strong>{{ $resource->nom }}</strong></p>
        </div>
        <div class="header-sec-date">
            <i class="fas fa-calendar-alt"></i> {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <!-- Carte contenant le formulaire -->
    <div class="card p-4 mb-4">
        <form action="{{ route('resources.update', $resource->id) }}" method="POST" id="resource-form">
            @csrf
            @method('PUT')

            <!-- Champ : Nom de la ressource -->
            <div class="mb-3">
                <label class="form-label d-flex align-items-center gap-1">
                    <i class="fas fa-tag"></i> Nom de la ressource
                </label>
                <input type="text" name="nom"
                       class="form-control @error('nom') bord-rad-lef2 c-dng @enderror"
                       value="{{ old('nom', $resource->nom) }}" required
                       placeholder="Entrez le nom de la ressource">
                @error('nom') 
                    <div class="text-right c-dng mt-1">
                        <small><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</small>
                    </div>
                @enderror
            </div>

            <!-- Champ : Catégorie -->
            <div class="mb-3">
                <label class="form-label d-flex align-items-center gap-1">
                    <i class="fas fa-layer-group"></i> Catégorie
                </label>
                <select name="category_id" class="form-control" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ $resource->category_id == $category->id ? 'selected' : '' }}>
                            {{ $category->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Champ : Responsable (visible uniquement pour les administrateurs) -->
            @if(auth()->user()->isAdmin())
            <div class="mb-3">
                <label class="form-label d-flex align-items-center gap-1">
                    <i class="fas fa-user-tie"></i> Responsable
                </label>
                <select name="responsable_id" class="form-control">
                    <option value="">— Aucun responsable —</option>
                    @foreach($responsables as $responsable)
                        <option value="{{ $responsable->id }}"
                            {{ $resource->responsable_id == $responsable->id ? 'selected' : '' }}>
                            {{ $responsable->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Champ : Statut -->
            <div class="mb-3">
                <label class="form-label d-flex align-items-center gap-1">
                    <i class="fas fa-circle"></i> Statut
                </label>
                <select name="statut" class="form-control" required>
                    @foreach(['disponible','réservé','maintenance','hors_service'] as $statut)
                        <option value="{{ $statut }}"
                            {{ $resource->statut === $statut ? 'selected' : '' }}
                            class="
                                @if($statut === 'disponible') c-info
                                @elseif($statut === 'réservé') c-warning
                                @elseif($statut === 'maintenance') c-info
                                @elseif($statut === 'hors_service') c-dng
                                @endif
                            ">
                            {{ ucfirst($statut) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Case à cocher : Ressource active -->
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox"
                       name="est_actif" value="1"
                       {{ $resource->est_actif ? 'checked' : '' }}
                       id="est_actif">
                <label class="form-check-label d-flex align-items-center gap-2" for="est_actif">
                    <i class="fas fa-power-off"></i> Ressource active
                </label>
                <small class="form-text">Décochez pour désactiver temporairement la ressource</small>
            </div>

            <!-- Champ : Description -->
            <div class="mb-4">
                <label class="form-label d-flex align-items-center gap-1">
                    <i class="fas fa-align-left"></i> Description
                </label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Décrivez la ressource...">{{ old('description', $resource->description) }}</textarea>
            </div>

            <!-- Section : Spécifications techniques -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="form-label d-flex align-items-center gap-1 mb-0">
                        <i class="fas fa-cogs"></i> Spécifications techniques
                    </label>
                    <button type="button" id="add-spec-btn" class="btn btn-sm btn-success d-flex align-items-center gap-1">
                        <i class="fas fa-plus"></i> Ajouter
                    </button>
                </div>

                <!-- Conteneur des spécifications -->
                <div id="specifications-container" class="mb-3">
                    @php
                        // Récupérer les spécifications existantes depuis la base de données
                        $specs = $resource->specifications ?? [];
                        // Si c'est une chaîne JSON, la décoder
                        if (is_string($specs)) {
                            $specs = json_decode($specs, true) ?? [];
                        }
                        $specs = is_array($specs) ? $specs : [];
                        $specIndex = 0; // Compteur pour les noms des champs
                    @endphp
                    
                    @forelse($specs as $key => $value)
                        <!-- Ligne pour chaque spécification existante -->
                        <div class="specification-item d-flex gap-2 mb-2">
                            <input type="text" 
                                   name="specs[{{ $specIndex }}][key]" 
                                   class="form-control spec-key" 
                                   placeholder="Nom (ex: CPU)" 
                                   value="{{ $key }}">
                            <input type="text" 
                                   name="specs[{{ $specIndex }}][value]" 
                                   class="form-control spec-value" 
                                   placeholder="Valeur (ex: i7)" 
                                   value="{{ $value }}">
                            <button type="button" class="btn btn-sm btn-danger remove-spec">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        @php $specIndex++; @endphp
                    @empty
                        <!-- Message affiché quand il n'y a pas de spécifications -->
                        <div class="alert alert-info p-2 text-center mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Aucune spécification définie. Ajoutez-en une ci-dessus.
                        </div>
                    @endforelse
                </div>

                <!-- Guide d'utilisation -->
                <div class="card p-3 bg-light">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-lightbulb c-info mt-1"></i>
                        <div>
                            <small class="font-wb d-block mb-1">Comment ajouter des spécifications :</small>
                            <small class="text-muted d-block">1. Cliquez sur "Ajouter" pour créer une nouvelle ligne</small>
                            <small class="text-muted d-block">2. Dans la première colonne, entrez le nom (ex: CPU, RAM, OS)</small>
                            <small class="text-muted d-block">3. Dans la deuxième colonne, entrez la valeur (ex: i7, 16GB, Windows)</small>
                            <small class="text-muted d-block">4. Pour supprimer, cliquez sur l'icône <i class="fas fa-trash text-danger"></i></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 trans-up">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="{{ route('resources.show', $resource->id) }}" 
                       class="btn btn-outline d-flex align-items-center gap-2 trans-up">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
                <a href="{{ route('resources.index') }}" class="link d-flex align-items-center gap-1">
                    <i class="fas fa-list"></i> Retour à la liste
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    /* Styles pour les lignes de spécifications */
    .specification-item {
        align-items: center;
        padding: 8px;
        border-radius: 8px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .specification-item:hover {
        background: #e9ecef;
        border-color: #dee2e6;
    }
    
    .spec-key {
        width: 40%;
        min-width: 150px;
    }
    
    .spec-value {
        width: 50%;
        min-width: 200px;
    }
    
    .remove-spec {
        width: 10%;
        min-width: 40px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<script>
// JavaScript pour gérer les spécifications dynamiquement
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('specifications-container');
    const addBtn = document.getElementById('add-spec-btn');
    let specCounter = {{ $specIndex }}; // Initialiser avec le nombre de spécifications existantes
    
    // Fonction pour ajouter une nouvelle ligne de spécification
    function addSpecificationItem(key = '', value = '') {
        // Supprimer le message d'information s'il existe
        const alertMsg = container.querySelector('.alert');
        if (alertMsg) {
            alertMsg.remove();
        }
        
        // Créer un nouvel élément de spécification
        const item = document.createElement('div');
        item.className = 'specification-item d-flex gap-2 mb-2';
        item.innerHTML = `
            <input type="text" 
                   name="specs[${specCounter}][key]" 
                   class="form-control spec-key" 
                   placeholder="Nom (ex: CPU)" 
                   value="${key}">
            <input type="text" 
                   name="specs[${specCounter}][value]" 
                   class="form-control spec-value" 
                   placeholder="Valeur (ex: i7)" 
                   value="${value}">
            <button type="button" class="btn btn-sm btn-danger remove-spec">
                <i class="fas fa-trash"></i>
            </button>
        `;
        
        // Ajouter l'élément au conteneur
        container.appendChild(item);
        specCounter++;
        
        // Ajouter l'événement de suppression au bouton
        item.querySelector('.remove-spec').addEventListener('click', function() {
            item.remove();
            
            // Si plus d'éléments, afficher le message d'information
            if (container.children.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info p-2 text-center mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Aucune spécification définie. Ajoutez-en une ci-dessus.
                    </div>
                `;
            }
        });
    }
    
    // Gérer le clic sur le bouton "Ajouter"
    addBtn.addEventListener('click', function() {
        addSpecificationItem();
    });
    
    // Ajouter les événements de suppression aux boutons existants
    container.querySelectorAll('.remove-spec').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.specification-item').remove();
            
            // Si plus d'éléments, afficher le message d'information
            if (container.children.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info p-2 text-center mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Aucune spécification définie. Ajoutez-en une ci-dessus.
                    </div>
                `;
            }
        });
    });
});
</script>
@endsection