<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

class UserController
{
    #[Middleware('auth:sanctum')]
    #[Get('user')]
    public function show(): UserResource
    {
        return new UserResource(Auth::user());
    }
}
