<?php


namespace Hiero7\Enums;




use BenSampo\Enum\Enum;
use Hiero7\Traits\ErrorTrait;

class InternalError extends Enum
{
    use ErrorTrait;

    const INTERNAL_ERROR = 5000;
    const INTERNAL_SERVICE_ERROR = 5001;
    /**
     * @var array
     */
    protected static $keys = [
        self::INTERNAL_ERROR  => 'Internal application error.',
        self::INTERNAL_SERVICE_ERROR  => 'Internal service error.',
    ];
}