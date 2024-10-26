<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'auth_provider' => ['required', Rule::in(['email', 'google'])],

            /**
             * required if auth_provider is email
             * @body  
             */
            'first_name' => [
                Rule::requiredIf(fn(): bool => $this->auth_provider === 'email'),
                'string',
                'max:255',
            ],

            /**
             * required if auth_provider is email
             * @body  
             */
            'last_name' => [
                Rule::requiredIf(fn(): bool => $this->auth_provider === 'email'),
                'string',
                'max:255',
            ],

            /**
             * required if auth_provider is email
             * @body
             */
            'email' => [
                Rule::requiredIf(fn(): bool => $this->auth_provider === 'email'),
                'string',
                'email',
                'max:255',
            ],

            'phone_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            /**
             * required if auth_provider is email
             *  @body
             */
            'password' => [
                Rule::requiredIf(fn(): bool => $this->auth_provider === 'email'),
                'confirmed',
                Password::defaults()
            ],

            /**
             * required if auth_provider is google
             *  @body
             */
            'code' => [
                Rule::requiredIf(fn(): bool => in_array($this->auth_provider, ['google'])),
                'string'
            ],
        ];
    }
}
