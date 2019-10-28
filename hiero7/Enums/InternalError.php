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
    const CHECK_S3_BUCKET_IF_EXISTS = 5003;
    const NO_S3_FILES_FROM_UIGD = 5004;
    const DNSPOD_ERROR = 5005;
    /**
     * @var array
     */
    protected static $keys = [
        self::INTERNAL_ERROR  => 'Internal application error.',
        self::INTERNAL_SERVICE_ERROR  => 'Internal service error.',
        self::CHECK_DATA_AND_SCHEME_SETTING => 'Check Mapping Value & Scheme ID.',
        self::CHECK_S3_BUCKET_IF_EXISTS => 'Check Bucket if exists.',
        self::NO_S3_FILES_FROM_UIGD => 'No Config Backups From this User\'s Group.',
        self::DNSPOD_ERROR => "Please contact iRoute admin.",
    ];
}