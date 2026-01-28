<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Reservation;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer toutes les réservations
        $reservations = Reservation::all();

        // Récupérer tous les admins (role_id = 1)
        $admins = User::where('role_id', 1)->get();

        // Récupérer tous les responsables (role_id = 2)
        $responsables = User::where('role_id', 2)->get();

        foreach ($reservations as $reservation) {
            // L'utilisateur qui a effectué la réservation
            $user = $reservation->user;

            // Le responsable du resource lié à la réservation (si présent)
            $responsable = $reservation->resource->supervisor ?? null;

            // Créer la liste des utilisateurs autorisés à commenter
            $allowedUsers = collect([$user])
                ->merge($admins)
                ->when($responsable, fn($collection) => $collection->push($responsable))
                ->unique('id');

            // Créer 1 à 3 messages aléatoires pour chaque réservation
            $messagesCount = rand(2, 3);
            for ($i = 0; $i < $messagesCount; $i++) {
                $messageUser = $allowedUsers->random();

                DB::table('conversations')->insert([
                    'reservation_id' => $reservation->id,
                    'user_id' => $messageUser->id,
                    'message' => 'Message factice ' . ($i + 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
