<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $users = [4, 5, 6];
        $resourceIds = range(1, 12);
        $statuses = ['en attente', 'approuvée', 'refusée', 'active', 'terminée'];
        $justifications = [
            'Réunion de projet',
            'Travail en groupe',
            'Consultation avec le professeur',
            'Étude individuelle',
            'Séance de laboratoire',
            'Réunion administrative',
            'Réservation pour formation',
        ];

        foreach ($users as $userId) {
            for ($i = 0; $i < 10; $i++) {
                $resourceId = $resourceIds[array_rand($resourceIds)];
                $statut = $statuses[array_rand($statuses)];

                $date_debut = Carbon::now()->subDays(rand(0, 30));
                $date_fin = (clone $date_debut)->addDays(rand(1, 10));

                Reservation::create([
                    'user_id' => $userId,
                    'resource_id' => $resourceId,
                    'date_debut' => $date_debut,
                    'date_fin' => $date_fin,
                    'justification' => $justifications[array_rand($justifications)],
                    'statut' => $statut,
                    'commentaire_responsable' => $statut === 'refusée' ? 'Refusée pour test' : null,
                    'approuve_par' => in_array($statut, ['approuvée', 'active']) ? 1 : null,
                    'approuve_le' => in_array($statut, ['approuvée', 'active']) ? Carbon::now() : null,
                ]);
            }
        }
    }
}
