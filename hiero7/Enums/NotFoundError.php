<?php


namespace Hiero7\Enums;

use BenSampo\Enum\Enum;
use Hiero7\Traits\ErrorTrait;

class NotFoundError extends Enum
{
    use ErrorTrait;

    const URL_NOT_FOUND = 2000;
    /**
     * @var array
     */
    protected static $keys = [
        self::URL_NOT_FOUND      => 'URL not found'
    ];
}