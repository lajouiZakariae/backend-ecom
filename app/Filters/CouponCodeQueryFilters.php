<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class CouponCodeQueryFilters
{
    public function __construct(
        protected ?string $sortBy = null,
        protected ?string $order = 'asc'
    ) {
    }

    public function __invoke(Builder $productsQueryBuilder): void
    {
        if ($this->sortBy) {
            $productsQueryBuilder->orderBy($this->sortBy, $this->order ?? 'asc');
        }
    }
}