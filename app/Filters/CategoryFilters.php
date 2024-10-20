<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class CategoryFilters
{
    public function __construct(
        private string $sortBy,
        private string $order
    ) {
    }

    public function __invoke(Builder $query): void
    {
        $query->orderBy($this->sortBy, $this->order);
    }
}
