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
                'name' => 'hiero7.test1',
                'cname' => 'hiero7.test1',
            ], [
                'user_group_id' => 1,
                'name' => 'hiero7.test2',
                'cname' => 'hiero7.test2',
            ], [
                'user_group_id' => 2,
                'name' => 'rd.test1',
                'cname' => 'rd.test1',
            ], [
                'user_group_id' => 2,
                'name' => 'rd.test2',
                'cname' => 'rd.test2',
            ], [
                'user_group_id' => 3,
                'name' => 'ops.test1',
                'cname' => 'ops.test1',
            ], [
                'user_group_id' => 3,
                'name' => 'ops.test2',
                'cname' => 'ops.test2',
            ],
        ];

        foreach ($data as $key => $value) {
            DB::table('domains')->insert($value);
        }
    }
}
