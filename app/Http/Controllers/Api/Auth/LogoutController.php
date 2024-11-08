<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Response;


/**
 * @tags Auth
 */
class LogoutController
{

    /**
     * Log the user out (Invalidate the token).
     */
    public function destroy(): Response
    {
        /**
         * @var \App\Models\User $user
         */
        $user = auth()->user();

        $user->currentAccessToken()->delete();

        return response()->noContent();
    }
}
