<?php

use Illuminate\Database\Seeder;

class ContinentTableSeeder extends Seeder
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
                'name' => 'all',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ], [
                'id' => 2,
                'name' => 'africa',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],  [
                'id' => 3,
                'name' => 'america',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],  [
                'id' => 4,
                'name' => 'asia',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],  [
                'id' => 5,
                'name' => 'europe',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],  [
                'id' => 6,
                'name' => 'oceania',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]
        ];

        foreach ($data as $key => $value) {
            DB::table('continents')->insert($value);
        }
    }
}
