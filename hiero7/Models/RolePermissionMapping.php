<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermissionMapping extends Model
{
    protected $table = 'role_permission_mapping';

    protected $primaryKey = 'id';

    protected $fillable = ['role_id', 'permission_id', 'actions', 'edited_by'];

    protected $hidden = ['created_at', 'updated_at'];


    public function permissions()
    {
        return $this->belongsTo(Permission::class);
        // return $this->hasMany(Permission::class, 'id', 'permission_id');
        // return $this->hasMany(Permission::class, 'permission_id', 'id');
    }
}
