<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/13
 * Time: 12:05 PM
 */

namespace Hiero7\Services;


use Hiero7\Repositories\continentRepository;

class ContinentService
{

    protected $continentRepository;

    /**
     * ContinentService constructor.
     */
    public function __construct(ContinentRepository $continentRepository)
    {
        $this->continentRepository = $continentRepository;
    }

    public function getAll()
    {
        return $this->continentRepository->getAll();
    }
}