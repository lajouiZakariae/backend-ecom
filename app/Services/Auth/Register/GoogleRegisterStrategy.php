<?php

namespace App\Services\Auth\Register;

use App\Enums\LoginMethods;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use App\Interfaces\AuthRegisterInterface;

class GoogleRegisterStrategy implements AuthRegisterInterface
{

    public function register(array $inputs): User
    {
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->redirectUrl(config('services.google.redirect'))
            ->user();

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
            'first_name' => explode(' ', $googleUser->getName())[0],
            'last_name' => explode(' ', $googleUser->getName())[1],
            'logged_in_with' => LoginMethods::EMAIL,
            'password' => Hash::make(data_get($inputs, 'password')),
            'detailable_id' => $detailableModel->id,
            'detailable_type' => get_class($detailableModel),
        ]);

        return $user;
    }
}
