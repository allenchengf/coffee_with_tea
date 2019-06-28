<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Hiero7\Enums\InputError;
use Hiero7\Enums\InternalError;
use Hiero7\Services\LocationDnsSettingService;
use Illuminate\Http\Request;
use Hiero7\Models\LocationNetwork;
use Hiero7\Models\{Domain,Cdn,LocationDnsSetting};
use App\Http\Requests\LocatinDnsSettingRequest;

class LocationDnsSettingController extends Controller
{
    protected $locationDnsSettingService;

    public function __construct(LocationDnsSettingService $locationDnsSettingService)
    {
        $this->locationDnsSettingService = $locationDnsSettingService;
    }

    public function getAll(Domain $domain)
    {
        $result = $this->locationDnsSettingService->getAll($domain->id);
        return $this->response('',null,$result);

    }

    public function editSetting(LocatinDnsSettingRequest $request, Domain $domain, LocationNetwork $locationNetworkId)
    {
        $message = '';
        $error = '';
        
        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
        ]);

        $cdnModel = $this->checkCdnIfExist($request->get('cdn_id'), $domain);

        if (!$cdnModel) {
            return $this->setStatusCode(400)->response($message,InputError::WRONG_PARAMETER_ERROR,'');
        }

        $existLocationDnsSetting = $this->checkExist($domain, $locationNetworkId);

        if (!collect($existLocationDnsSetting)->isEmpty()) {
            $result = $this->locationDnsSettingService->updateSetting($request->all(),$domain,$cdnModel, $existLocationDnsSetting);
        } else {
            $result = $this->locationDnsSettingService->createSetting($request->all(), $domain, $cdnModel ,$locationNetworkId);
        }

        if ($result == false) {
            return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
        }
        
        $data = $this->locationDnsSettingService->getAll($domain->id);

        return $this->response($message,$error,$data);
    }

    private function checkCdnIfExist(int $cdnId, Domain $domain)
    {
        return $domain->cdns()->where('id', $cdnId)->first();
    }

    private function checkExist(Domain $domain,LocationNetwork $locationNetwork)
    {
        $cdnId = Cdn::where('domain_id',$domain->id)->pluck('id');
        return LocationDnsSetting::where('location_networks_id',$locationNetwork->id)->whereIn('cdn_id',$cdnId)->first();
        
    }
}
