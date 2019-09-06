<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\MessageBag;

class BatchRequest extends FormRequest
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
        $action = 'batch';
        $routeName = $this->route()->getName();

        switch($routeName){
            case ($routeName == "domains.$action"):
                return [
                    'domains' => 'required|array',
                    'domains.*.name' => ['required', env('DOMAIN_REGULAR')],
                    'domains.*.cdns' => 'array',
                    'domains.*.cdns.*.name' => ['required', 'string'],
                    'domains.*.cdns.*.cname' => ['required', env('DOMAIN_REGULAR')],
                    'domains.*.cdns.*.ttl' => ['integer', 'min:'.env('CDN_TTL'), 'max:604800'],
                ];
                break;
            case ($routeName == "groups.$action"):
                return [
                    'domains' => 'required|array',
                    'domains.*.name' => ['required', env('DOMAIN_REGULAR')]
                ];
                break;
            default :
                return [];
                break;
        }

    }
    
    public function attributes()
    {
        return [
            'domains.*.name' => 'domain',
            'domains.*.cdns.*.name' => 'cdn',
            'domains.*.cdns.*.cname' => 'cname',
            'domains.*.cdns.*.ttl' => 'ttl',
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
            'message' => $errors,
            'errorCode' => null,
            'data' => null
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }    
}