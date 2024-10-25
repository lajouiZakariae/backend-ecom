<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class OrderQueryFilters
{
    public function __construct(
        protected ?int $userId = null,
        protected ?string $sortBy = 'created_at',
        protected ?string $order = 'desc',
    ) {
    }

    public function __invoke(Builder $productsQueryBuilder): void
    {
        if ($this->userId) {
            $productsQueryBuilder->where('user_id', '=', $this->userId);
        }

        if ($this->sortBy) {
            $productsQueryBuilder->orderBy($this->sortBy, $this->order ?? 'asc');
        }
    }
}