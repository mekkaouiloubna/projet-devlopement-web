<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Resource;
use App\Models\HistoryLog;
use App\Models\Notification;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReservationController extends Controller
{
    // Afficher toutes les réservations (avec filtres)
    public function index(Request $request)
    {
        $query = Reservation::with(['user', 'resource', 'approbateur']);

        // Filtrage selon l'utilisateur
        if (!Auth::user()->isAdmin() && !Auth::user()->isResponsable()) {
            $query->where('user_id', Auth::id());
        } elseif (Auth::user()->isResponsable()) {
            // Responsable: voir les réservations de ses ressources
            $query->whereHas('resource', function ($q) {
                $q->where('responsable_id', Auth::id());
            });
        }

        // Filtres supplémentaires
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('resource_id')) {
            $query->where('resource_id', $request->resource_id);
        }

        if ($request->has('date_debut')) {
            $query->whereDate('date_debut', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->whereDate('date_fin', '<=', $request->date_fin);
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(10);
        $resources = Resource::where('est_actif', true)->get();

        return view('reservations.index', compact('reservations', 'resources'));
    }

    // Afficher une réservation spécifique
    public function show($id)
    {
        $reservation = Reservation::with(['user', 'resource', 'approbateur', 'conversations.user'])
            ->findOrFail($id);

        // Vérifier les permissions
        $this->authorizeView($reservation);

        return view('reservations.show', compact('reservation'));
    }

    // Afficher le formulaire de création
    public function create(Request $request)
    {
        $resources = Resource::where('est_actif', true)
            ->where('statut', 'disponible')
            ->get();

        $resource_id = $request->get('resource_id');

        return view('reservations.create', compact('resources', 'resource_id'));
    }

    // Enregistrer une nouvelle réservation
    public function store(Request $request)
    {
        $validated = $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'date_debut' => 'required|date|after_or_equal:now',
            'date_fin' => 'required|date|after:date_debut',
            'justification' => 'required|string|min:10|max:1000',
        ]);

        // Vérifier la disponibilité
        $resource = Resource::findOrFail($validated['resource_id']);

        if (!$resource->isDisponible()) {
            return back()->withInput()->with('error', 'Cette ressource n\'est pas disponible.');
        }

        // Vérifier les conflits de réservation
        $conflict = Reservation::where('resource_id', $validated['resource_id'])
            ->where('statut', '!=', 'refusée')
            ->where(function ($q) use ($validated) {
                $q->whereBetween('date_debut', [$validated['date_debut'], $validated['date_fin']])
                    ->orWhereBetween('date_fin', [$validated['date_debut'], $validated['date_fin']])
                    ->orWhere(function ($q2) use ($validated) {
                        $q2->where('date_debut', '<=', $validated['date_debut'])
                            ->where('date_fin', '>=', $validated['date_fin']);
                    });
            })->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Cette ressource est déjà réservée pour cette période.');
        }

        // Créer la réservation
        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'resource_id' => $validated['resource_id'],
            'date_debut' => $validated['date_debut'],
            'date_fin' => $validated['date_fin'],
            'justification' => $validated['justification'],
            'statut' => 'en attente',
        ]);

        // Historique
        HistoryLog::create([
            'action' => 'création',
            'user_id' => Auth::id(),
            'description' => 'Nouvelle réservation créée pour la ressource: ' . $resource->nom,
            'nouvelles_valeurs' => $reservation->toArray()
        ]);

        // Notifications
        // Au responsable de la ressource
        if ($resource->responsable_id) {
            Notification::create([
                'user_id' => $resource->responsable_id,
                'titre' => 'Nouvelle demande de réservation',
                'message' => Auth::user()->prenom . ' ' . Auth::user()->nom . ' a demandé la ressource "' . $resource->nom . '".',
                'type' => 'réservation',
                'est_lu' => false,
            ]);
        }

        // À l'utilisateur
        Notification::create([
            'user_id' => Auth::id(),
            'titre' => 'Demande de réservation envoyée',
            'message' => 'Votre demande pour "' . $resource->nom . '" a été envoyée. Statut: En attente.',
            'type' => 'réservation',
            'est_lu' => false,
        ]);

        return redirect()->route('reservations.show', $reservation->id)
            ->with('success', 'Votre demande de réservation a été envoyée avec succès.');
    }

    public function edit($id)
    {
        $reservation = Reservation::with(['user', 'resource', 'resource.category', 'approbateur'])
            ->findOrFail($id);


        // Vérifier si la réservation peut être modifiée
        if (!$this->canBeEdited($reservation)) {
            return redirect()->route('reservations.show', $id)
                ->with('error', 'Cette réservation ne peut pas être modifiée.');
        }

        // Récupérer les ressources disponibles (pour modification si nécessaire)
        $resources = Resource::where('est_actif', true)
            ->when($reservation->statut === 'en attente', function ($query) {
                $query->where('statut', 'disponible');
            })
            ->get();

        return view('reservations.edit', compact('reservation', 'resources'));
    }


    public function update(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $reservation = Reservation::findOrFail($id);

        // Vérifier si la réservation peut être modifiée
        if (!$this->canBeEdited($reservation)) {
            return redirect()->route('reservations.show', $id)
                ->with('error', 'Cette réservation ne peut pas être modifiée.');
        }

        // Règles de validation selon le statut
        $rules = [
            'justification' => 'required|string|min:10|max:1000',
        ];

        // Si la réservation est en attente, on peut modifier les dates
        if ($reservation->statut === 'en attente') {
            $rules['date_debut'] = 'required|date|after_or_equal:now';
            $rules['date_fin'] = 'required|date|after:date_debut';

            // Vérifier si la ressource a changé (optionnel)
            if ($request->has('resource_id') && $request->resource_id != $reservation->resource_id) {
                $rules['resource_id'] = 'required|exists:resources,id';
            }
        }

        $validated = $request->validate($rules);

        // Vérifier les conflits si modification des dates/ressource
        if ($reservation->statut === 'en attente') {
            $hasChanges = false;
            $changes = [];

            // Vérifier si les dates ont changé
            $currentDateDebut = $reservation->date_debut->format('Y-m-d\TH:i');
            $currentDateFin = $reservation->date_fin->format('Y-m-d\TH:i');

            if ($request->date_debut != $currentDateDebut) {
                $hasChanges = true;
                $changes['date_debut'] = [
                    'old' => $currentDateDebut,
                    'new' => $request->date_debut
                ];
            }

            if ($request->date_fin != $currentDateFin) {
                $hasChanges = true;
                $changes['date_fin'] = [
                    'old' => $currentDateFin,
                    'new' => $request->date_fin
                ];
            }

            // Vérifier si la ressource a changé
            if ($request->has('resource_id') && $request->resource_id != $reservation->resource_id) {
                $hasChanges = true;
                $changes['resource_id'] = [
                    'old' => $reservation->resource_id,
                    'new' => $request->resource_id
                ];
                $resource = Resource::findOrFail($request->resource_id);
            } else {
                $resource = $reservation->resource;
            }

            // Vérifier la justification
            if ($request->justification != $reservation->justification) {
                $hasChanges = true;
                $changes['justification'] = [
                    'old' => $reservation->justification,
                    'new' => $request->justification
                ];
            }

            if (!$hasChanges) {
                return redirect()->route('reservations.edit', $id)
                    ->with('info', 'Aucune modification détectée.');
            }

            // Vérifier la disponibilité de la ressource (si changement de ressource ou dates)
            if (isset($changes['resource_id']) || isset($changes['date_debut']) || isset($changes['date_fin'])) {
                // Définir les dates à vérifier
                $dateDebut = $request->date_debut ?? $currentDateDebut;
                $dateFin = $request->date_fin ?? $currentDateFin;
                $resourceId = $request->resource_id ?? $reservation->resource_id;

                // Vérifier les conflits, en excluant la réservation actuelle
                $conflict = Reservation::where('resource_id', $resourceId)
                    ->where('id', '!=', $reservation->id)
                    ->whereIn('statut', ['en attente', 'approuvée'])
                    ->where(function ($q) use ($dateDebut, $dateFin) {
                        $q->whereBetween('date_debut', [$dateDebut, $dateFin])
                            ->orWhereBetween('date_fin', [$dateDebut, $dateFin])
                            ->orWhere(function ($q2) use ($dateDebut, $dateFin) {
                                $q2->where('date_debut', '<=', $dateDebut)
                                    ->where('date_fin', '>=', $dateFin);
                            });
                    })->exists();

                if ($conflict) {
                    return back()->withInput()
                        ->with('error', 'Cette ressource est déjà réservée pour cette période.');
                }

                // Vérifier si la ressource est disponible
                if (!$resource->isDisponible()) {
                    return back()->withInput()
                        ->with('error', 'Cette ressource n\'est pas disponible.');
                }
            }
        }

        // Sauvegarder les anciennes valeurs pour l'historique
        $anciennesValeurs = $reservation->toArray();

        // Mettre à jour la réservation
        $updateData = [
            'justification' => $validated['justification'],
        ];

        if ($reservation->statut === 'en attente') {
            $updateData['date_debut'] = $request->date_debut;
            $updateData['date_fin'] = $request->date_fin;

            if ($request->has('resource_id') && $request->resource_id != $reservation->resource_id) {
                $updateData['resource_id'] = $request->resource_id;
            }
        }

        $reservation->update($updateData);

        // Enregistrer dans l'historique
        HistoryLog::create([
            'action' => 'modification',
            'user_id' => Auth::id(),
            'description' => 'Réservation #' . $reservation->id . ' modifiée',
            'anciennes_valeurs' => $anciennesValeurs,
            'nouvelles_valeurs' => $reservation->fresh()->toArray()
        ]);

        // Mettre à jour le statut de la ressource si changement
        if (isset($changes['resource_id'])) {
            // Libérer l'ancienne ressource
            $oldResource = Resource::find($anciennesValeurs['resource_id']);
            if ($oldResource) {
                // Vérifier si d'autres réservations existent pour cette ressource
                $hasOtherReservations = Reservation::where('resource_id', $oldResource->id)
                    ->where('id', '!=', $reservation->id)
                    ->whereIn('statut', ['en attente', 'approuvée'])
                    ->exists();

                if (!$hasOtherReservations) {
                    $oldResource->update(['statut' => 'disponible']);
                }
            }

            // Réserver la nouvelle ressource
            $resource->update(['statut' => 'réservé']);
        }

        return redirect()->route('reservations.show', $reservation->id)
            ->with('success', 'Réservation mise à jour avec succès.');
    }


    public function complete(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        // Vérifier que l'utilisateur est autorisé
        $user = Auth::user();

        if (!$user->isAdminOrRespoResource($reservation->resource) && $reservation->user_id != $user->id) {
            abort(403, 'Action non autorisée.');
        }

        // Valider la requête
        $request->validate([
            'feedback' => 'nullable|string|max:500',
        ]);

        // Changer le statut
        $ancienStatut = $reservation->statut;
        $reservation->update([
            'statut' => 'terminée'
        ]);

        // Libérer la ressource
        $reservation->resource->update(['statut' => 'disponible']);

        // Historique
        HistoryLog::create([
            'action' => 'completion',
            'user_id' => Auth::id(),
            'description' => 'Réservation #' . $reservation->id . ' marquée comme terminée',
            'anciennes_valeurs' => ['statut' => $ancienStatut],
            'nouvelles_valeurs' => ['statut' => 'terminée']
        ]);

        return redirect()->route('reservations.show', $reservation->id)
            ->with('success', 'Réservation marquée comme terminée.');
    }

    /**
     * Vérifier si une réservation peut être modifiée
     *
     * @param  Reservation  $reservation
     * @return bool
     */
    private function canBeEdited($reservation)
    {
        // Seules les réservations en attente ou approuvées peuvent être modifiées
        // (avec restrictions selon le statut)
        if (!in_array($reservation->statut, ['en attente', 'approuvée'])) {
            return false;
        }

        // Si la réservation est terminée ou refusée, on ne peut pas la modifier
        if (in_array($reservation->statut, ['terminée', 'refusée'])) {
            return false;
        }

        // Vérifier si la date de début est passée
        if ($reservation->date_debut->isPast()) {
            return false;
        }

        return true;
    }

    // Approuver une réservation (Responsable/Admin)
    public function approve(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        $request->validate([
            'commentaire' => 'nullable|string|max:500',
        ]);

        $ancienStatut = $reservation->statut;
        $reservation->update([
            'statut' => 'approuvée',
            'commentaire_responsable' => $request->commentaire,
            'approuve_par' => Auth::id(),
            'approuve_le' => Carbon::now(),
        ]);

        // Historique
        HistoryLog::create([
            'action' => 'approbation',
            'user_id' => Auth::id(),
            'description' => 'Réservation approuvée pour: ' . $reservation->resource->nom,
            'anciennes_valeurs' => ['statut' => $ancienStatut],
            'nouvelles_valeurs' => ['statut' => 'approuvée']
        ]);

        // Notifications
        Notification::create([
            'user_id' => $reservation->user_id,
            'titre' => 'Réservation approuvée',
            'message' => 'Votre réservation pour "' . $reservation->resource->nom . '" a été approuvée.',
            'type' => 'réservation',
            'est_lu' => false,
        ]);

        // Mettre la ressource comme réservée
        $reservation->resource->update(['statut' => 'réservé']);

        return back()->with('success', 'Réservation approuvée avec succès.');
    }

    // Refuser une réservation (Responsable/Admin)
    public function reject(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        $request->validate([
            'commentaire' => 'required|string|min:10|max:500',
        ]);

        $ancienStatut = $reservation->statut;
        $reservation->update([
            'statut' => 'refusée',
            'commentaire_responsable' => $request->commentaire,
            'approuve_par' => Auth::id(),
            'approuve_le' => Carbon::now(),
        ]);

        // Historique
        HistoryLog::create([
            'action' => 'refus',
            'user_id' => Auth::id(),
            'description' => 'Réservation refusée pour: ' . $reservation->resource->nom,
            'anciennes_valeurs' => ['statut' => $ancienStatut],
            'nouvelles_valeurs' => ['statut' => 'refusée']
        ]);

        // Notification
        Notification::create([
            'user_id' => $reservation->user_id,
            'titre' => 'Réservation refusée',
            'message' => 'Votre réservation pour "' . $reservation->resource->nom . '" a été refusée. Raison: ' . $request->commentaire,
            'type' => 'réservation',
            'est_lu' => false,
        ]);

        return back()->with('success', 'Réservation refusée avec succès.');
    }

    // Annuler une réservation (Utilisateur)
    public function cancel($id)
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Action non autorisée.');
        }

        if (!in_array($reservation->statut, ['en attente', 'approuvée'])) {
            return back()->with('error', 'Cette réservation ne peut pas être annulée.');
        }

        $ancienStatut = $reservation->statut;
        $reservation->update(['statut' => 'terminée']);

        // Historique
        HistoryLog::create([
            'action' => 'annulation',
            'user_id' => Auth::id(),
            'description' => 'Réservation annulée pour: ' . $reservation->resource->nom,
            'anciennes_valeurs' => ['statut' => $ancienStatut],
            'nouvelles_valeurs' => ['statut' => 'terminée']
        ]);

        // Libérer la ressource si elle était approuvée
        if ($ancienStatut === 'approuvée') {
            $reservation->resource->update(['statut' => 'disponible']);
        }

        // Notifications au responsable
        if ($reservation->resource->responsable_id) {
            Notification::create([
                'user_id' => $reservation->resource->responsable_id,
                'titre' => 'Réservation annulée',
                'message' => Auth::user()->prenom . ' ' . Auth::user()->nom . ' a annulé sa réservation pour "' . $reservation->resource->nom . '".',
                'type' => 'réservation',
                'est_lu' => false,
            ]);
        }

        return back()->with('success', 'Réservation annulée avec succès.');
    }

    // Méthodes d'autorisation privées
    private function authorizeView($reservation)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isResponsable()) {
            if ($reservation->resource->responsable_id === $user->id) {
                return true;
            }
        }

        if ($reservation->user_id === $user->id) {
            return true;
        }

        abort(403, 'Accès non autorisé.');
    }

    public function addComment(Request $request, Reservation $reservation)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $user = auth()->user();

        Conversation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $user->id,
            'message' => $request->message
        ]);

        return back()->with('success', 'Commentaire ajouté avec succès.');
    }
}