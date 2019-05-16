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
        return $this->locationNetwork::with('network')->get();
    }

    public function create(array $data)
    {
        return $this->locationNetwork->create($data);
    }

    public function checkNetworkId(int $networkId)
    {
        return $this->locationNetwork->withTrashed()->where('network_id', $networkId)->exists();
    }

    public function getLinesById($schemeId)
    {
        $result = [];
        $data = $this->locationNetwork::with('network')->get();

        foreach ($data as $k => $v){
            if($v['network']['scheme_id'] == $schemeId){
                $result[] = $v;
            }
        }

        return $result;
    }
}