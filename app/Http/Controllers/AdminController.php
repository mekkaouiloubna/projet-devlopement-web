<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Resource;
use App\Models\User;
use App\Models\HistoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    // Tableau de bord administrateur
    public function dashboard()
    {
        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'total_resources' => Resource::count(),
            'total_reservations' => Reservation::count(),
            'pending_reservations' => Reservation::where('statut', 'en_attente')->count(),
            'active_reservations' => Reservation::where('statut', 'active')->count(),
            'pending_users' => User::where('account_status', 'pending')->count(),
        ];

        // Réservations récentes
        $recentReservations = Reservation::with(['user', 'resource'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Utilisateurs récents
        $recentUsers = User::with('role')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Statistiques d'utilisation par ressource
        $resourceUsage = DB::table('reservations')
            ->select('resources.nom', DB::raw('COUNT(reservations.id) as total_reservations'))
            ->join('resources', 'reservations.resource_id', '=', 'resources.id')
            ->where('reservations.statut', '!=', 'refusée')
            ->groupBy('resources.id', 'resources.nom')
            ->orderBy('total_reservations', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentReservations', 'recentUsers', 'resourceUsage'));
    }

    // Gérer les catégories de ressources
    public function categories()
    {
        $categories = \App\Models\ResourceCategory::withCount('resources')->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    // Gérer toutes les ressources
    public function resources()
    {
        $resources = Resource::with(['category', 'responsable'])
            ->paginate(15);
        
        return view('admin.resources', compact('resources'));
    }

    // Générer des rapports
    public function generateReport(Request $request)
    {
        $request->validate([
            'type' => 'required|in:reservations,users,resources',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);

        switch ($request->type) {
            case 'reservations':
                $data = Reservation::with(['user', 'resource', 'approbateur'])
                    ->whereBetween('created_at', [$start, $end])
                    ->get();
                $title = 'Rapport des réservations';
                break;

            case 'users':
                $data = User::with('role')
                    ->whereBetween('created_at', [$start, $end])
                    ->get();
                $title = 'Rapport des utilisateurs';
                break;

            case 'resources':
                $data = Resource::with(['category', 'responsable'])
                    ->whereBetween('created_at', [$start, $end])
                    ->get();
                $title = 'Rapport des ressources';
                break;
        }

        // Historique
        HistoryLog::create([
            'action' => 'Génération rapport',
            'table_concernée' => $request->type,
            'user_id' => Auth::id(),
            'description' => 'Génération du rapport: ' . $title . ' du ' . $start->format('d/m/Y') . ' au ' . $end->format('d/m/Y'),
        ]);

        return view('reports.create', compact('data', 'title', 'start', 'end'));
    }

    // Afficher les journaux d'activité
    public function activityLogs()
    {
        $logs = HistoryLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('history.index', compact('logs'));
    }

    // Vue d'ensemble du système
    public function systemOverview()
    {
        // Taux d'occupation des ressources
        $occupationRate = $this->calculateOccupationRate();

        // Utilisateurs par rôle
        $usersByRole = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->select('roles.nom', DB::raw('COUNT(users.id) as count'))
            ->groupBy('roles.id', 'roles.nom')
            ->get();

        // Statut des ressources
        $resourcesByStatus = DB::table('resources')
            ->select('statut', DB::raw('COUNT(*) as count'))
            ->groupBy('statut')
            ->get();

        // Réservations par statut
        $reservationsByStatus = DB::table('reservations')
            ->select('statut', DB::raw('COUNT(*) as count'))
            ->groupBy('statut')
            ->get();

        return view('admin.overview', compact(
            'occupationRate',
            'usersByRole',
            'resourcesByStatus',
            'reservationsByStatus'
        ));
    }

    // Calculer le taux d'occupation
    private function calculateOccupationRate()
    {
        $totalResources = Resource::where('est_actif', true)->count();
        $occupiedResources = Reservation::whereIn('statut', ['approuvée', 'active'])
            ->where('date_fin', '>', Carbon::now())
            ->distinct('resource_id')
            ->count('resource_id');

        if ($totalResources > 0) {
            return round(($occupiedResources / $totalResources) * 100, 2);
        }

        return 0;
    }
}