<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class DomainPin extends Model
{
    protected $table = 'domain_pins';
    protected $primaryKey = 'id';
    protected $fillable = ['user_group_id', 'name', 'edited_by'];
    public $timestamps = true;
    protected $hidden = ['created_at', 'updated_at'];
}
