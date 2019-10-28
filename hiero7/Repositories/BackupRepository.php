<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/13
 * Time: 12:22 PM
 */

namespace Hiero7\Repositories;


use Hiero7\Models\Backup;
use Hiero7\Traits\JwtPayloadTrait;

class BackupRepository
{
    use JwtPayloadTrait;

    protected $backup;
    /**
     * CountryRepository constructor.
     */
    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
    }

    public function index()
    {
        return $this->backup->all();
    }

    public function showByUgid()
    {
        $backup = $this->backup->where('user_group_id', $this->getJWTUserGroupId())->first();
        $backup['backedup_at'] = substr($backup['backedup_at'], 0, 5);
        
        return $backup;
    }

    public function create(array $data)
    {
        return $this->backup->create($data);
    }

    public function updateByWhere(array $inputs, array $conditions = null)
    {
        $update = $this->backup;

        if (is_array($conditions)) {
            foreach ($conditions as $k => $v){
                $update = $update->where($k, $v);
            }
        }

        return $update->update($inputs);
    }
}