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
        // Get Domain (pagination)
        // sidebar: Domains,Group,iRoueCDN
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 1, 'created_at' => $now],
            ['id' => 1]
        )
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 1, 'created_at' => $now],
            ['id' => 2]
        )
        ->updateOrInsert(
            ['permission_id' => 4, 'api_id' => 1, 'created_at' => $now],
            ['id' => 3]
        )
        // POST Create Domain
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 2, 'created_at' => $now],
            ['id' => 4]
        )
        // POST Batch Create Domain & Cdn
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 3, 'created_at' => $now],
            ['id' => 5]
        )
        // PUT Edit Domain
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 4, 'created_at' => $now],
            ['id' => 6]
        )
        // DELETE Domain
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 5, 'created_at' => $now],
            ['id' => 7]
        )


        ;

    }
}
