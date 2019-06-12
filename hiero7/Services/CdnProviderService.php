<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/6/10
 * Time: 4:33 PM
 */

namespace Hiero7\Services;


use App\Events\CdnWasBatchEdited;
use Hiero7\Enums\InternalError;
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

    public function updateDnsProviderTTL($cdnProvider, $cdn)
    {
        $change = 'ttl';
        $changeTo = $cdnProvider->ttl;
        return event(new CdnWasBatchEdited($changeTo, $cdn, $change));
    }

    public function updateDnsProviderStatus($cdn, $status)
    {
        $change = 'status';
        $changeTo = ($status == 'active')?'enable':'disable';
        return event(new CdnWasBatchEdited($changeTo, $cdn, $change));
    }
}