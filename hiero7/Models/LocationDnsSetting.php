<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class LocationDnsSetting extends Model
{
    protected $table = 'location_dns_settings';
    protected $primaryKey = 'id';
    protected $fillable = ['edited_by','user_group_id', 'pod_record_id', 'location_networks_id', 'cdn_id','domain_id'];

    public function cdn()
    {
        return $this->belongsTo(Cdn::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class,'domain_id');
    }

    public function locations()
    {
        return $this->belongsToMany(LocationNetwork::class,'location_networks_id','id');
    }

    public function location()
    {
        return $this->belongsTo(LocationNetwork::class,'location_networks_id','id');
    }


}
