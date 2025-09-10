<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Validator;
use App\Services\ResponseService;


class MedicationSearchRequest extends FormRequest
{
    public function rules()
    {
        return [
            'drug_name' => 'required|string|min:3',
        ];
    }

    public function messages()
    {
        return [
            'drug_name.required' => 'Drug name is required',
            'drug_name.string' => 'Drug name must be a string',
            'drug_name.min' => 'Drug name must be at least 3 characters long',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ResponseService::apiResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid inputs',   $validator->errors()));

    }
}
