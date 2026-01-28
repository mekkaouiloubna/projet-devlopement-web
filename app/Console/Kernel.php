<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\ReservationController;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Mettre à jour les statuts des réservations toutes les 5 minutes
        $schedule->call(function () {
            ReservationController::updateStatusStatic();
        })->everyFiveMinutes();

        // Vérifier les maintenances à venir et envoyer des notifications
        $schedule->call(function () {
            \App\Models\MaintenanceSchedule::where('date_debut', '<=', now()->addDay())
                ->where('date_debut', '>', now())
                ->where('notified', false)
                ->each(function ($maintenance) {
                    // Envoyer des notifications aux utilisateurs concernés
                    $reservations = \App\Models\Reservation::where('resource_id', $maintenance->resource_id)
                        ->where('date_debut', '>=', $maintenance->date_debut)
                        ->get();
                    
                    foreach ($reservations as $reservation) {
                        \App\Models\Notification::create([
                            'user_id' => $reservation->user_id,
                            'titre' => 'Maintenance proche',
                            'message' => "Maintenance planifiée pour {$maintenance->resource->nom} le {$maintenance->date_debut}",
                            'type' => 'maintenance'
                        ]);
                    }
                    
                    $maintenance->update(['notified' => true]);
                });
        })->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}