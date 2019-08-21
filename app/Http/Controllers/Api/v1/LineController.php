<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Hiero7\Enums\InputError;
use Hiero7\Models\LocationNetwork as Line;
use Hiero7\Services\DnsProviderService;
use Hiero7\Services\LineService;
use App\Http\Requests\LineRequest as Request;
use Hiero7\Services\SchemeService;

class LineController extends Controller
{

    protected $lineService;
    protected $schemeService;

    /**
     * LineController constructor.
     */
    public function __construct(LineService $lineService, SchemeService $schemeService)
    {
        $this->lineService = $lineService;
        $this->schemeService = $schemeService;
    }

    public function index()
    {
        $data = $this->lineService->getAll();
        return $this->response("Success", null, $data);
    }

    public function create(Request $request)
    {
        $this->requestMergeEditedBy($request);

        $errorCode = null;

        $line = [];
        if ($this->lineService->checkNetworkId($request->get('network_id'))) {
            $errorCode = InputError::THE_NETWORK_ID_EXIST;
        } else {
            $line = $this->lineService->create($request->all());
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode ? $errorCode : null,
            $line
        );
    }

    public function edit(Request $request, Line $line)
    {
        $this->requestMergeEditedBy($request);

        $line->update($request->only('continent_id', 'country_id', 'location', 'isp', 'mapping_value'));

        return $this->response("Success", null, $line);
    }

    public function destroy(Line $line, DnsProviderService $dnsProviderService)
    {
        $deleteData = $line->locationDnsSetting->map(function ($locationDnsSetting) {
            return ['id' => $locationDnsSetting->provider_record_id];
        });

        $dnsProviderService->syncRecordToDnsPod([
            'delele' => json_encode($deleteData)
        ]);

        $line->locationDnsSetting()->delete();

        $line->delete();

        return $this->response();
    }
}
