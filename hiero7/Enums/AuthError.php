<?php


namespace Hiero7\Enums;

use BenSampo\Enum\Enum;
use Hiero7\Traits\ErrorTrait;

class AuthError extends Enum
{
    use ErrorTrait;

    const INVALID_TOKEN = 1000;
    const ACCOUNT_NOT_FOUND = 1001;
    const WRONG_PASSWORD = 1002;
    const FAILED_TO_LOGIN = 1003;
    const IDENTITY_PROBLEM = 1004;
    /**
     * @var array
     */
    protected static $keys = [
        self::INVALID_TOKEN      => 'Invalid / expired Token',
        self::ACCOUNT_NOT_FOUND  => 'Can\'t find an account with this credentials',
        self::WRONG_PASSWORD     => 'Input password is wrong',
        self::FAILED_TO_LOGIN     => 'Failed to login, please try again',
        self::IDENTITY_PROBLEM      => 'User_type & Main_uid not suitable.'
    ];
}