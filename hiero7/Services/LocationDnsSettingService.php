<?php
namespace Hiero7\Services;

use Hiero7\Models\{Cdn,LocationDnsSetting};
use Hiero7\Models\Domain;
use Hiero7\Models\LocationNetwork;
use Hiero7\Repositories\LineRepository;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Services\DnsProviderService;
use Hiero7\Traits\DomainHelperTrait;

class LocationDnsSettingService
{
    use DomainHelperTrait;

    protected $locationDnsSettingRepository;

    public function __construct(LocationDnsSettingRepository $locationDnsSettingRepository, DnsProviderService $dnsProviderService,
        LineRepository $lineRepository) {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
        $this->dnsProviderService = $dnsProviderService;
        $this->lineRepository = $lineRepository;

    }

    public function indexByDomain(int $domainId)
    {
        $cdnModel = new Cdn;
        $lineResult = $this->lineRepository->getLinesById();
        $lineCollection = collect($lineResult);

        foreach ($lineCollection as $lineModel) {
            if ($cdnModel->all()->isEmpty()) {
                $lineModel->setAttribute('cdn', null);
                continue;
            }

            if ($this->locationDnsSettingRepository->getAll()->isEmpty()) {
                $lineModel->setAttribute('cdn', $this->getDefaultCdn($cdnModel, $domainId));
                continue;
            }
            $cdnId = $cdnModel->where('domain_id',$domainId)->pluck('id');
            $this->getDnsSettingAll($lineModel, $cdnModel, $domainId, $lineModel->locationDnsSetting()->whereIn('cdn_id',$cdnId)->first());
        }

        return $lineCollection;
    }

    public function updateSetting(array $data,Domain $domain,Cdn $cdn, LocationDnsSetting $locationDnsSetting)
    {
        $podResult = $this->dnsProviderService->editRecord([
            'sub_domain' => $domain->cname,
            'value' => $cdn->cname,
            'record_id' => $locationDnsSetting->provider_record_id,
            'record_line' => $locationDnsSetting->location()->first()->network()->first()->name,
            'ttl' => $cdn->cdnProvider->ttl,
        ]);

        if ($podResult['errorCode']) {
            return false;
        }

        return $this->locationDnsSettingRepository
                    ->updateLocationDnsSetting($locationDnsSetting, $data);
    }

    public function createSetting(array $data, Domain $domain,Cdn $cdn, LocationNetwork $locationNetwork)
    {
        $podResult = $this->dnsProviderService->createRecord([
            'sub_domain' => $domain->cname,
            'value' => $cdn->cname,
            'record_line' => $locationNetwork->network()->first()->name,
            'ttl' => $cdn->cdnProvider->ttl,
            'status' => $cdn->cdnProvider->status,
        ]);

        if ($podResult['errorCode']) {
            return false;
        }

        return $this->locationDnsSettingRepository
                    ->createSetting($locationNetwork, $podResult['data']['record']['id'], $data);
    }

    public function updateToDefaultCdnId(Cdn $targetCdn, Cdn $defaultCdn)
    {
        return $this->locationDnsSettingRepository->updateToDefaultCdnId($targetCdn->id, $defaultCdn->id);
    }

    public function destroy(LocationDnsSetting $locationDnsSetting)
    {
        $podResult = $this->dnsProviderService->deleteRecord([
            'record_id' => $locationDnsSetting->provider_record_id,
        ]);

        if ($podResult['errorCode']) {
            return false;
        }

        return $locationDnsSetting->delete();
    }

    private function getDnsSettingAll($lineModel, Cdn $cdnModel, int $domainId, $locationDnsSetting)
    {
        if (!$locationDnsSetting) {
            return $lineModel->setAttribute('cdn', $this->getDefaultCdn($cdnModel, $domainId));
        }

        $locationCdnResult = $locationDnsSetting->cdn()->select('id', 'cdn_provider_id')->with('cdnProvider')->first();

        return $lineModel->setAttribute('cdn', $locationCdnResult);
    }

    private function getDefaultCdn(Cdn $cdnModel, int $domainId)
    {
        return $cdnModel->select('id','cdn_provider_id')->where('domain_id', $domainId)->where('default', 1)->with('cdnProvider')->first();
    }

}
