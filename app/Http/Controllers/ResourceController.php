<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\HistoryLog;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourceController extends Controller
{
    // Afficher toutes les ressources (pour les invités aussi)
    public function index(Request $request)
    {
        $query = Resource::with(['category', 'responsable']);

        // Filtre par catégorie
        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        // Filtre par statut
        if ($request->has('statut') && $request->statut != '') {
            $query->where('statut', $request->statut);
        }

        // Filtre par recherche
        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Pour les invités et utilisateurs normaux, afficher seulement les ressources actives
        if (!Auth::check() || (Auth::check() && !Auth::user()->isAdmin() && !Auth::user()->isResponsable())) {
            $query->where('est_actif', true);
        }

        $resources = $query->paginate(12);
        $categories = ResourceCategory::all();

        // Statistiques
        $totalResources = Resource::count();
        $availableResources = Resource::where('statut', 'disponible')->where('est_actif', true)->count();
        $reservedResources = Resource::where('statut', 'réservé')->count();
        $maintenanceResources = Resource::where('statut', 'maintenance')->count();

        return view('resources.index', compact(
            'resources',
            'categories',
            'totalResources',
            'availableResources',
            'reservedResources',
            'maintenanceResources'
        ));
    }
    // Afficher une ressource spécifique
    public function show($id)
    {
        $resource = Resource::with(['category', 'responsable', 'reservations.user', 'maintenanceSchedules'])
            ->findOrFail($id);

        return view('resources.show', compact('resource'));
    }

    // Afficher le formulaire de création (Admin/Responsable)
    public function create()
    {
        $categories = ResourceCategory::all();
        $responsables = User::all();

        return view('resources.create', compact('categories', 'responsables'));
    }

    // Enregistrer une nouvelle ressource
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'category_id' => 'required|exists:resource_categories,id',
            'responsable_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'specifications' => 'nullable|json',
            'statut' => 'required|in:disponible,réservé,maintenance,hors_service',
            'est_actif' => 'boolean',
        ]);

        $resource = Resource::create($validated);

        // Historique
        HistoryLog::create([
            'action' => 'création',
            'table_concernée' => 'resources',
            'user_id' => Auth::id(),
            'description' => 'Création de la ressource: ' . $resource->nom,
            'nouvelles_valeurs' => $resource->toArray()
        ]);

        // Notification au responsable s'il existe
        if ($resource->responsable_id) {
            Notification::create([
                'user_id' => $resource->responsable_id,
                'titre' => 'Nouvelle ressource assignée',
                'message' => 'La ressource "' . $resource->nom . '" vous a été assignée.',
                'type' => 'système',
                'est_lu' => false,
            ]);
        }

        return redirect()->route('resources.show', $resource->id)
            ->with('success', 'Ressource créée avec succès.');
    }

    // Afficher le formulaire d'édition
    public function edit($id)
    {
        $resource = Resource::findOrFail($id);
        $user = auth()->user();

        if (
            !$user->isAdmin() &&
            !($user->isResponsable() && $user->id === $resource->responsable_id)
        ) {
            abort(403, 'Accès non autorisé');
        }

        $categories = ResourceCategory::all();
        $responsables = User::whereIn('role_id', [2, 3])->get();

        return view('resources.edit', compact('resource', 'categories', 'responsables'));
    }


    // Mettre à jour une ressource
    public function update(Request $request, $id)
    {
        $resource = Resource::findOrFail($id);
        $anciennesValeurs = $resource->toArray();

        // Validation des données du formulaire
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'category_id' => 'required|exists:resource_categories,id',
            'responsable_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'specifications' => 'nullable|json',
            'statut' => 'required|in:disponible,réservé,maintenance,hors_service',
            'est_actif' => 'boolean',
        ]);

        // Convertir les spécifications du format array au format JSON
        $specifications = [];
        if ($request->has('specs')) {
            foreach ($request->input('specs') as $spec) {
                if (!empty($spec['key']) && !empty($spec['value'])) {
                    $specifications[$spec['key']] = $spec['value'];
                }
            }
        }

        // Ajouter les spécifications converties aux données validées
        $validated['specifications'] = !empty($specifications) ? json_encode($specifications) : null;
        $validated['est_actif'] = $request->has('est_actif');

        // Mettre à jour la ressource
        $resource->update($validated);

        // Enregistrer l'action dans l'historique
        HistoryLog::create([
            'action' => 'modification',
            'table_concernée' => 'resources',
            'user_id' => Auth::id(),
            'description' => 'Modification de la ressource: ' . $resource->nom,
            'anciennes_valeurs' => $anciennesValeurs,
            'nouvelles_valeurs' => $resource->toArray()
        ]);

        // Notification si le responsable a changé
        if ($resource->wasChanged('responsable_id') && $resource->responsable_id) {
            Notification::create([
                'user_id' => $resource->responsable_id,
                'titre' => 'Ressource assignée',
                'message' => 'La ressource "' . $resource->nom . '" vous a été assignée.',
                'type' => 'système',
                'est_lu' => false,
            ]);
        }

        // Redirection avec message de succès
        return redirect()->back()
            ->with('success', 'Ressource mise à jour avec succès.');
    }

    // Supprimer une ressource
    public function destroy($id)
    {
        $resource = Resource::findOrFail($id);

        // Vérifier si la ressource a des réservations actives
        if ($resource->reservations()->whereIn('statut', ['active', 'en attente', 'approuvée'])->exists()) {
            return back()->with('error', 'Impossible de supprimer cette ressource car elle a des réservations actives.');
        }

        $anciennesValeurs = $resource->toArray();
        $nomResource = $resource->nom;
        $resource->delete();

        // Historique
        HistoryLog::create([
            'action' => 'annulation',
            'table_concernée' => 'resources',
            'user_id' => Auth::id(),
            'description' => 'Suppression de la ressource: ' . $nomResource,
            'anciennes_valeurs' => $anciennesValeurs
        ]);

        return redirect()->route('resources.index')
            ->with('success', 'Ressource supprimée avec succès.');
    }
}