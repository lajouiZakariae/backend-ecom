<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Resources\UserResource;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

/**
 * @tags Auth
 */
#[Middleware('auth:sanctum')]
class AuthenticatedUserController
{
    /**
     * Get User.
     */
    #[Get('user', 'user')]
    public function __invoke(): UserResource
    {
        return UserResource::make(auth()->user()->load(['detailable', 'profilePicture']));
    }
}
