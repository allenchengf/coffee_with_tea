<?php

namespace App\Http\Controllers;

use Hiero7\Enums\AuthError;
use Hiero7\Enums\InputError;
use Hiero7\Enums\InternalError;
use Hiero7\Enums\NotFoundError;
use Hiero7\Enums\PermissionError;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $statusCode = 200;

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function response($message = '', $errorCode = null, $data = [])
    {
        return response()->json([
            'message' => $message ?: $this->getErrorMessage($errorCode),
            'errorCode' => $errorCode,
            'data' => $data,
        ])->setStatusCode($this->statusCode);
    }

    public function getErrorMessage($errorCode = null)
    {
        switch (substr($errorCode, 0, 1)) {
            case 1:
                $message = AuthError::getDescription($errorCode);
                break;
            case 2:
                $message = NotFoundError::getDescription($errorCode);
                break;
            case 3:
                $message = PermissionError::getDescription($errorCode);
                break;
            case 4:
                $message = InputError::getDescription($errorCode);
                break;
            case 5:
                $message = InternalError::getDescription($errorCode);
                break;
            default:
                $message = "Success";
                break;
        }

        return $message;
    }

    public function requestValidator($input, $rules, array $custom_message = null)
    {
        $message = [
            'required' => 'The :attribute field is required.',
        ];

        return Validator::make($input, $rules, $custom_message ?? $message);
    }

    public function getJWTPayload()
    {
        $token = JWTAuth::getToken();
        return JWTAuth::getPayload($token)->toArray();
    }
}
