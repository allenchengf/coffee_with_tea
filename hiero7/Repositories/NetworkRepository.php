<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/9
 * Time: 4:34 PM
 */

namespace Hiero7\Repositories;

use Hiero7\Models\Network;

class NetworkRepository
{
    protected $network;

    /**
     * NetworkRepository constructor.
     */
    public function __construct(Network $network)
    {
        $this->network = $network;
    }

    public function getAll()
    {
        return $this->network::with('locationNetwork')->get();
    }


    public function getNetworksById()
    {
        return $this->network::with('locationNetwork')->where('scheme_id', env('SCHEME'))->get();
    }
    
    public function getNetworkName($networkId)
    {
        return $this->network->where('id', $networkId)->pluck('name')->first();

    }
}