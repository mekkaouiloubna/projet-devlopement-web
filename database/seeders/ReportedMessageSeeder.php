<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportedMessage;
use App\Models\User;
use App\Models\Resource;

class ReportedMessageSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer quelques utilisateurs et ressources existantes
        $users = User::whereIn('id', [4,5,6])->get();
        $resources = Resource::all();

        if ($users->count() === 0 || $resources->count() === 0) {
            $this->command->info('Il faut au moins un utilisateur et une ressource pour remplir les messages signalés.');
            return;
        }

        // Créer 10 messages signalés aléatoires
        for ($i = 1; $i <= 15; $i++) {
            ReportedMessage::create([
                'user_id' => $users->random()->id,
                'resource_id' => $resources->random()->id,
                'message' => 'Je signale ce message car il contient un contenu inapproprié et ne respecte pas les règles de la plateforme.',
                'est_lu' => false,
            ]);
        }
    }
}
