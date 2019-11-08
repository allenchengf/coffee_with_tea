<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'jobs';

    protected $fillable = ['queue', 'payload'];
    protected $hidden = ['created_at', 'available_at','reserved_at'];
}
