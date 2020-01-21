<?php

namespace Hiero7\Repositories;
use Hiero7\Models\Cdn;
use Hiero7\Enums\DbError;
use Hiero7\Enums\InputError;
use Illuminate\Support\Arr;
use Hiero7\Traits\OperationLogTrait;

class CdnRepository
{
    use OperationLogTrait;
    
    protected $cdn;
    public $jwtPayload;

    public function __construct(Cdn $cdn)
    {
        $this->cdn = $cdn;
        $this->setCategory(config('logging.category.cdn'));
    }

    public function store($info, int $id, $user, array $operationLogInfo = null)
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
            $cdnId = $this->cdn->store($row);

            $jwtPayload = isset($operationLogInfo['jwtPayload']) ? $operationLogInfo['jwtPayload'] : null;
            $ip = isset($operationLogInfo['ip']) ? $operationLogInfo['ip'] : null;
            
            $this->setChangeType('Create')
                    ->setJWTPayload($jwtPayload)
                    ->setClientIp($ip)
                    ->setChangeTo($this->cdn->fresh()->saveLog())
                    ->createOperationLog(); // SaveLog

            return $cdnId;
        } catch (\Exception $e) {
            if ($e->getCode() == '23000')
                return new \Exception(DbError::getDescription(DbError::DUPLICATE_ENTRY), DbError::DUPLICATE_ENTRY);  
            return $e;
        }
    }
    

    public function indexByWhere(array $conditions = null)
    {
        $select = $this->cdn;

        if (is_array($conditions)) {
            foreach ($conditions as $k => $v){
                $select = $select->where($k, $v);
            }
        }

        return $select->get();
    }


    public function indexByWhereIn(array $conditions = null)
    {
        $select = $this->cdn;

        if (is_array($conditions)) {
            foreach ($conditions as $k => $v){
                $select = $select->whereIn($k, $v);
            }
        }

        return $select->get();
    }


    public function updateByWhere(array $inputs, array $conditions = null)
    {
        $update = $this->cdn;

        if (is_array($conditions)) {
            foreach ($conditions as $k => $v){
                $update = $update->where($k, $v);
            }
        }

        return $update->update($inputs);
    }


    public function updateByWhereIn(array $inputs, array $conditions = null)
    {
        $update = $this->cdn;

        if (is_array($conditions)) {
            foreach ($conditions as $k => $v){
                $update = $update->whereIn($k, $v);
            }
        }

        return $update->update($inputs);
    }

    public function getCdnsByDomainId(int $domainId)
    {
        return $this->cdn->where('domain_id',$domainId)->get();
    }

    // Operation Log ++
    private function setClientIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    private function getClientIp()
    {
        return $this->ip;
    }

    public function setJWTPayload($jwtPayload): array
    {
        $this->jwtPayload = $jwtPayload;
        return $this;
    }

    public function getJWTUserId()
    {
        return $this->jwtPayload['sub'] ?? null;
    }

    public function getJWTUserGroupId()
    {
        return $this->jwtPayload['user_group_id'] ?? null;
    }

    public function setChangeType(string $type)
    {
        $this->changeType = $type;
        return $this;
    }
    // Operation Log --

}