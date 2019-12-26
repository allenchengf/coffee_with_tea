<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DomainPinRequest as Request;
use Hiero7\Models\DomainPin;

class DomainPinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(DomainPin $domainPin)
    {
        $domainPins = $domainPin->all();

        return $this->response('', '', $domainPins);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, DomainPin $domainPin)
    {
        $data = $request->only('user_group_id', 'name', 'edited_by');

        $domainPin = $domainPin->create($data);

        return $this->response('', '', $domainPin);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(DomainPin $domainPin)
    {
        return $this->response('', '', $domainPin);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DomainPin $domainPin)
    {
        $name = $domainPin->name;

        $domainPin->delete();

        return $this->response('', '', $name);
    }
}
