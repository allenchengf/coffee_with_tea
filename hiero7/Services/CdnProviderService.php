<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/6/10
 * Time: 4:33 PM
 */

namespace Hiero7\Services;


use Hiero7\Repositories\CdnProviderRepository;

class CdnProviderService
{

    protected $cdnProviderRepository;
    /**
     * CdnProviderService constructor.
     */
    public function __construct(CdnProviderRepository $cdnProviderRepository)
    {
        $this->cdnProviderRepository = $cdnProviderRepository;
    }

    public function getCdnProvider(int $ugid)
    {
        return $this->cdnProviderRepository->getCdnProvider($ugid);
    }
}