<?php

namespace App\Http\Requests;

class DomainRequest extends FormRequest
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
        $prefix = 'domain';
        $routeName = $this->route()->getName();
        switch ($routeName) {
            case ($routeName == "$prefix.create"):
                return [
                    "domain" => 'required|string',
                    "user_group_id" => 'required|integer',
                ];
                break;
            default:
                return [];
                break;

        }
    }
}
