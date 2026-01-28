<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\HistoryLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Afficher le formulaire de connexion
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Traiter la connexion
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Vérifier si le compte est actif
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Votre compte a été désactivé. Contactez l\'administrateur.',
                ]);
            }

            // Rediriger selon le rôle
            if ($user->isAdmin()) {
                return redirect()->route('dashboard.admin');
            } elseif ($user->isResponsable()) {
                return redirect()->route('responsable.dashboard');
            } else {
                return redirect()->route('dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'Les informations d\'identification sont incorrectes.',
        ]);
    }

    // Afficher le formulaire d'inscription
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Traiter l'inscription (pour les invités)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'type' => 'required|string|in:Ingénieur,Enseignant,Doctorant',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Créer l'utilisateur avec le rôle "Utilisateur" (id: 1 par défaut)
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 1, // Utilisateur par défaut
            'is_active' => false, // Doit être activé par l'admin
            'type' => $request->type,
            'account_status' => 'pending',
        ]);

        // Enregistrer dans l'historique
        HistoryLog::create([
            'action' => 'création',
            'user_id' => null, // Pas encore d'utilisateur connecté
            'description' => 'Nouvelle demande d\'inscription de ' . $request->prenom . ' ' . $request->nom,
            'nouvelles_valeurs' => ['email' => $request->email]
        ]);

        // Notification pour l'administrateur
        $admin = User::where('role_id', 3)->first();
        if ($admin) {
            Notification::create([
                'user_id' => $admin->id,
                'titre' => 'Nouvelle demande d\'inscription',
                'message' => $request->prenom . ' ' . $request->nom . ' a demandé un compte.',
                'type' => 'système',
                'est_lu' => false,
            ]);
        }

        return redirect()->route('login')->with('success', 'Votre demande a été envoyée. Elle sera traitée par un administrateur.');
    }

    // Déconnexion
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}