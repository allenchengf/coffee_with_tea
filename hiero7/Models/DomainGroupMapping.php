<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class DomainGroupMapping extends Model
{
    protected $table = 'domain_group_mapping';
    protected $primaryKey = 'id';
    protected $fillable = ['domain_id','domain_group_id'];
    protected $hidden = ['created_at','updated_at'];
    public $timestamps = true;

    public function domain()
    {
        return $this->belongsTo(Domain::class,'domain_id');
    }

    public function domainGroup()
    {
        return $this->belongsTo(DomainGroup::class,'domain_group_id');
    }

    public function saveLog()
    {
        $logArray = [];
        $logArray['domain'] = $this->domain()->first()->name;
        $logArray['domainGroup'] = $this->domainGroup()->first()->name;

        return $logArray;
    }
}
