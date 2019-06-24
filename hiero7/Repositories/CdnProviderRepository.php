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
use \DB;
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

    public function getDefault($domainId)
    {
        $results = DB::table('cdns')
                    ->join('cdn_providers', 'cdns.cdn_provider_id', '=', 'cdn_providers.id')
                    ->select('default')
                    ->where('domain_id', $domainId)
                    ->where('cdn_providers.status', 'active')
                    ->get()->pluck('default')->all();
        return $results;
    }
}
