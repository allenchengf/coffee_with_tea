<?php

namespace Hiero7\Services;

use Hiero7\Traits\JwtPayloadTrait;
use Hiero7\Traits\OperationLogTrait;

class OperationLogService
{
    use OperationLogTrait;
    use JwtPayloadTrait;

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

    public function get()
    {
        $query = $this->formatQuery();

        return $this->getEsLogByQuery($query);
    }

    public function show(string $category)
    {
        $query = $this->formatQuery(compact('category'));

        return $this->getEsLogByQuery($query);
    }

    private function formatQuery(array $searchList = [], int $user_group_id = null, int $from = null, int $size = null)
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
