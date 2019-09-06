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
                'id' => null,
                'continent_id' => 1,
                'country_id' => 2,
                'location' => 'All',
                'isp' => 'All',
                'network_id' => 2,
                'mapping_value' => 'Hunan Hengyang Telecom',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => null,
                'continent_id' => 4,
                'country_id' => 2,
                'location' => 'All',
                'isp' => 'All',
                'network_id' => 1,
                'mapping_value' => 'Jiangsu Zhenjiang Telecom',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => null,
                'continent_id' => 3,
                'country_id' => 1,
                'location' => 'All',
                'isp' => 'China Unicom',
                'network_id' => 3,
                'mapping_value' => 'Zhejiang Jiaxing Unicom',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => null,
                'continent_id' => 3,
                'country_id' => 1,
                'location' => 'All',
                'isp' => 'China Telecom',
                'network_id' => 4,
                'mapping_value' => null,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => null,
                'continent_id' => 2,
                'country_id' => 2,
                'location' => 'All',
                'isp' => 'All',
                'network_id' => 15,
                'mapping_value' => null,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => null,
                'continent_id' => 3,
                'country_id' => 1,
                'location' => 'All',
                'isp' => 'All',
                'network_id' => 16,
                'mapping_value' => null,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => null,
                'continent_id' => 3,
                'country_id' => 1,
                'location' => 'Beijing',
                'isp' => 'Yidong',
                'network_id' => 101,
                'mapping_value' => null,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ], [
                'id' => null,
                'continent_id' => 3,
                'country_id' => 1,
                'location' => 'Beijing',
                'isp' => 'China Unicom',
                'network_id' => 33,
                'mapping_value' => null,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]
        ];

        foreach ($data as $key => $value) {
            DB::table('location_networks')->insert($value);
        }
    }
}
