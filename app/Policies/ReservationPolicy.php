<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return !$user->isGuest();
    }

    public function view(User $user, Reservation $reservation): bool
    {
        // Admin peut tout voir
        if ($user->isAdmin()) {
            return true;
        }

        // Responsable peut voir les réservations de ses ressources
        if ($user->isManager() && $user->canManageResource($reservation->resource_id)) {
            return true;
        }

        // Utilisateur peut voir ses propres réservations
        return $reservation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isUser() || $user->isManager() || $user->isAdmin();
    }

    public function approve(User $user, Reservation $reservation): bool
    {
        // Admin peut tout approuver
        if ($user->isAdmin()) {
            return true;
        }

        // Responsable peut approuver les réservations de ses ressources
        return $user->isManager() && $user->canManageResource($reservation->resource_id);
    }

    public function cancel(User $user, Reservation $reservation): bool
    {
        // Admin peut tout annuler
        if ($user->isAdmin()) {
            return true;
        }

        // Utilisateur peut annuler ses propres réservations en attente ou approuvées
        return $reservation->user_id === $user->id 
            && in_array($reservation->status, ['pending', 'approved']);
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->isAdmin();
    }
}