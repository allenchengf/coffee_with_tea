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

        $countRoles = 4; // Role 個數
        $countPermissions = 8; // Sidebar 個數
        for ($i = 0; $i < $countRoles; $i++) {
            $role_id = $i + 1;
            $addInterval = $i * $countPermissions;

            for ($j = 1; $j <= $countPermissions; $j++) {
                $rolePermissionMapping
                ->updateOrCreate(
                    ['id' => $j + $addInterval],
                    ['role_id' => $role_id, 'permission_id' => $j, 'actions' => '{"read":1,"create":1,"update":1,"delete":1}', 'created_at' => $now]
                );
            }
        }
        
    }
}
