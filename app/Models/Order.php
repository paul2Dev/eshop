<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $cascadeDeletes = ['orderItems'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function booted()
    {
        static::deleting(function (Order $order) {
            if ($order->isForceDeleting()) {
                $order->orderItems()->forceDelete();
            } else {
                $order->orderItems()->delete();
            }
        });

        static::restoring(function (Order $order) {
            $order->orderItems()->withTrashed()->restore();
        });
    }

    // Method to update the total field in the Order
    public function updateTotal()
    {
        $total = $this->orderItems->sum('price');
        $this->update(['total' => $total]);
    }
}
