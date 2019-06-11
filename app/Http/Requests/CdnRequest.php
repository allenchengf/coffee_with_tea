<?php

namespace App\Http\Requests;

//use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DomainValidationRule;
use Hiero7\Services\CdnService;
use Illuminate\Validation\Rule;

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

            if ($cdnService->checkCurrentCdnIsDefault($this->domain, $this->cdn) and !$this->request->get('default')) {

                return false;
            } else if ($this->cdn->domain_id != $this->domain->id) {

                return abort(404);
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
                'cdn_provider_id' => [
                    'required',
                    'integer',
                    Rule::unique('cdns')->ignore($this->cdn->id)->where(function ($query) {
                        $query->where('domain_id', $this->domain->id);
                    }),
                    'exists:cdn_providers,id'
                ],
                'cname' => [
                    new DomainValidationRule,
                    'required',
                    Rule::unique('cdns')->ignore($this->cdn->id)->where(function ($query) {
                        $query->where('domain_id', $this->domain->id);
                    }),
                ],
                'default' => 'required|integer|boolean',
            ];

        }

        return [
            'cdn_provider_id' => [
                'required',
                'integer',
                Rule::unique('cdns')->where(function ($query) {
                    $query->where('domain_id', $this->domain->id);
                }),
                'exists:cdn_providers,id'
            ],
            'cname' => [
                new DomainValidationRule,
                'required',
                Rule::unique('cdns')->where(function ($query) {
                    $query->where('domain_id', $this->domain->id);
                }),
            ],
        ];
    }
}
