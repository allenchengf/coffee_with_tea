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
                "domain_id"          => $id,
                "cdn_provider_id"    => $info["cdn_provider_id"],
                "provider_record_id" => $info["provider_record_id"],
                "cname"              => $info["cname"],
                "edited_by"          => $user["uuid"],
                "default"            => $info["default"],
                "created_at"         => \Carbon\Carbon::now(),
            ];
            return $this->cdn->store($row);
        } catch (\Exception $e) {
            if ($e->getCode() == '23000')
                return new \Exception(DbError::getDescription(DbError::DUPLICATE_ENTRY), DbError::DUPLICATE_ENTRY);  
            return $e;
        }
    }
    

    public function getByWhere(array $conditions = null)
    {
        $select = $this->cdn;

        if (is_array($conditions)) {
            foreach ($conditions as $k => $v){
                $select = $select->where($k, $v);
            }
        }

        return $select->get();
    }
}