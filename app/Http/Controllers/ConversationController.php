<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Reservation;
use App\Models\HistoryLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    // Envoyer un message dans une conversation
    public function store(Request $request, $reservationId)
    {
        $request->validate([
            'message' => 'required|string|min:1|max:1000',
        ]);

        $reservation = Reservation::findOrFail($reservationId);

        // Vérifier les permissions
        $this->authorizeConversation($reservation);

        $message = Conversation::create([
            'reservation_id' => $reservationId,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'est_signalé' => false,
        ]);

        // Historique
        HistoryLog::create([
            'action' => 'Message conversation',
            'table_concernée' => 'conversations',
            'user_id' => Auth::id(),
            'description' => 'Nouveau message pour la réservation #' . $reservationId,
            'nouvelles_valeurs' => ['message_preview' => substr($request->message, 0, 50) . '...']
        ]);

        // Notifications aux autres participants
        $participants = Conversation::where('reservation_id', $reservationId)
            ->where('user_id', '!=', Auth::id())
            ->distinct('user_id')
            ->pluck('user_id');

        // Ajouter le propriétaire de la réservation et le responsable si différent
        if ($reservation->user_id !== Auth::id()) {
            $participants->push($reservation->user_id);
        }

        if ($reservation->resource->responsable_id && $reservation->resource->responsable_id !== Auth::id()) {
            $participants->push($reservation->resource->responsable_id);
        }

        // Envoyer les notifications
        foreach ($participants->unique() as $participantId) {
            Notification::create([
                'user_id' => $participantId,
                'titre' => 'Nouveau message',
                'message' => Auth::user()->prenom . ' a posté un nouveau message sur la réservation #' . $reservationId,
                'type' => 'alerte',
                'est_lu' => false,
            ]);
        }

        return back()->with('success', 'Message envoyé avec succès.');
    }

    // Signaler un message
    public function report($id)
    {
        $message = Conversation::findOrFail($id);
        $message->est_signalé = true;
        $message->save();

        // Historique
        HistoryLog::create([
            'action' => 'Message signalé',
            'table_concernée' => 'conversations',
            'user_id' => Auth::id(),
            'description' => 'Message signalé dans la réservation #' . $message->reservation_id,
        ]);

        // Notification au responsable de la ressource
        $reservation = Reservation::find($message->reservation_id);
        if ($reservation && $reservation->resource->responsable_id) {
            Notification::create([
                'user_id' => $reservation->resource->responsable_id,
                'titre' => 'Message signalé',
                'message' => 'Un message a été signalé dans la réservation #' . $reservation->id,
                'type' => 'alerte',
                'est_lu' => false,
            ]);
        }

        // Notification à l'administrateur
        $admin = \App\Models\User::where('role_id', 3)->first();
        if ($admin) {
            Notification::create([
                'user_id' => $admin->id,
                'titre' => 'Message signalé',
                'message' => 'Un message a été signalé par un utilisateur.',
                'type' => 'alerte',
                'est_lu' => false,
            ]);
        }

        return back()->with('success', 'Message signalé. Le responsable sera notifié.');
    }

    // Supprimer un message (Responsable/Admin)
    public function destroy($id)
    {
        $message = Conversation::findOrFail($id);
        $reservation = $message->reservation;

        // Vérifier les permissions (Responsable ou Admin)
        if (!Auth::user()->isResponsable() && !Auth::user()->isAdmin()) {
            abort(403, 'Action non autorisée.');
        }

        // Pour les responsables, vérifier qu'ils gèrent cette ressource
        if (Auth::user()->isResponsable() && $reservation->resource->responsable_id !== Auth::id()) {
            abort(403, 'Action non autorisée.');
        }

        $anciennesValeurs = $message->toArray();
        $message->delete();

        // Historique
        HistoryLog::create([
            'action' => 'Suppression message',
            'table_concernée' => 'conversations',
            'user_id' => Auth::id(),
            'description' => 'Message supprimé de la réservation #' . $reservation->id,
            'anciennes_valeurs' => $anciennesValeurs
        ]);

        // Notification à l'auteur du message
        if ($message->user_id !== Auth::id()) {
            Notification::create([
                'user_id' => $message->user_id,
                'titre' => 'Message supprimé',
                'message' => 'Votre message dans la réservation #' . $reservation->id . ' a été supprimé par un modérateur.',
                'type' => 'alerte',
                'est_lu' => false,
            ]);
        }

        return back()->with('success', 'Message supprimé avec succès.');
    }

    // Méthode d'autorisation privée
    private function authorizeConversation($reservation)
    {
        $user = Auth::user();
        
        // Admin peut participer à toutes les conversations
        if ($user->isAdmin()) {
            return true;
        }
        
        // Utilisateur peut participer à ses propres réservations
        if ($reservation->user_id === $user->id) {
            return true;
        }
        
        // Responsable peut participer aux conversations de ses ressources
        if ($user->isResponsable() && $reservation->resource->responsable_id === $user->id) {
            return true;
        }
        
        abort(403, 'Vous n\'êtes pas autorisé à participer à cette conversation.');
    }
}