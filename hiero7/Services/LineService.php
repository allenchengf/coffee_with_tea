<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/13
 * Time: 4:03 PM
 */

namespace Hiero7\Services;


use Hiero7\Repositories\LineRepository;

class LineService
{

    protected $lineRepository;
    /**
     * LineService constructor.
     */
    public function __construct(LineRepository $lineRepository)
    {
        $this->lineRepository = $lineRepository;
    }

    public function getAll()
    {
        return $this->lineRepository->getAll();
    }

    public function create(array $data)
    {
        return $this->lineRepository->create($data);
    }

    public function checkNetworkId($networkId)
    {
        return $this->lineRepository->checkNetworkId($networkId);
    }

    public function getLinesById()
    {
        return $this->lineRepository->getLinesById();
    }
}