<?php

namespace Hiero7\Traits;


trait PaginationTrait
{
    public static function getPaginationInfo($perPage=null, $page=null): array
    {
        return [
            $perPage = $perPage ?? 10,
            $columns = ['*'],
            $pageName = 'page',
            $currentPage = $page ?? 1,
        ];
    }
}