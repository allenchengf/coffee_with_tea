<?php

namespace App\Http\Controllers\Api\v1;

use Hiero7\Enums\DbError;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Hiero7\Services\LocationDnsSettingService;
use Symfony\Component\Console\Input\InputInterface;

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
        if($this->locationDnsSettingService->getByRid($domain,$rid)) 
        { //修改設定資料
            $result =  $this->locationDnsSettingService->updateSetting($request->all(),$domain,$rid);
            if ($result)
            {
                $message = '';
                $error = '';
                $data = $result;
            }else{
                $message = InputError::getDescription(InputError::WRONG_PARAMETER_ERROR);
                $error = InputError::WRONG_PARAMETER_ERROR;
                $data = $result;
            }
        }else{
            $result = $this->locationDnsSettingService->createSetting($request->all(),$domain); //新增設定資料
            if ($result)
            {
                $message = '';
                $error = '';
                $data = $result;
            }else{
                $message = DbError::getDescription(DbError::FOREIGN_CONSTRAINT_OR_CDN_SETTING);
                $error = DbError::FOREIGN_CONSTRAINT_OR_CDN_SETTING;
                $data = $result;
            }
        }

        return $this->setStatusCode($result ? 200 : 400)->response(
            $message,
            $error,
            $data
        );
    }
}
