<?php

namespace App\Http\Controllers;

use App\Models\ResourceCategory;
use Illuminate\Http\Request;
use App\Models\HistoryLog;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $search = $request->get('search');

        $categories = ResourceCategory::withCount('resources')
            ->when($search, function ($query) use ($search) {
                $query->where('nom', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->get(); 

        return view('categories.index', compact('categories', 'search'));
    }


    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => [
                'required',
                'string',
                'max:100',
                'unique:resource_categories,nom'
            ],
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $category = ResourceCategory::create([
                'nom' => $request->nom,
                'description' => $request->description
            ]);

            // Log de l'action
            HistoryLog::create([
                'action' => 'create_category',
                'user_id' => auth()->id(),
                'description' => "Catégorie créée : {$category->nom}",
                'nouvelles_valeurs' => $category->toArray()
            ]);

            return redirect()->route('categories.index')
                ->with('success', 'Catégorie créée avec succès !');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    public function edit(ResourceCategory $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, ResourceCategory $category)
    {
        $request->validate([
            'nom' => [
                'required',
                'string',
                'max:100',
                Rule::unique('resource_categories')->ignore($category->id)
            ],
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $oldData = $category->toArray();

            $category->update([
                'nom' => $request->nom,
                'description' => $request->description
            ]);

            // Log de l'action
            HistoryLog::create([
                'action' => 'update_category',
                'user_id' => auth()->id(),
                'description' => "Catégorie modifiée : {$category->nom}",
                'anciennes_valeurs' => $oldData,
                'nouvelles_valeurs' => $category->toArray()
            ]);

            return redirect()->route('categories.index')
                ->with('success', 'Catégorie mise à jour avec succès !');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    public function destroy(ResourceCategory $category)
    {
        // Vérifier s'il y a des ressources associées
        if ($category->resources()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Impossible de supprimer : des ressources sont associées à cette catégorie.');
        }

        try {
            $categoryName = $category->nom;

            // Log avant suppression
            HistoryLog::create([
                'action' => 'delete_category',
                'user_id' => auth()->id(),
                'description' => "Catégorie supprimée : {$categoryName}",
                'anciennes_valeurs' => $category->toArray()
            ]);

            $category->delete();

            return redirect()->route('categories.index')
                ->with('success', 'Catégorie supprimée avec succès !');
        } catch (\Exception $e) {
            return redirect()->route('categories.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}