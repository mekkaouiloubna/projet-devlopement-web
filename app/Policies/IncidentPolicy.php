<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

class IncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return !$user->isGuest();
    }

    public function view(User $user, Incident $incident): bool
    {
        // Admin peut tout voir
        if ($user->isAdmin()) {
            return true;
        }

        // Responsable peut voir les incidents de ses ressources
        if ($user->isManager() && $user->canManageResource($incident->resource_id)) {
            return true;
        }

        // Utilisateur peut voir ses propres incidents
        return $incident->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isUser() || $user->isManager() || $user->isAdmin();
    }

    public function updateStatus(User $user, Incident $incident): bool
    {
        // Admin peut tout modifier
        if ($user->isAdmin()) {
            return true;
        }

        // Responsable peut modifier les incidents de ses ressources
        return $user->isManager() && $user->canManageResource($incident->resource_id);
    }

    public function delete(User $user, Incident $incident): bool
    {
        return $user->isAdmin();
    }
}