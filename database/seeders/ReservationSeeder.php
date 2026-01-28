<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Resource;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        // Réservation approuvée (passée)
        Reservation::create([
            'user_id' => 4,
            'resource_id' => 1, //Serveur HP DL380
            'date_debut' => Carbon::now()->subDays(10),
            'date_fin' => Carbon::now()->subDays(7),
            'justification' => 'Tests de performance pour projet de recherche',
            'statut' => 'terminée',
            'commentaire_responsable' => 'Réservation approuvée pour tests',
            'approuve_par' => 2,
            'approuve_le' => Carbon::now()->subDays(11)
        ]);

        // Réservation active (en cours)
        Reservation::create([
            'user_id' => 5,
            'resource_id' => 3, //VM Web Server 01
            'date_debut' => Carbon::now()->subHours(2),
            'date_fin' => Carbon::now()->addDays(2),
            'justification' => 'Développement application web',
            'statut' => 'active',
            'commentaire_responsable' => 'OK pour développement',
            'approuve_par' => 2,
            'approuve_le' => Carbon::now()->subDays(1)
        ]);

        // Réservation en attente (future)
        Reservation::create([
            'user_id' => 4,
            'resource_id' => 1,
            'date_debut' => Carbon::now()->addDays(5),
            'date_fin' => Carbon::now()->addDays(7),
            'justification' => 'Analyse de données volumineuses',
            'statut' => 'en_attente',
            'commentaire_responsable' => null,
            'approuve_par' => null,
            'approuve_le' => null
        ]);

        // Réservation refusée
        Reservation::create([
            'user_id' => 5,
            'resource_id' => 3,
            'date_debut' => Carbon::now()->addDays(10),
            'date_fin' => Carbon::now()->addDays(20),
            'justification' => 'Projet long terme',
            'statut' => 'refusée',
            'commentaire_responsable' => 'Période trop longue, merci de réduire à 7 jours max',
            'approuve_par' => 3,
            'approuve_le' => Carbon::now()->subHours(3)
        ]);
    }
}