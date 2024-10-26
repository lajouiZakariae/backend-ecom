<?php

namespace App\Filters;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Builder;

class UserQueryFilters
{
    public function __construct(
        private ?RoleEnum $role = null,
        private string $sortBy = 'created_at',
        private string $order = 'asc',
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

        $usersQuery->orderBy($this->sortBy, $this->order);
    }
}
