<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    protected $table = 'apis';

    protected $primaryKey = 'id';

    protected $fillable = ['method', 'path_regex'];

    protected $hidden = ['created_at', 'updated_at'];
}
