<?php


namespace Hiero7\Enums;




use BenSampo\Enum\Enum;
use Hiero7\Traits\ErrorTrait;

class InternalError extends Enum
{
    use ErrorTrait;

    const INTERNAL_ERROR = 5000;
    const INTERNAL_SERVICE_ERROR = 5001;
    const CHECK_DATA_AND_SCHEME_SETTING = 5002;
    /**
     * @var array
     */
    protected static $keys = [
        self::INTERNAL_ERROR  => 'Internal application error.',
        self::INTERNAL_SERVICE_ERROR  => 'Internal service error.',
        self::CHECK_DATA_AND_SCHEME_SETTING => 'Check Mapping Value & Scheme ID.',
    ];
}