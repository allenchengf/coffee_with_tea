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
                'cname' => 'hiero7.test1.com',
            ], [
                'user_group_id' => 1,
                'name' => 'hiero7.test2.com',
                'cname' => 'hiero7.test2.com',
            ], [
                'user_group_id' => 2,
                'name' => 'rd.test1.com',
                'cname' => 'rd.test1.com',
            ], [
                'user_group_id' => 2,
                'name' => 'rd.test2.com',
                'cname' => 'rd.test2.com',
            ], [
                'user_group_id' => 3,
                'name' => 'ops.test1.com',
                'cname' => 'ops.test1.com',
            ], [
                'user_group_id' => 3,
                'name' => 'ops.test2.com',
                'cname' => 'ops.test2.com',
            ],
        ];

        foreach ($data as $key => $value) {
            DB::table('domains')->insert($value);
        }
    }
}
