<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Mahsulotlar';

    protected static ?string $modelLabel = 'Mahsulot';

    protected static ?string $pluralModelLabel = 'Mahsulotlar';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name_uz';

    /** Languages shown as tabs in the form */
    private const LOCALES = [
        'uz' => 'O\'zbekcha',
        'ru' => 'Русский',
        'en' => 'English',
    ];

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Tabs::make('Tillar')
                    ->columnSpan(['lg' => 2])
                    ->tabs(collect(self::LOCALES)->map(fn (string $label, string $locale) => Tab::make($label)
                        ->schema([
                            TextInput::make("name_{$locale}")
                                ->label('Nomi')
                                ->required()
                                ->maxLength(255),

                            Textarea::make("desc_{$locale}")
                                ->label('Qisqa tavsif')
                                ->helperText('Bosh sahifadagi kartada ko\'rinadi. 1–2 gap.')
                                ->rows(3)
                                ->maxLength(300),

                            FileUpload::make("offerta_{$locale}")
                                ->label('Oferta (PDF)')
                                ->disk('public')
                                ->directory('offerta')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240)
                                ->downloadable()
                                ->openable(),
                        ]))
                        ->values()
                        ->all()),

                Grid::make(1)
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Section::make('Holat')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Saytda ko\'rinadi')
                                    ->default(true),

                                TextInput::make('sort_order')
                                    ->label('Tartib raqami')
                                    ->helperText('Kichik raqam oldinda turadi.')
                                    ->numeric()
                                    ->default(0),
                            ]),

                        Section::make('Sozlamalar')
                            ->schema([
                                TextInput::make('route')
                                    ->label('Sahifa (route)')
                                    ->datalist(array_keys(Product::CATEGORIES))
                                    ->required()
                                    ->maxLength(100)
                                    ->helperText('Masalan: gas, property, osago. Havola: /uz/{route}'),

                                TextInput::make('icon')
                                    ->label('Ikonka')
                                    ->placeholder('bi bi-fire')
                                    ->maxLength(100)
                                    ->live(onBlur: true)
                                    ->prefix(fn (Get $get): HtmlString => new HtmlString(
                                        '<i class="' . e($get('icon') ?: 'bi bi-question') . '"></i>'
                                    ))
                                    ->helperText(new HtmlString(
                                        'Bootstrap Icons klassi. <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener" class="underline">Ro\'yxat</a>'
                                    )),
                            ]),
                    ]),
            ]);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('icon')
                    ->label('')
                    ->formatStateUsing(fn (?string $state): HtmlString => new HtmlString(
                        '<span class="xs-product-icon"><i class="' . e($state ?: 'bi bi-shield-check') . '"></i></span>'
                    ))
                    ->width('3.5rem'),

                TextColumn::make('name_uz')
                    ->label('Mahsulot')
                    ->description(fn (Product $record): ?string => $record->desc_uz)
                    ->searchable(['name_uz', 'name_ru', 'name_en'])
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('category')
                    ->label('Kategoriya')
                    ->state(fn (Product $record): ?string => $record->categoryKey()
                        ? __t('messages.product_categories.' . $record->categoryKey(), [], 'uz')
                        : null)
                    ->badge()
                    ->color('primary')
                    ->placeholder('—'),

                TextColumn::make('languages')
                    ->label('Oferta')
                    ->state(fn (Product $record): string => collect(array_keys(self::LOCALES))
                        ->map(fn (string $l) => strtoupper($l) . ($record->{"offerta_{$l}"} ? ' ✓' : ' —'))
                        ->implode('  '))
                    ->color(fn (Product $record): string => $record->offerta_uz && $record->offerta_ru && $record->offerta_en ? 'success' : 'warning')
                    ->size('sm'),

                ToggleColumn::make('is_active')
                    ->label('Saytda'),

                TextColumn::make('updated_at')
                    ->label('Yangilangan')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Saytda')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Product $record): string => $record->url(), shouldOpenInNewTab: true),
                EditAction::make()->label('Tahrirlash'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->reorderRecordsTriggerAction(fn (Action $action, bool $isReordering) => $action
                ->label($isReordering ? 'Tayyor' : 'Tartibni o\'zgartirish')
                ->button())
            ->paginated(false);
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
