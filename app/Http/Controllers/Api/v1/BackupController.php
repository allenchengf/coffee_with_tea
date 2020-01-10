<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hiero7\Repositories\BackupRepository;
use Hiero7\Traits\JwtPayloadTrait;
use Hiero7\Enums\InputError;
use App\Http\Requests\BackupRequest;

class BackupController extends Controller
{
    use JwtPayloadTrait;

    protected $backupRepository;

    public function __construct(BackupRepository $backupRepository)
    {
        $this->backupRepository = $backupRepository;
    }

    public function show(Request $request)
    {
        $result = $this->backupRepository->showByUgid();
        if (! $result)
            return $this->setStatusCode(400)->response('', InputError::GROUP_NOT_EXIST_BACKUPS, []);

        return $this->response('', null, $result);
    }

    public function create(BackupRequest $request)
    {
        $result = $this->backupRepository->showByUgid();
        if ($result)
            return $this->setStatusCode(400)->response('', InputError::GROUP_EXIST_BACKUPS, []);

        $inputs = $this->timePadZeroLeft($request);
        $inputs['user_group_id'] = $this->getJWTUserGroupId();

        $this->backupRepository->create($inputs);

        return $this->response('', null, []);
    }

    public function update(BackupRequest $request)
    {
        $result = $this->backupRepository->showByUgid();
        if (! $result)
            return $this->setStatusCode(400)->response('', InputError::GROUP_NOT_EXIST_BACKUPS, []);

        $inputs = $this->timePadZeroLeft($request);
    
        $conditions = [
            'user_group_id' => $this->getJWTUserGroupId(),
        ];

        $update = $this->backupRepository->updateByWhere($inputs, $conditions);

        return $this->response('', null, []);
    }

    public function timePadZeroLeft($request)
    {
        $request->backedup_hour = str_pad($request->backedup_hour, 2, '0', STR_PAD_LEFT);
        $request->backedup_minute = str_pad($request->backedup_minute, 2, '0', STR_PAD_LEFT);

        return [
            'backedup_at' => "$request->backedup_hour:$request->backedup_minute:00",
        ];
    }
}
