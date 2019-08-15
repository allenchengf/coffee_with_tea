<?php

namespace App\Http\Requests;

class ScanProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $prefix = 'scan';
        $routeName = $this->route()->getName() ?? $prefix;

        switch ($routeName) {
            case ($routeName == "$prefix.chage.routing-rule"):
                return [
                    "old_cdn_provider_id" => "required|integer|exists:cdn_providers,id",
                    "new_cdn_provider_id" => "required|integer|exists:cdn_providers,id",
                ];
                break;
            default :
                return [];
                break;
        }
    }
}
