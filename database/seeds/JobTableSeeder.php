<?php

use Illuminate\Database\Seeder;

class JobTableSeeder extends Seeder
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
                'queue' => 'batchCreateDomainAndCdnde20afd0-d009-4fbf-a3b0-2c3257915d101',
                'payload' => '',
                'attempts' => 0,
                'reserved_at' => \Carbon\Carbon::now()->timestamp,
                'available_at' => \Carbon\Carbon::now()->timestamp,
                'created_at' => \Carbon\Carbon::now()->timestamp,
            ],[
                'id' => 2,
                'queue' => 'batchCreateDomainAndCdnde20afd0-d009-4fbf-a3b0-2c3257915d101',
                'payload' => '',
                'attempts' => 0,
                'reserved_at' => \Carbon\Carbon::now()->timestamp,
                'available_at' => \Carbon\Carbon::now()->timestamp,
                'created_at' => \Carbon\Carbon::now()->timestamp,
            ]];
        
        DB::table('jobs')->insert($data);
    }
}