<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\Register\RegisterContext;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Post;
use Symfony\Component\HttpFoundation\Response;

/**
 * @tags Auth
 */
class RegisterController
{
    /**
     * Register Request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    #[Post('register', 'register')]
    public function store(RegisterRequest $request): JsonResponse
    {
        $registerContext = new RegisterContext($request->input('auth_provider'));

        $registeredUser = $registerContext->register($request->validated());

        event(new Registered($registeredUser));

        $token = $registeredUser->createToken('api_token')->plainTextToken;

        $registeredUser->load(['detailable', 'profilePicture']);

        return UserResource::make($registeredUser)
            ->additional(['meta' => ['token' => $token]])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
