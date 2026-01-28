<?php

namespace App\Http\Controllers;

use App\Models\HistoryLog;
use Illuminate\Http\Request;

class HistoryLogController extends Controller
{
    // Afficher tous les journaux
    public function index(Request $request)
    {
        $query = HistoryLog::with('user');

        // Filtres
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('table_concernée')) {
            $query->where('table_concernée', $request->table_concernée);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistiques
        $stats = [
            'total_logs' => HistoryLog::count(),
            'today_logs' => HistoryLog::whereDate('created_at', today())->count(),
            'actions_types' => HistoryLog::select('action', \DB::raw('COUNT(*) as count'))
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
        ];

        return view('history.index', compact('logs', 'stats'));
    }

    // Afficher un journal spécifique
    public function show($id)
    {
        $log = HistoryLog::with('user')->findOrFail($id);
        
        // Décoder les valeurs JSON si présentes
        if ($log->anciennes_valeurs) {
            $log->anciennes_valeurs = json_decode($log->anciennes_valeurs, true);
        }
        
        if ($log->nouvelles_valeurs) {
            $log->nouvelles_valeurs = json_decode($log->nouvelles_valeurs, true);
        }

        return view('history.show', compact('log'));
    }

    // Exporter les journaux (simplifié pour académique)
    public function export(Request $request)
    {
        $logs = HistoryLog::with('user')
            ->whereDate('created_at', '>=', $request->start_date ?? today()->subMonth())
            ->whereDate('created_at', '<=', $request->end_date ?? today())
            ->get();

        // Ici, vous pourriez générer un fichier CSV ou PDF
        // Pour l'exemple académique, on va simplement afficher
        return view('history.export', compact('logs'));
    }
}