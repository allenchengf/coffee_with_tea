<?php

namespace App\Http\Requests;

use Hiero7\Models\Domain;

class DomainGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Domain $domain)
    {
        if ($this->method() == 'POST') {
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
        $prefix = 'groups';
        $routeName = $this->route()->getName();

        switch ($routeName) {
            case ($routeName == "$prefix.create"):
                return [
                    "name" => "required|string",
                    "domain_id" => "required|integer",
                    "label" => "required|string",
                ];
                break;
            case ($routeName == "$prefix.edit"):
                return [
                    "name" => "required|string",
                    "default_cdn_provider_id" => "required|integer",
                    "label" => "required|string"
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
