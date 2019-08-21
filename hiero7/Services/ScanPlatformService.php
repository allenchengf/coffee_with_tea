<?php


namespace Hiero7\Services;


use Hiero7\Repositories\ScanPlatformRepository;

class ScanPlatformService
{
    protected $scanPlatformRepository;
    /**
     * ScanPlatformService constructor.
     */
    public function __construct(ScanPlatformRepository $scanPlatformRepository)
    {
        $this->scanPlatformRepository = $scanPlatformRepository;
    }

    public function getAll()
    {
        return $this->scanPlatformRepository->getAll();
    }

    public function create(array $data)
    {
        return $this->scanPlatformRepository->create($data);
    }

    public function checkScanPlatformName($scanPlatformName)
    {
        return $this->scanPlatformRepository->checkScanPlatformName($scanPlatformName);
    }
}
