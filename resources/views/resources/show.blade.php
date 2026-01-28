{{-- resources/views/resources/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Détails de la ressource')
@section('page-title', 'Détails de la ressource')
@section('page-icon', 'fas fa-server')

@section('content')

    <div class="container">
        <!-- Header amélioré -->
        <div class="header-sec" style="margin:35px 0;">
            <div class="header-sec-title">
                <h1>
                    <i class="fas fa-server"></i>
                    Detaille de la resource : {{ $resource->nom }}
                </h1>
                <p>Informations détaillées sur la ressource sélectionnée</p>
            </div>
            <div class="resource-header-meta">
                <div class="meta-item">
                    <i class="fas fa-tag"></i>
                    <span>{{ $resource->category->nom ?? 'N/A' }}</span>
                </div>
                <a href="{{ route('resources.index') }}" class="btn">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="resource-main-card">
            <div class="card-header">
                <h5>
                    <i class="fas fa-info-circle"></i>
                    Informations principales
                </h5>
                <div class="d-flex align-items-center gap-2">
                    @if($resource->statut == 'disponible')
                        <span class="badge badge-success">
                            <i class="fas fa-check-circle"></i> Disponible
                        </span>
                    @elseif($resource->statut == 'réservé')
                        <span class="badge badge-warning">
                            <i class="fas fa-clock"></i> Réservé
                        </span>
                    @elseif($resource->statut == 'maintenance')
                        <span class="badge badge-info">
                            <i class="fas fa-tools"></i> Maintenance
                        </span>
                    @else
                        <span class="badge badge-danger">
                            <i class="fas fa-times-circle"></i> Hors service
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-group">
                        <div class="info-label">
                            <i class="fas fa-user-tie"></i>
                            Responsable
                        </div>
                        <p class="info-value">
                            {{ $resource->responsable ? $resource->responsable->prenom . ' ' . $resource->responsable->nom : 'Aucun' }}
                        </p>
                    </div>

                    <div class="info-group">
                        <div class="info-label">
                            <i class="fas fa-list-alt"></i>
                            Catégorie
                        </div>
                        <p class="info-value">{{ $resource->category->nom ?? 'N/A' }}</p>
                    </div>

                    <div class="info-group">
                        <div class="info-label">
                            <i class="fas fa-calendar-plus"></i>
                            Créé le
                        </div>
                        <p class="info-value">{{ $resource->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <!-- Description -->
                <div class="info-group">
                    <div class="info-label">
                        <i class="fas fa-align-left"></i>
                        Description
                    </div>
                    <p class="info-value">{{ $resource->description ?? 'Aucune description' }}</p>
                </div>

                <!-- Spécifications -->
                @if($resource->specifications)
                    @php
                        $specs = json_decode($resource->specifications, true);
                    @endphp
                    @if($specs)
                        <div class="specifications-container info-group">
                            <div class="info-label">
                                <i class="fas fa-cogs"></i>
                                Spécifications techniques
                            </div>
                            <div class="specifications-grid">
                                @foreach($specs as $key => $value)
                                    <div class="spec-item">
                                        <span class="spec-label">{{ $key }} : </span>
                                        <span class="spec-value">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
                @auth
                <div class="resource-actions">
                    @if(auth()->user()->isAdminOrRespoResource($resource))
                        <a href="{{ route('resources.edit', $resource->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <form action="{{ route('resources.destroy', $resource->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Voulez-vous vraiment supprimer cette ressource ? Cette action est irréversible.')">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                    @elseif(auth()->user()->isUtilisateur() && $resource->isDisponible())
                        <a href="{{ route('reservations.create', ['resource_id' => $resource->id]) }}"
                            class="btn btn-success btn-lg">
                            <i class="fas fa-calendar-plus"></i> Réserver cette ressource
                        </a>
                    @endif
                </div>
                @endauth
            </div>
        </div>

        <!-- Actions utilisateur -->
        @auth
            <!-- Section Réservations -->
            @if(auth()->user()->isAdminOrRespoResource($resource))
                <div class="data-table-card">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-calendar-alt"></i>
                            Réservations
                            <span class="badge badge-primary radius-circle">{{ $resource->reservations->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($resource->reservations->count() > 0)
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Utilisateur</th>
                                            <th>Date début</th>
                                            <th>Date fin</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resource->reservations as $reservation)
                                            <tr>
                                                <td>
                                                    <strong>{{ $reservation->user->prenom }} {{ $reservation->user->nom }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $reservation->user->email }}</small>
                                                </td>
                                                <td>
                                                    <i class="fas fa-play text-success me-1"></i>
                                                    {{ \Carbon\Carbon::parse($reservation->date_debut)->format('d/m/Y H:i') }}
                                                </td>
                                                <td>
                                                    <i class="fas fa-stop text-danger me-1"></i>
                                                    {{ \Carbon\Carbon::parse($reservation->date_fin)->format('d/m/Y H:i') }}
                                                </td>
                                                <td>
                                                    @if($reservation->statut == 'approuvée')
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check"></i> Approuvée
                                                        </span>
                                                    @elseif($reservation->statut == 'en attente')
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock"></i> En attente
                                                        </span>
                                                    @elseif($reservation->statut == 'refusée')
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times"></i> Refusée
                                                        </span>
                                                    @elseif($reservation->statut == 'active')
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-spinner"></i> Active
                                                        </span>
                                                    @elseif($reservation->statut == 'terminée')
                                                        <span class="badge badge-primary">
                                                            <i class="fas fa-check-circle"></i> Terminée
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('reservations.show', $reservation->id) }}"
                                                        class="btn btn-sm btn-outline" title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-section">
                                <i class="fas fa-calendar-times"></i>
                                <p>Aucune réservation pour cette ressource</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Section Maintenances -->
                <div class="data-table-card">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-tools"></i>
                            Maintenances
                            <span class="badge badge-primary radius-circle">{{ $resource->maintenanceSchedules->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($resource->maintenanceSchedules->count() > 0)
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Date début</th>
                                            <th>Date fin</th>
                                            <th>Statut</th>
                                            <th>Raison</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resource->maintenanceSchedules as $maintenance)
                                            <tr>
                                                <td>
                                                    <i class="fas fa-play text-info me-1"></i>
                                                    {{ \Carbon\Carbon::parse($maintenance->date_debut)->format('d/m/Y H:i') }}
                                                </td>
                                                <td>
                                                    <i class="fas fa-stop text-info me-1"></i>
                                                    {{ \Carbon\Carbon::parse($maintenance->date_fin)->format('d/m/Y H:i') }}
                                                </td>
                                                <td>
                                                    @if($maintenance->statut == 'planifiée')
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-calendar-check"></i> Planifiée
                                                        </span>
                                                    @elseif($maintenance->statut == 'en cours')
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-tools"></i> En cours
                                                        </span>
                                                    @elseif($maintenance->statut == 'terminée')
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle"></i> Terminée
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>{{ $maintenance->raison }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-section">
                                <i class="fas fa-tools"></i>
                                <p>Aucune maintenance planifiée pour cette ressource</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endauth
    </div>
    {{-- Style spécifique à la page show.blade.php --}}
    <style>
        .resource-header-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
            position: relative;
            z-index: 1;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .meta-item i {
            opacity: 0.9;
        }

        /* Carte principale de détails */
        .resource-main-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 30px;
            border: none;
            transition: transform 0.3s ease;
        }

        .resource-main-card .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px 30px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .resource-main-card .card-header h5 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .resource-main-card .card-header h5 i {
            color: #667eea;
            font-size: 1.3em;
        }

        .resource-main-card .card-body {
            padding: 30px;
        }

        /* Grille d'informations */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }

        .info-group {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            border-left: 4px solid #667eea;
            transition: background 0.3s ease;
        }

        .info-group:hover {
            background: #e9ecef;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-label i {
            color: #667eea;
            width: 20px;
        }

        .info-value {
            font-size: 1.1rem;
            color: #2c3e50;
            margin: 0;
            word-break: break-word;
        }


        /* Spécifications */
        .specifications-container {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-top: 25px;
        }

        .specifications-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .spec-item {
            background: white;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .spec-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }

        .spec-value {
            font-weight: 700;
            color: #2c3e50;
        }

        /* Cartes de tableaux */
        .data-table-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 30px;
            border: none;
        }

        .data-table-card .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px 30px;
            border-bottom: 2px solid #e9ecef;
        }

        .data-table-card .card-header h5 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .data-table-card .card-body {
            padding: 30px;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .data-table thead th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #e9ecef;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .data-table tbody td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
            transition: background 0.2s ease;
        }

        .data-table tbody tr:hover td {
            background: #f8f9fa;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Section vide */
        .empty-section {
            text-align: center;
            padding: 40px 20px;
            background: #f8f9fa;
            border-radius: 15px;
            border: 2px dashed #dee2e6;
        }

        .empty-section i {
            font-size: 3rem;
            color: #adb5bd;
            margin-bottom: 15px;
        }

        .empty-section p {
            color: #6c757d;
            font-size: 1.1rem;
            margin: 0;
        }

        /* Actions */
        .resource-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin: 30px 0;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            border: 1px solid #dee2e6;
        }
    </style>
@endsection