<?php

use Illuminate\Database\Seeder;
use Hiero7\Models\Api;

class ApisTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Api $api)
    {
        $now = \Carbon\Carbon::now();

        $api
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'domains', 'created_at' => $now],
            ['id' => 1]
        );
    }
}
