<?php

namespace Hiero7\Models;

use Illuminate\Database\Eloquent\Model;

class ScanLog extends Model
{
    protected $table = 'scan_logs';

    protected $primaryKey = 'id';
    protected $fillable = [
        'cdn_provider_id',
        'scan_platform_id',
        'location_network_id',
        'latency',
    ];

    public function cdnProvider()
    {
        return $this->hasOne(CdnProvider::class);
    }

    public function scanPlatform()
    {
        return $this->hasOne(ScanPlatform::class);
    }

    public function locationNetwork()
    {
        return $this->hasOne(LocationNetwork::class);
    }
}
