<?php

namespace App\Filament\Resources\VariationAttributeResource\Pages;

use App\Filament\Resources\VariationAttributeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVariationAttribute extends EditRecord
{
    protected static string $resource = VariationAttributeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
