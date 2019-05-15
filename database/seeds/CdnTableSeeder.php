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
                'name' => 'hiero7',
                'cname' => 'speedlll.com',
                'ttl' => 600,
                'default' => 1,
            ], [
                'domain_id' => $i,
                'name' => 'dnspod',
                'cname' => 'dnspod.com',
                'ttl' => 600,
                'default' => 0,
            ], [
                'domain_id' => $i,
                'name' => str_random(6),
                'cname' => str_random(6) . '.com',
                'ttl' => 600,
                'default' => 0,
            ]];
            DB::table('cdns')->insert($data);
        }
    }
}
