<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use Hiero7\Traits\JwtPayloadTrait;
use Hiero7\Enums\PermissionError;

class RolePermission
{
    use JwtPayloadTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 直接給過: HEAD, OPTIONS
        // 待議: GET, POST, PUT, PATCH, DELETE
        $method = $request->method();
        $path = explode('/api/v1/', $request->getPathInfo())[1];
        switch ($method) {
            case ('GET'):
                $crud = 'read';
                break;
            case ('POST'):
                $crud = 'create';
                break;
            case ('PUT'||'PATCH'):
                $crud = 'update';
                break;
            case ('DELETE'):
                $crud = 'delete';
                break;
            default: // HEAD or OPTIONS
                return $next($request);
        }

        // 直接給過: ugid = 1
        $jwtPayload = $this->getJWTPayload();
        $role_id = 1; // $jwtPayload['role_id'];
        $user_group_id = $jwtPayload['user_group_id'];
        if ($user_group_id == 1) {
            return $next($request);
        }

        $selfPermissions = DB::table('permissions')
                            ->where('rpm.role_id', $role_id)
                            ->leftjoin('role_permission_mapping as rpm', 'permissions.id', '=', 'rpm.permission_id')
                            ->leftjoin('api_permission_mapping as apm', 'permissions.id', '=', 'apm.permission_id')
                            ->leftjoin('apis as a', 'apm.api_id', '=', 'a.id')
                            ->select('a.method', 'a.path_regex', 'rpm.actions')
                            ->get();

        if (! $selfPermissions || $selfPermissions->isEmpty()) {
            // 尚未設定權限，請聯絡客戶自身主管
            return $this->response(PermissionError::PERMISSION_DENIED);
        }

        foreach ($selfPermissions->toArray() as $row) {
            $actions = json_decode($row->actions, true);

            $isActionAllowed = $actions[$crud];
            $isPathMatch = preg_match('/^'. $row->path_regex .'$/', $path);
            if( $isActionAllowed === 1 && $isPathMatch === 1) {
                // 確認授權，給過
                return $next($request);
            }
        }

        // 無授權該使用者之 API
        return $this->response(PermissionError::YOU_DONT_HAVE_PERMISSION);
    }

    public function response($errorCode)
    {
        return response()->json([
            'message' => PermissionError::getDescription($errorCode),
            'errorCode' => $errorCode,
            'data' => [],
        ])->setStatusCode(400);
    }
}
