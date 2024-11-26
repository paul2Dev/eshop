<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderItemResource\Pages;
use App\Filament\Resources\OrderItemResource\RelationManagers;
use App\Models\OrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DecimalInput;
use App\Models\Product;

class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('order_id')
                ->relationship('order', 'id')
                ->required(),
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
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('order_id')
                ->label('Order ID')
                ->sortable(),
            TextColumn::make('product.name')->label('Product')->sortable(),
            TextColumn::make('quantity'),
            TextColumn::make('price')->money('USD'),
            TextColumn::make('created_at')->dateTime(),
        ])
        ->defaultSort('id', 'desc')
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderItems::route('/'),
            'create' => Pages\CreateOrderItem::route('/create'),
            'edit' => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }

    public static function setPrice(callable $set, callable $get)
    {
        // Ensure product_id and quantity are set
        if ($productId = $get('product_id') ?? null) {
            // Find the product by its ID
            $product = Product::find($productId);

            if ($product) {
                $quantity = $get('quantity') ?? 1; // Default to 1 if quantity is not provided
                $calculatedPrice = $product->price * $quantity; // Calculate price
                $set('price', $calculatedPrice); // Set the price field with 2 decimal places
            }
        }
    }
}
