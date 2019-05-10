<?php
/**
 * Created by PhpStorm.
 * User: hanhanhu
 * Date: 2019-05-09
 * Time: 14:17
 */

namespace Hiero7\Repositories;

use Hiero7\Models\Cdn;

class CdnRepository
{
    protected $model;

    /**
     * CdnRepository constructor.
     *
     * @param $model
     */
    public function __construct(Cdn $model)
    {
        $this->model = $model;
    }

    public function get($domain)
    {
        return $this->model::getByDomainId($domain)->get();
    }

    public function store($domain, $data)
    {
        $this->model::getByDomainId($domain)->create($data);
    }

    public function update($domain)
    {

    }

    public function destroy($domain)
    {

    }

}