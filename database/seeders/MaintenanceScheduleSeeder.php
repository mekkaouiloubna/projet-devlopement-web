<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaintenanceSchedule;
use App\Models\Resource;
use Carbon\Carbon;

class MaintenanceScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $resources = Resource::all();

        if ($resources->count() === 0) {
            $this->command->info('Il faut au moins une ressource pour créer des plannings de maintenance.');
            return;
        }

        $statuses = ['planifiée', 'en cours', 'terminée'];
        foreach ($resources as $resource) {
            // Créer 3 plannings de maintenance pour chaque ressource
            for ($i = 1; $i <= 2; $i++) {
                $start = Carbon::now()->addDays(rand(1, 30))->setTime(rand(8, 12), 0);
                $end = (clone $start)->addHours(rand(1, 4));
                MaintenanceSchedule::create([
                    'resource_id' => $resource->id,
                    'created_by' => rand(1, 3),
                    'date_debut' => $start,
                    'date_fin' => $end,
                    'raison' => 'Maintenance de routine #' . $i . ' pour la ressource ' . $resource->nom,
                    'statut' => $statuses[array_rand($statuses)],
                ]);

            }
        }
    }
}
