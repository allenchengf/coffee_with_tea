<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Hiero7\Models\Domain;


class LocationDnsSettingRequest extends FormRequest
{
        protected $prefix = 'iRoute';
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if($this->route()->getName() == "$this->prefix.edit" && !$this->domain->domainGroup->isEmpty()){
                return false;
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
            case ($routeName == "$this->prefix.edit"):
                return [
                    "cdn_provider_id" => "required|integer",
                ];
                break;
            default :
                return [];
                break;
        }
    }
}
