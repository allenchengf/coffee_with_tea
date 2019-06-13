<?php

use Faker\Generator as Faker;

$factory->define(\Hiero7\Models\LocationDnsSetting::class, function (Faker $faker) {
    
    $cdn = \Hiero7\Models\Cdn::inRandomOrder()->first();
    $locationNetwork = \Hiero7\Models\LocationNetwork::inRandomOrder()->first();

    return [
        'provider_record_id' => rand(1,999999),
        'location_networks_id'  => $locationNetwork->id,
        'cdn_id'     => $cdn->id,
        'domain_id' => $cdn->domain->id,
    ];
});
