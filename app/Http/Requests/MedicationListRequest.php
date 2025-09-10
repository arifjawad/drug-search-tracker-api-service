<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Validator;
use App\Services\ResponseService;

class MedicationListRequest extends FormRequest
{
    public function rules()
    {
        return [
            'per_page' => 'nullable|integer|max:500',
        ];
    }

    public function messages()
    {
        return [
            'per_page.integer' => 'Per page must be an integer',
            'per_page.max' => 'Per page must be less than 500',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ResponseService::apiResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid inputs',   $validator->errors()));

    }
}
