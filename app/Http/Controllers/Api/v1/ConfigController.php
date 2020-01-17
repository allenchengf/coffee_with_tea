<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hiero7\Models\{Domain, Cdn, CdnProvider, LocationDnsSetting, DomainGroup, DomainGroupMapping};
use Hiero7\Services\{ConfigService, DnsPodRecordSyncService, UserModuleService};
use Hiero7\Repositories\{BackupRepository, CdnProviderRepository};
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Hiero7\Traits\{DomainHelperTrait, JwtPayloadTrait};
use DB;
use Cache;
use Storage;
use AWS;
use Hiero7\Enums\{InputError, DbError, InternalError};

class ConfigController extends Controller
{
    use DomainHelperTrait;    
    use JwtPayloadTrait;
    protected $configService;

    public function __construct(
        ConfigService $configService,
        DnsPodRecordSyncService $dnsPodRecordSyncService,
        UserModuleService $userModuleService,
        BackupRepository $backupRepository,
        CdnProviderRepository $cdnProviderRepository)
    {
        $this->configService = $configService;
        $this->dnsPodRecordSyncService = $dnsPodRecordSyncService;
        $this->userModuleService = $userModuleService;
        $this->backupRepository = $backupRepository;
        $this->cdnProviderRepository = $cdnProviderRepository;
    }
    
    public function index(Request $request,Domain $domain, CdnProvider $cdnProvider,DomainGroup $domainGroup)
    {
        $userGroupId = $this->getUgid($request);
        $result = $this->getDataBaseAllSetting($userGroupId, $domain, $cdnProvider, $domainGroup );
        return $this->response('', null, $result);

    }
    
    public function storeBackup()
    {
        $nowTimestamp = strtotime('now'); // 當下時間戳
        $nowHoursMinutes = (string)date('H:i', $nowTimestamp); // (string) "小時:分鐘"
        $envBackedupAt = env('BACKUP_AT', '03:00'); // `env 設定統一排程備份時間`，當 user 未設定排程備份時間ㄉ時候。
        $boolSameTime = $nowHoursMinutes === $envBackedupAt; // 當下時間戳 同 `env 設定統一排程備份時間` ?
        $nowHoursMinutesRegexPattern = "/^$nowHoursMinutes/"; // Regex Pattern: 當下時間
        $results = [
            'backup_at' => date('Y-m-d H:i:s', $nowTimestamp),
            'consequences' => []
        ];

        // 預計備份時間，從 backups 表拿
        $backups = $this->backupRepository->index();

        // 取得完整的 all users' Ugid
        $userGroups = $this->cdnProviderRepository->getUgids();
        // (註解後有可能恢復) curl iRouteCDN>user_mudule 拿完整的 all users' Ugid
        // $userGroups = $this->userModuleService->getAllUserGroups($request)['data'];
        if (! $userGroups)
            return $this->setStatusCode(400)->response('', InternalError::INTERNAL_ERROR, []);

        $userGroups->each(function ($ug) use (&$backups, &$boolSameTime, &$nowHoursMinutesRegexPattern, &$nowTimestamp, &$results) {
            $backup = $backups->where('user_group_id', $ug['user_group_id'])->first();

            // user有設時間 依其時間，且剛好是現在時間～
            if (! is_null($backup) && preg_match($nowHoursMinutesRegexPattern, $backup->backedup_at)) {
                $results['consequences'][] = [
                    'ugid' => $ug['user_group_id'],
                    'upload_s3' => $this->storeBackupToS3($ug['user_group_id'], $nowTimestamp),
                ];
                return true;
            }

            // user沒設時間 依env時間，且env時間剛好是現在時間～
            if (is_null($backup) && $boolSameTime) {
                $results['consequences'][] = [
                    'ugid' => $ug['user_group_id'],
                    'upload_s3' => $this->storeBackupToS3($ug['user_group_id'], $nowTimestamp),
                ];
                return true;
            }
            
            // 非為備份時間
            $results['consequences'][] = [
                'ugid' => $ug['user_group_id'],
                'upload_s3' => [
                    'success' => false,
                    'message' => 'not in time',
                ]
            ];
        });
        return $this->response('', null, $results);

    }

    public function storeBackupByUgid()
    {
        $ugid = $this->getJWTUserGroupId();
        $nowTimestamp = strtotime('now');

        $data = $this->storeBackupToS3($ugid, $nowTimestamp);
        if(! isset($data['success']) || $data['success'] == false)
            return $this->setStatusCode(400)->response('', InternalError::INTERNAL_ERROR, []);

        return $this->response('', null, []);
    }
    

