<?php

namespace App\Http\Requests;

use Hiero7\Services\CdnService;

//use Illuminate\Foundation\Http\FormRequest;

class DeleteCdnRequest extends FormRequest
{
    /**
     * @param \Hiero7\Services\CdnService $cdnService
     *
     * @return bool
     */
    public function authorize(CdnService $cdnService)
    {
        if ($this->cdn->domain_id != $this->domain->id) {
            return false;
        }

        return !$cdnService->checkCurrentCdnIsDefault($this->domain, $this->cdn);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
