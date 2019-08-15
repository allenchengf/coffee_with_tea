<?php


namespace Hiero7\Repositories;


use Hiero7\Models\ScanPlatform;

/**
 * Class ScanPlatformRepository
 * @package Hiero7\Repositories
 */
class ScanPlatformRepository
{
    /**
     * @var ScanPlatform
     */
    protected $scanPlatform;
    /**
     * ScanPlatformRepository constructor.
     */
    public function __construct(ScanPlatform $scanPlatform)
    {
        $this->scanPlatform = $scanPlatform;
    }

    /**
     * @return ScanPlatform[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return $this->scanPlatform::all();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->scanPlatform->create($data);
    }

    /**
     * @param $scanPlatformName
     * @return mixed
     */
    public function checkScanPlatformName($scanPlatformName)
    {
        return $this->scanPlatform->where('name', $scanPlatformName)->exists();
    }
}
