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
use App\Events\CdnWasEdited;
use DB;
use Hiero7\Enums\InternalError;
use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Repositories\CdnProviderRepository;
use Hiero7\Repositories\CdnRepository;
use Illuminate\Support\Collection;
use \Hiero7\Models\CdnProvider;

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
        $changeTo = ($status == 'active') ? 'enable' : 'disable';
        return event(new CdnWasBatchEdited($changeTo, $cdn, $change));
    }

    public function cdnDefaultInfo($cdnProvider)
    {
        $situation = [
            'have_multi_cdn' => [],
            'only_default' => [],
        ];

        // 取得全部的 CdnProvider Status = Active
        $activeCdnProviders = $this->getCdnProviderStatusIsActive()->keyBy('id');

        foreach ($cdnProvider->domains as $domain) {
            if ($domain->cdns->default) {
                $cdns = app()->call([$this, 'getCdnsByDomain'],
                    [
                        'domain' => $domain,
                    ])->keyBy('cdn_provider_id');

                // 計算差異，如果有 Mapping 到數量會降低
                $diffCdnProviderCount = $cdns->diffKeys($activeCdnProviders)->count();

                (($cdns->count() - 1) > $diffCdnProviderCount) ?
                array_push($situation['have_multi_cdn'], $domain->name) :
                array_push($situation['only_default'], $domain->name);

            }
        }

        return $situation;
    }

    /**
     * 取得 Cdns By Domain
     *
     * @param CdnRepository $cdnRepository
     * @param integer $domainId
     * @return Collection
     */
    public function getCdnsByDomain(CdnRepository $cdnRepository, Domain $domain): Collection
    {
        return $cdnRepository->getCdnsByDomainId($domain->id);
    }

    public function getDefault($domainId)
    {
        return $this->cdnProviderRepository->getDefault($domainId);
    }

    private function getCdnProviderStatusIsActive(): Collection
    {
        return $this->cdnProviderRepository->getStatusIsActive();
    }

    /**
     * 當 Status 欄位 = stop 或 Url 欄位 = null 且 Scannable 狀態為 true 的時候，修改 Scannable 的狀態為 false。
     *
     * @param CdnProvider $cdnProvider
     * @param String $editedBy
     * @return void
     */
    public function checkWhetherStopScannable(CdnProvider $cdnProvider, String $editedBy)
    {
        if ((!$cdnProvider->status || empty($cdnProvider->url)) && $cdnProvider->scannable) {
            $result = $this->cdnProviderRepository->updateScannable($cdnProvider, 0, $editedBy);
        }

        return $result ?? true;
    }

    public function changeDefaultCDN($cdnProvider)
    {
        $domainIds = $cdnProvider->pluck('cdns')->flatten()->pluck('domain_id')->all();
        if (!empty($domainIds)) {
            foreach ($domainIds as $domainId) {
                $domain = Domain::where('id', $domainId)->first();
                $default = $this->getDefault($domainId);
                $check = Cdn::where('domain_id', $domainId)->where('default', 1)->where('cdn_provider_id', $cdnProvider[0]->id)->get();

                if (in_array(0, $default) && count($check) > 0) {
                    DB::beginTransaction();
                    $oldDefault = Cdn::where('domain_id', $domainId)->where('default', 1)->first();
                    $newDefault = Cdn::where('domain_id', $domainId)->where('default', 0)->first();

                    $oldDefault->update(['default' => 0]);
                    $newDefault->update(['default' => 1, 'provider_record_id' => $oldDefault->provider_record_id]);

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
        $cdns = Cdn::with('locationDnsSetting')->where('cdn_provider_id', $cdnProvider->id)->get();
        if ($cdns->count() > 0) {
            $result = $this->deleteCdnByCdnProviderId($cdns);
        }
        CdnProvider::where('id', $cdnProvider->id)->delete();
        DB::commit();
        return $result;
    }

    public function deleteCdnByCdnProviderId($cdns)
    {
        foreach ($cdns as $cdn) {
            $default = $this->getDefault($cdn->domain_id);
            if (in_array(0, $default)) {
                $domain = Domain::where('id', $cdn['domain_id'])->first();
                $oldDefault = Cdn::with('locationDnsSetting')->where('domain_id', $cdn['domain_id'])->where('default', 1)->first();
                $newDefault = Cdn::where('domain_id', $cdn['domain_id'])->where('default', 0)->first();
                $newDefault->update(['default' => 1, 'provider_record_id' => $oldDefault->provider_record_id]);
                $oldDefault->update(['default' => 0, 'provider_record_id' => 0]);
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
            } else {
                $cdn = Cdn::with('locationDnsSetting')->where('domain_id', $cdn['domain_id'])->where('default', 1)->first();
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
