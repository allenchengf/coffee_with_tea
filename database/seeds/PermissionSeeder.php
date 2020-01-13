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
        ->updateOrCreate(
            ['id' => 1],
            ['name' => 'CDN Providers', 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 2],
            ['name' => 'Domains', 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 3],
            ['name' => 'Grouping', 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 4],
            ['name' => 'iRouteCDN', 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 5],
            ['name' => 'Logs', 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 6],
            ['name' => 'Auto Scan', 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 7],
            ['name' => 'Config Backup', 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 8],
            ['name' => 'Users', 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 9],
            ['name' => 'Dashboard', 'created_at' => $now]
        );
    }
}
