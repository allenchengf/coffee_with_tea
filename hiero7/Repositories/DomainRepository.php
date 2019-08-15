<?php

namespace Hiero7\Repositories;

use Hiero7\Enums\DbError;
use Hiero7\Models\Domain;
use Hiero7\Traits\JwtPayloadTrait;

class DomainRepository
{
    use JwtPayloadTrait;

    protected $domain;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function getAll()
    {
        return $this->domain->all();
    }

    public function store($info, $user)
    {
        try {
            return $this->domain::create(
                [
                    "user_group_id" => $user["user_group_id"],
                    "name" => $info["name"],
                    "cname" => $info["cname"],
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

    public function checkUniqueCname(string $cname)
    {
        return $this->domain->where('cname', $cname)->exists();
    }

    public function getDomainsByCDNProviderList(array $cdnProviderIdList = [])
    {
        $countList = count($cdnProviderIdList);

        return $this->domain->where('user_group_id', $this->getJWTUserGroupId())
            ->with(array('cdnProvider' => function ($query) use ($cdnProviderIdList) {
                $query->where('cdn_providers.status', 'active')
                    ->whereIn('cdn_providers.id', $cdnProviderIdList);
            }))->get()->filter(function ($item) use ($countList) {
                return (count($item->cdnProvider) == $countList) ? true : false;
            })->values();
    }
}
