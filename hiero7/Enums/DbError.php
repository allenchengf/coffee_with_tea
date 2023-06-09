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
    const FOREIGN_CONSTRAINT_OR_CDN_SETTING = 9874;
    const IMPORT_RELATIONAL_DATA_HAVE_SOME_PROBLEM = 9875;


    /**
     * @var array
     */
    protected static $keys = [
        self::UNAFFECTED      => '0 rows affected.',
        self::DELETE_ON_NUll      => 'Target doesn\'t exist',
        self::DUPLICATE_ENTRY      => 'has existed.',
        self::FOREIGN_CONSTRAINT_OR_CDN_SETTING      => 'Constraint foreign key or CDN Setting wrong',
        self::IMPORT_RELATIONAL_DATA_HAVE_SOME_PROBLEM => 'Import relational data have some problem.'
    ];
}