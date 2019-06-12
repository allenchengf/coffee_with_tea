<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/6/10
 * Time: 4:33 PM
 */

namespace Hiero7\Services;


use Hiero7\Models\Cdn;
use Hiero7\Repositories\CdnProviderRepository;
use DB;
class CdnProviderService
{

    protected $cdnProviderRepository;
    /**
     * CdnProviderService constructor.
     */
    public function __construct(CdnProviderRepository $cdnProviderRepository)
    {
        $this->cdnProviderRepository = $cdnProviderRepository;
    }

    public function getCdnProvider(int $ugid)
    {
        return $this->cdnProviderRepository->getCdnProvider($ugid);
    }

    public function updateCdnProvider($info, $cdnProvider)
    {
        DB::beginTransaction();
        try {
            $cdnProvider->update($info->only('name','ttl', 'edited_by'));
            $cdns = Cdn::where('cdn_provider_id', $cdnProvider->id)->get();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        return $cdnProvider;
    }

    public function changeStatus($status, $cdnProvider)
    {
        DB::beginTransaction();
        try {
            $this->cdnProviderRepository->changeStatus($status, $cdnProvider);
            $cdns = Cdn::where('cdn_provider_id', $cdnProvider->id)->get();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        return $cdnProvider;
    }

}