<?php
/**
 * Created by PhpStorm.
 * User: hanhanhu
 * Date: 2019-05-02
 * Time: 09:08
 */

namespace Hiero7\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Ixudra\Curl\Facades\Curl;

trait OperationLogTrait
{
    use JwtPayloadTrait;

    protected $changeFrom = [], $changeTo = [], $changeType = null;
    protected $category   = null;

    protected function curlWithUri(string $domain, string $uri, array $body, string $method, $asJson = true, $timeout = 30)
    {
        return Curl::to($domain . $uri)
            ->withHeader('Authorization: ' . 'Bearer ' . $this->getJWTToken())
            ->withData($body)
            ->withTimeout($timeout)
            ->asJson($asJson)
            ->$method();
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
            'uid'          => $this->getJWTUserId(),
            'userGroup'    => $this->getJWTUserGroupId(),
            'platform'     => $this->getPlatform(),
            'category'     => $category ?? $this->getCategory(),
            'change_type'  => $this->getChangeType(),
            'changed_from' => json_encode($this->getChangeFrom()),
            'changed_to'   => json_encode($this->getChangeTo()),
            'message'      => $message,
            'ip'           => $this->getClientIp(),
            'method'       => $this->getRequestMethod(),
            'url'          => Request::url(),
            'input'        => json_encode(Request::except(['password', 'password_confirmation', 'edited_by', 'old', 'new'])),
        ];

        $callback = $this->curlWithUri(self::getOperationLogURL(), '/log/platform/iRouteCDN', $body, 'post', 1);

        // Log::info('[OperationLogTrait::createOperationLog()] ' . json_encode($callback));
    }

    public function getEsLog()
    {
        return $this->curlWithUri(self::getOperationLogURL(), "/log/platform/iRouteCDN", ['user_group_id' => $this->getJWTUserGroupId()], 'get', false);
    }

    public function getEsLogByCategory(string $category)
    {
        return $this->curlWithUri(self::getOperationLogURL(), "/log/platform/iRouteCDN/category/$category", ['user_group_id' => $this->getJWTUserGroupId()], 'get', false);
    }

    public function getEsLogByQuery(array $query)
    {
        return $this->curlWithUri(self::getOperationLogURL(), "/log/platform/iRouteCDN/query", $query, 'post', false);
    }

    protected function getMappingChangeType()
    {
        return [
            'GET'    => 'Check',
            'POST'   => 'Create',
            'PATCH'  => 'Update',
            'PUT'    => 'Update',
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

    private function getClientIp()
    {
        $header = Request::header();

        $ip = isset($header['x-forwarded-for']) ? $header['x-forwarded-for'][0] : null;

        $ip = (!$ip && isset($header['x-real-ip'])) ? $header['x-real-ip'][0] : $ip;

        $ip = $ip ?? Request::ip();

        return $ip;
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
     * 取得此專案的 Platform Key
     *
     * @return string
     */
    private function getPlatform(): string
    {
        return env('PLATFORM_KEY');
    }
}
