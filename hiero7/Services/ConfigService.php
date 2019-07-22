<?php 
namespace Hiero7\Services;
use Hiero7\Models\{Domain,Cdn,CdnProvider,LocationDnsSetting,DomainGroup};
use Hiero7\Traits\DomainHelperTrait;
use Illuminate\Support\Collection;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

Class ConfigService
{
    use DomainHelperTrait;

    public function checkDomainFormate(Array $data)
    {
        $error = [];
        foreach($data['domains'] as $domainData){
            $check = $this->validateDomain($domainData['name']);
            if (!$check){
                $error[]= $domainData;
            }
        }

        return $error ? ['errorData' => $error] : true;
    }
    
    public function insert(Array $InsertData, Model $targetTable)
    {
        $error = '';
        try{
        $targetTable->insert($InsertData);
        } catch (QueryException $e) {
            $error = $e->getMessage();
        }

        return $error ? ['errorData' => $error] : true;
    }

}