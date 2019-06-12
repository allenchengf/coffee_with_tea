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
use Illuminate\Http\Request;
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

    /**
     * get User Group ID function
     *
     * 判斷是否能夠取得 $request->user_group_id
     *
     * $request->user_group_id == null ，給予 login User_group_id
     * 權限符合，給予 $request->user_group_id
     * 權限不符合，給予 login User_group_id
     *
     * @param Request $request
     * @return int
     */
    public function getUgid(Request $request)
    {
        $getPayload = $this->getJWTPayload();

        $ugid = (($getPayload['user_group_id'] == $request->get('user_group_id')) ||
            ($getPayload['user_group_id'] == 1)) ?
            $request->get('user_group_id', $getPayload['user_group_id']) :
            $getPayload['user_group_id'];

        return $ugid;
    }
}
