<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/13
 * Time: 4:21 PM
 */

namespace Hiero7\Repositories;

use Hiero7\Models\LocationNetwork;

class LineRepository
{

    protected $locationNetwork;

    /**
     * LineRepository constructor.
     */
    public function __construct(LocationNetwork $locationNetwork)
    {
        $this->locationNetwork = $locationNetwork;
    }

    public function getAll()
    {
        return $this->locationNetwork->with('network')->get();
    }

    public function create(array $data)
    {
        return $this->locationNetwork->create($data);
    }

    public function checkNetworkId(int $networkId)
    {
        return $this->locationNetwork->where('network_id', $networkId)->exists();
    }

    public function getLinesById()
    {
        $result = [];
        $locationNetworks = $this->locationNetwork->with('continent', 'country')->get();

        $locationNetworks->map(function ($locationNetwork) use (&$result) {
            if ($locationNetwork['status'] == 1) {
                $result[] = $locationNetwork;
            }
        });

        return $result;
    }

    public function deleteByScheme(int $schemeId)
    {
        $this->locationNetwork->all()->each(function ($item) use ($schemeId) {
            if ($item->network['scheme_id'] == $schemeId) {
                $item->delete();
            }
        });
    }

    public function getRegion()
    {
        return $this->locationNetwork->with('continent', 'country')->where('status', 1)->get();
    }
}
