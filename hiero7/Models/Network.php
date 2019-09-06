<?php

namespace Hiero7\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Network extends Model
{
    use SoftDeletes;
    protected $table = 'networks';

    protected $primaryKey = 'id';

    protected $hidden = ['created_at', 'updated_at','deleted_at'];

    public function locationNetwork()
    {
        return $this->hasOne(LocationNetwork::class);
    }
}
