<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Livewire\Component;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';



    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(OrderItem::getForm($this->getOwnerRecord()->id));
    }

    public function table(Table $table): Table
    {
        return $table
        ->filtersTriggerAction(function ($action) {
            return $action->button()->label('Filters');
        })->columns([
            TextColumn::make('product.name')->label('Product')->sortable()->searchable(),
            TextColumn::make('quantity'),
            TextColumn::make('price')->money('USD'),
        ])
        ->defaultSort('id', 'desc')
        ->headerActions([
            Tables\Actions\CreateAction::make(),
        ])
        ->filters([
            Tables\Filters\TrashedFilter::make(),
        ])
        ->actions([
            Tables\Actions\EditAction::make()
            ->after(function (Component $livewire) {
                $livewire->dispatch('refreshProducts');
            }),
            Tables\Actions\DeleteAction::make()
            ->after(function (Component $livewire) {
                $livewire->dispatch('refreshProducts');
            }),
            Tables\Actions\ForceDeleteAction::make()
            ->after(function (Component $livewire) {
                $livewire->dispatch('refreshProducts');
            }),
            Tables\Actions\RestoreAction::make()
            ->after(function (Component $livewire) {
                $livewire->dispatch('refreshProducts');
            }),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                ->after(function (Component $livewire) {
                    $livewire->dispatch('refreshProducts');
                }),
                Tables\Actions\ForceDeleteBulkAction::make()
                ->after(function (Component $livewire) {
                    $livewire->dispatch('refreshProducts');
                }),
                Tables\Actions\RestoreBulkAction::make()
                ->after(function (Component $livewire) {
                    $livewire->dispatch('refreshProducts');
                }),
            ]),
        ]);
    }
}
