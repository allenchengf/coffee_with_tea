<?php

use Illuminate\Database\Seeder;

class DomainGroupTableSeeder extends Seeder
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
                'user_group_id' => 1,
                'name'=>'Group1',
                'label' =>'This is Group1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]];
        
        DB::table('domain_groups')->insert($data);
    }
}
