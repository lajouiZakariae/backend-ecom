<?php

namespace App\Services\Auth\Register;

use App\Enums\LoginMethods;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Interfaces\AuthRegisterInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class EmailRegisterStrategy implements AuthRegisterInterface
{
    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(array $inputs): User
    {
        $user = User::where('email', data_get($inputs, 'email'))->first();

        if ($user) {
            throw ValidationException::withMessages([
                'email' => __('User already registered'),
            ]);
        }

        if ($inputs['register_as'] === 'doctor') {
            $detailableModel = Doctor::create([
                'specialization' => $inputs['specialization'],
                'registration_number' => $inputs['registration_number'],
            ]);
        }

        if ($inputs['register_as'] === 'patient') {
            $detailableModel = Patient::create($inputs);
        }

        /**
         * @var \App\Models\User|null $user
         */
        $user = User::create([
            ...$inputs,
            'logged_in_with' => LoginMethods::EMAIL,
            'password' => Hash::make(data_get($inputs, 'password')),
            'detailable_id' => $detailableModel->id,
            'detailable_type' => get_class($detailableModel),
        ]);

        return $user;
    }
}