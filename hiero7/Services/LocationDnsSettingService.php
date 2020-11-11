<?php
namespace Hiero7\Services;

use Hiero7\Models\Cdn;use Hiero7\Models\Domain;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Models\LocationNetwork;
use Hiero7\Repositories\CdnRepository;
use Hiero7\Repositories\LineRepository;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Services\DnsProviderService;
use Hiero7\Traits\DomainHelperTrait;
use Hiero7\Traits\JwtPayloadTrait;
use Illuminate\Support\Collection;

class LocationDnsSettingService
{
    use DomainHelperTrait;
    use JwtPayloadTrait;

    protected $locationDnsSettingRepository;

    public function __construct(LocationDnsSettingRepository $locationDnsSettingRepository, DnsProviderService $dnsProviderService,
        LineRepository $lineRepository, CdnRepository $cdnRepository) {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
        $this->dnsProviderService = $dnsProviderService;
        $this->lineRepository = $lineRepository;
        $this->cdnRepository = $cdnRepository;
    }

    /**
     * get Domain's iRoute function
     *
     * 拿 domain 下的東西
     * @param Domain $domain
     * @return void
     */
    public function indexByDomain(Domain $domain)
    {
        $cdnsModelMass = $domain->cdns;

        if ($cdnsModelMass->isEmpty()) {
            $defaultCdn = [];
        } else {
            $cdnsModelMass->where('default', 1)->first()->cdnProvider;
            $defaultCdn = $cdnsModelMass->where('default', 1)->first();
        }

        foreach ($cdnsModelMass as $cdnsModel) {
            $cdnsModel->cdnProvider;
        }

        $cdns = $cdnsModelMass->keyBy('id');

        $lineResult = $this->lineRepository->getLinesById();
        $lineCollection = collect($lineResult);

        $dnsSetting = $domain->locationDnsSettings->keyBy('location_networks_id');

        //如果沒有設定在 locationDnsSetting 就放預設 cdn。
        foreach ($lineCollection as $lineModel) {

            if (!$dnsSetting->has($lineModel->id)) {
                $lineModel->setAttribute('cdn', $defaultCdn);
                continue;
            }

            $dnsCdnMapping = $dnsSetting->get(($lineModel->id));
            //用 locationDnsSetting 的 cdn_id 取出特定的 cdn 設定
            $cdnsTarget = $cdns->get($dnsCdnMapping->cdn_id);

            $lineModel->setAttribute('cdn', $cdnsTarget);

        }

        return $lineCollection;
    }

    /**
     * delete iRoute Setting function
     *
     * 會將 pod 上的設定刪掉
     *
     * @param LocationDnsSetting $locationDnsSetting
     * @return void
     */
    public function destroy(LocationDnsSetting $locationDnsSetting)
    {
        $podResult = $this->dnsProviderService->deleteRecord([
            'record_id' => $locationDnsSetting->provider_record_id,
        ]);

        if (!$this->dnsProviderService->checkAPIOutput($podResult)) {
            return false;
        }

        return $locationDnsSetting->delete();
    }

    /**
     * 判斷要執行 新增/修改/刪除 動作。
     *
     * 新增: 給的 cdn_provider 並 沒有 存在 locationDnsSetting Table 內。
     * 修改: 給的 cdn_provider 並 有 存在 locationDnsSetting Table 內。
     * 刪除: 給的 cdn_provider 是 default ，就會刪掉 locationDnsSetting 那筆設定。
     *
     * 如果提供的 cdn_provider 並未存在於該 domain 會回傳 'differentGroup' ， 離開 function。
     * 如果提供的 cdn_provider 是 Default，不會執行任何動作，離開。function。
     *
     * @param Int $cdnProviderId 
     * @param Domain $domain 目標 domain （要被更改的 Domain）
     * @param LocationNetwork $locationNetwork
     * @return boolean|'differentGroup'  (false 是打 pod 有問題)
     */
    public function decideAction(Int $cdnProviderId, Domain $domain, LocationNetwork $locationNetwork)
    {

        //使用 目標 Domain 和 預期 cdnProviderId 取得 CDN 的 id
        $cdnModel = $this->getTargetCdn($cdnProviderId, $domain);

        // $cdnModel 是空的代表 目標 Domain 裡沒有屬於 預期 cdnProviderId 的 CDN
        if (is_null($cdnModel)) {
            return 'differentGroup';
        }

        $existLocationDnsSetting = $this->getExistSetting($domain, $locationNetwork);

        $isExistLocationDnsSetting = collect($existLocationDnsSetting)->isNotEmpty();

        // cdnModel 是 Default 且 存在 LocaitonDnsSetting
        if ($cdnModel->default) {
            $result = $isExistLocationDnsSetting ? $this->destroy($existLocationDnsSetting) : true;
        } else {

            $data = [
                'cdn_id' => $cdnModel->id,
                'edited_by' => $this->getJWTPayload()['uuid'],
            ];

            if ($isExistLocationDnsSetting) {
                // 如果 從 DB 撈出來的設定($existLocationDnsSetting) 和 預計要被改的 （$cdnModel) ID 不一樣就要 update
                $result = ($existLocationDnsSetting->cdn_id == $cdnModel->id) ? true:
                $this->updateSetting($data, $domain, $cdnModel, $existLocationDnsSetting);
            } else {
                $result = $this->createSetting($data, $domain, $cdnModel, $locationNetwork);
            }
        }

        return $result;
    }

