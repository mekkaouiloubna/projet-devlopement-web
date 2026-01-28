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
    // Afficher toutes les maintenances
    public function index()
    {
        $maintenances = MaintenanceSchedule::with('resource')
            ->orderBy('date_debut', 'asc')
            ->paginate(10);

        return view('maintenance.index', compact('maintenances'));
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

    // Enregistrer une nouvelle maintenance
    public function store(Request $request)
    {
        $validated = $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'date_debut' => 'required|date|after_or_equal:now',
            'date_fin' => 'required|date|after:date_debut',
            'raison' => 'required|string|min:10|max:500',
            'statut' => 'required|in:planifiée,en_cours,terminée',
        ]);

        // Vérifier les conflits avec les réservations
        $resource = Resource::findOrFail($validated['resource_id']);
        
        $conflictingReservations = \App\Models\Reservation::where('resource_id', $validated['resource_id'])
            ->whereIn('statut', ['approuvée', 'active', 'en_attente'])
            ->where(function($q) use ($validated) {
                $q->whereBetween('date_debut', [$validated['date_debut'], $validated['date_fin']])
                  ->orWhereBetween('date_fin', [$validated['date_debut'], $validated['date_fin']])
                  ->orWhere(function($q2) use ($validated) {
                      $q2->where('date_debut', '<=', $validated['date_debut'])
                         ->where('date_fin', '>=', $validated['date_fin']);
                  });
            })->get();

        if ($conflictingReservations->count() > 0) {
            return back()->withInput()->with('error', 
                'Cette ressource a des réservations pendant cette période. Veuillez annuler ou déplacer les réservations d\'abord.');
        }

        // Créer la maintenance
        $maintenance = MaintenanceSchedule::create($validated);

        // Mettre à jour le statut de la ressource
        $resource->statut = 'maintenance';
        $resource->save();

        // Historique
        HistoryLog::create([
            'action' => 'Création maintenance',
            'table_concernée' => 'maintenance_schedules',
            'user_id' => Auth::id(),
            'description' => 'Maintenance planifiée pour: ' . $resource->nom,
            'nouvelles_valeurs' => $maintenance->toArray()
        ]);

        // Notifications
        // À tous les utilisateurs ayant des réservations futures
        $futureReservations = \App\Models\Reservation::where('resource_id', $validated['resource_id'])
            ->where('date_debut', '>', Carbon::now())
            ->whereIn('statut', ['approuvée', 'en_attente'])
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
                $reservation->update(['statut' => 'en_attente']);
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
            'statut' => 'required|in:planifiée,en_cours,terminée',
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
            'action' => 'Modification maintenance',
            'table_concernée' => 'maintenance_schedules',
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
            'action' => 'Suppression maintenance',
            'table_concernée' => 'maintenance_schedules',
            'user_id' => Auth::id(),
            'description' => 'Maintenance supprimée pour: ' . $resource->nom,
            'anciennes_valeurs' => $anciennesValeurs
        ]);

        return redirect()->route('maintenance.index')
            ->with('success', 'Maintenance supprimée avec succès.');
    }
}