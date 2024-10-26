<?php

namespace App\Services\Auth\Login;

use Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Interfaces\AuthStrategyInterface;


class EmailAuthStrategy implements AuthStrategyInterface
{
    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(array $payload): User
    {
        /**
         * @var \App\Models\User|null $user
         */
        $user = User::where('email', data_get($payload, 'email'))->first();

        if (
            !$user ||
            Hash::check(data_get($payload, 'password'), $user->password) === false
        ) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records'),
            ]);
        }

        return $user;
    }
}