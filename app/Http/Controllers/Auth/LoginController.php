<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request; // NE PAS OUBLIER CET IMPORT
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Redirection dynamique après connexion
     */
    protected function redirectTo()
    {
        $user = Auth::user();

        if (!$user->role) {
            return '/';
        }

        // Si role est une relation, on prend le nom, sinon la valeur directe
        $roleName = is_object($user->role) ? $user->role->name : $user->role;

        switch ($roleName) {
            case 'Admin':
                return route('admin.dashboard');
            case 'Responsable Technique':
                return route('tech.dashboard');
            case 'Utilisateur Interne':
                return route('user.dashboard');
            default:
                return '/';
        }
    }

    /**
     * Surcharge de la fonction login pour vérifier le statut
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Tentative de connexion avec email, password ET status active
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'status' => 'active'], $request->filled('remember'))) {
            return $this->sendLoginResponse($request);
        }

        // Vérification si l'échec est dû au statut
        $user = \App\Models\User::where('email', $request->email)->first();
        if ($user && $user->status !== 'active') {
            return back()->withErrors([
                'email' => 'Votre compte est désactivé. Veuillez contacter l\'administrateur.',
            ]);
        }

        return $this->sendFailedLoginResponse($request);
    }
}