    private function storeBackupToS3($userGroupId, $timestamp)
    {
        $fileName = $userGroupId . '_' . $timestamp .'.json'; // s3 上的檔名: "$ugid_$timestamp.json"

        $domain = new Domain;
        $cdnProvider = new CdnProvider;
        $domainGroup = new DomainGroup;
        
        // 取該備份的 json 內容
        $content = $this->getDataBaseAllSetting($userGroupId, $domain, $cdnProvider, $domainGroup);
        if (! $content)
            return [
                'success' => false,
                'message' => 'ConfigController::getDataBaseAllSetting() error',
            ];
        $jsonContent = json_encode($content);

        // 暫存於: Storage::disk('local')
        Storage::disk('local')->put($fileName, $jsonContent);
        // 取檔案絕對路徑
        $path = Storage::disk('local')->path($fileName);

        // 上傳 s3 
        $s3 = AWS::createClient('s3');
        $s3Callback = $s3->putObject([
            'Bucket'     => env('S3_BUCKET_NAME_CONFIG_BACKUP', 'iroutecdn-config-backup'), // Bucket 已設定內部檔案 lifecycle 為 30 天
            'Key'        => $fileName,
            'SourceFile' => $path,
            'ACL'        => 'public-read', // 上傳即公開 read 權限
        ]);

        // 上傳 s3 後，本地刪掉檔案
        Storage::disk('local')->delete($fileName);
        
        return [
            'success' => true,
        ];
    }
    
    public function indexBackupFromS3(Request $request)
    {
        $data = [];
        $s3BucketDomain = '';

        // ugid
        $userGroupId = $this->getUgid($request);

        // 建立 s3 channel
        $s3 = AWS::createClient('s3');

        // 來去 s3 找找，前綴 prefix 會是 "$ugid_"
        $s3Objects = $s3->listObjects([
            'Bucket' => env('S3_BUCKET_NAME_CONFIG_BACKUP', 'iroutecdn-config-backup'), // 記得設定 s3 lifecycle
            'Prefix' => $userGroupId . '_',
        ]);

        // err: S3 Bucket 不存在，或根本沒 ugid 的檔案
        if (! $s3Objects)
            return $this->setStatusCode(400)->response('', InternalError::CHECK_S3_BUCKET_IF_EXISTS, []);

        // err: S3 callback 異常
        if (! isset($s3Objects['@metadata']) || ! isset($s3Objects['@metadata']['effectiveUri']) || ! isset($s3Objects['Contents']))
            return $this->setStatusCode(400)->response('', InternalError::NO_S3_FILES_FROM_UIGD, []);

        // 取 s3 domain
        $s3BucketDomain = explode('/?prefix=', $s3Objects['@metadata']['effectiveUri'])[0];
        // 取 s3 files
        if (isset($s3Objects['Contents']))
            collect($s3Objects['Contents'])->each(function ($object) use (&$data, &$s3BucketDomain) {
                $matches = [];
                preg_match('/_([0-9]+)/', $object['Key'], $matches);
                $data[] = [
                    'key' => $matches[1],
                    'created_at' => date('Y-m-d H:i:s', $object['LastModified']->getTimestamp()),
                ];
            });

        // files 時間排序 Desc
        $data = collect($data)->sortByDesc('created_at')->values();
        
        return $this->response('', null, $data);
    }

    public function showBackupFromS3(Request $request, $key)
    {
        $data = $this->getBackupFromS3($request, $key);
        if (is_numeric($data))
            return $this->setStatusCode(400)->response('', $data, []);

        return $this->response('', null, $data);
    }


    public function getBackupFromS3(Request $request, $key)
    {
        // ugid
        $userGroupId = $this->getUgid($request);

        // 檔名與路徑
        $fileName = $userGroupId . '_' . $key . '.json';
        $filePath = storage_path('app') . '/' . $fileName;

        // 建立 s3 channel
        $s3 = AWS::createClient('s3');
        
        // 來去 s3 找找
        $s3Objects = $s3->listObjects([
            'Bucket' => env('S3_BUCKET_NAME_CONFIG_BACKUP', 'iroutecdn-config-backup'),
            'Prefix' => $fileName,
        ]);
        
        // err: S3 Bucket 檔案不存在
        if (! isset($s3Objects['Contents']))
            return InternalError::NO_S3_FILES_FROM_UIGD;
        
        // 取 s3 檔案暫存本地
        $s3Objects = $s3->getObject([
            'Bucket' => env('S3_BUCKET_NAME_CONFIG_BACKUP', 'iroutecdn-config-backup'),
            'Key'    => $fileName,
            'SaveAs' => $filePath
        ]);

        // 讀取本地檔案內容
        $data = json_decode(Storage::disk('local')->get($fileName), true);

        // 本地刪掉檔案
        Storage::disk('local')->delete($fileName);

        return $data;
    }

