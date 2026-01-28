<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $types = ['réservation', 'maintenance', 'système', 'alerte'];

        // Récupérer tous les utilisateurs
        $users = User::all();

        foreach ($users as $user) {
            // Créer entre 3 et 7 notifications aléatoires par utilisateur
            $count = rand(3, 7);

            for ($i = 0; $i < $count; $i++) {
                DB::table('notifications')->insert([
                    'user_id' => $user->id,
                    'titre' => ucfirst($types[array_rand($types)]) . ' - Notification ' . ($i+1),
                    'message' => 'Ceci est un message de notification de type ' . $types[array_rand($types)] ,
                    'type' => $types[array_rand($types)],
                    'est_lu' => rand(0, 1), // aléatoire lu/non lu
                    'created_at' => now()->subDays(rand(0, 10)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
