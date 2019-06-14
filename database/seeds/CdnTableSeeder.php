<?php

use Illuminate\Database\Seeder;

class CdnTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = 6;
        for ($i = 1; $i <= $count; $i++) {
            $data = [[
                'domain_id' => $i,
                'cdn_provider_id' => 1,
                'cname' => 'speedlll.com',
                'default' => 1,
            ], [
                'domain_id' => $i,
                'cdn_provider_id' => 2,
                'cname' => 'dnspod.com',
                'default' => 0,
            ], [
                'domain_id' => $i,
                'cdn_provider_id' => 3,
                'cname' => str_random(6) . '.com',
                'default' => 0,
            ]];
            DB::table('cdns')->insert($data);
        }
    }
}
