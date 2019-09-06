<?php

namespace App\Http\Requests;

use Hiero7\Models\Domain;

class DomainGroupRequest extends FormRequest
{
    protected $prefix = 'groups';
    /**
     * 如果 沒有這個 domain 就會 403
     * 如果 在 mapping 表內有資料也會是 403
     * @return bool
     */
    public function authorize(Domain $domain)
    {
        if ($this->route()->getName() == "$this->prefix.create") {
            $domainModel = $domain->find($this->domain_id);
            if(!$domainModel){
                return false;
            }
            if (!$domainModel->domainGroup()->get()->isEmpty()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $routeName = $this->route()->getName();

        switch ($routeName) {
            case ($routeName == "$this->prefix.create"):
                return [
                    "name" => "required|string",
                    "domain_id" => "required|integer",
                    "label" => "nullable|string",
                ];
                break;
            case ($routeName == "$this->prefix.edit"):
                return [
                    "name" => "required|string",
                    "label" => "nullable|string"
                ];
                break;
            case ($routeName == "$this->prefix.createDomainToGroup"):
                return [
                    "domain_id" => "required|integer",
                ];
                break;
            case ($routeName == "$this->prefix.changeDefaultCdn"):
                return [
                    "cdn_provider_id" => "required|integer"
                ];
                break;
            default :
                return [];
                break;
        }
    }
    
    public function messages()
    {
        return [
            'required' => ':attribute is required。',
            'string' => ':attribute must be string。',
            'integer' => ':attribute must be integer',
        ];
    } 
}
