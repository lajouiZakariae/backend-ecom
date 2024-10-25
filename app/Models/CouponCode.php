<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CouponCode extends Model
{
    /** @use HasFactory<\Database\Factories\CouponCodeFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'amount',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
