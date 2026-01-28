<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nom' => 'Utilisateur', 'description' => 'Utilisateur interne (Ingénieur/Enseignant/Doctorant)'],
            ['nom' => 'Responsable', 'description' => 'Responsable technique des ressources'],
            ['nom' => 'Admin', 'description' => 'Administrateur du Data Center'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}