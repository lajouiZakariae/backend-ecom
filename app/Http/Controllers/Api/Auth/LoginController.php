<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\Login\AuthContext;
use Hash;
use Illuminate\Http\JsonResponse;
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
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    #[Post('login', 'login')]
    public function store(LoginRequest $request): JsonResponse
    {
        $this->ensureIsNotRateLimited();

        try {
            $authContext = new AuthContext($request->input('auth_provider'));

            $user = $authContext->authenticate($request->validated());

            $token = $user->createToken('api_token')->plainTextToken;
        } catch (ValidationException $exception) {
            RateLimiter::hit($this->throttleKey(), 5);

            throw $exception;
        }

        return UserResource::make($user->load(['firstRole']))
            ->additional(['meta' => ['token' => $token]])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function authenticate($inputs): array
    {
        /**
         * @var \App\Models\User|null $user
         */
        $user = User::where('email', data_get($inputs, 'email'))->first();

        if (
            !$user ||
            Hash::check(data_get($inputs, 'password'), $user->password) === false
        ) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        $token = $user->createToken('api_token')->plainTextToken;

        return [$user, $token];
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
