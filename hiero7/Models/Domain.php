<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $table = 'domains';
    protected $primaryKey = 'id';
    protected $fillable = ['user_group_id', 'name', 'cname', 'label', 'edited_by'];
    protected $hidden = ['created_at', 'updated_at'];

    public function cdns()
    {
        return $this->hasMany(Cdn::class);
    }

    public function getCdnById($id)
    {
        return $this->cdns()->getById($id);
    }

    public function cdnProvider()
    {
        return $this->belongsToMany(CdnProvider::class, 'cdns', 'domain_id', 'cdn_provider_id')
            ->as('cdns')
            ->withPivot('id', 'cname', 'default')
            ->withTimestamps();
    }

    public function domainGroup()
    {
        return $this->belongsToMany(DomainGroup::class, 'domain_group_mapping')->as('domain_group_mapping');
    }
}
