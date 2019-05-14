<?php
namespace Hiero7\Services;

use Ixudra\Curl\Facades\Curl;

class DnsProviderService
{
    protected $dnsProviderApi;

    public function __construct()
    {
        $this->dnsProviderApi = env('DNS_PROVIDER_API') . '/dnspod';
        $this->dnsPodLoginToken = env('DNS_POD_LOGIN_TOKEN');
    }

    /**
     * Get DNS Pod Domain List
     *
     * @param string login_token DNS Pod LoginToken
     */
    public function getDomain(array $data = [])
    {
        $url = $this->dnsProviderApi . "/domain";
        $data['login_token'] = $data['login_token'] ?? $this->dnsPodLoginToken;

        return Curl::to($url)
            ->withData($data)
            ->asJson(true)
            ->post();
    }

    /**
     * Get DNS Pod Records function
     *
     * @param string login_token DNS Pod LoginToken
     * @param int domain_id 對應域名ID
     * @param int offset 記錄開始的偏移，第一條記錄為 0，依次類推，可選（僅當指定 length 參數時才生效）
     * @param int length 共要獲取的記錄數量的最大值，比如最多獲取20條，則為20，最大3000，可選
     * @param string sub_domain 子域名，如果指定則只返回此子域名的記錄，可選
     * @param string record_type 記錄類型，通過API記錄類型獲得，大寫英文，比如：A，可選
     * @param string record_line 記錄線路，通過API記錄線路獲得，中文，比如：默認，可選
     */
    public function getRecords(array $data = [])
    {
        $url = $this->dnsProviderApi . "/records";

        $data['login_token'] = $data['login_token'] ?? $this->dnsPodLoginToken;
        $data['domain_id'] = $data['domain_id'] ?? env('DNS_POD_DOMAIN_ID');

        return Curl::to($url)
            ->withData($data)
            ->asJson(true)
            ->post();
    }

    /**
     * Create DNS Pod Record function
     *
     * @param string login_token DNS Pod LoginToken
     * @param int domain_id 對應域名ID
     * @param string sub_domain 主機記錄
     * @param string record_type 記錄類型，大寫英文，比如：A，必選
     * @param string record_line 記錄線路，中文，比如：默認，必選
     * @param string value 記錄值， 如 IP:200.200.200.200， CNAME: cname.dnspod.com.， MX: mail.dnspod.com.，必選
     * @param int ttl {1-604800} TTL，範圍1-604800，不同等級域名最小值不同，可選
     * @param bool status 記錄狀態，默認為 True 可選
     */
    public function createRecord(array $data = [])
    {
        $url = $this->dnsProviderApi . "/records/create";

        $data['login_token'] = $data['login_token'] ?? $this->dnsPodLoginToken;
        $data['domain_id'] = $data['domain_id'] ?? env('DNS_POD_DOMAIN_ID');
        $data['record_type'] = $data['record_type'] ?? "CNAME";
        $data['record_line'] = $data['record_line'] ?? "默认";

        return Curl::to($url)
            ->withData($data)
            ->asJson(true)
            ->post();
    }

    /**
     * Edit DNS Pod Record function
     *
     * @param string login_token DNS Pod LoginToken
     * @param int domain_id 對應域名ID
     * @param int record_id 記錄ID，必選
     * @param string sub_domain 主機記錄
     * @param string record_type 記錄類型，大寫英文，比如：A，必選
     * @param string record_line 記錄線路，中文，比如：默認，必選
     * @param string value 記錄值， 如 IP:200.200.200.200， CNAME: cname.dnspod.com.， MX: mail.dnspod.com.，必選
     * @param int ttl {1-604800} TTL，範圍1-604800，不同等級域名最小值不同，可選
     * @param bool status 記錄狀態，默認為 True 可選
     */
    public function editRecord(array $data = [])
    {
        $url = $this->dnsProviderApi . "/records";

        $data['login_token'] = $data['login_token'] ?? $this->dnsPodLoginToken;
        $data['domain_id'] = $data['domain_id'] ?? env('DNS_POD_DOMAIN_ID');

        return Curl::to($url)
            ->withData($data)
            ->asJson(true)
            ->put();
    }

    /**
     * Destroy DNS Pod Record function
     *
     * @param string login_token DNS Pod LoginToken
     * @param int domain_id 對應域名ID
     * @param int record_id 記錄ID，必選
     */
    public function deleteRecord(array $data = [])
    {
        $url = $this->dnsProviderApi . "/records";

        $data['login_token'] = $data['login_token'] ?? $this->dnsPodLoginToken;
        $data['domain_id'] = $data['domain_id'] ?? env('DNS_POD_DOMAIN_ID');

        return Curl::to($url)
            ->withData($data)
            ->asJson(true)
            ->delete();
    }
}
