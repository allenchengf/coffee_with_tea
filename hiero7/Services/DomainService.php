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
use Illuminate\Http\Request;

class DomainService
{
    use DomainHelperTrait;

    protected $domainRepository;

    public function __construct(DomainRepository $domainRepository)
    {
        $this->domainRepository = $domainRepository;
    }

    public function getDomainById(int $domain_id)
    {
        return $this->domainRepository->getById($domain_id);
    }

    public function cnameFormat(Request $request, int $ugid)
    {
        $cname = $request->get('cname') ?? $request->get('name');

        return $this->formatDomainCname($cname) . '.' . $ugid;
    }

    public function checkUniqueCname(string $cname)
    {
        return $this->domainRepository->checkUniqueCname($cname) ? InputError::CNAME_EXIST : null;
    }
}
