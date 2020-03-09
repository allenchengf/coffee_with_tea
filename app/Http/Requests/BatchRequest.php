<?php

namespace App\Http\Requests;

use Hiero7\Models\Job;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BatchRequest extends FormRequest
{
    /**
     * 如果 Queue 內還有 job 沒處理完，就不能再被加入。
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
        $action    = 'batch';
        $routeName = $this->route()->getName();

        switch ($routeName) {
            case ($routeName == "domains.$action"):
                return [
                    'domains'               => 'required|array',
                    'domains.*.cdns'        => 'array',
                    'domains.*.cdns.*.name' => ['required', 'string'],
                    'domains.*.cdns.*.ttl'  => ['integer', 'min:' . env('CDN_TTL'), 'max:604800'],
                ];
                break;
            case ($routeName == "groups.$action"):
                return [
                    'domains' => 'required|array',
                ];
                break;
            default:
                return [];
                break;
        }

    }

    public function attributes()
    {
        return [
            'domains.*.name'         => 'domain',
            'domains.*.cdns.*.name'  => 'cdn',
            'domains.*.cdns.*.cname' => 'cname',
            'domains.*.cdns.*.ttl'   => 'ttl',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'This :attribute column is must.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json([
            'message'   => $errors,
            'errorCode' => null,
            'data'      => null,
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
