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
        'edited_by',
    ];

    public function cdnProvider()
    {
        return $this->belongsTo(CdnProvider::class);
    }

    public function scanPlatform()
    {
        return $this->belongsTo(ScanPlatform::class);
    }

    public function locationNetwork()
    {
        return $this->belongsTo(LocationNetwork::class);
    }
}
