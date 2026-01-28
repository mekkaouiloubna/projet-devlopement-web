@extends('layouts.app')

@section('content')
<div class="container">
    <div class="header-sec">
        <div class="header-sec-title">
            <h1>
                <i class="fas fa-user-edit"></i> Modifier le rôle
            </h1>
            <p>
                Modifiez le rôle de l'utilisateur
            </p>
        </div>
        <div class="page-actions">
            <a href="{{ route('users.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user-cog"></i> Modification du rôle</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.update-role', $user->id) }}" method="POST">
                @csrf
                
                <div class="form-group mb-4">
                    <label class="form-label">Utilisateur</label>
                    <div class="user-info-card p-3 bg-light rounded">
                        <div class="d-flex align-items-center gap-3">
                            <div class="user-avatar-lg">
                                {{ strtoupper(substr($user->prenom, 0, 1) . substr($user->nom, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="mb-1">{{ $user->nom }} {{ $user->prenom }}</h4>
                                <p class="mb-1 text-muted">{{ $user->email }}</p>
                                <small class="text-muted">ID: #{{ $user->id }} | Inscrit le: {{ $user->created_at->format('d/m/Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label">Rôle actuel</label>
                    <div class="current-role-display">
                        <span class="badge 
                            @if($user->role->nom == 'Admin') badge-danger
                            @elseif($user->role->nom == 'Responsable') badge-warning
                            @else badge-info @endif
                            p-2">
                            <i class="fas fa-user-tag"></i> {{ ucfirst($user->role->nom) }}
                        </span>
                    </div>
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label">Nouveau Rôle *</label>
                    <select name="role_id" class="form-control" required 
                            onchange="toggleResourcesField(this)">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" 
                                    {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                {{ ucfirst($role->nom) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div id="resourcesField" class="form-group mb-4" 
                     style="display: {{ $user->role->nom == 'Responsable' ? 'block' : 'none' }}">
                    <label class="form-label">
                        <i class="fas fa-server"></i> Ressources à gérer
                    </label>
                    <div class="row">
                        @foreach($resources as $resource)
                            <div class="col-md-6 mb-3">
                                <div class="resource-select-card p-3 border rounded">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               name="resources[]" 
                                               value="{{ $resource->id }}" 
                                               id="resource_{{ $resource->id }}"
                                               class="form-check-input"
                                               {{ in_array($resource->id, $user->resourcesGerees->pluck('id')->toArray()) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="resource_{{ $resource->id }}">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="resource-icon">
                                                    <i class="fas fa-server"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $resource->nom }}</strong>
                                                    <small class="d-block text-muted">
                                                        {{ $resource->category->nom }}
                                                    </small>
                                                    <small class="d-block">
                                                        Statut: 
                                                        <span class="badge 
                                                            @if($resource->statut == 'disponible') badge-success
                                                            @elseif($resource->statut == 'réservé') badge-warning
                                                            @elseif($resource->statut == 'maintenance') badge-info
                                                            @else badge-danger @endif">
                                                            {{ $resource->statut }}
                                                        </span>
                                                    </small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <small class="form-text text-muted">
                        Sélectionnez les ressources que cet utilisateur pourra gérer en tant que responsable
                    </small>
                </div>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline ml-2">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.user-avatar-lg {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
}

.user-info-card {
    border-left: 4px solid #3498db;
}

.resource-select-card {
    border: 2px solid #dee2e6;
    border-radius: 10px;
    transition: all 0.3s;
    cursor: pointer;
}

.resource-select-card:hover {
    border-color: #3498db;
    background: #f8f9fa;
}

.resource-select-card .form-check-input:checked + label {
    color: #3498db;
    font-weight: bold;
}

.resource-icon {
    width: 40px;
    height: 40px;
    background: #e3f2fd;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3498db;
}
</style>

<script>
function toggleResourcesField(select) {
    const roleName = select.options[select.selectedIndex].text;
    const resourcesField = document.getElementById('resourcesField');
    
    if (roleName === 'Responsable') {
        resourcesField.style.display = 'block';
    } else {
        resourcesField.style.display = 'none';
        // Désélectionner toutes les cases à cocher
        const checkboxes = resourcesField.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = false);
    }
}
</script>
@endsection