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

    public function store($info, int $id, $user)
    {
        try {
            $row = [
                "domain_id"=>$id,
                "name"=>$info["name"],
                "cname"=>$info["cname"],
                "edited_by"=>$user["uuid"],
                "ttl"=>$info["ttl"],
                "provider_record_id"=>$info["provider_record_id"],
                "created_at" =>  \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now(),
                "default" => $info["default"],
            ];
            $collection = collect($row);
            $filtered = $collection->only(['domain_id', 'name']);
            return $this->cdn->updateOrInsertGetId($filtered->all(), $collection->except(['created_at'])->all());
        } catch (\Exception $e) {
            if ($e->getCode() == '23000')
                return new \Exception(DbError::getDescription(DbError::DUPLICATE_ENTRY), DbError::DUPLICATE_ENTRY);  
            return $e;
        }
    }
}