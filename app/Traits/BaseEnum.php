<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait BaseEnum
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return \Illuminate\Support\Collection<int,mixed>
     */
    public static function collect(): Collection
    {
        return collect(self::values());
    }

    /**
     * @return \Illuminate\Support\Collection<int,mixed>
     */
    public static function map(callable $callback): Collection
    {
        return self::collect()->map($callback);
    }

    /**
     * @return \Illuminate\Support\Collection<int,mixed>
     */
    public static function each(callable $callback): Collection
    {
        return self::collect()->each($callback);
    }
}