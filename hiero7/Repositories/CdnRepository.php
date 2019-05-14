<?php

namespace Hiero7\Repositories;
use Hiero7\Models\Cdn;
use Hiero7\Enums\DbError;
use Hiero7\Enums\InputError;
use Illuminate\Support\Arr;

class CdnRepository
{
    protected $cdn;

    public function __construct(Cdn $cdn)
    {
        $this->cdn = $cdn;
    }

    public function store($info, int $id, $user, $defult)
    {
        try {
            $row = [
                "domain_id"=>$id,
                "name"=>$info["name"],
                "cname"=>$info["cname"],
                "edited_by"=>$user["uuid"],
                "ttl"=>$info["ttl"]??env("CDN_TTL"),
                "created_at" =>  \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now(),                      
            ];
            if($defult == 0)
                $row["default"] = 1;
            return $this->cdn::insertGetId($row);
        } catch (\Exception $e) {
            if ($e->getCode() == '23000')
                return new \Exception(DbError::getDescription(DbError::DUPLICATE_ENTRY)." for ".$info["name"], DbError::DUPLICATE_ENTRY);  
            return $e;
        }
    }
}