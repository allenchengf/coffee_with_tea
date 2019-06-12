<?php

use Illuminate\Database\Seeder;

class CdnProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [[
            'name' => 'Hiero7',
            'status' => 'active',
            'user_group_id' => 1,
            'ttl' => 600,
            'user_group_id' => 1
        ], [
            'name' => 'Cloudflare',
            'status' => 'active',
            'user_group_id' => 1,
            'ttl' => 600,
            'user_group_id' => 1
        ], [
            'name' => 'CloudFront',
            'status' => 'active',
            'user_group_id' => 1,
            'ttl' => 600,
            'user_group_id' => 1
        ]];
        DB::table('cdn_providers')->insert($data);
    }
}
