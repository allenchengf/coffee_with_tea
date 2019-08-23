<?php

namespace Hiero7\Traits;


trait PaginationTrait
{
    public static function getPaginationInfo($perPage=null, $page=null): array
    {
        return ($perPage || $page) ?
        // 給二者或其一，視為需: 換頁
        [
            $perPage = $perPage ? (int)$perPage : 20,
            $columns = ['*'],
            $pageName = 'page',
            $currentPage = $page ? (int)$page : 1,
        ]
        :
        // 都沒給，視為需: 全部列表
        [
            $perPage = null,
            $columns = ['*'],
            $pageName = 'page',
            $currentPage = null,
        ];
    }
}