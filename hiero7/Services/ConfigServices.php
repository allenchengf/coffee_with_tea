<?php 
namespace Hiero7\Services;
use Hiero7\Models\{Domain,Cdn,CdnProvider,LocationDnsSetting,DomainGroup};
use Hiero7\Traits\DomainHelperTrait;
use Illuminate\Support\Collection;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;


Class ConfigServices
{
    use DomainHelperTrait;

    public function getDifferent(Collection $importData, Collection $dbData)
    {
        //初次比對： Database 和  Import 不相同的。
        // Import 有 Database 沒有的
        $diffWithDateBase = $importData->diffKeys($dbData);
        // Database 有 Import 沒有的
        $diffWithImport = $dbData->diffKeys($importData);

        $importWithoutDataBase = $diffWithDateBase->keyBy('id');
        $dbWithoutImport  = $diffWithImport->keyBy('id');

        //第二次比對：將第一次比對後的結果，再取出 id 想同的資料。
        $updateData = [];
        foreach ($importWithoutDataBase as $key => $value) {
            if (isset($dbWithoutImport[$key])){
                $updateData[] = $value;
            }
        }

        //第三次比對：運用第一次比對的結果，去除 第二次比對的結果。會剩下「只存在 DataBase 有，但 import 沒有的，且 id 也不相同的資料」。
        $InsertData = $diffWithDateBase->keyBy('id')->diffKeys(collect($updateData)->keyBy('id'));
        $deleteData = $diffWithImport->keyBy('id')->diffKeys(collect($updateData)->keyBy('id'));

        return [collect($updateData) ,$InsertData, $deleteData];
    }

    public function update(Collection $updateData , Model $targetTable)
    {        
        $result = [];
        foreach($updateData as $data){
            try{
                $targetTable::where('id',$data['id'])->update(Arr::except($data,['id','hash']));
            } catch  (\Exception $e){
                $result['errorMessage'] = $e->getMessage();
            }
        }

        return $result;
    }

    public function insert(Collection $InsertData ,Model $targetTable)
    {
        $result = [];
        foreach($InsertData as $data){
            try{
                $targetTable::create(Arr::except($data,['id','hash']));
            } catch  (\Illuminate\Database\QueryException $e){
                $result['errorMessage'] = "please check relation column.";
            }
        }

        return $result;
    }

    public function delete(Collection $deleteData ,Model $targetTable)
    {
        $result = [];
        foreach($deleteData as $data){
            try{
                $targetTable::where('id',$data['id'])->delete();
            } catch  (\Exception $e){
                $result['errorMessage'] = $e->getMessage();
            }
        }

        return $result;
    }

}