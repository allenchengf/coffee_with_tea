<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scheme extends Model
{
    use SoftDeletes;

    protected $table = 'schemes';

    protected $dates = ['deleted_at'];

    protected $primaryKey = 'id';

    protected $fillable = ['name', 'edited_by'];

    protected $softCascade = ['networks'];

    public function networks()
    {
        return $this->hasMany(Network::class);
    }
}
