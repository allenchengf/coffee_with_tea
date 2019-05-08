<?php
namespace Hiero7\Services;

use Ixudra\Curl\Facades\Curl;

class UserModuleService
{
    protected $user_module_API;

    public function __construct()
    {
        $this->user_module_API = env('USER_MODULE');
    }

    /**
     * 驗證 User Module 是否 通過
     *
     * @param $request
     * @return array
     */
    public function authorization($request): array
    {
        $uid = $request->uid ?? null;
        $ugid = $request->ugid ?? null;
        $user_group_id = $request->user_group_id ?? null;

        return Curl::to($this->user_module_API . '/users/authorization')
            ->withHeaders(['Authorization: ' . $request->header('Authorization')])
            ->withData(compact('uid', 'ugid', 'user_group_id'))
            ->asJson(true)
            ->get();
    }

    /**
     * Get Login Information function
     *
     * @param $request
     * @return array
     */
    public function getSelf($request): array
    {
        $uid = $request->uid ?? null;
        $ugid = $request->ugid ?? null;
        $user_group_id = $request->user_group_id ?? null;
        
        return Curl::to($this->user_module_API . '/users/self')
            ->withHeaders(['Authorization: ' . $request->header('Authorization')])
            ->withData(compact('uid', 'ugid', 'user_group_id'))
            ->asJson(true)
            ->get();
    }

    /**
     * Get User Module User Data
     *
     * @param $request
     * @param integer $uid
     * @return array
     */
    public function getTargetUser($request, int $uid): array
    {
        return Curl::to($this->user_module_API . "/users/$uid/profile")
            ->withHeaders(['Authorization: ' . $request->header('Authorization')])
            ->asJson(true)
            ->get();
    }

    /**
     * Notifications By Users function
     *
     * @param $request
     * @param string $key
     * @param array $users
     * @param $message
     * @return array
     */
    public function notificationByUsers($request, string $key, array $users, $message)
    {
        return Curl::to($this->user_module_API . "/notifications/platforms/$key/users")
            ->withHeaders(['Authorization: ' . $request->header('Authorization')])
            ->withData(compact('users', 'message'))
            ->asJson(true)
            ->post();
    }

    /**
     * Notifications By Group function
     *
     * @param $request
     * @param string $key
     * @param array $users
     * @param $message
     * @return array
     */
    public function notificationByGroups($request, string $key, array $groups, $message)
    {
        return Curl::to($this->user_module_API . "/notifications/platforms/$key/groups")
            ->withHeaders(['Authorization: ' . $request->header('Authorization')])
            ->withData(compact('groups', 'message'))
            ->asJson(true)
            ->post();
    }
}
