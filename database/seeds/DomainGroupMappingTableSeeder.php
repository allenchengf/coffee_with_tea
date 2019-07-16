<?php

use Illuminate\Database\Seeder;

class DomainGroupMappingTableSeeder extends Seeder
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
                'domain_id' => 1,
                'domain_group_id' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],[
                'id' => 2,
                'domain_id' => 3,
                'domain_group_id' => 2,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]];
        
        DB::table('domain_group_mapping')->insert($data);
    }
}
