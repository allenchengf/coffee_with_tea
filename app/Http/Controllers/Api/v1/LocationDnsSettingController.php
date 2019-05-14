<?php

namespace App\Http\Controllers\Api\v1;

use Hiero7\Enums\DbError;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Hiero7\Services\LocationDnsSettingService;

class LocationDnsSettingController extends Controller
{
    protected $locationDnsSettingService;

    public function __construct(LocationDnsSettingService $locationDnsSettingService)
    {
        $this->locationDnsSettingService = $locationDnsSettingService;
    }

    public function getAll($domain)
    {
        $result = $this->locationDnsSettingService->getAll($domain);
        return $this->setStatusCode($result ? 400 : 200)->response(
            '',
            '',$result
        );

    }

    public function editSetting(Request $request,$domain,$rid)
    {
        $checkPodId = $this->locationDnsSettingService->checkPodId($domain,$rid);
        if($checkPodId) 
        { //修改設定資料
            $result =123;
            $message = '';
            // $result =  $this->locationDnsSettingService->updateSetting($request,$domain,$rid);
        }else{
            $result = $this->locationDnsSettingService->createSetting($request,$domain,$rid); //新增設定資料
            // $result = $this->locationDnsSettingService->getAll($$domain);

            if (!$result)
            {
                $message = DbError::getDescription(DbError::FOREIGN_CONSTRAINT);
                $error = DbError::FOREIGN_CONSTRAINT;
                $data = [];
            }else{
                $message = '';
                $error = '';
                $data = $result;
            }
        }

        return $this->setStatusCode($result ? 400 : 200)->response(
            $message,
            $error,
            $data
        );
    }
}
