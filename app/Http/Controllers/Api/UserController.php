<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
use App\Filters\UserQueryFilters;
use App\Http\Requests\UserStoreRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController
{
    public function index(Request $request): ResourceCollection
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'sortBy' => ['in:id,email,first_name,last_name,created_at'],
            'role' => ['nullable', Rule::enum(RoleEnum::class)],
            'order' => ['in:asc,desc'],
            'perPage' => ['integer', 'min:1', 'max:100'],
        ]);

        /**
         * @var User $authUser
         */
        $authUser = Auth::user();

        $users = User::query()
            ->tap(new UserQueryFilters($request->role ?? RoleEnum::CUSTOMER->value, $request->search, $authUser->hasRole(RoleEnum::ADMIN->value) ? [$authUser->id] : []))
            ->with('roles')
            ->orderBy($request->sortBy ?? 'created_at', $request->order ?? 'desc')
            ->paginate($request->perPage);

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

    public function destroy(int $userId): Response
    {
        $affectedRowsCount = User::destroy($userId);

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

        $affectedRowsCount = User::role(RoleEnum::CUSTOMER)
            ->whereIn('id', $request->ids)
            ->delete();

        if ($affectedRowsCount === 0) {
            throw new NotFoundHttpException(__(':resource not found', ['resource' => __('User')]));
        }

        return response()->noContent();
    }

    public function authenticatedUser(): UserResource
    {
        return new UserResource(Auth::user());
    }

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
