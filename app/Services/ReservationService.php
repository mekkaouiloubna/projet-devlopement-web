<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Resource;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function createReservation(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $resource = Resource::findOrFail($data['resource_id']);

            // Vérifier la disponibilité
            if (!$resource->isAvailableForPeriod($data['start_date'], $data['end_date'])) {
                throw new \Exception('Ressource non disponible pour cette période.');
            }

            // Créer la réservation
            $reservation = Reservation::create([
                'user_id' => $userId,
                'resource_id' => $data['resource_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'justification' => $data['justification'],
                'status' => 'pending',
            ]);

            // Notifier le responsable
            if ($resource->manager_id) {
                $this->notifyManager($resource, $reservation);
            }

            // Logger l'activité
            ActivityLog::log(
                'created',
                'Reservation',
                $reservation->id,
                'Nouvelle demande de réservation créée'
            );

            return $reservation;
        });
    }

    public function approveReservation(Reservation $reservation, $approverId)
    {
        return DB::transaction(function () use ($reservation, $approverId) {
            $reservation->update([
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);

            // Notifier l'utilisateur
            Notification::create([
                'user_id' => $reservation->user_id,
                'type' => 'reservation_approved',
                'title' => 'Réservation approuvée',
                'message' => 'Votre réservation pour ' . $reservation->resource->name . ' a été approuvée.',
                'data' => ['reservation_id' => $reservation->id],
            ]);

            ActivityLog::log(
                'approved',
                'Reservation',
                $reservation->id,
                'Réservation approuvée'
            );

            return $reservation;
        });
    }

    public function rejectReservation(Reservation $reservation, $approverId, $reason)
    {
        return DB::transaction(function () use ($reservation, $approverId, $reason) {
            $reservation->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);

            // Notifier l'utilisateur
            Notification::create([
                'user_id' => $reservation->user_id,
                'type' => 'reservation_rejected',
                'title' => 'Réservation refusée',
                'message' => 'Votre réservation pour ' . $reservation->resource->name . ' a été refusée.',
                'data' => ['reservation_id' => $reservation->id],
            ]);

            ActivityLog::log(
                'rejected',
                'Reservation',
                $reservation->id,
                'Réservation rejetée'
            );

            return $reservation;
        });
    }

    public function checkConflicts(Resource $resource, $startDate, $endDate, $excludeReservationId = null)
    {
        $query = $resource->reservations()
            ->whereIn('status', ['approved', 'active'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->get();
    }

    private function notifyManager(Resource $resource, Reservation $reservation)
    {
        Notification::create([
            'user_id' => $resource->manager_id,
            'type' => 'new_reservation',
            'title' => 'Nouvelle demande de réservation',
            'message' => $reservation->user->name . ' a demandé une réservation pour ' . $resource->name,
            'data' => ['reservation_id' => $reservation->id],
        ]);
    }

    public function getStatistics($userId = null, $role = null)
    {
        $query = Reservation::query();

        if ($userId && $role === 'user') {
            $query->where('user_id', $userId);
        }

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];
    }
}