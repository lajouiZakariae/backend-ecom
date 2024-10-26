<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
use App\Filters\UserQueryFilters;
use App\Http\Requests\UserStoreRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Put;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[ApiResource('users')]
#[Middleware('auth:sanctum')]
class UserController
{
    public function index(Request $request): ResourceCollection
    {
        $request->validate([
            'sort_by' => ['in:created_at'],
            'order' => ['in:asc,desc'],
            'per_page' => ['integer', 'min:1', 'max:100'],
        ]);

        $users = User::query()
            ->tap(new UserQueryFilters(RoleEnum::CUSTOMER))
            ->paginate($request->per_page);

        return UserResource::collection($users);
    }

    public function show(int $userId): UserResource
    {
        $user = User::role(RoleEnum::CUSTOMER)->find($userId);

        if (!$user) {
            throw new NotFoundHttpException(__(':resource not found', ['resource' => __('User')]));
        }

        return new UserResource($user);
    }

    public function store(UserStoreRequest $request): UserResource
    {
        $userValidatedData = $request->validated();

        $user = DB::transaction(function () use ($userValidatedData): User {
            $user = new User($userValidatedData);

            if (!$user->save()) {
                throw new BadRequestHttpException(__(':resource could not be created', ['resource' => __('User')]));
            }

            $user->assignRole(RoleEnum::CUSTOMER);

            return $user;
        });

        return new UserResource($user);
    }

    public function update(UserStoreRequest $request, int $userId): UserResource
    {
        $userValidatedData = $request->validated();

        $affectedRowsCount = User::role(RoleEnum::CUSTOMER)
            ->where('id', $userId)
            ->update($userValidatedData);

        if ($affectedRowsCount === 0) {
            throw new NotFoundHttpException(__(':resource not found', ['resource' => __('User')]));
        }

        return UserResource::make(User::find($userId));
    }

    public function destroy(int $userId): JsonResponse
    {
        $affectedRowsCount = User::destroy($userId);

        if ($affectedRowsCount === 0) {
            throw new NotFoundHttpException(__(':resource not found', ['resource' => __('User')]));
        }

        return response()->noContent();
    }

    #[Get('auth-user')]
    public function authenticatedUser(): UserResource
    {
        return new UserResource(Auth::user());
    }

    #[Put('auth-user')]
    public function updateAuthenticatedUser(Request $request): UserResource
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
