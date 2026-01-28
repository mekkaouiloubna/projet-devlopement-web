<?php

namespace App\Http\Controllers;

use App\Models\HistoryLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;

class HistoryLogController extends Controller
{
    /**
     * Display a listing of the history logs.
     */
    // Dans HistoryLogController, modifiez la méthode index :
    public function index(Request $request): View
    {
        // Récupérer tous les logs d'historique avec les relations utilisateur
        $query = HistoryLog::with(['user.role']);

        // Appliquer les filtres
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $historyLogs = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        // Données pour les filtres
        $actions = HistoryLog::select('action')->distinct()->pluck('action');
        $users = User::whereHas('historyLogs')->select('id', 'prenom', 'nom', 'email')->get();

        return view('history.index', compact('historyLogs', 'actions', 'users'));
    }

    /**
     * API endpoint pour récupérer les logs (si besoin pour frontend)
     */
    public function apiIndex(Request $request)
    {
        $query = HistoryLog::with([
            'user' => function ($query) {
                $query->select('id', 'prenom', 'nom', 'email');
            }
        ]);

        // Filtrage par action (optionnel)
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // Filtrage par utilisateur (optionnel)
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filtrage par date (optionnel)
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $logs,
            'message' => 'Historique récupéré avec succès'
        ]);
    }

    /**
     * Méthode pour exporter les logs en CSV
     */
    public function exportCsv()
    {
        $logs = HistoryLog::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="history_logs_' . date('Y-m-d_H-i') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // En-têtes du CSV
            fputcsv($file, [
                'ID',
                'Date',
                'Action',
                'Utilisateur',
                'Description',
                'Anciennes valeurs',
                'Nouvelles valeurs',
                'Créé le',
                'Mis à jour le'
            ]);

            // Données
            foreach ($logs as $log) {
                $userName = $log->user
                    ? $log->user->prenom . ' ' . $log->user->nom
                    : 'Utilisateur inconnu';

                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('d/m/Y H:i'),
                    $log->action,
                    $userName,
                    $log->description,
                    $log->anciennes_valeurs ? json_encode($log->anciennes_valeurs, JSON_UNESCAPED_UNICODE) : '',
                    $log->nouvelles_valeurs ? json_encode($log->nouvelles_valeurs, JSON_UNESCAPED_UNICODE) : '',
                    $log->created_at->format('d/m/Y H:i'),
                    $log->updated_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}