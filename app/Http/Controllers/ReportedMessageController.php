<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportedMessage;
use App\Models\Resource;
use Illuminate\Support\Facades\Auth;

class ReportedMessageController extends Controller
{
    // Afficher tous les messages signalés
    public function index(Request $request)
    {
        $superviseurId = auth()->id(); // ID du responsable connecté

        $query = ReportedMessage::with(['user', 'resource'])
            ->whereHas('resource', function ($q) use ($superviseurId) {
                $q->where('responsable_id', $superviseurId);
            })
            ->orderBy('created_at', 'desc');

        // Filtrage par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filtrage par ressource
        if ($request->filled('resource_id')) {
            $query->where('resource_id', $request->resource_id);
        }

        // Filtrage par statut "lu/non lu"
        if ($request->filled('est_lu')) {
            $isRead = $request->est_lu == '1' ? true : false;
            $query->where('est_lu', $isRead);
        }

        $messages = $query->paginate(10);

        return view('reports.index', compact('messages'));
    }


    // Afficher le formulaire pour créer un nouveau message signalé
    public function create()
    {
        // Récupérer toutes les ressources (ou seulement celles disponibles)
        $resources = Resource::all();

        // Retourner la vue avec les ressources
        return view('reported-messages.create', compact('resources'));
    }

    // Enregistrer le message signalé dans la base de données
    public function store(Request $request)
    {
        // Validation des champs
        $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'message' => 'required|string|max:1000',
        ]);

        // Création du message signalé
        ReportedMessage::create([
            'user_id' => Auth::id(),
            'resource_id' => $request->resource_id,
            'message' => $request->message,
        ]);

        // Redirection avec message de succès
        return redirect()->back()->with('success', 'Votre signalement a été envoyé avec succès !');
    }

    // Marquer un message comme lu
    public function markAsRead($id)
    {
        $message = ReportedMessage::findOrFail($id);
        $message->est_lu = true;
        $message->save();

        return redirect()->back()->with('success', 'Le message a été marqué comme lu.');
    }

public function destroy($id)
{
    $message = ReportedMessage::findOrFail($id);
    $message->delete();

    return redirect()->back()->with('success', 'Message supprimé avec succès.');
}
}
