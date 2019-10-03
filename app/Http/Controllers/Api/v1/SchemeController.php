<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchemeRequest as Request;
use Hiero7\Enums\InputError;
use Hiero7\Models\Scheme;
use Hiero7\Services\LineService;
use Hiero7\Services\SchemeService;

class SchemeController extends Controller
{
    protected $schemeService;
    protected $lineService;
    /**
     * SchemeController constructor.
     */
    public function __construct(SchemeService $schemeService, LineService $lineService)
    {
        $this->schemeService = $schemeService;
        $this->lineService = $lineService;
    }

    public function index()
    {
        $data = $this->schemeService->getAll();
        return $this->response("Success", null, $data);
    }

    public function create(Request $request)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);
        $errorCode = null;
        $scheme = [];
        if ($this->schemeService->checkSchemeName($request->get('name', ''))) {
            $errorCode = InputError::THE_SCHEME_NAME_EXIST;
        } else {
            $scheme = $this->schemeService->create($request->all());
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode ? $errorCode : null,
            $scheme
        );
    }

    public function edit(Request $request, Scheme $scheme)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);
        $scheme->update($request->only('name'));
        return $this->response("Success", null, $scheme);
    }

    public function destroy(Scheme $scheme)
    {
        $scheme->delete();

        return $this->response();
    }
}
