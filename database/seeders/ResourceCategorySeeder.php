<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ResourceCategory;

class ResourceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nom' => 'Serveurs Physiques',
                'description' => 'Serveurs physiques du data center'
            ],
            [
                'nom' => 'Machines Virtuelles',
                'description' => 'Instances virtuelles sur infrastructure cloud'
            ],
            [
                'nom' => 'Stockage',
                'description' => 'Baies de stockage et SAN'
            ],
            [
                'nom' => 'Équipements Réseau',
                'description' => 'Routeurs, switches, firewalls'
            ],
            [
                'nom' => 'Postes de Travail',
                'description' => 'Postes de travail haute performance'
            ]
        ];

        foreach ($categories as $category) {
            ResourceCategory::create($category);
        }
    }
}