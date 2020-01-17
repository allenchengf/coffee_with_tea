<?php 
namespace Hiero7\Services;
use Hiero7\Traits\DomainHelperTrait;
use Hiero7\Models\{Domain, Cdn, CdnProvider, LocationDnsSetting};
use Illuminate\Database\Eloquent\Model;
use Hiero7\Services\DnsProviderService;
use Illuminate\Support\Collection;
use App\Exceptions\ConfigException;
use Hiero7\Enums\DbError;
use Cache;
use DB;



Class ConfigService
{
    use DomainHelperTrait;

    public function __construct(DnsProviderService $dnsProviderService)
    {
        $this->dnsProviderService = $dnsProviderService;
    }

    public function checkDomainFormate(Array $data)
    {
        $error = [];
        foreach($data['domains'] as $domainData){
            $check = $this->validateDomain($domainData['name']);
            if (!$check){
                $error[]= $domainData;
            }
        }

        return $error ? ['errorData' => $error] : true;
    }
    
    public function checkCdnHaveDefault(Array $data)
    {
        $error = [];     
        foreach($data as $domainData){
            $cdnsArray = array_only($domainData,['cdns']); 
            
            if(empty($cdnsArray['cdns'])){
                continue;
            }
            
            $default = array_pluck($cdnsArray['cdns'],'default');

            if(!in_array(true,$default)){
                $error[]= $domainData['cdns'];
            }

        }

        return empty($error['errorData']) ? true : ['errorData' => array_collapse($error)];
    }
    
    public function insert(Array $InsertData, Model $targetTable, Int $userGroupId)
    {
        try{
            $targetTable->insert($InsertData);
        } catch (\Illuminate\Database\QueryException  $e) {
            DB::rollback();
            Cache::forget("Config_userGroupId$userGroupId");
            $res = $e->getMessage();
            throw new ConfigException(DbError::getDescription(DbError::IMPORT_RELATIONAL_DATA_HAVE_SOME_PROBLEM),
                                        DbError::IMPORT_RELATIONAL_DATA_HAVE_SOME_PROBLEM);
        }

        return true;
    }

    /**
     * 比較 原本在 DataBase 多餘 import 的 並且 刪除  DnsPOD 上的資料 
     * 
     * Domain 和 CdnProvider 都會處理 
     */

    public function compareDatabaseNotInImportRecordId(Collection $originDomain, Collection $originCdnProvider, Array $importDomain, Array $importCdnProvider)
    {
        $originDomains = $originDomain->pluck('name');
        $importDomains = collect($importDomain)->pluck('name');
        $originCdnProviders = $originCdnProvider->pluck('name');
        $importCdnProviders = collect($importCdnProvider)->pluck('name');

        $needToDeleteDomainCollection = $this->getNeedToDeleteData($originDomains, $importDomains);
        $domainDeleteRecordIdCollection = $this->getDomainRecords($needToDeleteDomainCollection);

        $needToDeleteCdnProviderCollection = $this->getNeedToDeleteData($originCdnProviders, $importCdnProviders);
        $cdnProviderDeleteRecordIdCollection = $this->checkNeedToDeleteCdnProvider($needToDeleteCdnProviderCollection);

        return $cdnProviderDeleteRecordIdCollection->merge($domainDeleteRecordIdCollection);
    }

    /**
     * 拿 CdnProvider 是 default 和 有 iRoute 設定的 Record Id
     */

    private function checkNeedToDeleteCdnProvider(Collection $cdnProviderCollection)
    {
        $defaultRecordIdCollection = collect();
        $locationDnsSettingRecordIdCollection = collect();

        foreach($cdnProviderCollection as $cdnProvider)
        {
            $target = CdnProvider::where('name',$cdnProvider)->first();
            $targetCdn = $target->cdns;

            $defaultRecordId = $targetCdn->where('default',1)->pluck('provider_record_id');
            $defaultRecordIdCollection->push($defaultRecordId);

            $targetCdnId = $targetCdn->pluck('id');
            $locationDnsSettingRecordId = LocationDnsSetting::whereIn('cdn_id',$targetCdnId)->pluck('provider_record_id');
            $locationDnsSettingRecordIdCollection->push($locationDnsSettingRecordId);
        }

        $defaultRecordIdCollection = $defaultRecordIdCollection->collapse();
        $locationDnsSettingRecordIdCollection = $locationDnsSettingRecordIdCollection->collapse();

        return $defaultRecordIdCollection->merge($locationDnsSettingRecordIdCollection);
    }

    /**
     * 取得 DB 多餘 import 的資料，也就是 代刪除的資料
     *
     * @param Collection $originData
     * @param Collection $importData
     * @return void
     */
    private function getNeedToDeleteData(Collection $originData, Collection $importData)
    {
        $sameNameCollection = $this->sameName($originData, $importData);

        return $this->originDataWithoutSameName($originData,$sameNameCollection);
    }

    /**
     * 刪除 所有 DnsPod 上面的 Record Id
     *
     * @param Collection $needToDeleteRecordIdCollection
     * @return void
     */
    private function deleteDnsPodByRecordId(Collection $needToDeleteRecordIdCollection)
    {        
        foreach ($needToDeleteRecordIdCollection as $key => $needToDeleteRecordId) {
            $deletePodRecord = $this->dnsProviderService->deleteRecord([
                'record_id' => $needToDeleteRecordId,
            ]);
        }

        return true;
    }

    /**
     * 取得 domain 下所有 record Id (包含 default Cdn 和 iRoute) 
     */

    private function getDomainRecords(Collection $domainNameCollection)
    {
        $cdnsRecordIdCollection = collect();
        $locationSettingRecordIdCollection = collect();
        foreach($domainNameCollection as $domainName){
            $domain = Domain::where('name',$domainName)->first();

            $cdnsRecordId = 
            $domain->cdns->pluck('provider_record_id')
                            ->reject(function ($recordId){
                                return $recordId == 0;
                            });
            
            $cdnsRecordIdCollection->push($cdnsRecordId);

            if(!$domain->locationDnsSettings->isEmpty())
            {
                $locationSettingRecordId = $domain->locationDnsSettings->pluck('provider_record_id');
                $locationSettingRecordIdCollection->push($locationSettingRecordId); 
            }

        }
        $cdnsRecordIdCollection = $cdnsRecordIdCollection->collapse();
        $locationSettingRecordIdCollection = $locationSettingRecordIdCollection->collapse();

        return $cdnsRecordIdCollection->merge($locationSettingRecordIdCollection);
    }

    /**
     * 找出 import 和 原本在 dataBase 裡一樣 name 的資料
     */

    private function sameName(Collection $originNames, Collection $importNames)
    {
        $sameNamesCollection = $importNames->filter(function ($name) use ($originNames){

            $sameName = '';

            foreach($originNames as $originName){
                if($originName == $name){
                    $sameName = $name;
                }
            }
            return $sameName;
        });

        return $sameNamesCollection;
    }

    /**
     * 找出 有在 DataBase 卻沒有在 import 裡的
     *
     * @param Collection $originDomains
     * @param Collection $sameDomainsCollection
     * @return void
     */
    private function originDataWithoutSameName(Collection $originData, Collection $sameNameCollection)
    {
        $needToDeleteData = $originData->diff($sameNameCollection);

        return $needToDeleteData->values();
    }

}