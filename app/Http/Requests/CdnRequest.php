<?php

namespace App\Http\Requests;

//use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CdnRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        if ($this->method() == 'PUT') {

            $getDefaultRecord = $this->domain->cdns()->default()->first();

            if ($getDefaultRecord and $getDefaultRecord->id == $this->cdn->id and ! $this->request->get('default')) {

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
        if ($this->method() == 'PUT') {

            return [
                'name'    => [
                    'required',
                    Rule::unique('cdns')->ignore($this->cdn->id)->where(function ($query) {
                        $query->where('domain_id', $this->domain->id);
                    }),
                ],
                'cname'   => [
                    'required',
                    Rule::unique('cdns')->ignore($this->cdn->id)->where(function ($query) {
                        $query->where('domain_id', $this->domain->id);
                    }),
                ],
                'ttl'     => 'required|integer',
                'default' => 'required|integer|boolean'
            ];

        }

        return [
            'name'  => [
                'required',
                Rule::unique('cdns')->where(function ($query) {
                    $query->where('domain_id', $this->domain->id);
                }),
            ],
            'cname' => [
                'required',
                Rule::unique('cdns')->where(function ($query) {
                    $query->where('domain_id', $this->domain->id);
                }),
            ],
            'ttl'   => 'required|integer',
        ];
    }
}
