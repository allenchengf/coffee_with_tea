<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CdnProviderSeeder::class);
        $this->call(SchemeTableSeeder::class);
        $this->call(ContinentTableSeeder::class);
        $this->call(CountryTableSeeder::class);
        $this->call(NetworkTableSeeder::class);
        $this->call(LocationNetworkTableSeeder::class);
        $this->call(ScanPlatformTableSeeder::class);
    }
}
