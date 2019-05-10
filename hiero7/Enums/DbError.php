<?php


namespace Hiero7\Enums;

use BenSampo\Enum\Enum;
use Hiero7\Traits\ErrorTrait;

class DbError extends Enum
{
    use ErrorTrait;

    const UNAFFECTED = 9871;
    const DELETE_ON_NUll = 9872;
    const DUPLICATE_ENTRY = 9873;
    /**
     * @var array
     */
    protected static $keys = [
        self::UNAFFECTED      => '0 rows affected.',
        self::DELETE_ON_NUll      => 'Target doesn\'t exist',
        self::DUPLICATE_ENTRY      => 'Duplicate entry',
    ];
}