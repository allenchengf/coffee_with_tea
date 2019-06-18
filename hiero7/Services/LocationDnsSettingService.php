<?php
namespace Hiero7\Services;

use Hiero7\Models\{Cdn,CdnProvider};
use Hiero7\Models\Domain;
use Hiero7\Models\LocationNetwork;
use Hiero7\Repositories\LineRepository;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Services\DnsProviderService;

class LocationDnsSettingService
{
    protected $locationDnsSettingRepository;

    public function __construct(LocationDnsSettingRepository $locationDnsSettingRepository, DnsProviderService $dnsProviderService,
        LineRepository $lineRepository) {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
        $this->dnsProviderService = $dnsProviderService;
        $this->lineRepository = $lineRepository;

    }

    public function getAll($domainId)
    {
        $cdnModel = new Cdn;
        $lineResult = $this->lineRepository->getLinesById();
        $lineCollection = collect($lineResult);

        $checkCdnModel = $cdnModel->all()->isEmpty();
        $checkDnsSetting = $this->locationDnsSettingRepository->getAll()->isEmpty();

        foreach ($lineCollection as $lineModel) {
            if ($checkCdnModel) {
                $lineModel->setAttribute('cdn', null);
                continue;
            }

            if ($checkDnsSetting) {
                $lineModel->setAttribute('cdn', $this->getDefaultCdn($cdnModel, $domainId));
                continue;
            }

            $this->getDnsSettingAll($lineModel, $cdnModel, $domainId, $lineModel->locationDnsSetting()->where('domain_id', $domainId)->first());
        }

        return $lineCollection;
    }

    public function updateSetting(array $data, Domain $domain, LocationNetwork $locationNetwork)
    {
        $cdnResult = $this->checkCdnIfExist($data, $domain);

        if (!$cdnResult) {
            return false;
        }

        $podResult = $this->dnsProviderService->editRecord([
            'sub_domain' => $domain->cname,
            'value' => $cdnResult->cname,
            'record_id' => $this->getPodId($locationNetwork->id, $domain->id),
            'record_line' => $locationNetwork->network()->first()->name,
        ]);

        if ($podResult['errorCode']) {
            return 'error';
        }

        $result = $this->locationDnsSettingRepository->updateLocationDnsSetting($domain, $cdnResult, $locationNetwork, $data['edited_by']);

        return $result;
    }

    public function createSetting(array $data, Domain $domain, LocationNetwork $locationNetwork)
    {
        $cdnResult = $this->checkCdnIfExist($data, $domain);

        if (!$cdnResult) {
            return false;
        }

        $podResult = $this->dnsProviderService->createRecord([
            'sub_domain' => $domain->cname,
            'value' => $cdnResult->cname,
            'record_line' => $locationNetwork->network()->first()->name,
        ]);

        if ($podResult['errorCode']) {
            return 'error';
        }

        return $this->locationDnsSettingRepository->createSetting($domain, $cdnResult, $locationNetwork, $podResult['data']['record']['id'], $data['edited_by']);
    }

    public function updateToDefaultCdnId(Cdn $targetCdn, Cdn $defaultCdn)
    {
        return $this->locationDnsSettingRepository->updateToDefaultCdnId($targetCdn->id, $defaultCdn->id);
    }

    private function getDnsSettingAll($lineModel, Cdn $cdnModel, int $domainId, $locationSetting)
    {
        if (!$locationSetting) {
            $lineModel->setAttribute('cdn', $this->getDefaultCdn($cdnModel, $domainId));

            return $lineModel;
        }

        $locationCdnResult = $locationSetting->cdn()->select('id', 'cdn_provider_id')->with('cdnProvider')->first();
        $lineModel->setAttribute('cdn', $locationCdnResult);

        return $lineModel;
    }

    private function checkCdnIfExist(array $data, Domain $domain)
    {
        return $domain->cdns()->where('id', $data['cdn_id'])->first();

    }

    private function getDefaultCdn(Cdn $cdnModel, int $domainId)
    {
        return $cdnModel->select('id','cdn_provider_id')->where('domain_id', $domainId)->where('default', 1)->with('cdnProvider')->first();
    }

    private function getPodId(int $locationNetworkId, int $domainId)
    {
        return $this->locationDnsSettingRepository->getPodId($locationNetworkId, $domainId);
    }

}
