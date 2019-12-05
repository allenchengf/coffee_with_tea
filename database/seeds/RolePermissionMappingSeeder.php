<?php

use Illuminate\Database\Seeder;
use Hiero7\Models\RolePermissionMapping;

class RolePermissionMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(RolePermissionMapping $rolePermissionMapping)
    {
        $now = \Carbon\Carbon::now();

        $rolePermissionMapping
        ->updateOrInsert(
            ['role_id' => 1, 'permission_id' => 2, 'actions' => '{"read":1,"create":1,"update":1,"delete":1}', 'created_at' => $now],
            ['id' => 1]
        );

    }
}
