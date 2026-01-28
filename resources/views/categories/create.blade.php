@extends('layouts.app')
@section('title', 'Nouvelle catégorie')
@section('page-title', 'Créer une catégorie')

@section('content')
    <div class="header-sec mt-6 p-tb-0">
        <div class="header-sec-title">
            <h1><i class="fas fa-plus-circle me-2"></i>Nouvelle catégorie</h1>
            <p> Ajoutez une nouvelle catégorie pour organiser vos ressources</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('categories.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left me-2"></i> Retour aux catégories
            </a>
        </div>
    </div>
    <div class="categories-create">
        <!-- Carte du formulaire -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-folder-plus me-2"></i>Informations de la catégorie</h3>
            </div>

            <div class="card-body">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf

                    <div class="form-group mb-4">
                        <label for="nom" class="form-label">
                            <i class="fas fa-tag mr-1 c-info"></i>Nom de la catégorie
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror"
                            value="{{ old('nom') }}" placeholder="Ex: Serveurs, Stockage..." required>
                        @error('nom')
                            <div class="invalid-feedback d-block">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <br><small class="form-text text-muted mt-1">
                            <i class="fas fa-info-circle me-1"></i>
                            Nom unique pour identifier la catégorie (maximum 100 caractères)
                        </small>
                    </div>

                    <div class="form-group mb-4">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left mr-1 c-info"></i>Description
                        </label>
                        <input name="description" id="description"
                            class="form-control w-80 h-80px @error('description') is-invalid @enderror"
                            placeholder="Décrivez le type de ressources que cette catégorie contient..."
                            style="resize: vertical;">{{ old('description') }}</input>
                        @error('description')
                            <div class="invalid-feedback d-block">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Optionnel - 500 caractères maximum
                            </small>
                            <small class="form-text" id="charCount">
                                <span id="charNumber">0</span>/500 caractères
                            </small>
                        </div>
                    </div>

                    <!-- Aperçu -->
                    <div class="preview-card mb-4">
                        <div class="preview-header">
                            <h4><i class="fas fa-eye me-2"></i>Aperçu de la catégorie</h4>
                        </div>
                        <div class="preview-body">
                            <div class="preview-category">
                                <div class="preview-icon">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <div class="preview-content">
                                    <h5 id="previewNom" class="preview-title">
                                        {{ old('nom') ?: 'Nom de la catégorie' }}
                                    </h5>
                                    <p id="previewDescription" class="preview-description">
                                        {{ old('description') ?: 'Description de la catégorie' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('categories.index') }}" class="btn btn-outline">
                                <i class="fas fa-times me-2"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i> Créer la catégorie
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .categories-create {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nomInput = document.getElementById('nom');
            const descriptionInput = document.getElementById('description');
            const charNumber = document.getElementById('charNumber');
            const charCount = document.getElementById('charCount');
            const previewNom = document.getElementById('previewNom');
            const previewDescription = document.getElementById('previewDescription');

            // Mise à jour de l'aperçu
            function updatePreview() {
                const nomValue = nomInput.value.trim();
                const descValue = descriptionInput.value.trim();

                previewNom.textContent = nomValue || 'Nom de la catégorie';
                previewDescription.textContent = descValue || 'Description de la catégorie';

                if (!descValue) {
                    previewDescription.classList.add('text-muted');
                    previewDescription.classList.add('fst-italic');
                } else {
                    previewDescription.classList.remove('text-muted');
                    previewDescription.classList.remove('fst-italic');
                }
            }

            // Compteur de caractères
            function updateCharCount() {
                const length = descriptionInput.value.length;
                charNumber.textContent = length;

                if (length > 500) {
                    charCount.classList.add('c-dng');
                    charCount.classList.remove('text-muted');
                    descriptionInput.classList.add('is-invalid');
                } else {
                    charCount.classList.remove('c-dng');
                    charCount.classList.add('text-muted');
                    descriptionInput.classList.remove('is-invalid');
                }
            }

            // Événements
            nomInput.addEventListener('input', updatePreview);
            descriptionInput.addEventListener('input', function () {
                updatePreview();
                updateCharCount();
            });

            // Initialisation
            updatePreview();
            updateCharCount();

            // Validation avant soumission
            const form = document.querySelector('form');
            form.addEventListener('submit', function (e) {
                const nom = nomInput.value.trim();
                const description = descriptionInput.value.trim();

                if (!nom) {
                    e.preventDefault();
                    nomInput.focus();
                    return;
                }

                if (description.length > 500) {
                    e.preventDefault();
                    descriptionInput.focus();
                    return;
                }
            });
        });
    </script>
@endsection