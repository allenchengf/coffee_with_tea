<?php
namespace Hiero7\Traits;

use Illuminate\Support\Facades\Redis;

trait DomainHelperTrait
{
    public function validateDoamin(string $domain)
    {
        // validate online: https://www.regextester.com/93928
        return preg_match("/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}?$/", $domain);
    }
}