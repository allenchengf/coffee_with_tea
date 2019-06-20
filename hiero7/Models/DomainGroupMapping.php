<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class DomainGroupMapping extends Model
{
    protected $table = 'domain_group_mapping';
    protected $primaryKey = 'id';
    protected $hidden = ['created_at','updated_at'];
}
