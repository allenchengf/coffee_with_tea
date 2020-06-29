<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeLogForPortal extends Model
{
    protected $table = 'change_log_for_portals';
    protected $primaryKey = 'id';
    protected $fillable = ['domains', 'changed_from', 'changed_to'];
    public $timestamps = true;

    protected $hidden = ['updated_at'];

    public function getDomainsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getChangedFromAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getChangedToAttribute($value)
    {
        return json_decode($value, true);
    }

}
