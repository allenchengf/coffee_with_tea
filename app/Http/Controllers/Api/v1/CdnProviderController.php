<?php

namespace App\Http\Controllers\Api\v1;

use Hiero7\Models\CdnProvider;
use App\Http\Controllers\Controller;
use Hiero7\Services\CdnProviderService;
use App\Http\Requests\CdnProviderRequest as Request;

class CdnProviderController extends Controller
{
    protected $cdnProviderService;
    /**
     * CdnProviderController constructor.
     */
    public function __construct(CdnProviderService $cdnProviderService)
    {
        $this->cdnProviderService = $cdnProviderService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_group_id = $this->getUgid($request);
        $result = $this->cdnProviderService->getCdnProvider($user_group_id);

        return $this->setStatusCode($result ? 200 : 404)->response('success', null, $result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, CdnProvider $cdnProvider)
    {
        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
            'status' => 'active'
        ]);
        $cdnProvider = $cdnProvider->create($request->all());
        return $this->response('', null, $cdnProvider);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Hiero7\Models\CdnProvider  $cdnProvider
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, CdnProvider $cdnProvider)
    {
        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
        ]);
        $cdnProvider->update($request->only('name','ttl'));
        return $this->response("Success", null, $cdnProvider);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Hiero7\Models\CdnProvider  $cdnProvider
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CdnProvider $cdnProvider)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Hiero7\Models\CdnProvider  $cdnProvider
     * @return \Illuminate\Http\Response
     */
    public function destroy(CdnProvider $cdnProvider)
    {
        //
    }
}
