<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class CdnProvider extends Model
{
    protected $table = 'cdn_providers';

    protected $fillable = ['name', 'ttl', 'edited_by', 'user_group_id'];
    public $timestamps = true;

    protected $hidden = ['created_at', 'updated_at', 'edited_by'];

    public function getStatusAttribute($value)
    {
        $status = ['active' => true, 'stop' => false];
        return $status[$value];
    }
}
