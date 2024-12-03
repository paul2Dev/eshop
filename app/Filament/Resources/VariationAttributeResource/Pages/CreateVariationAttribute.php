<?php

namespace App\Filament\Resources\VariationAttributeResource\Pages;

use App\Filament\Resources\VariationAttributeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVariationAttribute extends CreateRecord
{
    protected static string $resource = VariationAttributeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
