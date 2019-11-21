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
    use JwtPayloadTrait;

    protected $changeFrom = [], $changeTo = [], $changeType = null;
    protected $category = null;

    protected function curlWithUri(string $domain, string $uri, array $body, string $method, $asJson = true)
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

    /**
     * 新增操作 Log
     *
     * 要寫入 Log 一定要呼叫此 Method
     *
     * @param string $category Log 的種類
     * @param string $changeType Log 的型態(Create, Update , Delete)
     * @param string $message Log 訊息(Success, Fail, any other...)
     * @return void
     */
    public function createOperationLog(string $category = null, string $changeType = null, string $message = 'Success')
    {
        if (config('app.env') === 'testing') {
            return true;
        }

        $this->setChangeType($changeType);

        $body = [
            'uid' => $this->getJWTUserId(),
            'userGroup' => $this->getJWTUserGroupId(),
            'platform' => $this->getPlatform(),
            'category' => $category ?? $this->getCategory(),
            'change_type' => $this->getChangeType(),
            'changed_from' => $this->getChangeFrom(),
            'changed_to' => $this->getChangeTo(),
            'message' => $message,
            'ip' => Request::ip(),
            'method' => $this->getRequestMethod(),
            'url' => Request::url(),
            'input' => Request::except(['password', 'password_confirmation', 'edited_by', 'old', 'new']),
        ];

        $this->curlWithUri(self::getOperationLogURL(), '/log/platform/iRouteCDN', $body, 'post');
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

    protected function getMappingChangeType()
    {
        return [
            'GET' => 'Check',
            'POST' => 'Create',
            'PATCH' => 'Update',
            'PUT' => 'Update',
            'DELETE' => 'Delete',
        ];
    }

    /**
     * set Change From
     *
     * @param array $changeFrom
     * @return $this
     */
    private function setChangeFrom($changeFrom = [])
    {
        $this->changeFrom = $changeFrom;

        return $this;
    }

    /**
     * 設定 change Type
     *
     * 如果 Input is Null 會去自動 Mapping to Request Method
     * Mapping 沒有就會轉成 Undefined
     *
     * 如果已經有設定就會依直接照給予的設定
     *
     * @param string $changeType
     * @return $this
     */
    private function setChangeType(string $changeType = null)
    {
        if (!$changeType && !$this->changeType) {

            $mappingType = $this->getMappingChangeType();

            $method = $this->getRequestMethod();

            $this->changeType = $mappingType[$method] ?? 'Undefined';
        } else {
            $this->changeType = $changeType ?? $this->changeType;
        }

        return $this;
    }

    /**
     * 設定 Change 後得資訊
     */
    private function setChangeTo($changeTo = [])
    {
        $this->changeTo = $changeTo;

        return $this;
    }

    /**
     * 設定 Category 資訊
     *
     * @param string $category
     * @return $this
     */
    private function setCategory(string $category)
    {
        $this->category = $category;

        return $this;
    }

    private function getChangeFrom(): array
    {
        return $this->changeFrom;
    }

    /**
     * get Change To 的資訊
     *
     * 如果 ChangeType 是 Update 時
     * 會自動 Filter 出有改變的 Key Value
     *
     * @return array
     */
    private function getChangeTo(): array
    {
        if ($this->getChangeType() === 'Update') {
            return collect($this->changeTo)->diffAssoc($this->changeFrom)->all();
        }

        return $this->changeTo;
    }

    private function getCategory()
    {
        return $this->category ?? 'Undefined';
    }

    private function getChangeType()
    {
        return $this->changeType;
    }

    private function getRequestMethod()
    {
        return Request::method();
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
