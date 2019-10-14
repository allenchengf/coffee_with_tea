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

    protected function curlWithUri($domain, $uri, array $body, $method, $asJson = true)
    {
        return Curl::to($domain . $uri)->withHeader('Authorization: ' . 'Bearer ' . $this->getToken())->withData($body)->asJson($asJson)->$method();
    }

    private static function getKongOperationLogDomain()
    {
        return env('OPERATION_LOG_URL');
    }

    private static function getUserModuleDomain()
    {
        return env('USER_MODULE');
    }

    //createEsLog has not been testing,so there may be bugs
    public function createEsLog(int $targetUser, $category, $behavior, $item)
    {
        if (env('APP_ENV') === 'testing'){
            return true;
        }

        $targetUser = $this->getTargetUser($targetUser);

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

        $operatorData = $operator->data;

        if ($this->checkOperatorNTargetUserIsTheSame($operator, $targetUser)) {
            $message = "{$operatorData->name} ({$operatorData->email}) {$behavior} {$item}.";

        } else {
            $message = "{$operatorData->name} ({$operatorData->email}) {$behavior} {$targetUser->name}'s ({$targetUser->email}) {$item}.";
        }


        $body = [
            "uid"       => $operatorData->uid,
            "userGroup" => $operatorData->user_group_id,
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
        ], 'get', false);

    }


    private function getTargetUser($uid)
    {
        return $this->curlWithUri(self::getUserModuleDomain(), "/users/$uid/profile", [
            'uid'  => $uid,
        ], 'get', false);

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
        return env('PLATFORM_KEY');
    }


    private function checkOperatorNTargetUserIsTheSame($operator, $targetUser)
    {
        $targetUserData = $targetUser->data;
        $operatorData = $operator->data;
        return $operatorData->uid == $targetUserData->uid ? true : false;
    }

}
