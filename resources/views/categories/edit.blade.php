@extends('layouts.app')
@section('title', 'Modifier catégorie')
@section('page-title', 'Modifier la catégorie')

@section('content')
    <div class="header-sec mt-6 p-tb-0">
        <div class="header-sec-title">
            <h1><i class="fas fa-edit mr-1"></i>Modifier la catégorie</h1>
            <p>Modifiez les informations de la catégorie "{{ $category->nom }}"</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('categories.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left mr-1"></i> Retour aux catégories
            </a>
        </div>
    </div>

    <div class="categories-edit">
        <!-- Carte du formulaire -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-folder-plus mr-1"></i>Informations de la catégorie</h3>
            </div>

            <div class="card-body">
                <form action="{{ route('categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-4">
                        <label for="nom" class="form-label">
                            <i class="fas fa-tag mr-1 c-info"></i>Nom de la catégorie
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror"
                            value="{{ old('nom', $category->nom) }}" placeholder="Ex: Serveurs, Stockage..." required>
                        @error('nom')
                            <div class="invalid-feedback d-block">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left mr-1 c-info"></i>Description
                        </label>
                        <input name="description" id="description"
                            class="form-control w-80 h-80px @error('description') is-invalid @enderror"
                            placeholder="Décrivez le type de ressources que cette catégorie contient..." value="{{ old('description', $category->description) }}"></input>
                        @error('description')
                            <div class="invalid-feedback d-block">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Optionnel - 500 caractères maximum
                            </small>
                            <small class="form-text" id="charNumber">0/500 caractères</small>
                        </div>
                    </div>

                    <!-- Aperçu -->
                    <div class="preview-card mb-4">
                        <div class="preview-header">
                            <h4><i class="fas fa-eye mr-1"></i>Aperçu de la catégorie</h4>
                        </div>
                        <div class="preview-body">
                            <div class="preview-category">
                                <div class="preview-icon">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <div class="preview-content">
                                    <h5 id="previewNom" class="preview-title">
                                        {{ old('nom', $category->nom) }}
                                    </h5>
                                    <p id="previewDescription" class="preview-description">
                                        {{ old('description', $category->description) ?: 'Description de la catégorie' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('categories.index') }}" class="btn btn-outline">
                                <i class="fas fa-times mr-1"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .categories-edit {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nomInput = document.getElementById('nom');
            const descriptionInput = document.getElementById('description');
            const previewNom = document.getElementById('previewNom');
            const previewDescription = document.getElementById('previewDescription');
            const charNumber = document.getElementById('charNumber');

            function updatePreview() {
                const nomValue = nomInput.value.trim();
                const descValue = descriptionInput.value.trim();
                previewNom.textContent = nomValue || '{{ $category->nom }}';
                previewDescription.textContent = descValue || 'Description de la catégorie';
            }

            function updateCharCount() {
                const length = descriptionInput.value.length;
                charNumber.textContent = `${length}/500`;
                if (length > 500) {
                    charNumber.classList.add('c-dng');
                    charNumber.classList.remove('text-muted');
                    descriptionInput.classList.add('is-invalid');
                } else {
                    charNumber.classList.remove('c-dng');
                    charNumber.classList.add('text-muted');
                    descriptionInput.classList.remove('is-invalid');
                }
            }

            nomInput.addEventListener('input', updatePreview);
            descriptionInput.addEventListener('input', function () {
                updatePreview();
                updateCharCount();
            });

            updatePreview();
            updateCharCount();
        });
    </script>
@endsection