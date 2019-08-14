<?php


namespace Hiero7\Repositories;


use Hiero7\Models\ScanPlatform;

class ScanPlatformRepository
{
    protected $scanPlatform;
    /**
     * ScanPlatformRepository constructor.
     */
    public function __construct(ScanPlatform $scanPlatform)
    {
        $this->scanPlatform = $scanPlatform;
    }

    public function getAll()
    {
        return $this->scanPlatform::all();
    }

    public function create(array $data)
    {
        return $this->scanPlatform->create($data);
    }

    public function checkScanPlatformName($scanPlatformName)
    {
        return $this->scanPlatform->where('name', $scanPlatformName)->exists();
    }
}
