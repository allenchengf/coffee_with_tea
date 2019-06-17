<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';
    protected $primaryKey = 'id';
    protected $fillable = ['name','domain_id','label','edited_by'];
    protected $hidden = ['created_at','updated_at'];

    public function domains()
    {
        return $this->belongsToMany(Domain::class);
    }

}
