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
            'default',
            'edited_by'
        ];

    protected $hidden
        = [
            'created_at',
            'updated_at',
            'deleted_at'
        ];

    protected $casts = ['default' => 'boolean'];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function scopeGetById($query, $id)
    {
        return $query->where('id', $id);
    }

    public function scopeDefault($query)
    {
        return $query->where('default', true);
    }

    public function dnsSetting()
    {
        return $this->hasMany(LocationDnsSetting::class);
    }
}
