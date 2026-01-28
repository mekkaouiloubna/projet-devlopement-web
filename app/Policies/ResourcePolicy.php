<?php

namespace App\Policies;

use App\Models\Resource;
use App\Models\User;

class ResourcePolicy
{
    public function viewAny(?User $user): bool
    {
        // Tous les utilisateurs, même non authentifiés, peuvent voir les ressources
        return true;
    }

    public function view(?User $user, Resource $resource): bool
    {
        // Tous les utilisateurs peuvent voir les détails d'une ressource
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, Resource $resource): bool
    {
        // Admin peut tout modifier
        if ($user->isAdmin()) {
            return true;
        }

        // Responsable peut modifier ses propres ressources
        return $user->isManager() && $resource->manager_id === $user->id;
    }

    public function delete(User $user, Resource $resource): bool
    {
        return $user->isAdmin();
    }
}