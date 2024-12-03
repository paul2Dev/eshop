<?php

namespace App\Filament\Resources\VariationAttributeResource\Pages;

use App\Filament\Resources\VariationAttributeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVariationAttributes extends ListRecords
{
    protected static string $resource = VariationAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
