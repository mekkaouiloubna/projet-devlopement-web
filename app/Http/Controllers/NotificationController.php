<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Afficher toutes les notifications
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Marquer comme lues
        Notification::where('user_id', Auth::id())
            ->where('est_lu', false)
            ->update(['est_lu' => true]);

        return view('notifications.index', compact('notifications'));
    }

    // Marquer une notification comme lue
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->est_lu = true;
        $notification->save();

        return response()->json(['success' => true]);
    }

    // Marquer toutes les notifications comme lues
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('est_lu', false)
            ->update(['est_lu' => true]);

        return back()->with('success', 'Toutes les notifications marquées comme lues.');
    }

    // Supprimer une notification
    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->delete();

        return back()->with('success', 'Notification supprimée.');
    }

    // Supprimer toutes les notifications lues
    public function clearRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('est_lu', true)
            ->delete();

        return back()->with('success', 'Notifications lues supprimées.');
    }

    // Compter les notifications non lues (pour AJAX)
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('est_lu', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}