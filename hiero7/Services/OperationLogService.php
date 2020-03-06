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
        $query  = $this->formatQuery();
        $output = $this->getEsLogByQuery($query);

        return $output->data ?? [];
    }

    function show(string $category, int $page = 1, int $pageCount = 3000)
    {
        $from = ($page - 1) * $pageCount;

        $query = $this->formatQuery(compact('category'), $from, $pageCount);

        $output = $this->getEsLogByQuery($query);

        return $output->data;
    }

    function formatQuery(array $searchList = [], int $from = 0, int $size = null)
    {
        $match = [
            [
                "match" => [
                    "type" => $this->getPlatform(),
                ],
            ], [
                "match" => [
                    "user_group" => $this->getJWTUserGroupId(),
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
            "from"  => $from,
            "size"  => $size ?? env('OPERATION_LOG_SIZE'),
            "query" => [
                "bool" => [
                    "must" => $match,
                ],
            ],
            "sort"  => [
                [
                    "time.keyword" => [
                        "order" => "desc",
                    ],
                ],
            ],
        ];
    }
}
