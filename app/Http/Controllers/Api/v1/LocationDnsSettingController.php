<?php

namespace App\Http\Controllers\Api\v1;

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

    public function getAll()
    {
        return $this->locationDnsSettingService->getAll();
    }

    public function editSetting(Request $request,$settingId)
    {
        return $this->locationDnsSettingService->getAll($settingId);
    }
}
