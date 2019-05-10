<?php

namespace Hiero7\Enums;

use BenSampo\Enum\Enum;
use Hiero7\Traits\ErrorTrait;

class InputError extends Enum
{
    use ErrorTrait;

    const MISSING_PARAMETER_ERROR = 4000;
    const MISSING_EMAIL_OR_PASSWORD = 4001;
    const MISSING_EMAIL = 4002;
    const USER_TYPE_ERROR = 4003;
    const EMAIL_EXIST = 4004;
    const UID_USER_TYPE_NOT_ORIGIN = 4005;
    const UID_USER_TYPE_NEED_MAIN_UID = 4006;
    const CANT_CHANGE_THIS_USER_THE_USER_TYPE = 4007;
    const PLEASE_INPUT_NEW_PASSWORD = 4008;
    const PLEASE_INPUT_OLD_PASSWORD = 4009;
    const INPUT_WRONG_OLD_PASSWORD = 4010;
    const INPUT_UID_IS_NOT_ORIGIN = 4011;
    const PARAMETER_INVALID = 4012;
    const USER_HAS_BEEN_BUY_THE_PRODUCT = 4013;
    const RESET_PASSWORD_INPUT_ERROR = 4014;
    const PASSWORD_RESET_TOKEN_INVALID = 4015;
    const INVALID_VERIFICATION_CODE = 4016;
    const THE_PRODUCT_EXIST = 4017;
    const USER_NOT_EXIST = 4018;
    const WRONG_PARAMETER_ERROR = 4019;
    const DOMAIN_EXIST = 4020;
    const CNAME_EXIST = 4021;
    const DOMAIN_FORMAT_ERROR = 4022;
    const BATCH_INPUT_FORMAT_ERROR = 4023;

    /**
     * @var array
     */
    protected static $keys = [
        self::MISSING_PARAMETER_ERROR => 'missing parameter',
        self::MISSING_EMAIL_OR_PASSWORD => 'missing email or password',
        self::MISSING_EMAIL => 'missing email',
        self::USER_TYPE_ERROR => 'User Type Error',
        self::EMAIL_EXIST => 'The Email is Exist',
        self::UID_USER_TYPE_NOT_ORIGIN => 'The Uid user_type Not Origin',
        self::UID_USER_TYPE_NEED_MAIN_UID => 'The user_type Need main_uid',
        self::CANT_CHANGE_THIS_USER_THE_USER_TYPE => "Can't Change This User Type",
        self::PLEASE_INPUT_NEW_PASSWORD => "Please input new password.",
        self::PLEASE_INPUT_OLD_PASSWORD => "Please input old password.",
        self::INPUT_WRONG_OLD_PASSWORD => "Input wrong old password.",
        self::INPUT_UID_IS_NOT_ORIGIN => "Input uid is not origin.",
        self::PARAMETER_INVALID => 'parameter invalid',
        self::USER_HAS_BEEN_BUY_THE_PRODUCT => 'user has been buy the product',
        self::RESET_PASSWORD_INPUT_ERROR => 'Error, Incorrect email , password or token',
        self::PASSWORD_RESET_TOKEN_INVALID => 'This password reset token is invalid',
        self::INVALID_VERIFICATION_CODE => 'Invalid Verification Code, Please try again.',
        self::THE_PRODUCT_EXIST => 'The Product Exist',
        self::USER_NOT_EXIST => 'User Not Exist',
        self::WRONG_PARAMETER_ERROR => 'Wrowg Parameter Error',
        self::DOMAIN_EXIST => 'Domain Exist',
        self::CNAME_EXIST => 'CNAME Exist',
        self::DOMAIN_FORMAT_ERROR => 'Domain format error.',
        self::BATCH_INPUT_FORMAT_ERROR=> 'Batch input format error.',
    ];
}
