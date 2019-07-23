<?php 
namespace Hiero7\Services;
use Hiero7\Models\{Domain,Cdn,CdnProvider,LocationDnsSetting,DomainGroup};
use Hiero7\Traits\DomainHelperTrait;
use DB;
use Illuminate\Database\Eloquent\Model;
use Hiero7\Enums\DbError;
use Symfony\Component\HttpFoundation\Response;

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
    
    public function checkCdnHaveDefault(Array $data)
    {
        $error = [];        
        foreach($data as $domainData){
            $cdnsArray = array_only($domainData,['cdns']); 
            $default = array_pluck($cdnsArray['cdns'],'default');

            if(in_array(true,$default)){
                continue;
            }

            $error[]= $domainData['cdns'];
        }

        return $error ? ['errorData' => array_collapse($error)] : true;
    }
    
    public function insert(Array $InsertData, Model $targetTable)
    {
        try{
        $targetTable->insert($InsertData);
        } catch (\Illuminate\Database\QueryException  $e) {
            DB::rollback();
            return response()->json([
                'message' => DbError::INSERT_GOT_SOME_PROBLEM,
                'errorCode' => DbError::INSERT_GOT_SOME_PROBLEM,
                'data' => $targetTable." got some wrong"],400);
        }

        return true;
    }

}