<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/9
 * Time: 3:04 PM
 */

namespace Hiero7\Services;


use Hiero7\Repositories\NetworkRepository;

class NetworkService
{
    protected $networkRepository;
    /**
     * NetworkService constructor.
     */
    public function __construct(NetworkRepository $networkRepository)
    {
        $this->networkRepository = $networkRepository;
    }

    public function getAll()
    {
        return $this->networkRepository->getAll();
    }

    public function getNetworksById()
    {
        return $this->networkRepository->getNetworksById();
    }
}