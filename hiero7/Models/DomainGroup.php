<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class DomainGroup extends Model
{
    protected $table = 'domain_group';
    protected $primaryKey = 'id';
    protected $fillable = ['domain_id','group_id'];
    protected $hidden = ['created_at','updated_at'];
}
