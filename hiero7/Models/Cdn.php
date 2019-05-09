<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cdn extends Model
{
    use SoftDeletes;

    protected $fillable
        = [
            'domain_id',
            'name',
            'cname',
            'ttl',
            'edited_by'
        ];

    protected $hidden
        = [
            'created_at',
            'updated_at'
        ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function scopeGetByDomainId($query, $domainId)
    {
        return $query->where('domain_id', $domainId);
    }
}
