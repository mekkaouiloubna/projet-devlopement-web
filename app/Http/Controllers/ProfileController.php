<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $historyLogs = Auth::user()->historyLogs()->get();
        return view('profile.profile', compact('historyLogs'));
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'prenom' => 'required|string|max:50',
            'nom' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ]);
        
        $user->prenom = $request->prenom;
        $user->nom = $request->nom;
        $user->email = $request->email;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();
        
        return back()->with('success', 'Profil mis à jour avec succès.');
    }
    
    public function destroy(Request $request)
    {
        $user = Auth::user();
        
        // Supprimer les réservations associées
        $user->reservations()->delete();
        
        // Supprimer les notifications
        $user->notifications()->delete();
        
        // Déconnecter l'utilisateur
        Auth::logout();
        
        // Supprimer l'utilisateur
        $user->delete();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')->with('success', 'Votre compte a été supprimé avec succès.');
    }
}