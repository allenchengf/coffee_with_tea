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
            'ttl' => 600,
        ], [
            'name' => 'Cloudflare',
            'status' => 'active',
            'ttl' => 600,
        ], [
            'name' => 'CloudFront',
            'status' => 'active',
            'ttl' => 600,
        ]];
        DB::table('cdn_providers')->insert($data);
    }
}
