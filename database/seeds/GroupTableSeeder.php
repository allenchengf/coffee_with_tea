<?php

use Illuminate\Database\Seeder;

class GroupTableSeeder extends Seeder
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
            ],[
                'id' => 2,
                'user_group_id' => 1,
                'name'=>'Group2',
                'label' =>'This is Group2',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]];
        
        DB::table('groups')->insert($data);
    }
}
