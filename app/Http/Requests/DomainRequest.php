<?php

namespace App\Http\Requests;

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
                    'id' => 'nullable|integer',
                    'user_group_id' => 'nullable|integer',
                ];
                break;
            case ($routeName == "$prefix.create"):
                return [
                    'user_group_id' => 'nullable|integer',
                    'name' => 'required|string',
                    'cname' => 'nullable|string',
                ];
                break;
            case ($routeName == "$prefix.edit"):
                return [
                    'name' => 'nullable|string',
                    'cname' => 'nullable|string',
                ];
                break;
            default:
                return [];
                break;

        }
    }
}
