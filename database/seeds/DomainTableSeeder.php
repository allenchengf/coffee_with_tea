<?php

use Illuminate\Database\Seeder;

class DomainTableSeeder extends Seeder
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
                'user_group_id' => 1,
                'name' => 'hiero7.test1.com',
                'cname' => 'hiero7test1com.1',
            ], [
                'user_group_id' => 1,
                'name' => 'hiero7.test2.com',
                'cname' => 'hiero7test2com.1',
            ],[
                'user_group_id' => 1,
                'name' => 'hiero7.test3.com',
                'cname' => 'hiero7test3com.1',
            ], [
                'user_group_id' => 2,
                'name' => 'rd.test1.com',
                'cname' => 'rdtest1com.2',
            ], [
                'user_group_id' => 2,
                'name' => 'rd.test2.com',
                'cname' => 'rdtest2com.2',
            ], 
        ];

        foreach ($data as $key => $value) {
            DB::table('domains')->insert($value);
        }
    }
}
