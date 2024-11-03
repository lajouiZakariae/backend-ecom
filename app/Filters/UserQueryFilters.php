<?php

namespace App\Filters;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Builder;

class UserQueryFilters
{
    public function __construct(
        private ?RoleEnum $role = null,
        private ?string $search = null
    ) {
    }

    public function __invoke(Builder $usersQuery): void
    {
        if ($this->role) {
            $usersQuery->whereHas(
                'roles',
                fn(Builder $query): Builder => $query->where('name', $this->role)
            );
        }

        if ($this->search) {
            $usersQuery->whereAny(
                ['email', 'first_name', 'last_name'],
                'like',
                "%{$this->search}%",
            );
        }
    }
}
