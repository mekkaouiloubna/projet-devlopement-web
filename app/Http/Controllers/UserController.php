<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Resource;
use App\Models\HistoryLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // Afficher tous les utilisateurs avec filtres (Admin) - SANS PAGINATION
    public function index(Request $request)
    {
        // Vérifier si l'utilisateur est admin
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        // Récupérer les filtres
        $roleFilter = $request->input('role');
        $statusFilter = $request->input('status');
        $search = $request->input('search');

        // Récupérer les utilisateurs en attente (pendant)
        $pendingUsers = User::where('account_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Construire la requête pour tous les utilisateurs (sauf pending في هذا الجدول)
        $users = User::with(['role', 'resourcesGerees'])
            ->where('account_status', '!=', 'pending') // استبعاد pending من الجدول الرئيسي
            ->when($roleFilter, function ($query, $roleFilter) {
                return $query->whereHas('role', function ($q) use ($roleFilter) {
                    $q->where('nom', $roleFilter);
                });
            })
            ->when($statusFilter, function ($query, $statusFilter) {
                if ($statusFilter === 'active') {
                    return $query->where('is_active', true)
                        ->where('account_status', 'active');
                } elseif ($statusFilter === 'inactive') {
                    return $query->where('is_active', false);
                } elseif ($statusFilter === 'suspended') {
                    return $query->where('account_status', 'suspended');
                } elseif ($statusFilter === 'pending') {
                    // إذا تم اختيار pending، أعرضهم في الجدول الرئيسي
                    return $query->where('account_status', 'pending');
                }
                return $query;
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nom', 'LIKE', "%{$search}%")
                        ->orWhere('prenom', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $roles = Role::all();
        $resources = Resource::where('est_actif', true)->get();

        // Statistiques
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->where('account_status', 'active')->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admin' => User::whereHas('role', function ($q) {
                $q->where('nom', 'Admin');
            })->count(),
            'responsable' => User::whereHas('role', function ($q) {
                $q->where('nom', 'Responsable');
            })->count(),
            'utilisateur' => User::whereHas('role', function ($q) {
                $q->where('nom', 'Utilisateur');
            })->count(),
        ];

        $pendingUsersCount = User::where('account_status', 'pending')->count();

        return view('admin.users.index', compact(
            'users',
            'roles',
            'resources',
            'stats',
            'request',
            'pendingUsers',
            'pendingUsersCount'
        ));
    }

    // دالة لقبول طلب التسجيل
    public function approveRegistration($id)
    {
        $user = User::findOrFail($id);

        // تحديث حالة المستخدم
        $user->account_status = 'active';
        $user->is_active = true;
        $user->save();

        // إنشاء إشعار للمستخدم
        Notification::create([
            'user_id' => $user->id,
            'titre' => 'Demande d\'inscription acceptée',
            'message' => 'Votre demande d\'inscription a été acceptée. Vous pouvez maintenant vous connecter.',
            'type' => 'système',
            'est_lu' => false,
        ]);

        // تسجيل في السجل
        HistoryLog::create([
            'action' => 'approbation',
            'user_id' => Auth::id(),
            'description' => 'Demande d\'inscription acceptée pour: ' . $user->email,
            'nouvelles_valeurs' => ['account_status' => 'active', 'is_active' => true]
        ]);

        return back()->with('success', 'Demande d\'inscription acceptée avec succès.');
    }

    // دالة لرفض طلب التسجيل
    public function rejectRegistration($id)
    {
        $user = User::findOrFail($id);
        $oldData = $user->toArray();
        $user->delete();

        HistoryLog::create([
            'action' => 'rejet',
            'user_id' => Auth::id(),
            'description' => 'Demande d\'inscription refusée pour: ' . $user->email,
            'anciennes_valeurs' => $oldData
        ]);

        return back()->with('success', 'Demande d\'inscription refusée avec succès.');
    }

    // Afficher le profil utilisateur (inchangé)
    public function show($id)
    {
        $user = User::with([
            'role',
            'reservations.resource.category',
            'resourcesGerees.category',
            'notifications',
            'historyLogs' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }
        ])->findOrFail($id);

        // Vérifier les permissions
        if (Auth::id() !== $user->id && !Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        // Statistiques
        $reservationsCount = $user->reservations()->count();
        $conversationsCount = $user->conversations()->count();

        return view('profile.show', compact(
            'user',
            'reservationsCount',
            'conversationsCount'
        ));
    }

    public function dashboard(){
        if (auth()->user()->isAdmin()) {
            return redirect()->route('dashboard.admin');
        } elseif (auth()->user()->isResponsable()) {
            return redirect()->route('responsable.dashboard');
        } else {
            return view('dashboard.user');
        }
    }

    // Activer/désactiver un utilisateur (Admin) - Inchangé
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        $ancienStatus = $user->is_active;
        $user->is_active = !$user->is_active;
        $user->account_status = $user->is_active ? 'active' : 'suspended';
        $user->save();

        // Historique
        HistoryLog::create([
            'action' => 'modification',
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

    // Mettre à jour le rôle avec gestion des ressources (Admin)
    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'resources' => 'nullable|array',
            'resources.*' => 'exists:resources,id'
        ]);

        DB::beginTransaction();

        try {
            $ancienRole = $user->role_id;
            $anciennesResources = $user->resourcesGerees->pluck('id')->toArray();

            // Mettre à jour le rôle
            $user->role_id = $request->role_id;
            $user->save();

            // Si le nouveau rôle est "Responsable", assigner les ressources
            if ($request->role_id == Role::where('nom', 'Responsable')->first()->id) {
                if ($request->has('resources')) {
                    // Retirer l'ancien responsable des ressources assignées
                    Resource::whereIn('id', $anciennesResources)
                        ->update(['responsable_id' => null]);

                    // Assigner les nouvelles ressources
                    Resource::whereIn('id', $request->resources)
                        ->update(['responsable_id' => $user->id]);
                }
            } else {
                // Si le rôle n'est plus Responsable, retirer toutes les ressources
                Resource::where('responsable_id', $user->id)
                    ->update(['responsable_id' => null]);
            }

            // Historique
            HistoryLog::create([
                'action' => 'modification',
                'user_id' => Auth::id(),
                'description' => 'Changement de rôle pour: ' . $user->email,
                'anciennes_valeurs' => [
                    'role_id' => $ancienRole,
                    'resources_gerees' => $anciennesResources
                ],
                'nouvelles_valeurs' => [
                    'role_id' => $user->role_id,
                    'resources_gerees' => $request->resources ?? []
                ]
            ]);

            // Notification à l'utilisateur
            Notification::create([
                'user_id' => $user->id,
                'titre' => 'Rôle mis à jour',
                'message' => 'Votre rôle a été changé en: ' . $user->role->nom,
                'type' => 'système',
                'est_lu' => false,
            ]);

            DB::commit();

            return back()->with('success', 'Rôle utilisateur mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour du rôle.');
        }
    }

    // Mettre à jour le profil (Utilisateur) - Inchangé
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
            'action' => 'modification',
            'user_id' => $user->id,
            'description' => 'Mise à jour du profil utilisateur',
            'anciennes_valeurs' => $anciennesValeurs,
            'nouvelles_valeurs' => $user->only(['nom', 'prenom', 'email'])
        ]);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    // Afficher le formulaire de changement de rôle avec ressources
    public function editRole($id)
    {
        $user = User::with(['role', 'resourcesGerees'])->findOrFail($id);
        $roles = Role::all();
        $resources = Resource::where('est_actif', true)->get();

        return view('admin.users.edit-role', compact('user', 'roles', 'resources'));
    }

    // Rechercher des utilisateurs (AJAX)
    public function search(Request $request)
    {
        $search = $request->input('q');

        $users = User::with('role')
            ->where('nom', 'LIKE', "%{$search}%")
            ->orWhere('prenom', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->nom . ' ' . $user->prenom . ' (' . $user->email . ')',
                    'role' => $user->role->nom,
                    'status' => $user->is_active ? 'Actif' : 'Inactif'
                ];
            });

        return response()->json($users);
    }
}