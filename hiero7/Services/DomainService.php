<?php
/**
 * Created by PhpStorm.
 * User: hanhanhu
 * Date: 2019-05-07
 * Time: 14:55
 */

namespace Hiero7\Services;

use Hiero7\Enums\InputError;
use Hiero7\Repositories\DomainRepository;

class DomainService
{
    protected $domainRepository;

    public function __construct(DomainRepository $domainRepository)
    {
        $this->domainRepository = $domainRepository;
    }

    public function create(array $data): array
    {
        $errorCode = null;
        $domain = [];
        if ($this->domainRepository->checkDomain($data['domain'])) {

            $errorCode = InputError::DOMAIN_EXIST;
        } else {

            $domain = $this->domainRepository->createDomain($data);
        }

        return compact('errorCode', 'domain');
    }
}
