<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Validator;
use App\Services\ResponseService;
use Illuminate\Validation\Rules\Password;

class RegistrationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                    'required',
                    'string',
                    'email:rfc,dns',
                    'max:255',
                    'unique:users,email',
                ],
            'password' => [
                    'required',
                    'string',
                    Password::min(8)->mixedCase()->numbers()->max(255),
                ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email is invalid',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters long',
            'password.max' => 'Password must be less than 255 characters long',
            'password.uppercase' => 'Password must contain at least one uppercase letter',
            'password.lowercase' => 'Password must contain at least one lowercase letter',
            'password.numbers' => 'Password must contain at least one number',
            // 'password.symbols' => 'Password must contain at least one symbol',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ResponseService::apiResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid inputs',   $validator->errors()));

    }
}
