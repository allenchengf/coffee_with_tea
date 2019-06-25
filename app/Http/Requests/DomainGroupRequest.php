<?php

namespace App\Http\Requests;

use Hiero7\Models\Domain;

class DomainGroupRequest extends FormRequest
{
    protected $prefix = 'groups';
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Domain $domain)
    {
        if ($this->route()->getName() == "$this->prefix.create") {
            if (!$domain->find($this->domain_id)->domainGroup()->get()->isEmpty()) {
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
                    "label" => "required|string",
                ];
                break;
            case ($routeName == "$this->prefix.edit"):
                return [
                    "name" => "required|string",
                    "default_cdn_provider_id" => "required|integer",
                    "label" => "required|string"
                ];
                break;
            case ($routeName == "$this->prefix.createDomainToGroup"):
                return [
                    "domain_id" => "required|integer",
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
