<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\Http\Requests\ScanPlatformRequest as Request;
use Hiero7\Enums\InputError;
use Hiero7\Models\ScanPlatform;
use Hiero7\Services\ScanPlatformService;

class ScanPlatformController extends Controller
{
    protected $scanPlatformService;

    /**
     * ScanPlatformController constructor.
     */
    public function __construct(ScanPlatformService $scanPlatformService)
    {
        $this->scanPlatformService = $scanPlatformService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->scanPlatformService->getAll();
        return $this->response("Success", null, $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);
        $errorCode = null;
        $scanPlatform = [];
        if ($this->scanPlatformService->checkScanPlatformName($request->get('name', ''))) {
            $errorCode = InputError::THE_SCAN_PLATFORM_NAME_EXIST;
        } else {
            $scanPlatform = $this->scanPlatformService->create($request->all());
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode ? $errorCode : null,
            $scanPlatform
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ScanPlatform  $scanPlatform
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, ScanPlatform $scanPlatform)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);
        $scanPlatform->update($request->all());
        return $this->response("Success", null, $scanPlatform);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  ScanPlatform  $scanPlatform
     * @return \Illuminate\Http\Response
     */
    public function destroy(ScanPlatform $scanPlatform)
    {
        $scanPlatform->delete();
        return $this->response();
    }
}
