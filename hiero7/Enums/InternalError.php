<?php


namespace Hiero7\Enums;




use BenSampo\Enum\Enum;
use Hiero7\Traits\ErrorTrait;

class InternalError extends Enum
{
    use ErrorTrait;

    const INTERNAL_ERROR = 5000;
    /**
     * @var array
     */
    protected static $keys = [
        self::INTERNAL_ERROR  => 'Internal application error',
    ];
}