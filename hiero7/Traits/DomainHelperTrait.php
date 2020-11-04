<?php

namespace Hiero7\Traits;

use Hiero7\Models\DomainPin;

trait DomainHelperTrait
{
    /**
     * 驗證格式，如果符合回傳 1 ，不符合回傳 0
     *
     * @param string $domain
     * @return void
     */
    public function validateDomain(string $domain)
    {
        // validate online: https://www.regextester.com/93928
        return preg_match("/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9]+\.[a-zA-Z]{2,11}?$/", $domain);
    }

    /**
     * 調整 Domain->cname 格式
     *
     * 如果 DNS Pod 為付費版本，
     * 會保留原本格式
     *
     * 後尾再加上 {user_group_id}
     *
     * CNAME = www.iroute.com
     * 免費版本 => wwwiroutecom.{user_group_id}
     * 付費版本 => www.iroute.com.{user_group_id}
     *
     * 輸出時都會轉為小寫
     *
     * @param string $cname
     * @param integer $ugid
     * @return string
     */
    public function formatDomainCname(string $cname, int $ugid): string
    {
        if (env('SCHEME', 1) == 1) {
            $cname = preg_replace("/[[:punct:]]/i", '', $cname);
        }

        $pin = DomainPin::getName($ugid);

        return strtolower($cname . '.' . $pin);
    }
}
