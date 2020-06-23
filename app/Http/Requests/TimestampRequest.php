<?php

namespace App\Http\Requests;

class TimestampRequest extends FormRequest
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
        return [
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after_or_equal:start_time',
        ];
    }
}
