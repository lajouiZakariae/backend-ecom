<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class ProductQueryFilters
{
    public function __construct(
        protected ?float $priceFrom = null,
        protected ?float $priceTo = null,
        protected ?string $sortBy = null,
        protected ?string $order = 'asc'
    ) {
    }

    public function __invoke(Builder $productsQueryBuilder): void
    {
        if ($this->priceFrom !== null) {
            $productsQueryBuilder->where('price', '>=', $this->priceFrom);
        }

        if ($this->priceTo !== null) {
            $productsQueryBuilder->where('price', '<=', $this->priceTo);
        }

        if ($this->sortBy) {
            $productsQueryBuilder->orderBy($this->sortBy, $this->order ?? 'asc');
        }
    }
}