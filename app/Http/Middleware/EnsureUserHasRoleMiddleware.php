<?php

namespace App\Http\Middleware\pkg_shop;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        /**
         * @var \App\Models\User
         */
        $user = auth()->user();

        if (!$user || $user->hasAnyRole($roles) === false) {
            throw new AuthenticationException();
        }

        return $next($request);
    }
}
