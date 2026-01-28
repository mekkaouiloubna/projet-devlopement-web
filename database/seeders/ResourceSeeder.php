<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\User;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        // Serveurs Physiques
        $serveursPhysiques = [
            [
                'nom' => 'Serveur HP DL380',
                'category_id' => 1,
                'responsable_id' => 2,
                'description' => 'Serveur rack 2U, double processeur',
                'specifications' => json_encode([
                    'cpu' => 'Gold 6314U',
                    'ram' => '256 Go DDR4',
                    'stockage' => 'To SSD',
                    'os' => 'Ubuntu 22.04'
                ]),
                'statut' => 'disponible',
                'est_actif' => true
            ],
            [
                'nom' => 'Serveur Dell R740',
                'category_id' => 1,
                'responsable_id' => 3,
                'description' => 'Serveur haute performance pour calcul',
                'specifications' => json_encode([
                    'cpu' => '2x AMD EPYC 7763',
                    'ram' => '512 Go DDR4',
                    'stockage' => '960 Go SSD',
                    'os' => 'CentOS 8'
                ]),
                'statut' => 'disponible',
                'est_actif' => true
            ],
            [
                'nom' => 'Serveur Lenovo',
                'category_id' => 1,
                'responsable_id' => 2,
                'description' => 'Serveur polyvalent pour virtualisation',
                'specifications' => json_encode([
                    'cpu' => 'Intel 4314',
                    'ram' => '128 Go DDR4',
                    'stockage' => '2 To HDD',
                    'os' => 'ESXi 8'
                ]),
                'statut' => 'disponible',
                'est_actif' => true
            ],
            [
                'nom' => 'Serveur Fujitsu RX2540',
                'category_id' => 1,
                'responsable_id' => 3,
                'description' => 'Serveur pour applications critiques',
                'specifications' => json_encode([
                    'cpu' => '2x Intel Xeon Gold 6230',
                    'ram' => '384 Go DDR4',
                    'stockage' => '6x 1 To SSD',
                    'os' => 'Red Linux 9'
                ]),
                'statut' => 'maintenance',
                'est_actif' => true
            ]
        ];

        // Machines Virtuelles
        $machinesVirtuelles = [
            [
                'nom' => 'VM Web Server 01',
                'category_id' => 2,
                'responsable_id' => 2,
                'description' => 'Serveur web pour applications',
                'specifications' => json_encode([
                    'cpu' => '4 vCPU',
                    'ram' => '16 Go',
                    'stockage' => '200 Go SSD',
                    'os' => 'Ubuntu 20.04'
                ]),
                'statut' => 'disponible',
                'est_actif' => true
            ],
            [
                'nom' => 'VM Database 01',
                'category_id' => 2,
                'responsable_id' => 3,
                'description' => 'Serveur de base de données',
                'specifications' => json_encode([
                    'cpu' => '8 vCPU',
                    'ram' => '32 Go',
                    'stockage' => '500 Go SSD',
                    'os' => 'Server 2022'
                ]),
                'statut' => 'maintenance',
                'est_actif' => true
            ],
            [
                'nom' => 'VM DevOps CI',
                'category_id' => 2,
                'responsable_id' => 2,
                'description' => 'Serveur CI/CD pour pipelines',
                'specifications' => json_encode([
                    'cpu' => '6 vCPU',
                    'ram' => '24 Go',
                    'stockage' => '300Go SSD',
                    'os' => 'Ubuntu 22.04'
                ]),
                'statut' => 'disponible',
                'est_actif' => true
            ],
            [
                'nom' => 'VM Backup Server',
                'category_id' => 2,
                'responsable_id' => 3,
                'description' => 'Serveur de sauvegarde',
                'specifications' => json_encode([
                    'cpu' => '4 vCPU',
                    'ram' => '16 Go',
                    'stockage' => '2 To',
                    'os' => 'Debian 12'
                ]),
                'statut' => 'disponible',
                'est_actif' => true
            ]
        ];

        // Stockage
        $stockages = [
            [
                'nom' => 'SAN NetApp A300',
                'category_id' => 3,
                'responsable_id' => 2,
                'description' => 'Système de stockage SAN',
                'specifications' => json_encode([
                    'capacite' => '100 To',
                    'type' => 'Flash',
                    'protocoles' => 'iSCSI, CIFS'
                ]),
                'statut' => 'disponible',
                'est_actif' => true
            ],
            [
                'nom' => 'NAS Synology DS3622xs+',
                'category_id' => 3,
                'responsable_id' => 3,
                'description' => 'Système de stockage NAS',
                'specifications' => json_encode([
                    'capacite' => '48 To',
                    'type' => 'HDD',
                    'protocoles' => 'SMB, NFS'
                ]),
                'statut' => 'disponible',
                'est_actif' => true
            ]
        ];

        // Équipements Réseau
        $equipementsReseau = [
            [
                'nom' => 'Switch Cisco Nexus 9336C',
                'category_id' => 4,
                'responsable_id' => 3,
                'description' => 'Switch réseau 10/25/40/100GbE',
                'specifications' => json_encode([
                    'ports' => '36 ports',
                    'debit' => '3.6 Tbps',
                    'fabrication' => 'Cisco'
                ]),
                'statut' => 'disponible',
                'est_actif' => true
            ],
            [
                'nom' => 'Routeur Juniper MX480',
                'category_id' => 4,
                'responsable_id' => 2,
                'description' => 'Routeur haute performance',
                'specifications' => json_encode([
                    'debit' => 'up to 2 Tbps',
                    'modules' => 'various interface',
                    'fabrication' => 'Juniper'
                ]),
                'statut' => 'disponible',
                'est_actif' => true
            ]
        ];

        // Fusionner toutes les ressources
        $ressources = array_merge($serveursPhysiques, $machinesVirtuelles, $stockages, $equipementsReseau);

        foreach ($ressources as $ressource) {
            Resource::create($ressource);
        }
    }
}