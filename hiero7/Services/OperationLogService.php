<?php

namespace Hiero7\Services;

use Hiero7\Traits\JwtPayloadTrait;
use Hiero7\Traits\OperationLogTrait;

class OperationLogService
{
    use OperationLogTrait, JwtPayloadTrait {
        OperationLogTrait::getJWTToken insteadof JwtPayloadTrait;
        OperationLogTrait::getJWTPayload insteadof JwtPayloadTrait;
        OperationLogTrait::getJWTUserId insteadof JwtPayloadTrait;
        OperationLogTrait::getJWTUserGroupId insteadof JwtPayloadTrait;
        OperationLogTrait::getJWTUuid insteadof JwtPayloadTrait;
    }

    const GROUP_HIERO7 = 1;

    protected $userModuleService;

    /**
     * OperationLogService constructor.
     *
     * @param $userModuleService
     */
    function __construct(UserModuleService $userModuleService)
    {
        $this->userModuleService = $userModuleService;
    }

    function get()
    {
        $query = $this->formatQuery();
        $output = $this->getEsLogByQuery($query);

        return $output->data;
    }

    function show(string $category)
    {
        $query = $this->formatQuery(compact('category'));

        $output = $this->getEsLogByQuery($query);

        return $output->data;

    }

    function formatQuery(array $searchList = [], int $user_group_id = null, int $from = null, int $size = null)
    {
        $match = [
            [
                "match" => [
                    "type" => $this->getPlatform(),
                ],
            ], [
                "match" => [
                    "user_group" => $user_group_id ?? $this->getJWTUserGroupId(),
                ],
            ],
        ];

        foreach ($searchList as $key => $value) {
            $match[] = [
                "match" => [
                    $key => $value,
                ],
            ];
        }

        return [
            "from" => $from ?? 0,
            "size" => $size ?? env('OPERATION_LOG_SIZE'),
            "query" => [
                "bool" => [
                    "must" => $match,
                ],
            ],
        ];
    }
}
