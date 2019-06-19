<?php
/**
 * Created by PhpStorm.
 * User: hanhanhu
 * Date: 2019-05-07
 * Time: 14:55
 */

namespace Hiero7\Services;

use Hiero7\Repositories\DomainRepository;

class DomainService
{
    protected $domainRepository;

    public function __construct(DomainRepository $domainRepository)
    {
        $this->domainRepository = $domainRepository;
    }

    public function getDomainById(int $domain_id)
    {
        return $this->domainRepository->getById($domain_id);
    }
}
