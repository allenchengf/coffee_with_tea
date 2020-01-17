<?php

use Faker\Generator as Faker;

$factory->define(\Hiero7\Models\CdnProvider::class, function (Faker $faker) {
    return [
        'name'          => $faker->word,
        'status'        => rand(0, 1) ? 'stop' : 'active',
        'user_group_id' => rand(1, 4),
        'ttl'           => rand(600, 604800),
        'url'           => $faker->url,
        'scannable'     => $faker->boolean
    ];
});
