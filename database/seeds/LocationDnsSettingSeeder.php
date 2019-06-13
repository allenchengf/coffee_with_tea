<?php

use Illuminate\Database\Seeder;

class LocationDnsSettingSeeder extends Seeder
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
                'provider_record_id' => 123456,
                'location_networks_id' => 1,
                'cdn_id' => 1,
                'domain_id' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]];
        
            DB::table('location_dns_settings')->insert($data);
    }
}
