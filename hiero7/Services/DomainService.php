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
use Hiero7\Traits\DomainHelperTrait;

class DomainService
{
    use DomainHelperTrait;
    protected $domainRepository;

    public function __construct(DomainRepository $domainRepository)
    {
        $this->domainRepository = $domainRepository;
    }

    public function getAllDomain()
    {
        return $this->domainRepository->getAll();
    }

    public function getDomain(int $ugid)
    {
        return $this->domainRepository->getByUgid($ugid);
    }

    public function getDomainbyId(int $domain_id)
    {
        return $this->domainRepository->getByid($domain_id);
    }

    public function checkDomainAndCnameUnique(array $data): int
    {
        $checkDomain = $this->checkDomainName($data['name']);
        $checkCname = $this->checkCname($data['cname']);

        $errorCode = $checkDomain ?? $checkCname;
        return (int) $errorCode;
    }

    public function checkDomainName(string $name, int $domain_id = 0)
    {
        if ($this->domainRepository->checkDomain($name, $domain_id)) {
            return InputError::DOMAIN_EXIST;
        } else if (!$this->validateDoamin($name)) {
            return InputError::DOMAIN_VERIFICATION_ERROR;
        }

        return null;
    }

    public function checkCname(string $cname, int $domain_id = 0)
    {
        if ($this->domainRepository->checkCname($cname, $domain_id)) {
            return InputError::CNAME_EXIST;
        }

        return null;
    }
}
