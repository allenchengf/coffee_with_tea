<?php

namespace App\Http\Requests;

use App\Rules\DomainValidationRule;
use App\Rules\DomainWithCdnProviderGroupMapping;
use Hiero7\Services\CdnService;
use Illuminate\Validation\Rule;

class CdnCreateRequest extends FormRequest
{
    public function authorize()
    {
        if(!$this->domain->domainGroup->isEmpty()){
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
        return [
            'cdn_provider_id' => [
                'required',
                'integer',
                Rule::unique('cdns')->where(function ($query) {
                    $query->where('domain_id', $this->domain->id);
                }),
                'exists:cdn_providers,id',
                new DomainWithCdnProviderGroupMapping($this->domain),
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
