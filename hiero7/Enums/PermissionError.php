<?php

namespace Hiero7\Enums;

use BenSampo\Enum\Enum;
use Hiero7\Traits\ErrorTrait;

class PermissionError extends Enum
{
    use ErrorTrait;

    const PERMISSION_DENIED = 3000;
    const CANT_OPERATIONS_OTHER_USER = 3001;
    const YOU_DONT_HAVE_PERMISSION = 3002;
    const UID_DIFFERENT_ORIGIN = 3003;
    const YOU_ARE_NOT_AN_ADMINISTRATOR = 3004;
    const TOKEN_EXPIRED = 3005;
    const TOKEN_INVALID = 3006;
    const TOKEN_ABSENT = 3007;

    /**
     * @var array
     */
    protected static $keys = [
        self::PERMISSION_DENIED => 'Permission Denied',
        self::CANT_OPERATIONS_OTHER_USER => "You Can't Operations Other User.",
        self::YOU_DONT_HAVE_PERMISSION => "You Don't Have Permission.",
        self::UID_DIFFERENT_ORIGIN => "UID Different Origin.",
        self::YOU_ARE_NOT_AN_ADMINISTRATOR => "You Are Not An Administrator.",
        self::TOKEN_EXPIRED => "Token Expired.",
        self::TOKEN_INVALID => "Token invalid.",
        self::TOKEN_ABSENT => "Token absent.",
    ];
}
