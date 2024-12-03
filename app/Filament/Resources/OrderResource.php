<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use App\Enums\OrderStatus;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()
                ->schema([
                    Select::make('user_id')
                        ->label('User')
                        ->relationship('user', 'name')
                        ->required()
                        ->disabled(fn ($get) => $get('id') !== null),
                    Select::make('status')
                        ->enum(OrderStatus::class)
                        ->options(OrderStatus::class)
                        ->required(),
                    Placeholder::make('total')
                        ->label('Total')
                        ->content(function (Order $record) {
                            return $record->total ? $record->total : null;
                        }),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->filtersTriggerAction(function ($action) {
            return $action->button()->label('Filters');
        })->columns([
            TextColumn::make('id')
            ->label('Order ID')
            ->sortable(),
            TextColumn::make('user.name')->sortable()->searchable(),
            TextColumn::make('total')->money('USD')->sortable(),
            TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->colors([
                        'warning' => OrderStatus::Pending->value,
                        'info' => OrderStatus::Processing->value,
                        'success' => OrderStatus::Completed->value,
                        'danger' => OrderStatus::Canceled->value,
                    ]),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])
        ->defaultSort('id', 'desc')
        ->filters([
            //Tables\Filters\TrashedFilter::make(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
            Tables\Actions\ForceDeleteAction::make(),
            Tables\Actions\RestoreAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }
}
