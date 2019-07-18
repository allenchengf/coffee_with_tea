<?php


namespace Hiero7\Services;


use Hiero7\Traits\OperationLogTrait;

class OperationLogService
{
    use OperationLogTrait;

    const GROUP_HIERO7 = 1;

    protected $userModuleService;

    /**
     * OperationLogService constructor.
     *
     * @param $userModuleService
     */
    public function __construct(UserModuleService $userModuleService)
    {
        $this->userModuleService = $userModuleService;
    }


    public function get($request)
    {
        $user = $this->checkUserType($request);

        $query = $this->formatQuery($user['data']['user_group_id']);

        return $this->getEsLogByQuery($query);
    }

    public function checkUserType($request)
    {
        return $this->userModuleService->getSelf($request);
    }


    private function formatQuery($userGroup)
    {
        if ($userGroup == self::GROUP_HIERO7) {
            return [
                "query" => [
                    "bool" => [
                        "must" => ["match" => ["type" => $this->getPlatform()]],
                    ]
                ]
            ];
        }

        return [
            "query" => [
                "bool" => [
                    "must"   => ["match" => ["user_group" => $userGroup]],
                    "filter" => ["match" => ["type" => $this->getPlatform()]]
                ]
            ]
        ];

    }
}