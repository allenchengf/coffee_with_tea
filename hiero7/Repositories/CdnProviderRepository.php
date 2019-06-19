<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/6/10
 * Time: 5:05 PM
 */

namespace Hiero7\Repositories;


use Hiero7\Models\Cdn;
use Hiero7\Models\CdnProvider;

class CdnProviderRepository
{
    protected $cdnProvider;
    /**
     * CdnProviderRepository constructor.
     */
    public function __construct(CdnProvider $cdnProvider)
    {
        $this->cdnProvider = $cdnProvider;
    }

    public function getCdnProvider(int $ugid)
    {
        return $this->cdnProvider::where('user_group_id', $ugid)->orderBy('created_at', 'asc')->get();
    }

    public function changeDefaultCDN(array $domainId)
    {
        foreach ($domainId as $k => $v){
            $default = Cdn::where('domain_id',$v)->get()->pluck('default')->flatten()->all();
            if (in_array(0,$default)){
                $oldDefault = Cdn::where('domain_id', $v)->where('default', 1)->first();
                $newDefault = Cdn::where('domain_id', $v)->where('default', 0)->first();

                $oldDefault->update(['default'=>0]);
                $newDefault->update(['default'=>1, 'provider_record_id'=>$oldDefault->provider_record_id]);
            }
        }
    }
}