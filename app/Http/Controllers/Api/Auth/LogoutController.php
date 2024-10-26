<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;


/**
 * @tags Auth
 */
#[Middleware('auth:sanctum')]
class LogoutController
{

    /**
     * Log the user out (Invalidate the token).
     */
    #[Post('logout', 'logout')]
    public function destroy(): Response
    {
        auth()->user()->tokens()->delete();

        return response()->noContent();
    }
}
