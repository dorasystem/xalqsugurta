<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Mahsulotlar';

    protected static ?string $modelLabel = 'Mahsulot';

    protected static ?string $pluralModelLabel = 'Mahsulotlar';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asosiy ma\'lumotlar')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name_uz')
                            ->label('Nomi (UZ)')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name_ru')
                            ->label('Nomi (RU)')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name_en')
                            ->label('Nomi (EN)')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('desc_uz')
                            ->label('Tavsif (UZ)')
                            ->rows(3)
                            ->columnSpan(1),

                        Textarea::make('desc_ru')
                            ->label('Tavsif (RU)')
                            ->rows(3)
                            ->columnSpan(1),

                        Textarea::make('desc_en')
                            ->label('Tavsif (EN)')
                            ->rows(3)
                            ->columnSpan(1),
                    ]),

                Section::make('Texnik sozlamalar')
                    ->columns(2)
                    ->schema([
                        TextInput::make('route')
                            ->label('Route nomi')
                            ->required()
                            ->maxLength(100)
                            ->helperText('Masalan: accident, property, gas'),

                        TextInput::make('sort_order')
                            ->label('Tartib raqami')
                            ->numeric()
                            ->default(0),

                        TextInput::make('icon')
                            ->label('Icon klassi')
                            ->maxLength(100)
                            ->helperText('Masalan: bi bi-fire')
                            ->columnSpan(1),

                        Toggle::make('is_active')
                            ->label('Faol')
                            ->default(true)
                            ->columnSpan(1),

                        ColorPicker::make('icon_color')
                            ->label('Icon rangi')
                            ->columnSpan(1),

                        ColorPicker::make('icon_bg')
                            ->label('Icon fon rangi')
                            ->columnSpan(1),
                    ]),

                Section::make('Offerta fayllari')
                    ->columns(3)
                    ->schema([
                        FileUpload::make('offerta_uz')
                            ->label('Offerta (UZ)')
                            ->disk('public')
                            ->directory('offerta')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->downloadable()
                            ->columnSpan(1),

                        FileUpload::make('offerta_ru')
                            ->label('Offerta (RU)')
                            ->disk('public')
                            ->directory('offerta')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->downloadable()
                            ->columnSpan(1),

                        FileUpload::make('offerta_en')
                            ->label('Offerta (EN)')
                            ->disk('public')
                            ->directory('offerta')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->downloadable()
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                TextColumn::make('name_uz')
                    ->label('Nomi (UZ)')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('name_ru')
                    ->label('Nomi (RU)')
                    ->searchable(),

                TextColumn::make('route')
                    ->label('Route')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('icon')
                    ->label('Icon'),

                IconColumn::make('is_active')
                    ->label('Faol')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Yangilangan')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
