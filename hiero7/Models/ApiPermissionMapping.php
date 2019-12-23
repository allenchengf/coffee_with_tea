<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class ApiPermissionMapping extends Model
{
    protected $table = 'api_permission_mapping';

    protected $primaryKey = 'id';

    protected $fillable = ['permission_id', 'api_id'];

    protected $hidden = ['created_at', 'updated_at'];
}
