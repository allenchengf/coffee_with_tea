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
                "ttl"=>$info["ttl"],
                "dns_provider_id"=>$info["dns_provider_id"],
                "created_at" =>  \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now(),
                "default" => $info["default"],
            ];
            return $this->cdn::insertGetId($row);
        } catch (\Exception $e) {
            if ($e->getCode() == '23000')
                return new \Exception(DbError::getDescription(DbError::DUPLICATE_ENTRY), DbError::DUPLICATE_ENTRY);  
            return $e;
        }
    }
}