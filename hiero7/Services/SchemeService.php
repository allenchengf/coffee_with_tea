<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/16
 * Time: 12:32 PM
 */

namespace Hiero7\Services;

use Hiero7\Repositories\SchemeRepository;
class SchemeService
{

    protected $schemeRepository;
    /**
     * LineService constructor.
     */
    public function __construct(SchemeRepository $schemeRepository)
    {
        $this->schemeRepository = $schemeRepository;
    }

    public function getAll()
    {
        return $this->schemeRepository->getAll();
    }

    public function create(array $data)
    {
        return $this->schemeRepository->create($data);
    }

    public function checkSchemeName($schemeName)
    {
        return $this->schemeRepository->checkSchemeName($schemeName);
    }
}