    /**
     * 處理 目標 Domain 的特定 CdnProvider 的其他 iRoute 線路設定。
     * 
     * 刪除 該 CdnProvider 下其他線路設定。
     *
     * @param Int $cdnProviderId
     * @param Domain $domain
     * @param LocationNetwork $locationNetwork
     * @return void
     */
    public function handelTargetDomainsIrouteSetting(Int $cdnProviderId, Domain $domain, Collection $locationNetworkCollection)
    {
        //使用 目標 Domain 和 預期 cdnProviderId 取得 CDN 的 id
        $cdnModel = $this->getTargetCdn($cdnProviderId, $domain);

        if($locationNetworkCollection->isEmpty()){
                    return false;
                }

        // 如果有多個 locationSetting，就要比完之後繼續比
        $ExtraSetting = $cdnModel->locationDnsSetting->filter(function ($item) use ($locationNetworkCollection){
            return $item->location_networks_id != $locationNetworkCollection->first()->location_networks_id;
        });

        if($locationNetworkCollection->count() > 1){
            foreach($locationNetworkCollection as $locationNetwork){
                $ExtraSetting = $ExtraSetting->filter(function ($item) use ($locationNetwork){
                    return $item->location_networks_id != $locationNetwork->location_networks_id;
                });
            }
        }

        $ExtraSetting->each(function ($locationSetting){
            $this->destroy($locationSetting);
        });

        return true;

    }

    /**
     * 拿目標 Domain 和 預期 cdnProviderId 取得 CDN 的 id
     *
     * @param Int $cdnProviderId
     * @param Domain $domain
     * @return void
     */
    private function getTargetCdn(Int $cdnProviderId, Domain $domain)
    {
        return $this->cdnRepository->indexByWhere(['cdn_provider_id' => $cdnProviderId, 'domain_id' => $domain->id])->first();
    }

    private function getExistSetting(Domain $domain, LocationNetwork $locationNetwork)
    {
        $cdnId = Cdn::where('domain_id', $domain->id)->pluck('id');

        return LocationDnsSetting::where('location_networks_id', $locationNetwork->id)->whereIn('cdn_id', $cdnId)->first();

    }

    /**
     * update iRoute Setting function
     *
     * 會把設定打上去 pod
     *
     * @param array $data
     * @param Domain $domain
     * @param Cdn $cdn
     * @param LocationDnsSetting $locationDnsSetting
     * @return void
     */
    public function updateSetting(array $data, Domain $domain, Cdn $cdn, LocationDnsSetting $locationDnsSetting)
    {
        $podResult = $this->dnsProviderService->editRecord([
            'sub_domain' => $domain->cname,
            'value' => $cdn->cname,
            'record_id' => $locationDnsSetting->provider_record_id,
            'record_line' => $locationDnsSetting->location()->first()->network()->first()->name,
            'ttl' => $cdn->cdnProvider->ttl,
        ]);

        if (!$this->dnsProviderService->checkAPIOutput($podResult)) {
            return false;
        }

        return $this->locationDnsSettingRepository
            ->updateLocationDnsSetting($locationDnsSetting, $data);
    }

    /**
     * create  iRoute Setting function
     *
     *  會把設定打上去 pod
     *
     * @param array $data
     * @param Domain $domain
     * @param Cdn $cdn
     * @param LocationNetwork $locationNetwork
     * @return void
     */
    public function createSetting(array $data, Domain $domain, Cdn $cdn, LocationNetwork $locationNetwork)
    {
        $podResult = $this->dnsProviderService->createRecord([
            'sub_domain' => $domain->cname,
            'value' => $cdn->cname,
            'record_line' => $locationNetwork->network()->first()->name,
            'ttl' => $cdn->cdnProvider->ttl,
            'status' => $cdn->cdnProvider->status,
        ]);

        if (!$this->dnsProviderService->checkAPIOutput($podResult)) {
            return false;
        }

        return $this->locationDnsSettingRepository
            ->createSetting($locationNetwork, $podResult['data']['record']['id'], $data);
    }
}
