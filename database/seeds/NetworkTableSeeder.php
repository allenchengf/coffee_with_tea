<?php

use Illuminate\Database\Seeder;

class NetworkTableSeeder extends Seeder
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
                'schemes_id' => 1,
                'name' => '默认',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ], [
                'id' => 2,
                'schemes_id' => 1,
                'name' => '国内',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ], [
                'id' => 3,
                'schemes_id' => 1,
                'name' => '国外',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ], [
                'id' => 4,
                'schemes_id' => 1,
                'name' => '电信',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ], [
                'id' => 5,
                'schemes_id' => 1,
                'name' => '联通',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ], [
                'id' => 6,
                'schemes_id' => 1,
                'name' => '教育网',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],[
                'id' => 7,
                'schemes_id' => 1,
                'name' => '移动',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],[
                'id' => 8,
                'schemes_id' => 1,
                'name' => '百度',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],[
                'id' => 9,
                'schemes_id' => 1,
                'name' => '谷歌',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],[
                'id' => 10,
                'schemes_id' => 1,
                'name' => '搜搜',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],[
                'id' => 11,
                'schemes_id' => 1,
                'name' => '有道',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],[
                'id' => 12,
                'schemes_id' => 1,
                'name' => '必应',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'id' => 13,
                'schemes_id' => 1,
                'name' => '搜狗',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'id' => 14,
                'schemes_id' => 1,
                'name' => '奇虎',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'id' => 15,
                'schemes_id' => 1,
                'name' => '搜索引擎',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
        ];

        foreach ($data as $key => $value) {
            DB::table('networks')->insert($value);
        }
    }
}
