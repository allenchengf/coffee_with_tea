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
            ['role_id' => 1, 'permission_id' => 1, 'actions' => '{"read":1,"create":1,"update":1,"delete":1}', 'created_at' => $now],
            ['id' => 1]
        )
        ->updateOrInsert(
            ['role_id' => 1, 'permission_id' => 2, 'actions' => '{"read":1,"create":1,"update":1,"delete":1}', 'created_at' => $now],
            ['id' => 2]
        )
        ->updateOrInsert(
            ['role_id' => 1, 'permission_id' => 3, 'actions' => '{"read":1,"create":1,"update":1,"delete":1}', 'created_at' => $now],
            ['id' => 3]
        )
        ->updateOrInsert(
            ['role_id' => 1, 'permission_id' => 4, 'actions' => '{"read":1,"create":1,"update":1,"delete":1}', 'created_at' => $now],
            ['id' => 4]
        )
        ->updateOrInsert(
            ['role_id' => 1, 'permission_id' => 5, 'actions' => '{"read":1,"create":1,"update":1,"delete":1}', 'created_at' => $now],
            ['id' => 5]
        )
        ->updateOrInsert(
            ['role_id' => 1, 'permission_id' => 6, 'actions' => '{"read":1,"create":1,"update":1,"delete":1}', 'created_at' => $now],
            ['id' => 6]
        )
        ->updateOrInsert(
            ['role_id' => 1, 'permission_id' => 7, 'actions' => '{"read":1,"create":1,"update":1,"delete":1}', 'created_at' => $now],
            ['id' => 7]
        )
        ->updateOrInsert(
            ['role_id' => 1, 'permission_id' => 8, 'actions' => '{"read":1,"create":1,"update":1,"delete":1}', 'created_at' => $now],
            ['id' => 8]
        );

    }
}
