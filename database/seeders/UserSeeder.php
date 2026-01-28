<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer l'administrateur id 1
        User::create([
            'nom' => 'Admin',
            'prenom' => 'System',
            'email' => 'admin@test.com',
            'password' => bcrypt('data123'),
            'role_id' => 3,
            'is_active' => true,
            'type' => 'Enseignant',
            'account_status' => 'active'
        ]);

        // Créer des responsables id 2 --> 3
        $responsables = [
            [
                'nom' => 'Martin',
                'prenom' => 'Pierre',
                'email' => 'respo1@test.com',
                'password' => bcrypt('data123'),
                'role_id' => 2,
                'is_active' => true,
                'type' => 'Ingénieur',
                'account_status' => 'active'
            ],
            [
                'nom' => 'Dubois',
                'prenom' => 'Sophie',
                'email' => 'respo2@test.com',
                'password' => bcrypt('data123'),
                'role_id' => 2,
                'is_active' => true,
                'type' => 'Enseignant',
                'account_status' => 'active'
            ]
        ];

        foreach ($responsables as $responsable) {
            User::create($responsable);
        }

        // Créer des utilisateurs internes id 4 ---> 6
        $utilisateurs = [
            [
                'nom' => 'Bernard',
                'prenom' => 'Jean',
                'email' => 'user1@test.com',
                'password' => bcrypt('data123'),
                'role_id' => 1,
                'is_active' => true,
                'type' => 'Ingénieur',
                'account_status' => 'active'
            ],
            [
                'nom' => 'Leroy',
                'prenom' => 'Marie',
                'email' => 'user2@test.com',
                'password' => bcrypt('data123'),
                'role_id' => 1,
                'is_active' => true,
                'type' => 'Doctorant',
                'account_status' => 'active'
            ],
            [
                'nom' => 'Petit',
                'prenom' => 'Thomas',
                'email' => 'user3@test.com',
                'password' => bcrypt('data123'),
                'role_id' => 1,
                'is_active' => true,
                'type' => 'Enseignant',
                'account_status' => 'active'
            ]
        ];

        foreach ($utilisateurs as $utilisateur) {
            User::create($utilisateur);
        }
    }
}