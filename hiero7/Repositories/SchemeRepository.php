<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/16
 * Time: 12:33 PM
 */

namespace Hiero7\Repositories;


use Hiero7\Models\Scheme;

class SchemeRepository
{

    protected $scheme;
    /**
     * SchemeRepository constructor.
     */
    public function __construct(Scheme $scheme)
    {
        $this->scheme = $scheme;
    }

    public function getAll()
    {
        return $this->scheme::all();
    }

    public function create(array $data)
    {
        return $this->scheme->create($data);
    }

    public function checkSchemeName($schemeName)
    {
        return $this->scheme->withTrashed()->where('name', $schemeName)->exists();
    }

    public function getSchemeIdByName($schemeName)
    {
        return $this->scheme->withTrashed()->where('name', $schemeName)->pluck('id')->first();
    }
}