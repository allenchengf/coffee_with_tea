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
                'domain' => 'hiero7.test1',
            ],[
                'user_group_id' => 1,
                'domain' => 'hiero7.test2',
            ],[
                'user_group_id' => 2,
                'domain' => 'rd.test1',
            ],[
                'user_group_id' => 2,
                'domain' => 'rd.test2',
            ],[
                'user_group_id' => 3,
                'domain' => 'ops.test2',
            ],[
                'user_group_id' => 3,
                'domain' => 'ops.test2',
            ],
        ];

        foreach ($data as $key => $value) {
            DB::table('domains')->insert($value);
        }
    }
}
