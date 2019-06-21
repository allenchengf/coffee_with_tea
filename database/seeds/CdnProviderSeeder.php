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
            'ttl' => rand(600,604800),
        ], [
            'name' => 'Cloudflare',
            'status' => 'active',
            'user_group_id' => 1,
            'ttl' => rand(600,604800),
        ], [
            'name' => 'CloudFront',
            'status' => 'active',
            'user_group_id' => 1,
            'ttl' => rand(600,604800),
        ],[
            'name' => 'Akamai',
            'status' => 'active',
            'user_group_id' => 2,
            'ttl' => rand(600,604800),
        ],[
            'name' => 'HostAdvice',
            'status' => 'active',
            'user_group_id' => 2,
            'ttl' => rand(600,604800),
        ],[
            'name' => 'AlibabaCloud',
            'status' => 'active',
            'user_group_id' => 2,
            'ttl' => rand(600,604800),
        ]];
        DB::table('cdn_providers')->insert($data);
    }
}
