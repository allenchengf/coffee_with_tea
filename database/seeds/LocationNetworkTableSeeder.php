<?php

use Illuminate\Database\Seeder;

class LocationNetworkTableSeeder extends Seeder
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
                'id' => 1,
                'continent_id' => 1,
                'country_id' => 2,
                'location' => 'All',
                'isp' => 'All',
                'network_id' => 2,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => 2,
                'continent_id' => 3,
                'country_id' => 1,
                'location' => 'All',
                'isp' => 'All',
                'network_id' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => 3,
                'continent_id' => 3,
                'country_id' => 1,
                'location' => 'All',
                'isp' => 'China Unicom',
                'network_id' => 3,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => 4,
                'continent_id' => 3,
                'country_id' => 1,
                'location' => 'All',
                'isp' => 'China Telecom',
                'network_id' => 4,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => 5,
                'continent_id' => 1,
                'country_id' => 2,
                'location' => 'All',
                'isp' => 'All',
                'network_id' => 15,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => 6,
                'continent_id' => 3,
                'country_id' => 1,
                'location' => 'All',
                'isp' => 'All',
                'network_id' => 16,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => 7,
                'continent_id' => 3,
                'country_id' => 1,
                'location' => 'Beijing',
                'isp' => 'Yidong',
                'network_id' => 101,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => 8,
                'continent_id' => 3,
                'country_id' => 1,
                'location' => 'Beijing',
                'isp' => 'China Unicom',
                'network_id' => 33,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]
        ];

        foreach ($data as $key => $value) {
            DB::table('location_networks')->insert($value);
        }
    }
}
