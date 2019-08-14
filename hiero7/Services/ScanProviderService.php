<?php

namespace Hiero7\Services;

use Hiero7\Models\Domain;
use Hiero7\Models\LocationNetwork;
use Illuminate\Support\Collection;
use Hiero7\Repositories\DomainRepository;

class ScanProviderService
{

    protected $locationDnsSettionService;

    /**
     * NetworkService constructor.
     */
    public function __construct(LocationDnsSettingService $locationDnsSettingService)
    {
        $this->locationDnsSettionService = $locationDnsSettingService;
    }

    /**
     *  Select A Change To B Cdn Provider by IRoute
     *
     * @param LocationNetwork $locationNetwork
     * @param int $fromCdnProviderId
     * @param int $toCdnProviderId
     * @return mixed
     */
    public function changeCDNProviderByIRoute(LocationNetwork $locationNetwork, int $fromCdnProviderId, int $toCdnProviderId)
    {
        $domains = app()->call([$this, 'getDomainsByCDNProviderIdList'], [
            'cdnProviderIdList' => [$fromCdnProviderId, $toCdnProviderId],
        ]);

        $domains->map(function (Domain $domain) use ($locationNetwork, $toCdnProviderId) {
            $this->locationDnsSettionService->decideAction($toCdnProviderId, $domain, $locationNetwork);
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
