<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/13
 * Time: 12:22 PM
 */

namespace Hiero7\Services;


use Hiero7\Repositories\CountryRepository;

class CountryService
{

    protected $countryRepository;
    /**
     * CountryService constructor.
     */
    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function getAll()
    {
        return $this->countryRepository->getAll();
    }
}