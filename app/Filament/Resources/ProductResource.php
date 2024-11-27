<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\HasManyRepeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('slug')->required()->unique(Product::class, 'slug', ignoreRecord: true),
            Textarea::make('description')->nullable(),
            TextInput::make('price')->numeric()->required(),
            TextInput::make('stock')->numeric()->required(),
            Select::make('category_id')
                ->relationship('category', 'name')
                ->required(),
            Repeater::make('images')
                ->label('Images')
                ->collapsed() // Optionally collapse the images section by default
                ->defaultItems(1) // Default to 1 image field
                ->schema([
                    FileUpload::make('file_path')  // The file upload for images
                        ->label('Image')
                        ->directory('products/images')  // Directory where the images will be stored
                        ->maxSize(1024)  // Maximum size in KB
                        ->image()  // Ensure only images are uploaded
                        ->required(),  // Ensure the file is required

                    TextInput::make('alt_text')  // Optional input for alt text for the image
                        ->label('Alt Text')
                        ->nullable(),  // Allow nullable values for alt text
                ])
                ->relationship('images')  // Relationship to the Image model (handles the foreign key linking)
                ->dehydrated(false),  // Prevent data from being dehydrated (kept in request)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->filtersTriggerAction(function ($action) {
            return $action->button()->label('Filters');
        })->columns([
            TextColumn::make('name')->sortable()->searchable(),
            TextColumn::make('price')->money('USD'),
            TextColumn::make('stock')->sortable(),
            TextColumn::make('category.name')->label('Category')->sortable(),
            TextColumn::make('created_at')->dateTime(),
        ])
        ->defaultSort('id', 'desc')
        ->filters([
            Tables\Filters\TrashedFilter::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
