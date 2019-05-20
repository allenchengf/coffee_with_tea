<?php

namespace App\Http\Requests;

//use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Hiero7\Services\CdnService;

class CdnRequest extends FormRequest
{
    /**
     * @param \Hiero7\Services\CdnService $cdnService
     *
     * @return bool
     */
    public function authorize(CdnService $cdnService)
    {
        if ($this->method() == 'PUT') {

            if ($cdnService->checkCurrentCdnIsDefault($this->domain, $this->cdn) and ! $this->request->get('default')) {

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
                'ttl'     => 'integer' . '|min:' . env('CDN_TTL') . '|max:604800',
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
            'ttl'   => 'integer' . '|min:' . env('CDN_TTL') . '|max:604800',
        ];
    }
}
