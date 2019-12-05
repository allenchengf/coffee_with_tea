<?php

use Illuminate\Database\Seeder;
use Hiero7\Models\ApiPermissionMapping;

class ApiPermissionMappingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(ApiPermissionMapping $apiPermissionMapping)
    {
        $now = \Carbon\Carbon::now();

        $apiPermissionMapping
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 1, 'created_at' => $now],
            ['id' => 1]
        );

    }
}
