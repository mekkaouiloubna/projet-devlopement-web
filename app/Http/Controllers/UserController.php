<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\HistoryLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Afficher tous les utilisateurs (Admin)
    public function index()
    {
        $users = User::with('role')->paginate(15);
        $roles = Role::all();

        return view('admin.users', compact('users', 'roles'));
    }

    // Afficher le profil utilisateur
    public function show($id)
    {
        $user = User::with(['role', 'reservations.resource', 'resourcesGerees', 'notifications'])
            ->findOrFail($id);

        // Vérifier les permissions
        if (Auth::id() !== $user->id && !Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        return view('profile.index', compact('user'));
    }

    // Activer/désactiver un utilisateur (Admin)
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        $ancienStatus = $user->is_active;
        $user->is_active = !$user->is_active;
        $user->account_status = $user->is_active ? 'active' : 'suspended';
        $user->save();

        // Historique
        HistoryLog::create([
            'action' => 'Modification statut',
            'table_concernée' => 'users',
            'user_id' => Auth::id(),
            'description' => ($user->is_active ? 'Activation' : 'Désactivation') . ' de l\'utilisateur: ' . $user->email,
            'anciennes_valeurs' => ['is_active' => $ancienStatus],
            'nouvelles_valeurs' => ['is_active' => $user->is_active]
        ]);

        // Notification à l'utilisateur
        Notification::create([
            'user_id' => $user->id,
            'titre' => $user->is_active ? 'Compte activé' : 'Compte désactivé',
            'message' => $user->is_active 
                ? 'Votre compte a été activé. Vous pouvez maintenant vous connecter.' 
                : 'Votre compte a été désactivé. Contactez l\'administrateur.',
            'type' => 'système',
            'est_lu' => false,
        ]);

        return back()->with('success', 'Statut utilisateur mis à jour avec succès.');
    }

    // Mettre à jour le rôle (Admin)
    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $ancienRole = $user->role_id;
        $user->role_id = $request->role_id;
        $user->save();

        // Historique
        HistoryLog::create([
            'action' => 'Modification rôle',
            'table_concernée' => 'users',
            'user_id' => Auth::id(),
            'description' => 'Changement de rôle pour: ' . $user->email,
            'anciennes_valeurs' => ['role_id' => $ancienRole],
            'nouvelles_valeurs' => ['role_id' => $user->role_id]
        ]);

        // Notification à l'utilisateur
        Notification::create([
            'user_id' => $user->id,
            'titre' => 'Rôle mis à jour',
            'message' => 'Votre rôle a été changé en: ' . $user->role->nom,
            'type' => 'système',
            'est_lu' => false,
        ]);

        return back()->with('success', 'Rôle utilisateur mis à jour avec succès.');
    }

    // Mettre à jour le profil (Utilisateur)
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:6|confirmed',
        ]);

        // Vérifier le mot de passe actuel si changement demandé
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
            }
            
            $user->password = Hash::make($request->password);
        }

        $anciennesValeurs = $user->only(['nom', 'prenom', 'email']);
        $user->nom = $validated['nom'];
        $user->prenom = $validated['prenom'];
        $user->email = $validated['email'];
        $user->save();

        // Historique
        HistoryLog::create([
            'action' => 'Mise à jour profil',
            'table_concernée' => 'users',
            'user_id' => $user->id,
            'description' => 'Mise à jour du profil utilisateur',
            'anciennes_valeurs' => $anciennesValeurs,
            'nouvelles_valeurs' => $user->only(['nom', 'prenom', 'email'])
        ]);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}