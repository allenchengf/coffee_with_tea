<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class CdnProvider extends Model
{
    protected $table = 'cdn_providers';

    protected $fillable = ['name', 'ttl', 'edited_by', 'user_group_id', 'status', 'url','scannable'];
    public $timestamps = true;

    protected $hidden = ['created_at', 'updated_at', 'edited_by'];
    protected $casts = ['scannable' => 'boolean'];

    public function getStatusAttribute($value)
    {
        $status = ['active' => true, 'stop' => false];
        return $status[$value];
    }

    public function domains()
    {
        return $this->belongsToMany(Domain::class, 'cdns', 'cdn_provider_id', 'domain_id')
            ->as('cdns')
            ->withPivot('id', 'cname', 'default')
            ->withTimestamps();
    }

    public function cdns()
    {
        return $this->hasMany(Cdn::class);
    }
}
