<?php

namespace App\Http\Controllers;

use App\Models\ResourceCategory;
use App\Models\HistoryLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    // Afficher toutes les catégories
    public function index()
    {
        $categories = ResourceCategory::withCount('resources')->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    // Enregistrer une nouvelle catégorie
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:resource_categories',
            'description' => 'nullable|string',
        ]);

        $category = ResourceCategory::create($validated);

        // Historique
        HistoryLog::create([
            'action' => 'Création',
            'table_concernée' => 'resource_categories',
            'user_id' => Auth::id(),
            'description' => 'Création de la catégorie: ' . $category->nom,
            'nouvelles_valeurs' => $category->toArray()
        ]);

        // Notification aux administrateurs
        $admins = \App\Models\User::where('role_id', 3)->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'titre' => 'Nouvelle catégorie créée',
                'message' => 'La catégorie "' . $category->nom . '" a été créée.',
                'type' => 'système',
                'est_lu' => false,
            ]);
        }

        return back()->with('success', 'Catégorie créée avec succès.');
    }

    // Mettre à jour une catégorie
    public function update(Request $request, $id)
    {
        $category = ResourceCategory::findOrFail($id);
        
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:resource_categories,nom,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $anciennesValeurs = $category->toArray();
        $category->update($validated);

        // Historique
        HistoryLog::create([
            'action' => 'Modification',
            'table_concernée' => 'resource_categories',
            'user_id' => Auth::id(),
            'description' => 'Modification de la catégorie: ' . $category->nom,
            'anciennes_valeurs' => $anciennesValeurs,
            'nouvelles_valeurs' => $category->toArray()
        ]);

        return back()->with('success', 'Catégorie mise à jour avec succès.');
    }

    // Supprimer une catégorie
    public function destroy($id)
    {
        $category = ResourceCategory::findOrFail($id);

        // Vérifier si la catégorie a des ressources
        if ($category->resources()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer cette catégorie car elle contient des ressources.');
        }

        $anciennesValeurs = $category->toArray();
        $nomCategory = $category->nom;
        $category->delete();

        // Historique
        HistoryLog::create([
            'action' => 'Suppression',
            'table_concernée' => 'resource_categories',
            'user_id' => Auth::id(),
            'description' => 'Suppression de la catégorie: ' . $nomCategory,
            'anciennes_valeurs' => $anciennesValeurs
        ]);

        return back()->with('success', 'Catégorie supprimée avec succès.');
    }
}