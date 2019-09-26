<?php

namespace Hiero7\Repositories;

use Hiero7\Models\ScanLog;
use Hiero7\Enums\DbError;
use Hiero7\Traits\JwtPayloadTrait;
use DB;

class ScanLogRepository
{
    use JwtPayloadTrait;

    protected $scanLogModel;

    public function __construct(ScanLog $scanLog)
    {
        $this->scanLogModel = $scanLog;
    }

    public function indexAll()
    {
        return $this->scanLogModel->orderBy('created_at', 'desc')->get();
    }

    /**
     * 根據 Login User Group & Cdn Provider 取得最後一次掃描結果
     * @param int $cdnProviderId
     * @param int/null $scanPlatformId
     * @return mixed
     */
    public function indexLatestLogs($cdnProviderId, $scanPlatformId=null)
    {
        $scanLog = $this->scanLogModel
                        ->select(DB::raw('group_concat(COALESCE(latency, "null")) as latency, group_concat(location_network_id) as location_network_id, scan_logs.created_at'))
                        ->leftJoin('cdn_providers', 'cdn_providers.id', '=', 'scan_logs.cdn_provider_id')
                        ->where('cdn_providers.user_group_id', $this->getJWTUserGroupId())
                        ->where('scan_logs.cdn_provider_id', $cdnProviderId)
                        ->groupBy('scan_logs.created_at')
                        ->orderBy('scan_logs.created_at', 'desc');
        
        if(! is_null($scanPlatformId))
            $scanLog->where('scan_platform_id', $scanPlatformId);

        return $scanLog->first();
    }

    /**
     * 輔助 indexEarlierLogs 用
     * @param int/null $cdnProviderId
     * @param int/null $scanPlatformId
     * @return mixed
     */
    public function showLatestLog($cdnProviderId=null, $scanPlatformId=null)
    {
        $scanLog = $this->scanLogModel
                        ->select('scan_logs.*')
                        ->leftJoin('cdn_providers', 'cdn_providers.id', '=', 'scan_logs.cdn_provider_id')
                        ->where('cdn_providers.user_group_id', $this->getJWTUserGroupId())
                        ->orderBy('scan_logs.created_at', 'desc');

        if(! is_null($cdnProviderId))
            $scanLog = $scanLog->where('scan_logs.cdn_provider_id', $cdnProviderId);
        
        if(! is_null($scanPlatformId))
            $scanLog = $scanLog->where('scan_logs.scan_platform_id', $scanPlatformId);

        return $scanLog->first();
    }

    /**
     * 根據 Login User Group 取得最後一次掃描結果
     * @param int/null $cdnProviderId
     * @param int/null $scanPlatformId
     * @return mixed
     */
    public function indexEarlierLogs($lastCreatedAt, $cdnProviderId=null, $scanPlatformId=null)
    {
        $interval = env('SCAN_LOG_INTERVAL');

        $to = &$lastCreatedAt;
        $timestamp = strtotime($to) - $interval; // 最近一筆 Log.created_at 時間，往前推 $interval 秒。
        $from = date('Y-m-d H:i:s', $timestamp);

        $scanLogs = $this->scanLogModel
                    ->select('scan_logs.*')
                    ->leftJoin('cdn_providers', 'cdn_providers.id', '=', 'scan_logs.cdn_provider_id')
                    ->where('cdn_providers.user_group_id', $this->getJWTUserGroupId())
                    ->whereBetween('scan_logs.created_at', [$from, $to]);
        
        if(! is_null($cdnProviderId))
            $scanLogs = $scanLogs->where('scan_logs.cdn_provider_id', $cdnProviderId);
    
        if(! is_null($scanPlatformId))
            $scanLogs = $scanLogs->where('scan_logs.scan_platform_id', $scanPlatformId);

        return $scanLogs->get();
    }

    public function create(array $data)
    {
        try {
            return $this->scanLogModel->create($data);
        } catch (\Exception $e) {
            if ($e->getCode() == '23000')
                return new \Exception('', DbError::DUPLICATE_ROW);  
            return $e;
        }
    }

    public function delete($id)
    {
        try {
            return $this->scanLogModel->find($id)->delete();
        } catch (\Exception $e) {
            return $e;
        }
    }
}
