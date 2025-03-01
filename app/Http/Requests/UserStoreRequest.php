<?php

namespace App\Http\Requests;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserStoreRequest extends FormRequest
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
            'first_name' => [
                'required',
                'string',
                'max:255',
            ],

            'last_name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                Rule::unique(User::class),
                'string',
                'email',
                'max:255',
            ],

            'role' => [
                'required',
                'string',
                Rule::enum(RoleEnum::class),
            ],

            'phone_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::defaults()
            ],
        ];
    }
}
