<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationNetwork extends Model
{
    use SoftDeletes;

    protected $table = 'location_networks';

    protected $primaryKey = 'id';

    protected $fillable = ['continent_id', 'country_id', 'location', 'network_id','isp', 'edited_by'];

    protected $dates = ['deleted_at'];

    public function network()
    {
        return $this->belongsTo(Network::class)->withDefault();
    }
}
