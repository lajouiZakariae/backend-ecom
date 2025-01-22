<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class CategoryFilters
{
    public function __construct(
        private ?string $search,
    ) {
    }

    public function __invoke(Builder $query): void
    {
        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }
    }
}
