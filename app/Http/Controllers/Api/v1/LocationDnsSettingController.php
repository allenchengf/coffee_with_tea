<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Hiero7\Enums\InputError;
use Hiero7\Enums\InternalError;
use Hiero7\Services\LocationDnsSettingService;
use Illuminate\Http\Request;

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
            '', $result
        );
    }

    public function editSetting(Request $request, $domain, $locationNetworkId)
    {
        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
        ]);

        if ($this->locationDnsSettingService->checkExistDnsSetting($domain, $locationNetworkId)) {
            $result = $this->locationDnsSettingService->updateSetting($request->all(), $domain, $locationNetworkId);
        } else {
            $result = $this->locationDnsSettingService->createSetting($request->all(), $domain, $locationNetworkId);
        }

        if ($result === 'error') {
            return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
        } elseif ($result == false) {
            $message = InputError::getDescription(InputError::WRONG_PARAMETER_ERROR);
            $error = InputError::WRONG_PARAMETER_ERROR;
            $data = $result;
        } else {
            $message = '';
            $error = '';
            $data = $this->locationDnsSettingService->getAll($domain);
        }

        return $this->setStatusCode($result ? 200 : 400)->response(
            $message,
            $error,
            $data
        );
    }
}
