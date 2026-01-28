<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Reservation;
use App\Models\Conversation;
use App\Models\HistoryLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ResponsableController extends Controller
{
    // Tableau de bord responsable
    public function dashboard()
    {
        $user = Auth::user();

        // Ressources gérées par ce responsable
        $managedResources = Resource::where('responsable_id', $user->id)
            ->withCount(['reservations', 'maintenanceSchedules'])
            ->get();

        // Statistiques
        $stats = [
            'total_resources' => $managedResources->count(),
            'pending_reservations' => Reservation::whereHas('resource', function($q) use ($user) {
                $q->where('responsable_id', $user->id);
            })->where('statut', 'en_attente')->count(),
            'active_reservations' => Reservation::whereHas('resource', function($q) use ($user) {
                $q->where('responsable_id', $user->id);
            })->where('statut', 'active')->count(),
            'reported_messages' => Conversation::whereHas('reservation.resource', function($q) use ($user) {
                $q->where('responsable_id', $user->id);
            })->where('est_signalé', true)->count(),
        ];

        // Réservations en attente
        $pendingReservations = Reservation::with(['user', 'resource'])
            ->whereHas('resource', function($q) use ($user) {
                $q->where('responsable_id', $user->id);
            })
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Messages signalés
        $reportedMessages = Conversation::with(['user', 'reservation.resource'])
            ->whereHas('reservation.resource', function($q) use ($user) {
                $q->where('responsable_id', $user->id);
            })
            ->where('est_signalé', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.responsable', compact(
            'managedResources',
            'stats',
            'pendingReservations',
            'reportedMessages'
        ));
    }

    // Gérer les ressources du responsable
    public function myResources()
    {
        $resources = Resource::where('responsable_id', Auth::id())
            ->with(['category', 'reservations' => function($q) {
                $q->whereIn('statut', ['active', 'approuvée']);
            }])
            ->paginate(10);

        return view('responsable.resources', compact('resources'));
    }

    // Gérer les réservations pour les ressources du responsable
    public function reservations()
    {
        $reservations = Reservation::with(['user', 'resource', 'approbateur'])
            ->whereHas('resource', function($q) {
                $q->where('responsable_id', Auth::id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('responsable.reservations', compact('reservations'));
    }

    // Afficher les messages signalés
    public function reportedMessages()
    {
        $messages = Conversation::with(['user', 'reservation.resource'])
            ->whereHas('reservation.resource', function($q) {
                $q->where('responsable_id', Auth::id());
            })
            ->where('est_signalé', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('responsable.reported-messages', compact('messages'));
    }

    // Désigner un nouveau responsable pour une ressource
    public function assignResponsable(Request $request, $resourceId)
    {
        $resource = Resource::findOrFail($resourceId);

        // Vérifier que l'utilisateur actuel est le responsable
        if ($resource->responsable_id !== Auth::id()) {
            abort(403, 'Action non autorisée.');
        }

        $request->validate([
            'new_responsable_id' => 'required|exists:users,id',
        ]);

        $ancienResponsable = $resource->responsable_id;
        $nouveauResponsable = \App\Models\User::find($request->new_responsable_id);

        // Vérifier que le nouveau responsable a le bon rôle
        if (!$nouveauResponsable->isResponsable() && !$nouveauResponsable->isAdmin()) {
            return back()->with('error', 'Le nouveau responsable doit avoir le rôle Responsable ou Admin.');
        }

        $resource->responsable_id = $request->new_responsable_id;
        $resource->save();

        // Historique
        HistoryLog::create([
            'action' => 'Transfert responsabilité',
            'table_concernée' => 'resources',
            'user_id' => Auth::id(),
            'description' => 'Transfert de responsabilité pour: ' . $resource->nom,
            'anciennes_valeurs' => ['responsable_id' => $ancienResponsable],
            'nouvelles_valeurs' => ['responsable_id' => $resource->responsable_id]
        ]);

        // Notifications
        // Ancien responsable
        Notification::create([
            'user_id' => $ancienResponsable,
            'titre' => 'Responsabilité transférée',
            'message' => 'Vous n\'êtes plus responsable de: ' . $resource->nom,
            'type' => 'système',
            'est_lu' => false,
        ]);

        // Nouveau responsable
        Notification::create([
            'user_id' => $resource->responsable_id,
            'titre' => 'Nouvelle responsabilité',
            'message' => 'Vous êtes maintenant responsable de: ' . $resource->nom,
            'type' => 'système',
            'est_lu' => false,
        ]);

        return back()->with('success', 'Responsabilité transférée avec succès.');
    }

    // Générer un rapport d'utilisation des ressources
    public function resourceUsageReport($resourceId)
    {
        $resource = Resource::where('responsable_id', Auth::id())
            ->findOrFail($resourceId);

        // Statistiques d'utilisation
        $usageStats = [
            'total_reservations' => $resource->reservations()->count(),
            'approved_reservations' => $resource->reservations()->where('statut', 'approuvée')->count(),
            'active_reservations' => $resource->reservations()->where('statut', 'active')->count(),
            'completion_rate' => $resource->reservations()->whereIn('statut', ['terminée', 'active'])->count(),
            'average_duration' => $resource->reservations()
                ->where('statut', 'terminée')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, date_debut, date_fin)) as avg_hours')
                ->first()->avg_hours ?? 0,
        ];

        // Réservations récentes
        $recentReservations = $resource->reservations()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Utilisateurs fréquents
        $frequentUsers = \DB::table('reservations')
            ->select('users.id', 'users.nom', 'users.prenom', \DB::raw('COUNT(reservations.id) as reservation_count'))
            ->join('users', 'reservations.user_id', '=', 'users.id')
            ->where('reservations.resource_id', $resourceId)
            ->where('reservations.statut', '!=', 'refusée')
            ->groupBy('users.id', 'users.nom', 'users.prenom')
            ->orderBy('reservation_count', 'desc')
            ->limit(5)
            ->get();

        return view('responsable.resource-report', compact(
            'resource',
            'usageStats',
            'recentReservations',
            'frequentUsers'
        ));
    }
}