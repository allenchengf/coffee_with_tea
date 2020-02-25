<?php

namespace Hiero7\Services;

use DB;
use Hiero7\Models\Cdn;
use Hiero7\Models\CdnProvider;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Repositories\CdnProviderRepository;
use Hiero7\Repositories\CdnRepository;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Repositories\NetworkRepository;
use Illuminate\Database\Eloquent\Collection;

class SyncAllRecordService
{
    protected $defaultLine;
    protected $domainRepository, $cdnProviderRepository, $locationDnsSettingRepository, $networkRepository;

    private $domainCNAME, $cdnProvider, $cdns;

    private $domainArray;

    private $cdnProviderArray;

    private $cdnArray;

    private $locationDnsSettings;

    private $lines;

    private $cdnsArray = [];

    private $record = [];

    public function __construct(
        DomainRepository             $domainRepository,
        CdnRepository                $cdnRepository,
        CdnProviderRepository        $cdnProviderRepository,
        LocationDnsSettingRepository $locationDnsSettingRepository,
        NetworkRepository            $networkRepository
    ) {
        $this->defaultLine = "默认";

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

        $this->getIRouteRecord();

        return $this->record;
    }

    public function getDefaultRecords()
    {
        $this->cdnArray->map(function ($cdn, $key) {

            if ($cdn->default) {
                $domainCNAME = $this->domainArray[$cdn->domain_id]['cname'];

                $cdnProvider = $this->cdnProvider[$cdn->cdn_provider_id];

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

    public function getIRouteRecord()
    {
        $this->getLocationDnsSetting();

        if ($this->locationDnsSettings->isNotEmpty()) {
            $this->getLocationNetworkLine();

            $this->locationDnsSettings->map(function ($locationDnsSetting, $key) {

                $cdn = $this->cdnArray[$locationDnsSetting->cdn_id];

                $domainCNAME = $this->domainArray[$cdn->domain_id]['cname'];

                $line = $this->lines[$locationDnsSetting->location_networks_id];

                $cdnProvider = $this->cdnProvider[$cdn->cdn_provider_id];

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
     * Get Location DNS Setting To Record Data
     *
     * @param array $locationDnsSettings
     * @return array
     */
    private function getIRouteSetting(Collection $locationDnsSettings = null)
    {
        $record = [];

        $locationDnsSettings->map(function ($value, $key) use (&$record) {

            $cdn = $this->cdns[$value->cdn_id];

            $line = $value->location()->first()->network()->first()->name;

            $cdnProvider = $this->cdnProvider[$cdn['cdn_provider_id']];

            $data = [
                'id'          => (int) $value->provider_record_id,
                'ttl'         => (int) $cdnProvider['ttl'],
                'value'       => $cdn['cname'],
                'enabled'     => (bool) $cdnProvider['status'],
                'name'        => $this->domainToChinese($this->domainCNAME),
                'origin_name' => $this->domainCNAME,
                'line'        => $line,
                'type'        => "CNAME",
            ];

            $data['hash'] = $this->hashRecord($data);

            $record[] = $data;

        });

        $this->record = array_merge($this->record, $record);

        return $record;
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
        $this->cdnProvider = $this->cdnProvider ?? $this->cdnProviderRepository->getAll()->keyBy('id');
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
