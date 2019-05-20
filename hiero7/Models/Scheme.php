<?php

namespace Hiero7\Models;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scheme extends Model
{
    use SoftDeletes,CascadeSoftDeletes;

    protected $table = 'schemes';

    protected $dates = ['deleted_at'];

    protected $primaryKey = 'id';

    protected $fillable = ['name', 'edited_by'];

    protected $cascadeDeletes = ['networks'];

    public function networks()
    {
        return $this->hasMany(Network::class);
    }
}
