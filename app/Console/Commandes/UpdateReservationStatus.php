<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\Notification;

class UpdateReservationStatus extends Command
{
    protected $signature = 'reservations:update-status';
    protected $description = 'Mettre à jour le statut des réservations en fonction des dates';

    public function handle()
    {
        $now = now();

        // Activer les réservations approuvées dont la date de début est atteinte
        $toActivate = Reservation::where('status', 'approved')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>', $now)
            ->get();

        foreach ($toActivate as $reservation) {
            $reservation->update(['status' => 'active']);
            $reservation->resource->update(['status' => 'reserved']);
            
            Notification::create([
                'user_id' => $reservation->user_id,
                'type' => 'reservation_started',
                'title' => 'Réservation activée',
                'message' => 'Votre réservation pour ' . $reservation->resource->name . ' est maintenant active.',
                'data' => ['reservation_id' => $reservation->id],
            ]);
            
            $this->info("Réservation #{$reservation->id} activée");
        }

        // Terminer les réservations actives dont la date de fin est atteinte
        $toComplete = Reservation::where('status', 'active')
            ->where('end_date', '<=', $now)
            ->get();

        foreach ($toComplete as $reservation) {
            $reservation->update(['status' => 'completed']);
            
            // Remettre la ressource disponible si pas d'autre réservation
            $hasActiveReservation = Reservation::where('resource_id', $reservation->resource_id)
                ->where('status', 'active')
                ->where('id', '!=', $reservation->id)
                ->exists();
                
            if (!$hasActiveReservation) {
                $reservation->resource->update(['status' => 'available']);
            }
            
            Notification::create([
                'user_id' => $reservation->user_id,
                'type' => 'reservation_completed',
                'title' => 'Réservation terminée',
                'message' => 'Votre réservation pour ' . $reservation->resource->name . ' est terminée.',
                'data' => ['reservation_id' => $reservation->id],
            ]);
            
            $this->info("Réservation #{$reservation->id} terminée");
        }

        // Notifier pour les réservations qui expirent bientôt (dans 3 jours)
        $expiringSoon = Reservation::where('status', 'active')
            ->whereBetween('end_date', [$now, $now->copy()->addDays(3)])
            ->get();

        foreach ($expiringSoon as $reservation) {
            $daysRemaining = $now->diffInDays($reservation->end_date);
            
            Notification::create([
                'user_id' => $reservation->user_id,
                'type' => 'reservation_expiring',
                'title' => 'Réservation expire bientôt',
                'message' => "Votre réservation pour {$reservation->resource->name} expire dans {$daysRemaining} jour(s).",
                'data' => ['reservation_id' => $reservation->id],
            ]);
            
            $this->info("Notification d'expiration envoyée pour la réservation #{$reservation->id}");
        }

        $this->info('Mise à jour des réservations terminée!');
        return 0;
    }
}