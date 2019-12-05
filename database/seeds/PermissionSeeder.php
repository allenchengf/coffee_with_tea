<?php

use Illuminate\Database\Seeder;
use Hiero7\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Permission $permission)
    {
        $now = \Carbon\Carbon::now();

        $permission
        ->updateOrInsert(
            ['name' => 'CDN Providers', 'created_at' => $now],
            ['id' => 1]
        )
        ->updateOrInsert(
            ['name' => 'Domains', 'created_at' => $now],
            ['id' => 2]
        )
        ->updateOrInsert(
            ['name' => 'Grouping', 'created_at' => $now],
            ['id' => 3]
        )
        ->updateOrInsert(
            ['name' => 'iRouteCDN', 'created_at' => $now],
            ['id' => 4]
        )
        ->updateOrInsert(
            ['name' => 'Logs', 'created_at' => $now],
            ['id' => 5]
        )
        ->updateOrInsert(
            ['name' => 'Auto Scan', 'created_at' => $now],
            ['id' => 6]
        )
        ->updateOrInsert(
            ['name' => 'Config Backup', 'created_at' => $now],
            ['id' => 7]
        );
    }
}
