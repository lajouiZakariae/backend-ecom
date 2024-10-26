<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\RoleEnum;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Spatie\RouteAttributes\Attributes\Post;
use \Symfony\Component\HttpFoundation\Response;

/**
 * @tags Auth
 */
class GoogleAuthController
{
    #[Post('google-with-google', 'google-with-google')]
    public function store(): JsonResponse
    {
        /**
         * @var \Laravel\Socialite\Contracts\User $googleUser
         */
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->redirectUrl(config('services.google.redirect'))
            ->user();

        /**
         * @var User
         */
        $user = User::updateOrCreate(
            [
                'email' => $googleUser->getEmail()
            ],
            [
                'first_name' => str($googleUser->getName())->split(' ')->first(),
                'last_name' => str($googleUser->getName())->split(' ')->last(),
                'google_id' => $googleUser->getId(),
            ]
        );

        if ($user->wasRecentlyCreated) {
            $user->assignRole(RoleEnum::CUSTOMER);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return UserResource::make($user->load(['firstRole']))
            ->additional(['meta' => ['token' => $token]])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
