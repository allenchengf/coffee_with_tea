<?php

namespace Hiero7\Services;

use Hiero7\Models\Domain;
use Hiero7\Repositories\CdnProviderRepository;
use Hiero7\Repositories\CdnRepository;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Repositories\NetworkRepository;
use Hiero7\Services\DnsProviderService;

class SyncAllRecordService
{
    protected $defaultLine;
    protected $dnsProviderService;
    protected $domainRepository, $cdnProviderRepository, $locationDnsSettingRepository, $networkRepository;

    private $cdnProvider, $cdns;

    private $domainArray, $cdnProviderArray, $cdnArray;
    private $locationDnsSettings, $lines;

    private $record = [];

    public function __construct(
        DnsProviderService           $dnsProviderService,
        DomainRepository             $domainRepository,
        CdnRepository                $cdnRepository,
        CdnProviderRepository        $cdnProviderRepository,
        LocationDnsSettingRepository $locationDnsSettingRepository,
        NetworkRepository            $networkRepository
    ) {
        $this->defaultLine = "默认";

        $this->dnsProviderService = $dnsProviderService;

        $this->domainRepository             = $domainRepository;
        $this->cdnRepository                = $cdnRepository;
        $this->cdnProviderRepository        = $cdnProviderRepository;
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
        $this->networkRepository            = $networkRepository;
    }

    public function getAllRecord()
    {
        $this->getCdnProviders();
        $this->getDomains();
        $this->getCDNs();

        $this->getDefaultRecords();
        $this->getiRouteRecord();

        return $this->record;
    }

    /**
     * Get Different Record
     *
     * @param array $record
     * @param string $domainCname
     * @return array
     */
    public function getDifferent(array $reocrds = [])
    {
        $data = [
            'records' => json_encode($reocrds),
        ];

        $response = $this->dnsProviderService->getDiffRecord($data);

        if ($this->dnsProviderService->checkAPIOutput($response)) {

            $this->syncDnsProviderRecordId($reocrds, $response['data']['match']);

            return $response['data'];
        }

        return ['error' => true];
    }

    public function syncRecords(array $records = [])
    {
        $data = [
            'create'    => json_encode($records['create'] ?? []),
            'different' => json_encode($records['different'] ?? []),
            'delete'    => json_encode($records['delete'] ?? []),
        ];

        $this->dnsProviderService->syncRecordToDnsPod($data);
    }

    public function getDefaultRecords()
    {
        $this->cdnArray->map(function ($cdn) {
            if ($cdn->default) {
                $domainCNAME = $this->domainArray[$cdn->domain_id]['cname'];

                $cdnProvider = $this->cdnProviderArray[$cdn->cdn_provider_id];

                $record = [
                    'id'          => (int) $cdn->provider_record_id,
                    'ttl'         => (int) $cdnProvider['ttl'],
                    'value'       => $cdn->cname,
                    'enabled'     => (bool) $cdnProvider['status'],
                    'name'        => $this->domainToChinese($domainCNAME),
                    'origin_name' => $domainCNAME,
                    'line'        => $this->defaultLine,
                    'type'        => "CNAME",
                ];

                $record['hash'] = $this->hashRecord($record);
                $this->record[] = $record;
            }
        });
    }

    public function getiRouteRecord()
    {
        $this->getLocationDnsSetting();

        if ($this->locationDnsSettings->isNotEmpty()) {
            $this->getLocationNetworkLine();

            $this->locationDnsSettings->map(function ($locationDnsSetting) {

                $cdn = $this->cdnArray[$locationDnsSetting->cdn_id];

                $domainCNAME = $this->domainArray[$cdn->domain_id]['cname'];

                $line = $this->lines[$locationDnsSetting->location_networks_id];

                $cdnProvider = $this->cdnProviderArray[$cdn->cdn_provider_id];

                $record = [
                    'id'          => (int) $locationDnsSetting->provider_record_id,
                    'ttl'         => (int) $cdnProvider->ttl,
                    'value'       => $cdn['cname'],
                    'enabled'     => (bool) $cdnProvider->status,
                    'name'        => $this->domainToChinese($domainCNAME),
                    'origin_name' => $domainCNAME,
                    'line'        => $line,
                    'type'        => "CNAME",
                ];

                $record['hash'] = $this->hashRecord($record);
                $this->record[] = $record;
            });
        }
    }

    /**
     * 將 Match 的 Record_id 寫回 DB
     *
     * @param array $soruceRecord
     * @param array $matchData
     * @return void
     */
    private function syncDnsProviderRecordId(array $soruceRecord = [], array $matchRecords = [])
    {
        $soruceRecord = collect($soruceRecord)->keyBy('hash');

        $matchRecords = collect($matchRecords)->keyBy('hash');

        $this->domainArray = $this->domainArray->keyBy('cname');

        $this->linesName = array_flip($this->lines);

        $matchRecords->map(function ($matchRecord, $key) use (&$soruceRecord) {
            if (!isset($soruceRecord[$key])) {
                return;
            }

            // 同樣的 hash ，但是 id 不相同
            if ($soruceRecord[$key]['id'] != $matchRecord['id']) {

                $domain = $this->domainArray[$matchRecord["name"]];

                ($matchRecord['line'] == $this->defaultLine) ?
                $this->updateDefaultRecordId($domain, $matchRecord) :
                $this->updateiRouteRecordId($domain, $matchRecord);
            }
        });
    }

    protected function updateDefaultRecordId(Domain $domain, array $record)
    {
        $this->cdnRepository->updateRecordIdByDomainId($domain->id, $record['id']);
    }

    protected function updateiRouteRecordId(Domain $domain, array $record)
    {
        $locationNetworkId = $this->linesName[$record['line']];

        $cdn = $this->cdnRepository->getCdnsByDomainIdAndCname($domain->id, $record['value']);

        $this->locationDnsSettingRepository->updateRecordIdByCdnIdAndLocationNetworkId($cdn->id, $locationNetworkId, $record['id']);
    }

    public function getLocationNetworkLine()
    {
        $this->lines = $this->lines ?? $this->networkRepository->getLinesMappingToLocationNetworks();
    }

    /**
     * 將 Domain 轉換成中文
     *
     * @param string $domain
     * @return string
     */
    private function domainToChinese(string $domain)
    {
        $idm = idn_to_utf8($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        return $idm;
    }

    /**
     * 計算 Record Hash
     *
     * @param array $data
     * @return string
     */
    private function hashRecord(array $data)
    {
        $data = collect($data)->only('ttl', 'value', 'enabled', 'name', 'line', 'type')->toArray();

        return sha1(json_encode($data));
    }

    /**
     * 取得 Domains
     *
     * @param Domain $domain
     * @return void
     */
    private function getDomains()
    {
        $this->domainArray = $this->domainArray ?? $this->domainRepository->getAll()->keyBy('id');
    }

    /**
     * 取得 CDNs
     *
     * @param Domain $domain
     * @return void
     */
    private function getCDNs()
    {
        $this->cdnArray = $this->cdnArray ?? $this->cdnRepository->getAll()->keyBy('id');
    }

    /**
     * 取得 CDNProviders
     *
     * @param Domain $domain
     * @return void
     */
    private function getCdnProviders()
    {
        $this->cdnProviderArray = $this->cdnProviderArray ?? $this->cdnProviderRepository->getAll()->keyBy('id');
    }

    /**
     * 取得 LocationDnsSetting
     *
     * @param Domain $domain
     * @return void
     */
    private function getLocationDnsSetting()
    {
        $this->locationDnsSettings = $this->locationDnsSettings ?? $this->locationDnsSettingRepository->all()->keyBy('id');
    }
}
