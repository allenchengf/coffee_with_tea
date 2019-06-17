<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $table = 'domains';
    protected $primaryKey = 'id';
    protected $fillable = ['user_group_id', 'name', 'cname', 'label', 'edited_by'];

    public function cdns()
    {
        return $this->hasMany(Cdn::class);
    }

    public function getCdnById($id)
    {
        return $this->cdns()->getById($id);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}
