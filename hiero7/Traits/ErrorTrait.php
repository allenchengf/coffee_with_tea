<?php

namespace Hiero7\Traits;


trait ErrorTrait
{
    /**
     * @param int|string $value
     * @return string
     */
    public static function getDescription($value): string
    {
        if (isset(self::$keys[$value])) {
            return ucwords(self::$keys[$value]);
        } else {
            return 'unknown error';
        }
    }
}