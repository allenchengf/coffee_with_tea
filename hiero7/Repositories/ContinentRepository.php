<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/13
 * Time: 12:08 PM
 */

namespace Hiero7\Repositories;


use Hiero7\Models\Continent;

class ContinentRepository
{
    protected $continent;

    /**
     * NetworkRepository constructor.
     */
    public function __construct(Continent $continent)
    {
        $this->continent = $continent;
    }

    public function getAll()
    {
        return $this->continent->all();
    }

    public function getContinentName($continentId)
    {
        return $this->continent::where('id', $continentId)->pluck('name')->first();
    }
}