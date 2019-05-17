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
        return $this->setStatusCode($result ? 200 : 400)->response(
            '',
            '',$result
        );
    }

    public function editSetting(Request $request,$domain,$rid)
    {
        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid']
        ]);
            // dd($this->locationDnsSettingService->checkExit($domain,$rid));
        if($this->locationDnsSettingService->checkExit($domain,$rid)) 
        { 
            $result =  $this->locationDnsSettingService->updateSetting($request->all(),$domain,$rid);

            if ($result === 'error')
            {
                return $this->setStatusCode(409)->response('please contact the admin', null, []);

            }elseif($result == false){
                $message = InputError::getDescription(InputError::WRONG_PARAMETER_ERROR);
                $error = InputError::WRONG_PARAMETER_ERROR;
                $data = $result;
            }else{
                $message = '';
                $error = '';
                $data = $result;
            }

        }else{
            $result = $this->locationDnsSettingService->createSetting($request->all(),$domain,$rid);
// dd($result);
            if ($result === 'error')
            {
                return $this->setStatusCode(409)->response('please contact the admin', null, []);

            }elseif($result == false){
                $message = DbError::getDescription(DbError::FOREIGN_CONSTRAINT_OR_CDN_SETTING);
                $error = DbError::FOREIGN_CONSTRAINT_OR_CDN_SETTING;
                $data = $result;
            }else{
                $message = '';
                $error = '';
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
