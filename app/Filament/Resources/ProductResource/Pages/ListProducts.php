<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use App\Models\Product;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Products'),

            'out_of_stock' => Tab::make('Out of stock')
                ->modifyQueryUsing(fn ($query) => $query->where('stock', '=', 0))
                ->badge(function () {
                    $count = Product::where('stock', '=', 0)->count();
                    return $count > 0 ? $count : null;
                })
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
