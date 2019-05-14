<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    protected $table = 'networks';

    protected $primaryKey = 'id';

    protected $hidden = ['created_at', 'updated_at'];

    public function locationNetwork()
    {
        return $this->hasOne(LocationNetwork::class);
    }
}
