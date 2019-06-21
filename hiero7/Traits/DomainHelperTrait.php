<?php

namespace Hiero7\Traits;

use Illuminate\Support\Facades\Redis;

trait DomainHelperTrait
{
    public function validateDomain(string $domain)
    {
        // validate online: https://www.regextester.com/93928
        return preg_match("/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}?$/", $domain);
    }

    public function formatDomainCname(string $cname)
    {
        if(env('SCHEME')==1){
            return preg_replace("/[[:punct:]]/i",'',$cname);
        }
        return $cname;
    }
}