<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class LocationDnsSetting extends Model
{
    protected $table = 'location_dns_settings';
    protected $primaryKey = 'id';
    protected $fillable = ['edited_by','user_group_id', 'pod_record_id', 'location_networks_id', 'cdn_id'];

    // public function locationNetwork()
    // {
    //     return $this->belongsTo(locationNetwork::class, 'location_networks_id', 'id');
    // }

    public function dnsProviders()
    {
        return $this->belongsTo(Cdn::class, 'cdn_id', 'id');
    }
}
