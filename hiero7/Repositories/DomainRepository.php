<?php
namespace Hiero7\Repositories;

use Hiero7\Enums\DbError;
use Hiero7\Models\Domain;

class DomainRepository
{
    protected $domain;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function store($info, $user)
    {
        try {
            return $this->domain::create(
                [
                    "user_group_id" => $user["user_group_id"],
                    "name" => $info["name"],
                    "cname" => $info["name"],
                    "edited_by" => $user["uuid"],
                    "created_at" => \Carbon\Carbon::now(),
                ]
            );
        } catch (\Exception $e) {
            if ($e->getCode() == '23000') {
                return new \Exception(DbError::getDescription(DbError::DUPLICATE_ENTRY), DbError::DUPLICATE_ENTRY);  
            }

            return $e;
        }
    }

    public function getDomainIdIfExist(string $domain, int $user_group_id)
    {
        return $this->domain->where('name', $domain)->where('user_group_id', $user_group_id)->first();
    }

    public function getById(int $domain_id)
    {
        return $this->domain->find($domain_id);
    }
}
