<?php

use Illuminate\Database\Seeder;

class ProductDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SchemeTableSeeder::class);
        $this->call(ContinentTableSeeder::class);
        $this->call(CountryTableSeeder::class);
        $this->call(NetworkTableSeeder::class);
        $this->call(LocationNetworkTableSeeder::class);
    }
}
