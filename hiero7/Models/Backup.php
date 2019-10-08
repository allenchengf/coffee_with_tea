<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $table = 'backups';

    protected $primaryKey = 'id';

    protected $fillable = ['user_group_id', 'backedup_at'];

}
