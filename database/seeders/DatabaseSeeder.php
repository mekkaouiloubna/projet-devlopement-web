<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ResourceCategorySeeder::class,
            UserSeeder::class,
            ResourceSeeder::class,
            ReservationSeeder::class,
            NotificationSeeder::class,
            MaintenanceScheduleSeeder::class,
            ReportedMessageSeeder::class,
            ConversationSeeder::class,
            HistoryLogsSeeder::class,
        ]);
    }
}