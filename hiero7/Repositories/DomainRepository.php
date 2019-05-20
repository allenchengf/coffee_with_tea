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
            return $this->domain::insertGetId(
                [
                    "user_group_id" => $user["user_group_id"],
                    "name" => $info["name"],
                    "cname" => $info["name"],
                    "edited_by" => $user["uuid"],
                    "created_at" => \Carbon\Carbon::now(),
                    "updated_at" => \Carbon\Carbon::now(),
                ]
            );
            return;
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

    public function getAll()
    {
        return $this->domain->all();
    }

    public function getByid(int $domain_id)
    {
        return $this->domain->find($domain_id);
    }

    public function getByUgid(int $user_group_id)
    {
        return $this->domain->where(compact('user_group_id'))->get();
    }

    public function createDomain($data)
    {
        return $this->domain->create($data);
    }

    public function checkDomain(string $domain, int $id = 0)
    {
        return $this->domain->where('name', $domain)
            ->whereNotIn('id', [$id])
            ->exists();
    }

    public function checkCname(string $cname, int $id = 0)
    {
        return $this->domain->where('cname', $cname)
            ->whereNotIn('id', [$id])
            ->exists();
    }
}
