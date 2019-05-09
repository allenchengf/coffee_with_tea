<?php
/**
 * Created by PhpStorm.
 * User: hanhanhu
 * Date: 2019-05-07
 * Time: 14:55
 */

namespace Hiero7\Repositories;

use Hiero7\Models\Domain;

class DomainRepository
{
    protected $domain;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function createDomain($data)
    {
        return $this->domain->create($data);
    }

    public function checkDomain(string $domain)
    {
        return $this->domain->where('name', $domain)->exists();
    }

    public function checkCNAME(string $cname)
    {
        return $this->domain->where('cname', $cname)->exists();
    }

    
}
