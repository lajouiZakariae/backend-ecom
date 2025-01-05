<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class UserQueryFilters
{
    public function __construct(
        private ?string $roleName = null,
        private ?string $search = null,
        private ?array $exludeIds = null,
    ) {
    }

    public function __invoke(Builder $usersQuery): void
    {
        if ($this->roleName) {
            $usersQuery->whereHas(
                'roles',
                fn(Builder $query): Builder => $query->where('name', $this->roleName)
            );
        }

        if ($this->search) {
            $usersQuery->whereAny(
                ['email', 'first_name', 'last_name'],
                'like',
                "%{$this->search}%",
            );
        }

        if ($this->exludeIds !== null && count($this->exludeIds) > 0) {
            $usersQuery->whereNotIn('id', $this->exludeIds);
        }
    }


}
