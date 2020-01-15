<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\MessageBag;

class BackupRequest extends FormRequest
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
        $prefix = 'backups';
        $routeName = $this->route()->getName() ?? $prefix;

        switch ($routeName) {
            case ($routeName == "$prefix.upsert"):
                return [
                    "backedup_hour" => ['required','integer','between:0,23'],
                    "backedup_minute" => ['required','integer','between:0,59'],
                ];
            default :
                return [];
        }
    }

    /**
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = collect((new ValidationException($validator))->errors())->flatten();

        throw new HttpResponseException(response()->json([
            'message'   => $errors,
            'errorCode' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            'data'      => [],
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
