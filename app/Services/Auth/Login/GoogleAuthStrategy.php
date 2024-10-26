<?php

namespace App\Services\Auth\Login;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use App\Interfaces\AuthStrategyInterface;


/**
 * @tags Auth
 */
class GoogleAuthStrategy implements AuthStrategyInterface
{

    public function authenticate(array $payload): User
    {
        /**
         * @var \Laravel\Socialite\Contracts\User $googleUser
         */
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->redirectUrl(config('services.google.redirect'))
            ->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => __('User not registered'),
            ]);
        }

        /** @var \App\Models\User $user */
        $user = User::where('email', $googleUser->email)
            ->update([
                'google_id' => $googleUser->id,
            ]);

        return $user;
    }
}
