<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Put;

#[Middleware('auth:sanctum')]
class UserController
{
    #[Get('user')]
    public function show(): UserResource
    {
        return new UserResource(Auth::user());
    }

    #[Put('user')]
    public function update(Request $request): UserResource
    {
        $user = Auth::user();

        $user->update($request->validate([
            'name' => ['string', 'max:255'],
            'email' => ['string', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'password' => ['string', 'min:8', 'confirmed'],
        ]));

        return new UserResource($user);
    }
}
