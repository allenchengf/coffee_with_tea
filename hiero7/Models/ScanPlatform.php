<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class ScanPlatform extends Model
{
    protected $fillable = ['name', 'url', 'edited_by'];

    protected $hidden = ['created_at', 'updated_at'];
}
