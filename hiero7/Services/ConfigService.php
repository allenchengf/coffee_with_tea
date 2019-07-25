<?php 
namespace Hiero7\Services;
use Hiero7\Traits\DomainHelperTrait;
use DB;
use Illuminate\Database\Eloquent\Model;
use Hiero7\Enums\DbError;
use App\Exceptions\ConfigException;
use Cache;

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
            
            if(empty($cdnsArray['cdns'])){
                continue;
            }
            
            $default = array_pluck($cdnsArray['cdns'],'default');

            if(!in_array(true,$default)){
                $error[]= $domainData['cdns'];
            }

        }

        return empty($error['errorData']) ? true : ['errorData' => array_collapse($error)];
    }
    
    public function insert(Array $InsertData, Model $targetTable)
    {
        try{
            $targetTable->insert($InsertData);
        } catch (\Illuminate\Database\QueryException  $e) {
            DB::rollback();
            Cache::flush();
            $res = $e->getMessage();
            throw new ConfigException(DbError::getDescription(DbError::IMPORT_RELATIONAL_DATA_HAVE_SOME_PROBLEM),
                                        DbError::IMPORT_RELATIONAL_DATA_HAVE_SOME_PROBLEM);
        }

        return true;
    }

}