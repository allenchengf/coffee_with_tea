<?php

use Faker\Generator as Faker;


$factory->define(\Hiero7\Models\Cdn::class, function (Faker $faker) {

    $domain = \Hiero7\Models\Domain::inRandomOrder()->first();

    return [
        'domain_id' => $domain->id,
        'name'      => $faker->name,
        'cname'     => $faker->domainName
    ];
});
