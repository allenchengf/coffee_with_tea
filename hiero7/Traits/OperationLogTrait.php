<?php
/**
 * Created by PhpStorm.
 * User: hanhanhu
 * Date: 2019-05-02
 * Time: 09:08
 */

namespace Hiero7\Traits;

use Illuminate\Support\Facades\Request;
use Ixudra\Curl\Facades\Curl;

trait OperationLogTrait
{

    protected $changeFrom = [], $changeTo = [];

    use JwtPayloadTrait;

    protected function curlWithUri($domain, $uri, array $body, $method, $asJson = true)
    {
        return Curl::to($domain . $uri)
            ->withHeader('Authorization: ' . 'Bearer ' . $this->getJWTToken())
            ->withData($body)
            ->asJson($asJson)
            ->$method();
    }

    //createEsLog has not been testing,so there may be bugs
    public function createEsLog(int $targetUser, $category, $behavior, $item)
    {
        if (env('APP_ENV') === 'testing') {
            return true;
        }

        $targetUser = $this->getTargetUser($targetUser);

        $data = $this->formatBehavior($this->getLoginUser(), $targetUser, $category, $behavior, $item);

        $this->curlWithUri(self::getOperationLogURL(), '/log/platform', $data, 'post');
    }

    public function setChangeFrom(array $changeFrom = [])
    {
        $this->changeFrom = $changeFrom;
        return $this;
    }

    public function setChangeTo(array $changeTo = [])
    {
        $this->changeTo = $changeTo;
        return $this;
    }

    /**
     * 新增操作 Log
     *
     * @param string $category
     * @param string $changeType
     * @param string $message
     * @return void
     */
    public function createOperationLog(string $category, string $changeType, string $message = null)
    {
        if (env('APP_ENV') === 'testing') {
            return true;
        }

        $body = [
            'category' => $category,
            'uid' => $this->getJWTUserId(),
            'uuid' => $this->getJWTUuid(),
            'userGroup' => $this->getJWTUserGroupId(),
            'platform' => $this->getPlatform(),
            'ip' => Request::ip(),
            'method' => Request::method(),
            'url' => Request::url(),
            'input' => Request::except(['password', 'password_confirmation', 'edited_by', 'old', 'new']),
            'change_type' => $changeType,
            'changed_from' => json_encode($this->changeFrom),
            'changed_to' => json_encode($this->changeTo),
            'message' => $message,
        ];

        return $this->curlWithUri(self::getOperationLogURL(), '/log/platform/iRouteCDN', $body, 'post');
    }

    public function getEsLog($platform, $category)
    {
        return $this->curlWithUri(self::getOperationLogURL(), "/log/platform/$platform/category/$category", [], 'get');
    }

    public function getEsLogByQuery($query)
    {
        return $this->curlWithUri(self::getOperationLogURL(), "/log/platform/query", $query, 'post');
    }

    private function formatBehavior($operator, $targetUser, $category, $behavior, $item)
    {
        $message = '';

        $operatorData = $operator->data;

        if ($this->checkOperatorNTargetUserIsTheSame($operator, $targetUser)) {
            $message = "{$operatorData->name} ({$operatorData->email}) {$behavior} {$item}.";

        } else {
            $message = "{$operatorData->name} ({$operatorData->email}) {$behavior} {$targetUser->name}'s ({$targetUser->email}) {$item}.";
        }

        $body = [
            "uid" => $operatorData->uid,
            "userGroup" => $operatorData->user_group_id,
            "platform" => $this->getPlatform(),
            "category" => $category,
            "message" => $message,
        ];

        return $body;
    }

    public function getLoginUser()
    {
        return $this->curlWithUri(self::getUserModuleDomain(),
            '/users/self',
            [
                'uid' => $this->getJWTUserId(),
                'ugid' => $this->getJWTUserGroupId(),
            ],
            'get',
            false);

    }

    private function getTargetUser($uid)
    {
        return $this->curlWithUri(self::getUserModuleDomain(),
            "/users/$uid/profile",
            [
                'uid' => $uid,
            ],
            'get', false);

    }

    private function checkOperatorNTargetUserIsTheSame($operator, $targetUser)
    {
        $targetUserData = $targetUser->data;
        $operatorData = $operator->data;
        return $operatorData->uid == $targetUserData->uid ? true : false;
    }

    /**
     * 取得 Operation API URL
     *
     * @return string
     */
    private static function getOperationLogURL(): string
    {
        return env('OPERATION_LOG_URL');
    }

    /**
     * 取得 User module API URL
     *
     * @return string
     */
    private static function getUserModuleDomain(): string
    {
        return env('USER_MODULE');
    }

    /**
     * 取得此專案的 Platform Key
     *
     * @return string
     */
    private function getPlatform(): string
    {
        return env('PLATFORM_KEY');
    }
}