    private function getDataBaseAllSetting(int $userGroupId,Domain $domain, CdnProvider $cdnProvider,DomainGroup $domainGroup)
    {
        $domains = $domain->with('cdns','locationDnsSettings')->where('user_group_id',$userGroupId)->get();
        $cdnProviders = $cdnProvider->where('user_group_id',$userGroupId)->get();
        $domainGroups = $domainGroup->where('user_group_id',$userGroupId)->with('mapping')->get();

        return compact('domains','cdnProviders','domainGroups');
    }

    public function import(Request $request)
    {
        $res = $this->import2($request);
        if (is_numeric($res) && $res !== true)
            return $this->setStatusCode(400)->response('', $res, []);

        return $this->response("Success", null,'');
    }


    public function restoreBackupFromS3(Request $request, $key)
    {
        $data = $this->getBackupFromS3($request, $key);
        if (is_numeric($data))
            return $this->setStatusCode(400)->response('', $data, []);

        $request['domains'] = $data['domains'];
        $request['cdnProviders'] = $data['cdnProviders'];
        $request['domainGroups'] = $data['domainGroups'];

        $res = $this->import2($request);
        if (is_numeric($res) && $res !== true)
            return $this->setStatusCode(400)->response('', $res, []);

        return $this->response("Success", null,'');
    }

    // 原著: Yuan
    // 修改: Justin
    // [修改為重複使用，修改處]
    // 1. function 參數僅 (Request $request)
    // 2. return (數字) InputError::XXX
    public function import2(Request $request)
    {
        $userGroupId = $this->getUgid($request);

        Cache::put("Config_userGroupId$userGroupId" , true , env('CONFIG_WAIT_TIME'));
        DB::beginTransaction();

        $importData = $this->formateDataAndCheckCdn($request);

        if(isset($importData['errorData'])){
            DB::rollback();
            Cache::forget("Config_userGroupId$userGroupId");
            return InputError::WRONG_PARAMETER_ERROR;
        }

        $checkDomainResult = $this->configService->checkDomainFormate($importData);

        //不符合格式 return false 並 rollback
        if(isset($checkDomainResult['errorData'])){
            DB::rollback();
            Cache::forget("Config_userGroupId$userGroupId");
            return InputError::WRONG_PARAMETER_ERROR;
        }

        $domain = new Domain;
        $cdnProvider = new CdnProvider;
        $domainGroup = new DomainGroup;

        $originDomains = $domain->where('user_group_id',$userGroupId)->get();
        $originCdnProviders = $cdnProvider->where('user_group_id',$userGroupId)->get();

        // 拿到 import 沒有 但 DB 有的 Record id
        $allRecordId = $this->configService->compareDatabaseNotInImportRecordId($originDomains, $originCdnProviders,$importData['domains'],$importData['cdnProviders']);

        // 刪除各個 table 裡的資料
        $this->deleteLocationDnsSetting($domain,$cdnProvider,$userGroupId);
        $this->deleteTableData($originDomains);
        $this->deleteTableData($originCdnProviders);
        $domainGroup->where('user_group_id',$userGroupId)->delete();

        //新增 Domain
        $this->configService->insert($importData['domains'],$domain, $userGroupId);
        //新增 cdnProvider
        $this->configService->insert($importData['cdnProviders'], $cdnProvider, $userGroupId);
        //新增 cdns
        $this->configService->insert($importData['cdns'],new Cdn, $userGroupId);
        //新增 LocationDns
        $this->configService->insert($importData['locationDnsSetting'],new LocationDnsSetting, $userGroupId);
        //新增 DomainGroup
        $this->configService->insert($importData['domainGroups'], $domainGroup, $userGroupId);
        //新增 DomainGroupMapping
        $this->configService->insert($importData['domainGroupsMapping'], new DomainGroupMapping, $userGroupId);

        DB::commit();

        // 等 DB 資料都確認後在刪除 Pod 上的資料 
        $this->configService->deleteDnsPodByRecordId($allRecordId);
        $this->callSync($domain, $userGroupId);

        Cache::forget("Config_userGroupId$userGroupId");
        return true;
    }

