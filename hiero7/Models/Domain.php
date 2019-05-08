<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $table = 'domains';
    protected $primaryKey = 'id';
    protected $fillable = ['user_group_id','domain','uuid'];
    
}
