<?php

use Faker\Generator as Faker;


$factory->define(\Hiero7\Models\Cdn::class, function (Faker $faker) {

    $domain = \Hiero7\Models\Domain::inRandomOrder()->first();
    $cdnProvider = \Hiero7\Models\CdnProvider::inRandomOrder()->first();

    return [
        'domain_id' => $domain->id,
        'cdn_provider_id'  => $cdnProvider->id,
        'cname'     => $faker->domainName,
        'default' => false
    ];
});
