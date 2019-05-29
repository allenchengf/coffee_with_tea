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
            'dns_provider_id',
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

    public function locationDnsSetting()
    {
        return $this->hasOne(LocationDnsSetting::class);
    }

    public function updateOrInsertGetId(array $attributes, array $values = []):int
    {
        $instance = $this;
        foreach($attributes as $key => $val)
            $instance = $instance->where($key, $val);
        
        if(is_null($instance = $instance->first()))
            return $this->insertGetId($attributes + $values);

        $instance->fill($values)->save();
        return $instance->id;
    } 
}
