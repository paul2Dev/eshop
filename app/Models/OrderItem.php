<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;


class OrderItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'product_id' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariation()
    {
        return $this->belongsTo(ProductVariation::class);
    }

    // Recalculate order total when order item is created or updated
    protected static function booted()
    {
        static::saved(function ($orderItem) {
            $orderItem->order->updateTotal();
        });

        static::deleted(function ($orderItem) {
            $orderItem->order->updateTotal();
        });

        static::restored(function ($orderItem) {
            $orderItem->order->updateTotal();
        });
    }

    public static function getForm($orderID = null) {
        return [
            Select::make('order_id')
                ->relationship('order', 'id')
                ->required()
                ->hidden(function() use ($orderID) {
                    return $orderID !== null;
                }),
            Select::make('product_id')
                ->label('Product')
                ->relationship('product', 'name')// Populate with available products
                ->required()
                ->reactive() // Make it reactive so we can recalculate price when the product is selected
                ->afterStateUpdated(function (callable $set, callable $get) {
                    return self::setPrice($set, $get); // Static call to the setPrice method
                }),

             // Quantity field (DecimalInput replaced with TextInput)
            TextInput::make('quantity')
                ->label('Quantity')
                ->type('number') // Use type number for quantity
                ->default(1)
                ->required()
                ->reactive()
                ->afterStateUpdated(function (callable $set, callable $get) {
                    return self::setPrice($set, $get); // Static call to the setPrice method
                }),

            // Price field (using TextInput)
            TextInput::make('price')
                ->label('Price')
                ->required()
                ->disabled() // Disable the field so the price is auto-calculated
                ->numeric()
                ->dehydrated()
        ];
    }

    public static function setPrice(callable $set, callable $get)
    {
        // Ensure product_id and quantity are set
        if ($productId = $get('product_id') ?? null) {
            // Find the product by its ID
            $product = Product::find($productId);

            if ($product && $product->stock > 0) {
                $quantity = $get('quantity') ?? 1; // Default to 1 if quantity is not provided
                $calculatedPrice = $product->price * $quantity; // Calculate price
                $set('price', $calculatedPrice); // Set the price field with 2 decimal places
            } else {
                $set('price', 0); // Set the price to 0 if the product is not found or out of stock
                Notification::make()
                ->title('Product '.$product->name.' is out of stock')
                ->danger()
                ->persistent()
                ->send();
            }
        }
    }
}
