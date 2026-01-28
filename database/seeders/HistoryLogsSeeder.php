<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Resource;
use App\Models\User;
use Carbon\Carbon;

class HistoryLogsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all(); // tous les utilisateurs
        $resources = Resource::all();

        for ($i = 0; $i < 500; $i++) {

            $user = $users->random();
            $resource = $resources->random();
            $typeUser = $user->role_id; // 'utilisateur', 'responsable', 'admin'
            $action = '';
            $description = '';
            $oldValues = null;
            $newValues = null;

            // Événements pour l'utilisateur interne
            if ($typeUser == 1) {
                $actions = ['création', 'annulation', 'modification', 'commentaire', 'réclamation'];
                $action = $actions[array_rand($actions)];
                $startDate = Carbon::now()->addDays(rand(1, 30))->format('d/m/Y H:i');
                $endDate = Carbon::now()->addDays(rand(31, 60))->format('d/m/Y H:i');

                switch ($action) {
                    case 'création':
                        $description = "Vous avez créé une réservation pour la ressource {$resource->nom} du {$startDate} au {$endDate}";
                        $newValues = [
                            'resource' => $resource->nom,
                            'date_debut' => $startDate,
                            'date_fin' => $endDate,
                            'statut' => 'en attente'
                        ];
                        break;
                    case 'annulation':
                        $description = "Vous avez annulé une réservation pour la ressource {$resource->nom} du {$startDate} au {$endDate}";
                        $oldValues = [
                            'resource' => $resource->nom,
                            'date_debut' => $startDate,
                            'date_fin' => $endDate,
                            'statut' => 'en attente'
                        ];
                        break;
                    case 'modification':
                        $description = "Vous avez modifié votre réservation pour la ressource {$resource->nom}, nouvelles dates : {$startDate} au {$endDate}";
                        $oldValues = [
                            'resource' => $resource->nom,
                            'date_debut' => Carbon::now()->subDays(rand(1, 30))->format('d/m/Y H:i'),
                            'date_fin' => Carbon::now()->subDays(rand(1, 30))->addHours(3)->format('d/m/Y H:i'),
                            'statut' => 'en attente'
                        ];
                        $newValues = [
                            'resource' => $resource->nom,
                            'date_debut' => $startDate,
                            'date_fin' => $endDate,
                            'statut' => 'en attente'
                        ];
                        break;
                    case 'commentaire':
                        $description = "Vous avez ajouté un commentaire sur la réservation de la ressource {$resource->nom}";
                        break;
                    case 'réclamation':
                        $description = "Vous avez soumis une réclamation concernant la ressource {$resource->nom}";
                        break;
                    case 'approbation':
                        $description = "Vous avez accepte un reservation sur la ressource {$resource->nom}";
                        break;
                }
            }

            // Événements pour le responsable technique
            if ($typeUser == 2) {
                $actions = ['approbation', 'refus', 'modification'];
                $action = $actions[array_rand($actions)];

                switch ($action) {
                    case 'approbation':
                        $description = "Vous avez approuvé la réservation de {$resource->nom} pour l'utilisateur {$users->random()->prenom} {$users->random()->nom}";
                        break;
                    case 'refus':
                        $description = "Vous avez refusé la réservation de {$resource->nom} pour l'utilisateur {$users->random()->prenom} {$users->random()->nom}";
                        break;
                    case 'modification':
                        $nouveauStatut = ['actif', 'maintenance', 'désactivé'][array_rand(['actif','maintenance','désactivé'])];
                        $description = "Vous avez changé le statut de la ressource {$resource->nom} à {$nouveauStatut}";
                        $oldValues = ['statut' => 'actif'];
                        $newValues = ['statut' => $nouveauStatut];
                        break;
                }
            }

            // Événements pour l'administrateur
            if ($typeUser == 3) {
                $actions = ['création', 'modification'];
                $action = $actions[array_rand($actions)];

                switch ($action) {
                    case 'création':
                        $description = "Vous avez créé la ressource {$resource->nom}";
                        $newValues = ['nom' => $resource->nom];
                        break;
                    case 'modification':
                        $userTarget = $users->random();
                        $nouveauRole = ['utilisateur', 'responsable', 'admin'][array_rand(['utilisateur','responsable','admin'])];
                        $description = "Vous avez modifié l'utilisateur {$userTarget->prenom} {$userTarget->nom}, nouveau rôle : {$nouveauRole}";
                        $oldValues = ['role' => $userTarget->role];
                        $newValues = ['role' => $nouveauRole];
                        break;
                }

                // Admin hérite aussi des actions du responsable
                if(rand(0,1)) {
                    $resAction = ['approbation', 'refus', 'modification'][array_rand(['approbation','refus','modification'])];
                    if($resAction === 'approbation') $description .= " (Admin) Vous avez approuvé une réservation.";
                    if($resAction === 'refus') $description .= " (Admin) Vous avez refusé une réservation.";
                    if($resAction === 'modification') $description .= " (Admin) Vous avez modifié le statut d'une ressource.";
                }
            }

            DB::table('history_logs')->insert([
                'action' => $action,
                'user_id' => $user->id,
                'description' => $description,
                'anciennes_valeurs' => $oldValues ? json_encode($oldValues) : null,
                'nouvelles_valeurs' => $newValues ? json_encode($newValues) : null,
                'created_at' => Carbon::now()->subDays(rand(0,30)),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}