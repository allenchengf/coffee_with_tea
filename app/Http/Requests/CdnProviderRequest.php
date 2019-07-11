<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
class CdnProviderRequest extends FormRequest
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
        $prefix = 'cdn_providers';
        $routeName = $this->route()->getName();

        switch ($routeName) {
            case ($routeName == "$prefix.store"):
                return [
                    'user_group_id' => 'required|integer',
                    'name'  => [
                        'required',
                        Rule::unique('cdn_providers')->where(function ($query) {
                            $query->where('name', $this->name)
                                  ->where('user_group_id',$this->request->get('user_group_id'));
                        }),
                    ],
                    'ttl'   => 'integer' . '|min:' . env('CDN_TTL') . '|max:604800',
                ];
                break;
            case ($routeName == "$prefix.update"):
                return [
                    'name'  => [
                        Rule::unique('cdn_providers')->where(function ($query) {
                            $query->where('name', $this->name)
                                ->where('id', '!=',$this->id)
                                ->where('user_group_id',$this->request->get('user_group_id'));
                        }),
                    ],
                    'ttl'   => 'integer' . '|min:' . env('CDN_TTL') . '|max:604800',
                ];
                break;
            case ($routeName == "$prefix.status"):
                return [
                    'status'   => 'required|integer',
                ];
                break;
            default :
                return [];
                break;
        }
    }
}
