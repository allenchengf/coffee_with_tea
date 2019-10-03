<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LineRequest as Request;
use Hiero7\Enums\InternalError;
use Hiero7\Models\LocationNetwork as Line;
use Hiero7\Services\DnsProviderService;
use Hiero7\Services\LineService;
use Hiero7\Services\SchemeService;
use Illuminate\Support\Collection;

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

    /**
     * @param Request $request
     * @return LineController
     */
    public function create(Request $request)
    {
        $this->requestMergeEditedBy($request);

        $line = $this->lineService->create($request->all());

        return $this->response('', null, $line);
    }

    /**
     * @param Request $request
     * @param Line $line
     * @return LineController
     */
    public function edit(Request $request, Line $line)
    {
        $this->requestMergeEditedBy($request);

        $line->update($request->only('continent_id', 'country_id', 'location', 'isp', 'mapping_value'));

        return $this->response("Success", null, $line);
    }


    /**
     * @param Line $line
     * @param DnsProviderService $dnsProviderService
     * @return LineController
     * @throws \Exception
     */
    public function destroy(Line $line, DnsProviderService $dnsProviderService)
    {
        $deleteData = $line->locationDnsSetting->map(function ($locationDnsSetting) {
            return ['id' => $locationDnsSetting->provider_record_id];
        });

        if (!$this->deletDNSRecord($dnsProviderService, $deleteData)) {
            return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR);
        }

        $line->locationDnsSetting()->delete();

        $line->delete();

        $line->network->delete();

        return $this->response();
    }

    /**
     * 刪除 DNS Record 會判斷是否刪除成功
     *
     * @param DnsProviderService $dnsProviderService
     * @param Collection $deleteData
     * @return bool
     */
    private function deletDNSRecord(DnsProviderService $dnsProviderService, Collection $deleteData): bool
    {
        if (!$deleteData->count()) {
            return true;
        }

        $response = $dnsProviderService->syncRecordToDnsPod([
            'delele' => json_encode($deleteData),
        ]);

        if ($response && !$response['errorCode']) {
            return ($deleteData->count() == count($response['data']['deleteSync'])) ? true : false;
        }

        return false;
    }
}
