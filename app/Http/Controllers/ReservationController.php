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
            ->where(function($q) use ($validated) {
                $q->whereBetween('date_debut', [$validated['date_debut'], $validated['date_fin']])
                  ->orWhereBetween('date_fin', [$validated['date_debut'], $validated['date_fin']])
                  ->orWhere(function($q2) use ($validated) {
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
            'statut' => 'en_attente',
        ]);

        // Historique
        HistoryLog::create([
            'action' => 'Création',
            'table_concernée' => 'reservations',
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

    // Approuver une réservation (Responsable/Admin)
    public function approve(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        
        $this->authorizeApprove($reservation);

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
            'action' => 'Approbation',
            'table_concernée' => 'reservations',
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
        
        $this->authorizeApprove($reservation);

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
            'action' => 'Refus',
            'table_concernée' => 'reservations',
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

        if (!in_array($reservation->statut, ['en_attente', 'approuvée'])) {
            return back()->with('error', 'Cette réservation ne peut pas être annulée.');
        }

        $ancienStatut = $reservation->statut;
        $reservation->update(['statut' => 'terminée']);

        // Historique
        HistoryLog::create([
            'action' => 'Annulation',
            'table_concernée' => 'reservations',
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

    private function authorizeApprove($reservation)
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
        
        abort(403, 'Vous n\'êtes pas autorisé à approuver cette réservation.');
    }
}