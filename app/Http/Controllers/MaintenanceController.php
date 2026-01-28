<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use App\Models\Resource;
use App\Models\HistoryLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    // Afficher toutes les maintenances avec filtres
    public function index(Request $request)
    {
        $query = MaintenanceSchedule::with('resource');

        // Filtre par statut
        if ($request->has('statut') && $request->statut !== '') {
            $query->where('statut', $request->statut);
        }

        // Filtre par ressource
        if ($request->has('resource_id') && $request->resource_id !== '') {
            $query->where('resource_id', $request->resource_id);
        }

        // Filtre par période
        // Filtre par période (CORRECT)
        if ($request->filled('date_debut')) {
            $query->where('date_debut', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->where('date_fin', '<=', $request->date_fin);
        }


        // Filtre par recherche textuelle
        if ($request->filled('search')) {
            $search = trim($request->search);
            $searchTerm = "%{$search}%";

            $query->where(function ($q) use ($searchTerm) {
                $q->where('raison', 'LIKE', $searchTerm)
                    ->orWhereHas('resource', function ($q2) use ($searchTerm) {
                        $q2->where('nom', 'LIKE', $searchTerm);
                    });
            });
        }

        // Tri
        $sort = $request->get('sort', 'date_debut');
        $order = $request->get('order', 'asc');
        $query->orderBy($sort, $order);

        // Récupérer toutes les données sans pagination
        $maintenances = $query->get();

        // Récupérer toutes les ressources pour le filtre
        $resources = Resource::where('est_actif', true)->get();

        // Compter les maintenances par statut
        $counts = [
            'all' => MaintenanceSchedule::count(),
            'planifiée' => MaintenanceSchedule::where('statut', 'planifiée')->count(),
            'en cours' => MaintenanceSchedule::where('statut', 'en cours')->count(),
            'terminée' => MaintenanceSchedule::where('statut', 'terminée')->count(),
        ];

        return view('maintenance.index', compact('maintenances', 'resources', 'counts'));
    }

    // Afficher une maintenance spécifique
    public function show($id)
    {
        $maintenance = MaintenanceSchedule::with('resource')->findOrFail($id);
        return view('maintenance.show', compact('maintenance'));
    }

    // Afficher le formulaire de création
    public function create(Request $request)
    {
        $resources = Resource::where('est_actif', true)->get();
        $resource_id = $request->get('resource_id');

        return view('maintenance.create', compact('resources', 'resource_id'));
    }

    // Afficher le formulaire de modification
    public function edit($id)
    {
        $maintenance = MaintenanceSchedule::with('resource')->findOrFail($id);
        $resources = Resource::where('est_actif', true)->get();

        return view('maintenance.edit', compact('maintenance', 'resources'));
    }

    // Enregistrer une nouvelle maintenance
    public function store(Request $request)
    {
        $validated = $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'date_debut' => 'required|date|after_or_equal:now',
            'date_fin' => 'required|date|after:date_debut',
            'raison' => 'required|string|min:10|max:500',
            'statut' => 'required|in:planifiée,en cours,terminée',
        ]);

        // Vérifier les conflits avec les réservations
        $resource = Resource::findOrFail($validated['resource_id']);

        $conflictingReservations = \App\Models\Reservation::where('resource_id', $validated['resource_id'])
            ->whereIn('statut', ['approuvée', 'active', 'en attente'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('date_debut', [$validated['date_debut'], $validated['date_fin']])
                    ->orWhereBetween('date_fin', [$validated['date_debut'], $validated['date_fin']])
                    ->orWhere(function ($q2) use ($validated) {
                        $q2->where('date_debut', '<=', $validated['date_debut'])
                            ->where('date_fin', '>=', $validated['date_fin']);
                    });
            })->get();

        if ($conflictingReservations->count() > 0) {
            return back()->withInput()->with(
                'error',
                'Cette ressource a des réservations pendant cette période. Veuillez annuler ou déplacer les réservations d\'abord.'
            );
        }

        // Créer la maintenance
        $maintenance = MaintenanceSchedule::create($validated);

        // Mettre à jour le statut de la ressource
        $resource->statut = 'maintenance';
        $resource->save();

        // Historique
        HistoryLog::create([
            'action' => 'création',
            'user_id' => Auth::id(),
            'description' => 'Maintenance planifiée pour: ' . $resource->nom,
            'nouvelles_valeurs' => $maintenance->toArray()
        ]);

        // Notifications
        // À tous les utilisateurs ayant des réservations futures
        $futureReservations = \App\Models\Reservation::where('resource_id', $validated['resource_id'])
            ->where('date_debut', '>', Carbon::now())
            ->whereIn('statut', ['approuvée', 'en attente'])
            ->get();

        foreach ($futureReservations as $reservation) {
            Notification::create([
                'user_id' => $reservation->user_id,
                'titre' => 'Maintenance planifiée',
                'message' => 'La ressource "' . $resource->nom . '" sera en maintenance du ' .
                    Carbon::parse($validated['date_debut'])->format('d/m/Y') . ' au ' .
                    Carbon::parse($validated['date_fin'])->format('d/m/Y') . '. Votre réservation pourrait être affectée.',
                'type' => 'maintenance',
                'est_lu' => false,
            ]);

            // Mettre à jour le statut de la réservation si nécessaire
            if ($reservation->statut === 'approuvée') {
                // Vérifier si le modèle est bien Eloquent
                if (method_exists($reservation, 'update')) {
                    $reservation->update(['statut' => 'en attente']);
                } else {
                    // Sinon mise à jour via Query Builder
                    \App\Models\Reservation::where('id', $reservation->id)
                        ->update(['statut' => 'en attente']);
                }
            }
        }


        // Notification au responsable
        if ($resource->responsable_id) {
            Notification::create([
                'user_id' => $resource->responsable_id,
                'titre' => 'Maintenance planifiée',
                'message' => 'Maintenance planifiée pour votre ressource: ' . $resource->nom,
                'type' => 'maintenance',
                'est_lu' => false,
            ]);
        }

        return redirect()->route('maintenance.show', $maintenance->id)
            ->with('success', 'Maintenance planifiée avec succès.');
    }

    // Mettre à jour une maintenance
    public function update(Request $request, $id)
    {
        $maintenance = MaintenanceSchedule::findOrFail($id);
        $resource = $maintenance->resource;

        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'raison' => 'required|string|min:10|max:500',
            'statut' => 'required|in:planifiée,en cours,terminée',
        ]);

        $anciennesValeurs = $maintenance->toArray();
        $maintenance->update($validated);

        // Mettre à jour le statut de la ressource si nécessaire
        if ($maintenance->statut === 'terminée' && $resource->statut === 'maintenance') {
            $resource->statut = 'disponible';
            $resource->save();
        }

        // Historique
        HistoryLog::create([
            'action' => 'modification',
            'user_id' => Auth::id(),
            'description' => 'Maintenance mise à jour pour: ' . $resource->nom,
            'anciennes_valeurs' => $anciennesValeurs,
            'nouvelles_valeurs' => $maintenance->toArray()
        ]);

        // Notification si maintenance terminée
        if ($maintenance->wasChanged('statut') && $maintenance->statut === 'terminée') {
            Notification::create([
                'user_id' => $resource->responsable_id ?: 0,
                'titre' => 'Maintenance terminée',
                'message' => 'La maintenance de "' . $resource->nom . '" est terminée. La ressource est de nouveau disponible.',
                'type' => 'maintenance',
                'est_lu' => false,
            ]);
        }

        return back()->with('success', 'Maintenance mise à jour avec succès.');
    }

    // Supprimer une maintenance
    public function destroy($id)
    {
        $maintenance = MaintenanceSchedule::findOrFail($id);
        $resource = $maintenance->resource;

        // Restaurer le statut de la ressource si nécessaire
        if ($resource->statut === 'maintenance') {
            $resource->statut = 'disponible';
            $resource->save();
        }

        $anciennesValeurs = $maintenance->toArray();
        $maintenance->delete();

        // Historique
        HistoryLog::create([
            'action' => 'annulation',
            'user_id' => Auth::id(),
            'description' => 'Maintenance supprimée pour: ' . $resource->nom,
            'anciennes_valeurs' => $anciennesValeurs
        ]);

        return redirect()->route('maintenance.index')
            ->with('success', 'Maintenance supprimée avec succès.');
    }
}