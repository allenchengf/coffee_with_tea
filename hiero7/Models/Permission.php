<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $primaryKey = 'id';

    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at'];

    public function rolePermissionMapping()
    {
        return $this->hasMany(RolePermissionMapping::class)->withDefault();
    }
}
