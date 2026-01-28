<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Resource;
use App\Models\User;
use App\Models\HistoryLog;
use Illuminate\Http\Request;
use App\Models\MaintenanceSchedule;
use App\Models\ReportedMessage;
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
            'pending_users' => User::where('account_status', 'pending')->count(),

            'total_resources' => Resource::count(),
            'dispo_resources' => Resource::where('statut', 'disponible')->count(),
            'resources_en_maintenance' => Resource::where('statut', 'maintenance')->count(),

            'total_reservations' => Reservation::count(),
            'pending_reservations' => Reservation::where('statut', 'en attente')->count(),
            'active_reservations' => Reservation::where('statut', 'active')->count(),
            'reservation_approuvee' => Reservation::where('statut', 'approuvée')->count(),
            'reservation_terminee' => Reservation::where('statut', 'terminée')->count(),
            'reservation_refusee' => Reservation::where('statut', '')->count(),

            'total_maintenance' => MaintenanceSchedule::count(),
            'planifiee_maintenance' => MaintenanceSchedule::where('statut', 'planifiée')->count()
        ];

        // Réservations récentes
        $recentReservations = Reservation::with(['user', 'resource'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Historique 
        $history = HistoryLog::with('user')->latest()->take(3)->get();

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

        $occupationWeek = [];
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days->push($date->format('D'));
            $totalReservations = Reservation::whereDate('date_debut', '<=', $date)
                ->whereDate('date_fin', '>=', $date)
                ->count();
            $totalResources = Resource::count();
            $occupationWeek[] = $totalResources > 0 ? round(($totalReservations / $totalResources) * 100) : 0;
        }

        $occupationMonth = [];
        $days = collect();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        for ($date = $startOfMonth; $date <= $endOfMonth; $date->addDay()) {
            $days->push($date->format('d')); // رقم اليوم
            $totalReservations = Reservation::whereDate('date_debut', '<=', $date)
                ->whereDate('date_fin', '>=', $date)
                ->count();
            $totalResources = Resource::count();
            $occupationMonth[] = $totalResources > 0 ? round(($totalReservations / $totalResources) * 100) : 0;
        }

        $occupationYear = [];
        $months = collect();
        for ($i = 1; $i <= 12; $i++) {
            $months->push(Carbon::create()->month($i)->format('M')); 

            $totalReservations = Reservation::whereMonth('date_debut', '<=', $i)
                ->whereMonth('date_fin', '>=', $i)
                ->count();

            $totalResources = Resource::count();
            $occupationYear[] = $totalResources > 0 ? round(($totalReservations / $totalResources) * 100) : 0;
        }

        return view('dashboard.admin', compact('stats','history' ,'months', 'occupationYear', 'occupationMonth', 'occupationWeek', 'days', 'recentReservations', 'recentUsers', 'resourceUsage'));
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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $type = $request->type;
        $includeAll = $request->has('include_all');

        if ($includeAll) {
            // Inclure toutes les données sans filtre de date
            switch ($type) {
                case 'reservations':
                    $data = Reservation::with(['user', 'resource', 'approbateur'])->get();
                    $title = 'Rapport complet des réservations';
                    break;

                case 'users':
                    $data = User::with('role')->get();
                    $title = 'Rapport complet des utilisateurs';
                    break;

                case 'resources':
                    $data = Resource::with(['category', 'responsable'])->get();
                    $title = 'Rapport complet des ressources';
                    break;
            }

            $start = null;
            $end = null;
        } else {
            // Filtrer par dates
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);

            switch ($type) {
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
        }

        // Historique
        HistoryLog::create([
            'action' => 'création',
            'user_id' => Auth::id(),
            'description' => 'Génération du rapport: ' . $title .
                ($includeAll ? ' (toutes données)' :
                    ' du ' . ($start ? $start->format('d/m/Y') : 'N/A') .
                    ' au ' . ($end ? $end->format('d/m/Y') : 'N/A')),
        ]);

        return view('reports.create', compact('data', 'title', 'start', 'end', 'type', 'includeAll'));
    }

    // Afficher les journaux d'activité
    public function activityLogs()
    {
        $logs = HistoryLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('history.index', compact('logs'));
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
