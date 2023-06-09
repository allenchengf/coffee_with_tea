<?php

use Illuminate\Database\Seeder;

class ScanPlatformTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => null,
                'name' => 'chinaz',
                'url' => 'http://10.88.55.111:3003/api/v1/chinaz/crawler',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'id' => null,
                'name' => '17ce',
                'url' => 'http://10.88.55.111:3002/api/v1/17ce/crawler',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'id' => null,
                'name' => 'boce',
                'url' => 'http://10.88.55.111:3005/api/v1/boce/crawler',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]
        ];

        foreach ($data as $key => $value) {
            DB::table('scan_platforms')->insert($value);
        }
    }
}
