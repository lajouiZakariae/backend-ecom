<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Filters\UserQueryFilters;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController
{
    public function index(Request $request): ResourceCollection
    {
        $request->validate([
            'sortBy' => ['in:id,email,first_name,last_name,created_at,status'],
            'order' => ['in:asc,desc'],
            'perPage' => ['integer', 'min:1', 'max:100'],
            'role' => ['nullable', Rule::enum(RoleEnum::class)],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(UserStatusEnum::class)],
        ]);

        /**
         * @var User $authUser
         */
        $authUser = Auth::user();

        $userQueyFilters = new UserQueryFilters(
            $request->role,
            $request->search,
            $request->status,
            $authUser->hasRole(RoleEnum::ADMIN) && ($request->role === RoleEnum::ADMIN->value || !$request->role) ? [$authUser->id] : []
        );

        $users = User::query()
            ->tap($userQueyFilters)
            ->with('roles')
            ->orderBy($request->sortBy ?? 'created_at', $request->order ?? 'desc')
            ->paginate($request->perPage);

        return UserResource::collection($users);
    }

    public function show(int $userId): UserResource
    {
        /**
         * @var User $authUser
         */
        $authUser = Auth::user();

        $user = User::whereNot('id', $authUser->id)->find($userId);

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

            if ($userValidatedData['role'] === RoleEnum::CUSTOMER->value) {
                $user->status = UserStatusEnum::ACTIVE;
            }

            if (!$user->save()) {
                throw new BadRequestHttpException(__(':resource could not be created', ['resource' => __('User')]));
            }

            $user->assignRole($userValidatedData['role']);

            return $user;
        });

        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request, int $userId): UserResource
    {
        $userValidatedData = $request->validated();

        /**
         * @var User $authUser
         */
        $authUser = Auth::user();

        if ($authUser->id === $userId) {
            throw new NotFoundHttpException(__(':resource not found', ['resource' => __('User')]));
        }

        if (!$request->password) {
            unset($userValidatedData['password']);
        }

        $affectedRowsCount = User::where('id', $userId)->update($userValidatedData);

        if ($affectedRowsCount === 0) {
            throw new NotFoundHttpException(__(':resource not found', ['resource' => __('User')]));
        }

        return UserResource::make(User::find($userId));
    }

    public function destroy(int $userId): Response
    {
        /**
         * @var User $authUser
         */
        $authUser = Auth::user();

        if ($authUser->id === $userId) {
            throw new NotFoundHttpException(__(':resource not found', ['resource' => __('User')]));
        }

        $affectedRowsCount = User::where('id', $userId)->delete();

        if ($affectedRowsCount === 0) {
            throw new NotFoundHttpException(__(':resource not found', ['resource' => __('User')]));
        }

        return response()->noContent();
    }

    public function destroyMany(Request $request): Response
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        /**
         * @var User $authUser
         */
        $authUser = Auth::user();

        $usersIdsToDestroy = collect($request->ids)->filter(fn(int $id): bool => $id !== $authUser->id);

        $affectedRowsCount = User::role(RoleEnum::CUSTOMER)->whereIn('id', $usersIdsToDestroy)->delete();

        if ($affectedRowsCount === 0) {
            throw new NotFoundHttpException(__(':resource not found', ['resource' => __('User')]));
        }

        return response()->noContent();
    }

    public function authenticatedUser(): UserResource
    {
        return new UserResource(Auth::user()->load('roles'));
    }

    public function updateAuthenticatedUser(Request $request): UserResource
    {
        $user = Auth::user();

        $user->update($request->validate([
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'email' => ['string', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'password' => ['string', 'min:8', 'confirmed'],
        ]));

        return new UserResource($user);
    }
}
