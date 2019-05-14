<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class LocationNetwork extends Model
{
    protected $table = 'location_networks';

    protected $primaryKey = 'id';

    protected $fillable = ['continent_id', 'country_id', 'location', 'network_id', 'edited_by'];

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

    public function dnsSetting()
    {
        return $this->hasMany(LocationDnsSetting::class);
    }
}
