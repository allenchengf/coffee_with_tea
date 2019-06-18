<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class Cdn extends Model
{
    protected $fillable = [
        'domain_id',
        'cdn_provider_id',
        'provider_record_id',
        'cname',
        'default',
        'edited_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = ['default' => 'boolean'];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function cdnProvider()
    {
        return $this->belongsTo(CdnProvider::class);
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
        return $this->hasMany(LocationDnsSetting::class);
    }

    public function getlocationDnsSettingDomainId($cdnId)
    {
        return $this->locationDnsSetting()->getDnsRecordId($cdnId);
    }

    public function updateOrInsertGetId(array $attributes, array $values = []): int
    {
        $instance = $this;
        foreach ($attributes as $key => $val) {
            $instance = $instance->where($key, $val);
        }

        if (is_null($instance = $instance->first())) {
            return $this->insertGetId($attributes + $values);
        }

        $instance->fill($values)->save();
        return $instance->id;
    }
}
