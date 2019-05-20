<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/13
 * Time: 12:22 PM
 */

namespace Hiero7\Repositories;


use Hiero7\Models\Country;

class CountryRepository
{

    protected $country;
    /**
     * CountryRepository constructor.
     */
    public function __construct(Country $country)
    {
        $this->country = $country;
    }

    public function getAll()
    {
        return $this->country->all();
    }

    public function getCountryName($countryId)
    {
        return $this->country::where('id', $countryId)->pluck('name')->first();
    }
}