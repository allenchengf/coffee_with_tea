<?php

namespace App\Http\Requests;

class DomainPinRequest extends FormRequest
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
        return [
            "user_group_id" => 'required|unique:domain_pins,user_group_id',
            "name" => "required|string",
        ];
    }
}
