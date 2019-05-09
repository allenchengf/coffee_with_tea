<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    protected $primaryKey = 'id';

    protected $fillable = ['name'];
}