    private function deleteTableData($originData)
    {
        foreach($originData as $data)
        {
            $data->delete();
        }

        return ;
    }

    private function deleteLocationDnsSetting(Domain $domain, CdnProvider $cdnProvider, int $userGroupId)
    {
        $domainId = $domain->where('user_group_id',$userGroupId)->pluck('id');
        $cdnProviderId = $cdnProvider->where('user_group_id',$userGroupId)->pluck('id');
        $cdnId = Cdn::whereIn('domain_id',$domainId)->whereIn('cdn_provider_id',$cdnProviderId)->pluck('id');
        
        return LocationDnsSetting::whereIn('cdn_id',$cdnId)->delete();
    }

    private function callSync(Domain $domain,Int $userGroupId)
    {
        $domains = $domain->where('user_group_id',$userGroupId)->get();
        
        $result = [];
        foreach($domains as $domainModel){
            $result[] = $this->dnsPodRecordSyncService->syncAndCheckRecords($domainModel);
        }

        return $result;
    }

    private function formateDataAndCheckCdn(Request $request)
    {
        $importData = $request->all();

        $domain =  $this->formateDomainArray($importData['domains']);
        $checkCdnResult = $this->configService->checkCdnHaveDefault($importData['domains']);

        if(isset($checkCdnResult['errorData'])){
            return $checkCdnResult;
        }

        $cdn =  $this->formateCdnArray($importData['domains']);
        $locationDnsSetting =  $this->formateLocationDnsSettingArray($importData['domains']);
        list($domainGroup,$domainGroupMapping) = $this->formateDomainGroupMapping($importData['domainGroups']);

        $cdnProvider= $this->formateCdnProvider($importData['cdnProviders']);
        
        $result = ['domains' => $domain,
                    'cdns' => $cdn,
                    'locationDnsSetting' => $locationDnsSetting,
                    'cdnProviders' => $cdnProvider,
                    'domainGroups' => $domainGroup,
                    'domainGroupsMapping' => $domainGroupMapping];

        return $result;
    }

    private function formateCdnProvider(Array $cdnProviders)
    {
        foreach($cdnProviders as &$cdnProvidersModel){
            if($cdnProvidersModel['status'] == 'true'){
                $cdnProvidersModel['status'] = "active";
            }else{
                $cdnProvidersModel['status'] = "stop";
            }
        }

        return $cdnProviders;
    }
    private function formateDomainGroupMapping(array $domainGroupWithMapping)
    {
        $domainGroupArray = [];
        $domainGroupMapping = [];
        foreach($domainGroupWithMapping as $domainGroup){
            $mappingArray = array_only($domainGroup,['mapping']);
            $domainGroupMapping[] = $mappingArray['mapping'];
            $domainGroupArray[] =array_except($domainGroup,['mapping']);
        }

        return [$domainGroupArray,array_collapse($domainGroupMapping)];
    }

    private function formateDomainArray(array $domainWithOther)
    {
        $domains = [];
        foreach($domainWithOther as $domain){
            $domains[]  = array_except($domain,['cdns','location_dns_settings']);
        }

        return $domains;
    }

    private function formateCdnArray(array $domainWithOther)
    {
        $cdns = [];
        foreach($domainWithOther as $domain){
            $cdnsArray = array_only($domain,['cdns']); 

            if(empty($cdnsArray)){
                continue;
            }        
            $cdns [] = $cdnsArray['cdns'];
        }
        $cdns = collect($cdns)->collapse();
        
        return $cdns->all();
    }

    private function formateLocationDnsSettingArray(array $domainWithOther)
    {
        $locationDnsSetting = [];
        foreach($domainWithOther as $domain){
            $locationDnsSettingArray = array_only($domain,['location_dns_settings']);  
            if(empty($locationDnsSettingArray)){
                continue;
            }
            foreach($locationDnsSettingArray['location_dns_settings'] as &$array){
                $array = array_except($array,['domain_id']);
            }
            $locationDnsSetting [] = $locationDnsSettingArray['location_dns_settings'];
        }
        $locationDnsSettings = collect($locationDnsSetting)->collapse();
        
        return $locationDnsSettings->all();
    }
}
