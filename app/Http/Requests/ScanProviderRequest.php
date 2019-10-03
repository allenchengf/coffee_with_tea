<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Hiero7\Traits\JwtPayloadTrait;

class ScanProviderRequest extends FormRequest
{
    use JwtPayloadTrait;

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
        $prefix = 'scan';
        $routeName = $this->route()->getName() ?? $prefix;

        $userGroupId = $this->getJWTUserGroupId();

        switch ($routeName) {
            case ($routeName == "$prefix.chage.routing-rule"):
                return [
                    "old_cdn_provider_id" => "required|integer|exists:cdn_providers,id",
                    "new_cdn_provider_id" => "required|integer|exists:cdn_providers,id",
                ];
            case ($routeName == "$prefix.create"):
                return [
                    "cdn_provider_id" => [
                        'required',
                        'integer',
                        Rule::exists('cdn_providers', 'id')->where(function ($query) use (&$userGroupId) {
                            return $query->where('user_group_id', $userGroupId)->where('scannable', '>', 0);
                        }),
                    ],
                    "scanned_at" => [
                        'required',
                    ],
                ];
            case ($routeName == "$prefix.show"):
                return [
                    "cdn_provider_id" => [
                        'required',
                        'integer',
                        Rule::exists('cdn_providers', 'id')->where(function ($query) use (&$userGroupId) {
                            return $query->where('user_group_id', $userGroupId)->where('scannable', '>', 0);
                        }),
                    ],
                ];
            default :
                return [];
        }
    }
}
