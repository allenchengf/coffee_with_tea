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
            'url' => 'http://www.hiero7.com',
            'scannable' => false
        ], [
            'name' => 'Cloudflare',
            'status' => 'active',
            'user_group_id' => 1,
            'ttl' => rand(600,604800),
            'url' => 'http://www.cloudflare.com',
            'scannable' => false
        ], [
            'name' => 'CloudFront',
            'status' => 'active',
            'user_group_id' => 1,
            'ttl' => rand(600,604800),
            'url' => 'http://www.cloudFront.com',
            'scannable' => false
        ],[
            'name' => 'Akamai',
            'status' => 'active',
            'user_group_id' => 2,
            'ttl' => rand(600,604800),
            'url' => 'http://www.akamai.com',
            'scannable' => false
        ],[
            'name' => 'HostAdvice',
            'status' => 'active',
            'user_group_id' => 2,
            'ttl' => rand(600,604800),
            'url' => '',
            'scannable' => false
        ],[
            'name' => 'AlibabaCloud',
            'status' => 'stop',
            'user_group_id' => 2,
            'ttl' => rand(600,604800),
            'url' => 'http://www.alibabaCloud.com',
            'scannable' => false
        ]];
        DB::table('cdn_providers')->insert($data);
    }
}
