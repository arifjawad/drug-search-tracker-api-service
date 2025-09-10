<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Validator;
use App\Services\ResponseService;

class MedicationAddRequest extends FormRequest
{
    public function rules()
    {
        return [
            'rxcui' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'rxcui.required' => 'RxCUI is required',
            'rxcui.string' => 'RxCUI must be a string',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ResponseService::apiResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid inputs',   $validator->errors()));

    }
}
