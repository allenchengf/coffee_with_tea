<?php

namespace App\Http\Requests;

//use Illuminate\Foundation\Http\FormRequest;

class CdnRequest extends FormRequest
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
        if ($this->method() == 'PUT') {

            return [
                'name'    => 'required|unique:cdns,name,' . $this->cdn,
                'cname'   => 'required|unique:cdns,cname,' . $this->cdn,
                'ttl'     => 'required|integer',
                'default' => 'required|integer|boolean'
            ];

        }

        return [
            'name'  => 'required|unique:cdns,name',
            'cname' => 'required|unique:cdns,cname',
            'ttl'   => 'required|integer',
        ];
    }
}
