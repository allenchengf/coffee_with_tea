<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class DomainGroup extends Model
{
    protected $table = 'domain_groups';
    protected $primaryKey = 'id';
    protected $fillable = ['user_group_id','name','domain_id','label','edited_by'];
    public $timestamps = true;
    protected $hidden = ['created_at','updated_at'];

    public function domains()
    {
        return $this->belongsToMany(Domain::class,'domain_group_mapping')->as('domain_group_mapping');
    }

    public function mapping()
    {
        return $this->hasMany(DomainGroupMapping::class,'domain_group_id');
    }

    public function saveLog()
    {
        return $this->only('name', 'label');
    }
}
