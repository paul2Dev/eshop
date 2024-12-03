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

use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    protected $with = ['productVariation.variationAttributes'];

    public static function form(Form $form): Form
    {
        return $form->schema([

            Tabs::make('Tabs')
            ->tabs([
                Tabs\Tab::make('Details')
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')->required()->maxLength(255)->columnSpan(2),
                                TextInput::make('slug')->required()->unique(Product::class, 'slug', ignoreRecord: true),
                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->required(),
                                    TextInput::make('price')->numeric()->required(),
                                TextInput::make('stock')->numeric()->required(),
                            ])->columns(3),




                        RichEditor::make('description')->nullable()->columnSpanFull(),
                    ])->icon('heroicon-o-bars-3-center-left'),
                Tabs\Tab::make('Images')
                    ->schema([
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
                    ])->icon('heroicon-o-photo'),
                Tabs\Tab::make('Variations')
                    ->schema([
                        // Managing Product Variations
                        Forms\Components\Repeater::make('productVariation')
                        ->collapsed()
                        ->columnSpan('full')
                        ->columns(4)
                        ->relationship('productVariation')
                        ->label('Product Variations')
                        ->schema([
                            // Variation fields
                            Forms\Components\TextInput::make('price')
                                ->label('Variation Price')
                                ->numeric()
                                ->nullable(),
                            Forms\Components\TextInput::make('stock')
                                ->label('Variation Stock')
                                ->numeric()
                                ->nullable(),
                            Forms\Components\TextInput::make('sku')
                                ->label('SKU')
                                ->nullable(),
                            Forms\Components\TextInput::make('discount')
                                ->label('Discount')
                                ->numeric()
                                ->nullable(),
                                //add filament form fields for attibute and attribute value here

                            Repeater::make('variationAttributes')
                                ->relationship('variationAttributes')
                                ->collapsed()
                                ->columnSpan('full')
                                ->columns(2)
                                ->label('Attributes')
                                ->schema([
                                    Select::make('attribute_id')
                                        ->label('Attribute')
                                        ->options(\App\Models\Attribute::query()->pluck('name', 'id')->toArray())
                                        ->searchable() // Enable searching
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $get, callable $set) {
                                            $set('attribute_value_id', null); // Reset attribute_value_id when attribute changes
                                        })
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')
                                                ->required(),
                                            // Forms\Components\TextInput::make('slug')
                                            //     ->required(),
                                        ])
                                        ->createOptionUsing(function (array $data) {
                                            return \App\Models\Attribute::create($data)->id;
                                        })
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),


                                    Select::make('attribute_value_id')
                                        ->label('Attribute Value')
                                        ->options(function (callable $get) {
                                            $attributeId = $get('attribute_id');
                                            return $attributeId
                                                ? \App\Models\AttributeValue::where('attribute_id', $attributeId)
                                                    ->pluck('value', 'id')
                                                : [];
                                        })
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('value')
                                                ->required(),
                                            // Forms\Components\TextInput::make('slug')
                                            //     ->required(),
                                        ])
                                        ->createOptionUsing(function (array $data, callable $get) {
                                            // Create the new Attribute Value
                                            return \App\Models\AttributeValue::create([
                                                'attribute_id' => $get('attribute_id'), // Make sure to pass the attribute_id here
                                                'value' => $data['value'],
                                                //'slug' => $data['slug'],
                                            ])->id;
                                        })
                                        ->searchable() // Enable searching
                                        ->required()
                                ])
                                ->addActionLabel('Add Attribute')

                        ])
                        ->addActionLabel('Add Variation')
                    ])->icon('heroicon-o-square-3-stack-3d'),
            ])->columnSpanFull(),
        ]);
    }



    public static function table(Table $table): Table
    {
        return $table
        ->filtersTriggerAction(function ($action) {
            return $action->button()->label('Filters');
        })->columns([
            TextColumn::make('name')->sortable()->searchable(),
            TextColumn::make('price')->money('USD')->sortable(),
            TextColumn::make('stock')->sortable()->sortable(),
            TextColumn::make('category.name')->label('Category')->sortable()->searchable(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ReviewsRelationManager::class,
        ];
    }
}
