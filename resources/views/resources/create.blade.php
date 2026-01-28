@extends('layouts.app')

@section('title', 'Créer une nouvelle ressource')

@section('content')
<div class="container">
    <!-- En-tête de la page -->
    <div class="header-sec mt-4">
        <div class="header-sec-title">
            <h1><i class="fas fa-plus-circle"></i> Créer une nouvelle ressource</h1>
            <p>Ajoutez une nouvelle ressource au système</p>
        </div>
        <div class="header-sec-date">
            <i class="fas fa-calendar-alt"></i> {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <!-- Carte contenant le formulaire -->
    <div class="card p-4 mb-4">
        <form action="{{ route('resources.store') }}" method="POST" id="resource-form">
            @csrf

            <!-- Champ : Nom de la ressource -->
            <div class="mb-3">
                <label class="form-label d-flex align-items-center gap-1">
                    <i class="fas fa-tag"></i> Nom de la ressource <span class="text-danger">*</span>
                </label>
                <input type="text" name="nom"
                       class="form-control @error('nom') is-invalid @enderror"
                       value="{{ old('nom') }}" required
                       placeholder="Entrez le nom de la ressource">
                @error('nom')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Champ : Catégorie -->
            <div class="mb-3">
                <label class="form-label d-flex align-items-center gap-1">
                    <i class="fas fa-layer-group"></i> Catégorie <span class="text-danger">*</span>
                </label>
                <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                    <option value="">— Sélectionnez une catégorie —</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->nom }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Champ : Responsable -->
            <div class="mb-3">
                <label class="form-label d-flex align-items-center gap-1">
                    <i class="fas fa-user-tie"></i> Responsable
                </label>
                <select style="color:black !important;" name="responsable_id" class="form-control @error('responsable_id') is-invalid @enderror">
                    <option value="">— Aucun responsable —</option>
                    @foreach($responsables as $responsable)
                        <option value="{{ $responsable->id }}"
                            {{ old('responsable_id') == $responsable->id ? 'selected' : '' }}>
                            {{ $responsable->name }}
                        </option>
                    @endforeach
                </select>
                @error('responsable_id')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </div>
                @enderror
               <br> <small class="form-text text-muted">
                    <i class="fas fa-info-circle"></i> Laisser vide si la ressource n'a pas de responsable spécifique
                </small>
            </div>

            <!-- Champ : Statut -->
            <div class="mb-3">
                <label class="form-label d-flex align-items-center gap-1">
                    <i class="fas fa-circle"></i> Statut <span class="text-danger">*</span>
                </label>
                <select name="statut" class="form-control @error('statut') is-invalid @enderror" required>
                    <option value="">— Sélectionnez un statut —</option>
                    @foreach(['disponible','réservé','maintenance','hors_service'] as $statut)
                        <option value="{{ $statut }}"
                            {{ old('statut') === $statut ? 'selected' : '' }}
                            class="
                                @if($statut === 'disponible') text-success
                                @elseif($statut === 'réservé') text-warning
                                @elseif($statut === 'maintenance') text-info
                                @elseif($statut === 'hors_service') text-danger
                                @endif
                            ">
                            {{ ucfirst($statut) }}
                        </option>
                    @endforeach
                </select>
                @error('statut')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Case à cocher : Ressource active -->
            <div class="form-check mb-4">
                <input class="form-check-input @error('est_actif') is-invalid @enderror" 
                       type="checkbox"
                       name="est_actif" value="1"
                       {{ old('est_actif', true) ? 'checked' : '' }}
                       id="est_actif">
                <label class="form-check-label d-flex align-items-center gap-2" for="est_actif">
                    <i class="fas fa-power-off"></i> Ressource active
                </label>
                @error('est_actif')
                    <div class="invalid-feedback d-block">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </div>
                @enderror
                <small class="form-text text-muted">
                    <i class="fas fa-info-circle"></i> Décochez pour créer la ressource comme inactive
                </small>
            </div>

            <!-- Champ : Description -->
            <div class="mb-4">
                <label class="form-label d-flex align-items-center gap-1">
                    <i class="fas fa-align-left"></i> Description
                </label>
                <textarea name="description" 
                          class="form-control @error('description') is-invalid @enderror" 
                          rows="3"
                          placeholder="Décrivez la ressource...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </div>
                @enderror
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
                    <!-- Message par défaut -->
                    <div class="alert alert-info p-2 text-center mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Aucune spécification définie. Cliquez sur "Ajouter" pour en créer.
                    </div>
                </div>

                <!-- Guide d'utilisation -->
                <div class="card p-3 bg-light">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-lightbulb text-info mt-1"></i>
                        <div>
                            <small class="font-weight-bold d-block mb-1">Comment ajouter des spécifications :</small>
                            <small class="text-muted d-block">1. Cliquez sur "Ajouter" pour créer une nouvelle ligne</small>
                            <small class="text-muted d-block">2. Dans la première colonne, entrez le nom (ex: CPU, RAM, OS)</small>
                            <small class="text-muted d-block">3. Dans la deuxième colonne, entrez la valeur (ex: i7, 16GB, Windows)</small>
                            <small class="text-muted d-block">4. Pour supprimer, cliquez sur l'icône <i class="fas fa-trash text-danger"></i></small>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                Formatage automatique en JSON dans la base de données
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Champ caché pour les spécifications (format JSON) -->
            <input type="hidden" name="specifications" id="specifications-json">

            <!-- Boutons d'action -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                        <i class="fas fa-save"></i> Créer la ressource
                    </button>
                    <button type="reset" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </button>
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
    
    .form-text i {
        margin-right: 4px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('specifications-container');
    const addBtn = document.getElementById('add-spec-btn');
    const form = document.getElementById('resource-form');
    const jsonInput = document.getElementById('specifications-json');
    let specCounter = 0;
    
    // Fonction pour mettre à jour le champ JSON caché
    function updateJsonSpecifications() {
        const specs = {};
        container.querySelectorAll('.specification-item').forEach(item => {
            const key = item.querySelector('.spec-key').value.trim();
            const value = item.querySelector('.spec-value').value.trim();
            if (key && value) {
                specs[key] = value;
            }
        });
        jsonInput.value = JSON.stringify(specs);
    }
    
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
                   value="${key}"
                   onchange="updateJsonSpecifications()">
            <input type="text" 
                   name="specs[${specCounter}][value]" 
                   class="form-control spec-value" 
                   placeholder="Valeur (ex: i7)" 
                   value="${value}"
                   onchange="updateJsonSpecifications()">
            <button type="button" class="btn btn-sm btn-danger remove-spec">
                <i class="fas fa-trash"></i>
            </button>
        `;
        
        // Ajouter l'élément au conteneur
        container.appendChild(item);
        specCounter++;
        
        // Mettre à jour le JSON
        updateJsonSpecifications();
        
        // Ajouter l'événement de suppression au bouton
        item.querySelector('.remove-spec').addEventListener('click', function() {
            item.remove();
            
            // Si plus d'éléments, afficher le message d'information
            if (container.children.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info p-2 text-center mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Aucune spécification définie. Cliquez sur "Ajouter" pour en créer.
                    </div>
                `;
            }
            
            // Mettre à jour le JSON
            updateJsonSpecifications();
        });
        
        // Ajouter les événements de changement pour les inputs
        const keyInput = item.querySelector('.spec-key');
        const valueInput = item.querySelector('.spec-value');
        
        keyInput.addEventListener('input', updateJsonSpecifications);
        valueInput.addEventListener('input', updateJsonSpecifications);
    }
    
    // Gérer le clic sur le bouton "Ajouter"
    addBtn.addEventListener('click', function() {
        addSpecificationItem();
    });
    
    // Gérer la soumission du formulaire
    form.addEventListener('submit', function(e) {
        // Validation des spécifications
        const specItems = container.querySelectorAll('.specification-item');
        let hasError = false;
        
        specItems.forEach(item => {
            const key = item.querySelector('.spec-key').value.trim();
            const value = item.querySelector('.spec-value').value.trim();
            
            if ((key && !value) || (!key && value)) {
                hasError = true;
                item.style.borderColor = '#dc3545';
                item.style.backgroundColor = '#f8d7da';
            } else {
                item.style.borderColor = '';
                item.style.backgroundColor = '';
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Veuillez remplir à la fois le nom ET la valeur pour chaque spécification, ou supprimer la ligne incomplète.');
        }
    });
    
    // Initialiser avec les anciennes valeurs s'il y en a (en cas d'erreur de validation)
    @if(old('specs'))
        const oldSpecs = @json(old('specs', []));
        if (Array.isArray(oldSpecs) && oldSpecs.length > 0) {
            // Supprimer le message d'alerte initial
            container.innerHTML = '';
            
            // Ajouter chaque spécification
            oldSpecs.forEach(spec => {
                if (spec && spec.key && spec.value) {
                    addSpecificationItem(spec.key, spec.value);
                }
            });
        }
    @endif
});
</script>
@endsection