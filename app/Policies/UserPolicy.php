<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        // Admin peut voir tous les utilisateurs
        if ($user->isAdmin()) {
            return true;
        }

        // Utilisateur peut voir son propre profil
        return $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        // Admin peut modifier tous les utilisateurs
        if ($user->isAdmin()) {
            return true;
        }

        // Utilisateur peut modifier son propre profil (limité)
        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        // Admin peut supprimer (sauf lui-même)
        return $user->isAdmin() && $user->id !== $model->id;
    }

    public function viewStatistics(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
}