<?php

namespace Hiero7\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    protected $table = 'networks';

    protected $primaryKey = 'id';
    protected $fillable = ['scheme_id', 'name'];
    protected $hidden = ['created_at', 'updated_at'];

    public function locationNetwork()
    {
        return $this->hasOne(LocationNetwork::class);
    }
}
