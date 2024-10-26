<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Resources\UserResource;
use App\Models\User;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Post;
use \Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @tags Auth
 */
class LoginController
{
    /**
     * Login Request.
     * 
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    #[Post('login', 'login')]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited();

        /**
         * @var \App\Models\User|null $user
         */
        $user = User::where('email', $request->email)->first();

        if (
            !$user ||
            Hash::check($request->password, $user->password) === false
        ) {
            RateLimiter::hit($this->throttleKey(), 5);

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records'),
            ]);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return UserResource::make($user->load(['firstRole']))
            ->additional(['meta' => ['token' => $token]])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('Too many login attempts. Please try again in :seconds seconds', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower(request()->input('email')) . '|' . request()->ip());
    }
}
