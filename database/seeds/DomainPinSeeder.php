<?php

use Hiero7\Models\DomainPin;
use Illuminate\Database\Seeder;

class DomainPinSeeder extends Seeder
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
                'name' => 'hiero7',
            ],
            [
                'id' => 2,
                'user_group_id' => 2,
                'name' => 'h7rd',
            ],
            [
                'id' => 3,
                'user_group_id' => 3,
                'name' => 'ops',
            ],
            [
                'id' => 4,
                'user_group_id' => 4,
                'name' => 'cst',
            ],
        ];

        foreach ($data as $key => $value) {
            DomainPin::updateOrCreate(
                ['id' => $value['id']],
                $value
            );
        }
    }
}
