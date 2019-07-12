<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hiero7\Models\Domain;

class ConfigController extends Controller
{
    public function get(Request $request,Domain $domain)
    {
        $result = $domain->with('cdns','cdnProvider','locationDnsSettings','domainGroup')->where('user_group_id',$this->getUgid($request))->get();

        return $this->response('', null, $result);
    }
}
