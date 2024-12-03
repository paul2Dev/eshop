<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Enums\ReviewStatus;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\Section;


class Review extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getForm($productID = null): array
    {
        return [
            Section::make()
                ->schema([
                    Select::make('product_id')
                        ->label('Product')
                        ->relationship('product', 'name')
                        ->required()
                        ->disabled(fn ($get) => $get('id') !== null)
                        ->hidden(function() use ($productID) {
                            return $productID !== null;
                        }),
                    Select::make('user_id')
                        ->label('User')
                        ->relationship('user', 'name')
                        ->required(),
                    Select::make('status')
                        ->enum(ReviewStatus::class)
                        ->options(ReviewStatus::class)
                        ->required(),
                    TextInput::make('title')
                        ->label('Title')
                        ->required()->columnSpan(2),
                    TextInput::make('rating')
                        ->label('Rating')
                        ->required(),
                    Textarea::make('comment')
                        ->label('Comment')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(3),

        ];
    }

    public static function getTableColumns(): array
    {
        return [
            TextColumn::make('user.name')->sortable()->searchable(),
            TextColumn::make('product.name')->sortable()->searchable(),
            TextColumn::make('rating')->sortable(),
            TextColumn::make('title')->sortable()->searchable(),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable()
                ->colors([
                    'warning' => ReviewStatus::Pending->value,
                    'success' => ReviewStatus::Approved->value,
                    'danger' => ReviewStatus::Rejected->value,
                ]),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ];
    }

    public static function getActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
            ActionGroup::make([
                Action::make('approve')
                    ->action(fn ($record) => $record->approve())
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === ReviewStatus::Rejected->value || $record->status === ReviewStatus::Pending->value),
                Action::make('reject')
                    ->action(fn ($record) => $record->reject())
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === ReviewStatus::Approved->value || $record->status === ReviewStatus::Pending->value),
            ]),
        ];
    }

    public function approve(): void
    {
        $this->update(['status' => ReviewStatus::Approved]);
    }

    public function reject(): void
    {
        $this->update(['status' => ReviewStatus::Rejected]);
    }

}
