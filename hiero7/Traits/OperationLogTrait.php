<?php
/**
 * Created by PhpStorm.
 * User: hanhanhu
 * Date: 2019-05-02
 * Time: 09:08
 */

namespace Hiero7\Traits;

use JWTAuth;
use Ixudra\Curl\Facades\Curl;
use App;

trait OperationLogTrait
{

    protected function curlWithUri($domain, $uri, array $body, $method)
    {
        return Curl::to($domain . $uri)->withHeader('Authorization: ' . 'Bearer ' . $this->getToken())->withData($body)->asJson(true)->$method();
    }

    private static function getKongOperationLogDomain()
    {
        return env('KONG_OPERATION_LOG');
    }

    private static function getUserModuleDomain()
    {
        return env('USER_MODULE');
    }

    //createEsLog has not been testing,so there may be bugs
    public function createEsLog($targetUser, $category, $behavior, $item)
    {
        $data = $this->formatBehavior($this->getLoginUser(), $targetUser, $category, $behavior, $item);

        $this->curlWithUri(self::getKongOperationLogDomain(), '/log/platform', $data, 'post');
    }

    public function getEsLog($platform, $category)
    {
        return $this->curlWithUri(self::getKongOperationLogDomain(), "/log/platform/$platform/category/$category", [],
            'get');
    }

    public function getEsLogByQuery($query)
    {
        return $this->curlWithUri(self::getKongOperationLogDomain(), "/log/platform/query", $query, 'post');
    }


    private function formatBehavior($operator, $targetUser, $category, $behavior, $item)
    {
        $message = '';

        if ($this->checkOperatorNTargetUserIsTheSame($operator, $targetUser)) {
            $message = "{$operator->name} ({$operator->email}) {$behavior} {$item}.";

        } else {
            $message = "{$operator->name} ({$operator->email}) {$behavior} {$targetUser->name}'s ({$targetUser->email}) {$item}.";
        }


        $body = [
            "uid"       => $operator->uid,
            "userGroup" => $operator->user_group_id,
            "platform"  => $this->getPlatform(),
            "category"  => $category,
            "message"   => $message
        ];

        return $body;
    }

    public function getLoginUser()
    {
        return $this->curlWithUri(self::getUserModuleDomain(), '/users/self', [
            'uid'  => $this->parseToken()->payload()->get('sub'),
            'ugid' => $this->parseToken()->getPayload()->get('user_group_id')
        ], 'get');

    }


    private function getTargetUser($uid, $ugid)
    {
        return $this->curlWithUri(self::getUserModuleDomain(), '/users/self', [
            'uid'  => $uid,
            'ugid' => $ugid
        ], 'get');

    }

    private function parseToken()
    {
        return JWTAuth::parseToken();

    }

    private function getToken()
    {
        return JWTAuth::getToken();
    }

    private function getPlatform()
    {
        $payload = JWTAuth::getPayload();

        return $payload['platformKey'];
    }


    private function checkOperatorNTargetUserIsTheSame($operator, $targetUser)
    {
        return $operator->uid == $targetUser->uid ? true : false;
    }

}