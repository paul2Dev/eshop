<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VariationAttributeResource\Pages;
use App\Filament\Resources\VariationAttributeResource\RelationManagers;
use App\Models\VariationAttribute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VariationAttributeResource extends Resource
{
    protected static ?string $model = VariationAttribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Products';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_variation_id')
                    ->label('Product Variation')
                    ->relationship('productVariation', 'id') // Or another identifying field
                    ->required(),
                Forms\Components\Select::make('attribute_value_id')
                    ->label('Attribute Value')
                    ->relationship('attributeValue', 'value')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('productVariation.product.name')
                    ->label('Product')
                    ->sortable(),
                Tables\Columns\TextColumn::make('productVariation.sku')->label('SKU'),
                Tables\Columns\TextColumn::make('attributeValue.attribute.name')->label('Attribute'),
                Tables\Columns\TextColumn::make('attributeValue.value')->label('Value'),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVariationAttributes::route('/'),
            'create' => Pages\CreateVariationAttribute::route('/create'),
            'edit' => Pages\EditVariationAttribute::route('/{record}/edit'),
        ];
    }
}
