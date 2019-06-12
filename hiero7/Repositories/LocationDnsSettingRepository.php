<?php

namespace Hiero7\Repositories;

use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Models\LocationNetwork;

class LocationDnsSettingRepository
{

    protected $locationDnsSetting;

    public function __construct(LocationDnsSetting $locationDnsSetting)
    {
        $this->locationDnsSetting = $locationDnsSetting;
    }

    public function getAll()
    {
        return $this->locationDnsSetting->with('cdn', 'location')->get();
    }

    public function createSetting(Domain $domain, Cdn $cdn, LocationNetwork $locationNetwork, int $podId, string $editedBy)
    {
        return $this->locationDnsSetting->insert([
            'pod_record_id' => $podId,
            'location_networks_id' => $locationNetwork->id,
            'cdn_id' => $cdn->id,
            'domain_id' => $domain->id,
            'edited_by' => $editedBy,
            'created_at' => \Carbon\Carbon::now(),
        ]);
    }

    public function updateLocationDnsSetting(Domain $domain, Cdn $cdn, LocationNetwork $locationNetwork, string $editedBy)
    {
        $result = $this->locationDnsSetting->where('location_networks_id', $locationNetwork->id)->where('domain_id', $domain->id)->update([
            'cdn_id' => $cdn->id,
            'edited_by' => $editedBy,
        ]);

        return $result ? true : false;
    }

    public function getPodId($locationNetworkId, $domainId)
    {
        return $this->locationDnsSetting->where('location_networks_id', $locationNetworkId)->where('domain_id', $domainId)->pluck('pod_record_id')->first();
    }

    public function updateToDefaultCdnId(int $targetCdnId, int $defaultCdnId)
    {
        return $this->locationDnsSetting->where('cdn_id', $targetCdnId)
        ->update(['cdn_id' => $defaultCdnId]);

    }
}
