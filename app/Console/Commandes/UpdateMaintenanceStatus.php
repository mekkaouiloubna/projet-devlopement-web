<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Maintenance;

class UpdateMaintenanceStatus extends Command
{
    protected $signature = 'maintenances:update-status';
    protected $description = 'Mettre à jour le statut des maintenances en fonction des dates';

    public function handle()
    {
        $now = now();

        // Démarrer les maintenances planifiées
        $toStart = Maintenance::where('status', 'scheduled')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>', $now)
            ->get();

        foreach ($toStart as $maintenance) {
            $maintenance->update(['status' => 'in_progress']);
            $maintenance->resource->update(['status' => 'maintenance']);
            
            $this->info("Maintenance #{$maintenance->id} démarrée pour {$maintenance->resource->name}");
        }

        // Terminer les maintenances en cours
        $toComplete = Maintenance::where('status', 'in_progress')
            ->where('end_date', '<=', $now)
            ->get();

        foreach ($toComplete as $maintenance) {
            $maintenance->update(['status' => 'completed']);
            $maintenance->resource->update(['status' => 'available']);
            
            $this->info("Maintenance #{$maintenance->id} terminée pour {$maintenance->resource->name}");
        }

        $this->info('Mise à jour des maintenances terminée!');
        return 0;
    }
}