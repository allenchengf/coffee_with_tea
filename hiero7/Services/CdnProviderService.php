<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/6/10
 * Time: 4:33 PM
 */

namespace Hiero7\Services;


use App\Events\CdnProviderWasDelete;
use App\Events\CdnWasBatchEdited;
use App\Events\CdnWasDelete;
use App\Events\CdnWasEdited;
use Hiero7\Enums\InternalError;
use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Repositories\CdnProviderRepository;
use DB;
use \Hiero7\Models\CdnProvider;
use phpDocumentor\Reflection\Types\Object_;

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

    public function updateCdnProviderTTL($cdnProvider, $cdn)
    {
        $change = 'ttl';
        $changeTo = (string) $cdnProvider->ttl;
        return event(new CdnWasBatchEdited($changeTo, $cdn, $change));
    }

    public function updateCdnProviderStatus($cdn, $status)
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
                $default = $this->getDefault($v);
                $check = Cdn::where('domain_id',$v)->where('default', 1)->where('cdn_provider_id', $cdnProvider[0]->id)->get();
                if(count($check) > 0){
                    if (in_array(0,$default)){
                        array_push($situation['have_multi_cdn'],$domain[0]);
                    }else{
                        array_push($situation['only_default'],$domain[0]);
                    }
                }
            }
        }
        return $situation;
    }

    public function getDefault($domainId)
    {
        return $this->cdnProviderRepository->getDefault($domainId);
    }

    public function changeDefaultCDN($cdnProvider)
    {
        $domainId = $cdnProvider->pluck('cdns')->flatten()->pluck('domain_id')->all();
        if (!empty($domainId)){
            foreach ($domainId as $k => $v){
                $domain = Domain::where('id',$v)->first();
                $default = $this->getDefault($v);
                $check = Cdn::where('domain_id',$v)->where('default', 1)->where('cdn_provider_id', $cdnProvider[0]->id)->get();

                if (in_array(0,$default) && count($check) > 0){
                    DB::beginTransaction();
                    $oldDefault = Cdn::where('domain_id', $v)->where('default', 1)->first();
                    $newDefault = Cdn::where('domain_id', $v)->where('default', 0)->first();

                    $oldDefault->update(['default'=>0]);
                    $newDefault->update(['default'=>1, 'provider_record_id'=>$oldDefault->provider_record_id]);

                    $editedDnsProviderRecordResult = event(new CdnWasEdited($domain, $newDefault));
                    if (!$editedDnsProviderRecordResult) {
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

    public function deleteCDNProvider(CdnProvider $cdnProvider)
    {
        $result = false;
        DB::beginTransaction();
        $cdns = Cdn::with('locationDnsSetting')->where('cdn_provider_id',$cdnProvider->id)->get();
        if($cdns->count() > 0){
            $result =  $this->deleteCdnByCdnProviderId($cdns);
        }
//        CdnProvider::where('id', $cdnProvider->id)->delete();
        DB::commit();
        return $result;
    }

    public function deleteCdnByCdnProviderId($cdns)
    {
        foreach ($cdns as $k => $v) {
            $default = $this->getDefault($v->domain_id);
            if (in_array(0,$default)){
                $domain = Domain::where('id',$v['domain_id'])->first();
                $oldDefault = Cdn::with('locationDnsSetting')->where('domain_id', $v['domain_id'])->where('default', 1)->first();
                $newDefault = Cdn::where('domain_id', $v['domain_id'])->where('default', 0)->first();
                $newDefault->update(['default'=>1, 'provider_record_id'=>$oldDefault->provider_record_id]);
                $oldDefault->update(['default'=>0, 'provider_record_id'=>0]);
                LocationDnsSetting::where('cdn_id', $oldDefault->id)->delete();
                if (!event(new CdnWasEdited($domain, $newDefault))) {
                    DB::rollback();
                    return true;
                }
                if (!event(new CdnProviderWasDelete($oldDefault))) {
                    event(new CdnWasEdited($domain, $oldDefault));
                    DB::rollback();
                    return true;
                }
                $oldDefault->delete();
            }else{
                $cdn = Cdn::with('locationDnsSetting')->where('domain_id', $v['domain_id'])->where('default', 1)->first();
                LocationDnsSetting::where('cdn_id', $cdn->id)->delete();
                if (!event(new CdnProviderWasDelete($cdn))) {
                    DB::rollback();
                    return true;
                }
                $cdn->delete();
            }
        }
    }
}
