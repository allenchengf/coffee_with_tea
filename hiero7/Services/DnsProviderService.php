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
        $url = $this->dnsProviderApi . "/domains";

        $data['login_token'] = $data['login_token'] ?? $this->dnsPodLoginToken;

        return Curl::to($url)
            ->withData($data)
            ->asJson(true)
            ->get();
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

        $data = $this->addLoginTokenAndDomainId($data);

        return Curl::to($url)
            ->withData($data)
            ->asJson(true)
            ->get();
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

        $data = $this->addLoginTokenAndDomainId($data);
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
     * @param string sub_domain 主機記錄，必選
     * @param string record_type 記錄類型，大寫英文，比如：A，必選
     * @param string record_line 記錄線路，中文，比如：默認，必選
     * @param string value 記錄值， 如 IP:200.200.200.200， CNAME: cname.dnspod.com.， MX: mail.dnspod.com.，必選
     * @param int ttl {1-604800} TTL，範圍1-604800，不同等級域名最小值不同，可選
     * @param bool status 記錄狀態，默認為 True 可選
     */
    public function editRecord(array $data = [])
    {
        $url = $this->dnsProviderApi . "/records";

        $data = $this->addLoginTokenAndDomainId($data);
        $data['record_type'] = $data['record_type'] ?? "CNAME";

        return Curl::to($url)
            ->withData($data)
            ->asJson(true)
            ->put();
    }

    /**
     * Batch Edit DNS Pod Record function
     *
     * @param string login_token DNS Pod LoginToken
     * @param int domain_id 對應域名ID
     * @param string record_id 記錄的ID，多個record_id用英文的逗號分割
     * @param string change 要修改的字段，可選值為["sub_domain"，"record_type"，"area"，"value"，"ttl"，"status"] 中的一個
     * @param string change_to 修改為，具體依賴改變字段，必填參數
     * @param string value (可選) 要修改到的記錄值，僅當更改字段為 "record_type" 時為必填參數
     */
    public function batchEditRecord(array $data = [])
    {
        $url = $this->dnsProviderApi . "/records/batch";

        $data = $this->addLoginTokenAndDomainId($data);

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

        $data = $this->addLoginTokenAndDomainId($data);

        return Curl::to($url)
            ->withData($data)
            ->asJson(true)
            ->delete();
    }

    /**
     * 由 Hiero7 實作的功能
     * 
     * Get Soruce Record With DNS Pod Record Diff function
     *
     * @param string login_token DNS Pod LoginToken
     * @param int domain_id 對應域名ID
     * @param string sub_domain 主機記錄
     * @param json records 記錄
     */
    public function getDiffRecord(array $data = [])
    {
        $url = $this->dnsProviderApi . "/check-diff";

        $data = $this->addLoginTokenAndDomainId($data);

        return Curl::to($url)
            ->withData($data)
            ->asJson(true)
            ->post();
    }

    /**
     * 由 Hiero7 實作的功能
     * 
     * Get Soruce Record With DNS Pod Record Diff function
     *
     * @param string login_token DNS Pod LoginToken
     * @param int domain_id 對應域名ID
     * @param json diff 記錄
     * @param json create 記錄
     * @param json delele 記錄
     */
    public function syncRecordToDnsPod(array $data = [])
    {
        $url = $this->dnsProviderApi . "/records-sync";

        $data = $this->addLoginTokenAndDomainId($data);
        
        $response = Curl::to($url)
            ->withData($data)
            ->asJson(true)
            ->post();

        return $response;

    }

    public function checkAPIOutput($response = []): bool
    {
        if (!$response || array_key_exists('errors', $response) || !is_null($response['errorCode'])) {
            return false;
        }
        return true;
    }

    private function addLoginTokenAndDomainId(array $data)
    {
        $data['login_token'] = $data['login_token'] ?? $this->dnsPodLoginToken;
        $data['domain_id'] = $data['domain_id'] ?? env('DNS_POD_DOMAIN_ID');
        return $data;
    }
}
