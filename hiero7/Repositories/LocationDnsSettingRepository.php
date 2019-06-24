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

    public function createSetting(LocationNetwork $locationNetwork, int $podId, array $data)
    {
        return $this->locationDnsSetting->insert([
            'provider_record_id' => $podId,
            'location_networks_id' => $locationNetwork->id,
            'cdn_id' => $data['cdn_id'],
            'edited_by' => $data['edited_by'],
            'created_at' => \Carbon\Carbon::now(),
        ]);
    }

    public function updateLocationDnsSetting(LocationDnsSetting $locationDnsSetting, array $data)
    {
        $result = $this->locationDnsSetting->where('id', $locationDnsSetting->id)->update($data);

        return $result ? true : false;
    }

    public function updateToDefaultCdnId(int $targetCdnId, int $defaultCdnId)
    {
        return $this->locationDnsSetting->where('cdn_id', $targetCdnId)
        ->update(['cdn_id' => $defaultCdnId]);

    }
}
