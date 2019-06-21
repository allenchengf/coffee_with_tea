<?php

namespace App\Http\Requests;

use App\Rules\DomainValidationRule;
use Illuminate\Validation\Rule;
use Hiero7\Services\CdnService;

class CdnUpdateRequest extends FormRequest
{
    /**
     * @param \Hiero7\Services\CdnService $cdnService
     *
     * @return bool
     */
    public function authorize(CdnService $cdnService)
    {
        $prefix = 'cdn';
        $routeName = $this->route()->getName() ?? $prefix;

        if ($routeName === "$prefix.default") {

            if ($cdnService->checkCurrentCdnIsDefault($this->domain, $this->cdn) and !$this->request->get('default')) {

                return false;
            } 
        }

        if ($this->cdn->domain_id != $this->domain->id) {

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
        $prefix = 'cdn';
        $routeName = $this->route()->getName() ?? $prefix;
        switch ($routeName) {
            case ($routeName === "$prefix.default"):
                return [
                    'default' => 'required|integer|boolean',
                ];
                break;
            case ($routeName === "$prefix.cname"):
                return [
                    'cname' => [
                        'required',
                        new DomainValidationRule,
                        Rule::unique('cdns')->ignore($this->cdn->id)->where(function ($query) {
                            $query->where('domain_id', $this->domain->id);
                        }),
                    ],
                ];
                break;
        }
    }
}
