<?php

namespace App\Http\Requests;

class LineRequest extends FormRequest
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
        $prefix = 'lines';
        $routeName = $this->route()->getName();

        switch ($routeName) {
            case ($routeName == "$prefix.create"):
                return [
                    "continent_id" => "required|integer",
                    "country_id" => "required|integer",
                    "location" => "required|string",
                    "network_id" => "required|integer",
                    "isp" => "required|string",
                ];
                break;
            case ($routeName == "$prefix.edit"):
                return [
                    "continent_id" => "required|integer",
                    "country_id" => "required|integer",
                    "location" => "required|string",
                    "isp" => "required|string",
                ];
                break;
            default :
                return [];
                break;
        }
    }
}
