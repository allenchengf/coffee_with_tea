<?php

namespace App\Http\Requests;

use App\Rules\DomainValidationRule;
use Illuminate\Validation\Rule;

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
            case ($routeName == "$prefix.get"):
                return [
                    'user_group_id' => 'nullable|integer',
                ];
                break;
            case ($routeName == "$prefix.create"):
                return [
                    'user_group_id' => 'nullable|integer',
                    'name' => [
                        'required',
                        'string',
                        new DomainValidationRule,
                        'unique:domains,name',
                    ],
                    'cname' => 'nullable|string|unique:domains,cname',
                ];
                break;
            case ($routeName == "$prefix.edit"):
                return [
                    'name' => [
                        'nullable',
                        'string',
                        new DomainValidationRule,
                        Rule::unique('domains')->ignore($this->domain->id),
                    ],
                    'cname' => [
                        'nullable',
                        'string',
                        Rule::unique('domains')->ignore($this->domain->id),
                    ],
                ];
                break;
            default:
                return [];
                break;

        }
    }
}
