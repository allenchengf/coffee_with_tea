<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/6/10
 * Time: 4:33 PM
 */

namespace Hiero7\Services;


use App\Events\CdnWasBatchEdited;
use App\Events\CdnWasEdited;
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
                $check = Cdn::where('domain_id',$v)->where('default', 1)->where('cdn_provider_id', $cdnProvider[0]->id)->get();
                if (in_array(0,$default) && count($check) > 0){
                    array_push($situation['have_multi_cdn'],$domain[0]);
                }else if(count($check) > 0){
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
            foreach ($domainId as $k => $v){
                $domain = Domain::where('id',$v)->first();
                $default = Cdn::where('domain_id',$v)->get()->pluck('default')->flatten()->all();
                $check = Cdn::where('domain_id',$v)->where('default', 1)->where('cdn_provider_id', $cdnProvider[0]->id)->get();

                if (in_array(0,$default) && count($check) > 0){
                    DB::beginTransaction();
                    $oldDefault = Cdn::where('domain_id', $v)->where('default', 1)->first();
                    $newDefault = Cdn::where('domain_id', $v)->where('default', 0)->first();

                    $oldDefault->update(['default'=>0]);
                    $newDefault->update(['default'=>1, 'provider_record_id'=>$oldDefault->provider_record_id]);

                    $editedDnsProviderRecordResult = event(new CdnWasEdited($domain, $newDefault));
                    if (!is_null($editedDnsProviderRecordResult[0]['errorCode'])) {
                        DB::rollback();
                        return response()->json([
                            'message' => 'please contact the admin', InternalError::INTERNAL_ERROR,
                            'errorCode' => null,
                            'data' => [],
                        ])->setStatusCode(409);
                    }
                    DB::commit();
                }
            }
        }
    }
}
