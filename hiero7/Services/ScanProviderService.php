<?php

namespace Hiero7\Services;

use Hiero7\Models\Domain;
use Hiero7\Models\Cdn;
use Hiero7\Models\LocationNetwork;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Traits\JwtPayloadTrait;
use Illuminate\Support\Collection;

class ScanProviderService
{
    use JwtPayloadTrait;

    protected $locationDnsSettionService;

    /**
     * NetworkService constructor.
     */
    public function __construct(
        LocationDnsSettingService $locationDnsSettingService
    )
    {
        $this->locationDnsSettionService = $locationDnsSettingService;
    }

    /**
     *  Select A Change To B Cdn Provider by IRoute
     *
     * @param LocationNetwork $locationNetwork
     * @param int $fromCdnProviderId
     * @param int $targetCdnProviderId
     * @return mixed
     */
    public function changeCDNProviderByIRoute(LocationNetwork $locationNetwork, int $fromCdnProviderId, int $targetCdnProviderId)
    {
        $domains = app()->call([$this, 'getDomainsByCDNProviderIdList'], [
            'cdnProviderIdList' => [$fromCdnProviderId, $targetCdnProviderId]
        ]);

        $domains->map(function ($domain) use ($locationNetwork) {
            $this->locationDnsSettionService->updateSetting([],$domain,$domain->cdns()->first(),$domain->locationDnsSettings()->first());
        });

        return $domains;
    }

    /**
     * Get Domains By CDN Provider Id List
     *
     * @param DomainRepository $domainRepository
     * @param array $cdnProviderIdList
     * @return Collection
     */
    public function getDomainsByCDNProviderIdList(DomainRepository $domainRepository, $cdnProviderIdList = []): Collection
    {
        return $domainRepository->getDomainsByCDNProviderList($cdnProviderIdList);
    }

}
