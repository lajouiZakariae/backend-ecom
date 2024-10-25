<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['order_id'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
