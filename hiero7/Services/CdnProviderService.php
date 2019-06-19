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
use Hiero7\Models\Domain;
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
        $changeTo = (string) $cdnProvider->ttl;
        return event(new CdnWasBatchEdited($changeTo, $cdn, $change));
    }

    public function updateDnsProviderStatus($cdn, $status)
    {
        $change = 'status';
        $changeTo = ($status == 'active')?'enable':'disable';
        return event(new CdnWasBatchEdited($changeTo, $cdn, $change));
    }

    public function cdnDefaultInfo($cdnProvider)
    {
        $situation = [];
        $situation['have_multi_cdn'] = [];
        $situation['only_default'] = [];

        $domainId = $cdnProvider->pluck('cdns')->flatten()->pluck('domain_id')->all();

        if (!empty($domainId)){
            foreach ($domainId as $k => $v){
                $domain = Domain::where('id',$v)->get()->pluck('name')->all();
                $default = Cdn::where('domain_id',$v)->get()->pluck('default')->flatten()->all();
                if (in_array(0,$default)){
                    array_push($situation['have_multi_cdn'],$domain[0]);
                }else{
                    array_push($situation['only_default'],$domain[0]);
                }
            }
        }
        return $situation;
    }

    public function changeDefaultCDN($cdnProvider)
    {
        $domainId = $cdnProvider->pluck('cdns')->flatten()->pluck('domain_id')->all();
        if (!empty($domainId)){
            $this->cdnProviderRepository->changeDefaultCDN($domainId);
        }
    }
}