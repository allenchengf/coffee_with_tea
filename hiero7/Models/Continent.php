<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class Continent extends Model
{
    protected $table = 'continents';

    protected $primaryKey = 'id';

    protected $fillable = ['name'];
}
