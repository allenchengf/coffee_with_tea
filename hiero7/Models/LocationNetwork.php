<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class LocationNetwork extends Model
{
    protected $table = 'location_networks';

    protected $primaryKey = 'id';

    protected $fillable = ['continent_id', 'country_id', 'location', 'network_id', 'isp', 'status', 'edited_by', 'mapping_value'];

    protected $hidden = ['edited_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = ['status' => 'boolean'];

    public function network()
    {
        return $this->belongsTo(Network::class)->withDefault();
    }

    public function continent()
    {
        return $this->belongsTo(Continent::class)->withDefault();
    }

    public function country()
    {
        return $this->belongsTo(Country::class)->withDefault();
    }

    public function locationDnsSetting()
    {
        return $this->hasMany(LocationDnsSetting::class, 'location_networks_id');
    }
}
