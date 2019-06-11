<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class CdnProvider extends Model
{
    //
    public function getStatusAttribute($value)
    {
        $status = ['active' => true, 'stop' => false];
        return $status[$value];
    }
}
