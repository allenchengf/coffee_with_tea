<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class LocationNetwork extends Model
{
    protected $table = 'location_networks';

    protected $primaryKey = 'id';

    protected $fillable = ['continent_id', 'country_id', 'location', 'network_id', 'edited_by'];
}
