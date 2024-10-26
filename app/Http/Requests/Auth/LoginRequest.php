<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'auth_provider' => ['required', Rule::in(['email', 'google'])],

            /**
             * required if auth_provider is email
             * 
             * @body
             */
            'email' => ['required', 'string', 'email'],

            /**
             * required if auth_provider is email
             * 
             * @body
             */
            'password' => ['required_if:auth_provider,email', 'string'],

            /**
             * required if auth_provider is google
             * 
             * @body
             */
            'code' => ['required_if:auth_provider,google', 'string'],
        ];
    }
}
