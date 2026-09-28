<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Filament\Admin\Resources\ProductResource\RelationManagers\SettingChangesRelationManager;
use App\Models\Product;
use App\Services\ProductSettings;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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

                            Section::make('Mahsulot sahifasi')
                                ->description('/' . $locale . '/products/… sahifasi. Bo\'sh bo\'limlar ko\'rsatilmaydi; hammasi bo\'sh bo\'lsa, bosh sahifadagi karta to\'g\'ridan-to\'g\'ri rasmiylashtirishga olib boradi.')
                                ->collapsible()
                                ->schema([
                                    RichEditor::make("content.{$locale}.about")
                                        ->label('Mahsulot haqida')
                                        ->helperText('Nimalar qoplanadi, sug\'urta summasi, kimlar uchun.')
                                        ->toolbarButtons(['bold', 'italic', 'underline', 'h2', 'h3', 'bulletList', 'orderedList', 'link', 'blockquote', 'undo', 'redo']),
                                    RichEditor::make("content.{$locale}.claim")
                                        ->label('Sug\'urta hodisasi yuz berganda')
                                        ->helperText('Mijoz nima qilishi kerak: qayerga murojaat, qaysi hujjatlar, muddatlar.')
                                        ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link', 'undo', 'redo']),
                                    Repeater::make("content.{$locale}.faq")
                                        ->label('Ko\'p so\'raladigan savollar')
                                        ->schema([
                                            TextInput::make('q')->label('Savol')->required()->maxLength(255),
                                            Textarea::make('a')->label('Javob')->required()->rows(3)->maxLength(2000),
                                        ])
                                        ->itemLabel(fn (array $state): ?string => $state['q'] ?? null)
                                        ->collapsible()
                                        ->reorderable()
                                        ->defaultItems(0)
                                        ->addActionLabel('Savol qo\'shish'),
                                    FileUpload::make("rules_{$locale}")
                                        ->label('Sug\'urta qoidalari (PDF)')
                                        ->disk('public')
                                        ->directory('rules')
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->maxSize(20480)
                                        ->downloadable()
                                        ->openable(),
                                ]),
                        ]))
                        ->values()
                        ->all()),

                Grid::make(1)
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Section::make('Holat')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Sotuvda')
                                    ->helperText('O\'chirilsa, mahsulot saytdan yashiriladi va uning sahifalari yangi ariza qabul qilmaydi.')
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
                                    ->regex('#^[a-z0-9_/-]+$#i')
                                    ->live(onBlur: true)
                                    ->validationMessages(['regex' => 'Faqat lotin harflari, raqamlar, "-", "_" va "/" ishlatiladi.'])
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

                self::pricingSection(),
            ]);
    }

    /**
     * Rate, sum limits, presets and term for products with a configurable flow
     * (ProductSettings::CONTROLLERS). Empty fields keep the built-in value shown under them.
     * Values are normalized and cross-checked by ProductSettings::clean() on save.
     */
    private static function pricingSection(): Section
    {
        $money = fn (string $key, string $label) => TextInput::make("settings.{$key}")
            ->label($label)
            ->numeric()
            ->minValue(0)
            ->suffix('so\'m')
            ->helperText(fn (Get $get): string => self::builtIn($get('route'), $key));

        $rateOnly = fn (Get $get): bool => ProductSettings::hasRate($get('route'));

        return Section::make('Narx va chegaralar')
            ->description('Bo\'sh maydon standart qiymatni ishlatadi. Saqlangach saytda darhol qo\'llanadi, allaqachon yaratilgan buyurtmalar o\'zgarmaydi.')
            ->icon('heroicon-o-calculator')
            ->visible(fn (Get $get): bool => ProductSettings::supports($get('route')))
            ->columnSpanFull()
            ->columns(['md' => 2, 'xl' => 4])
            ->schema([
                TextInput::make('settings.rate')
                    ->label('Stavka')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0.01)
                    ->maxValue(100)
                    ->suffix('%')
                    ->helperText(fn (Get $get): string => 'Mukofot = summa × stavka. ' . self::builtIn($get('route'), 'rate'))
                    ->visible($rateOnly),

                $money('min_premium', 'Minimal mukofot')->visible($rateOnly),

                $money('min', 'Minimal summa'),
                $money('max', 'Maksimal summa'),
                $money('default', 'Standart summa'),
                $money('step', 'Slayder qadami'),

                TagsInput::make('settings.presets')
                    ->label('Tezkor tanlov tugmalari')
                    ->placeholder('Summa (so\'m) va Enter')
                    ->helperText(fn (Get $get): string => 'Ko\'pi bilan 8 ta. ' . self::builtIn($get('route'), 'presets'))
                    ->columnSpan(['md' => 2]),

                TextInput::make('settings.term_months')
                    ->label('Shartnoma muddati')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(60)
                    ->suffix('oy')
                    ->helperText(fn (Get $get): string => self::builtIn($get('route'), 'term_months')),

                Select::make('settings.start_offset')
                    ->label('Eng erta boshlanish')
                    ->options(ProductSettings::START_OFFSETS)
                    ->placeholder('Standart')
                    ->helperText(fn (Get $get): string => self::builtIn($get('route'), 'start_offset')),

                TextInput::make('settings.max_start_days')
                    ->label('Eng kech boshlanish')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(365)
                    ->suffix('kundan keyin')
                    ->helperText('Bugundan necha kun keyingacha. Bo\'sh — cheklovsiz.'),
            ]);
    }

    /** "Standart: 5 000 000 so'm" — the value used when the field is left empty */
    private static function builtIn(?string $route, string $key): string
    {
        if (!ProductSettings::supports($route)) {
            return '';
        }

        return 'Standart: ' . ProductSettings::display($key, ProductSettings::defaults($route)[$key] ?? null);
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

                TextColumn::make('pricing')
                    ->label('Narx')
                    ->state(function (Product $record): ?string {
                        $flow = ProductSettings::effective($record->route, $record->settings);

                        return match (true) {
                            $flow === null         => null,
                            isset($flow['rate'])   => $flow['rateLabel'] . '% · ' . self::shortRange($flow),
                            default                => 'API · ' . self::shortRange($flow),
                        };
                    })
                    ->placeholder('API')
                    ->color('gray')
                    ->size('sm'),

                ToggleColumn::make('is_active')
                    ->label('Sotuvda'),

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

    /**
     * Normalizes the pricing fields before create/save. Invalid combinations (min ≥ max,
     * a step that misses a preset, …) come back as errors under the offending field.
     */
    public static function withCleanSettings(array $data): array
    {
        $data['settings'] = ProductSettings::clean(
            (string) ($data['route'] ?? ''),
            (array) ($data['settings'] ?? []),
            'data.settings.',
        ) ?: null;

        return $data;
    }

    /** "5 mln – 500 mln" */
    private static function shortRange(array $flow): string
    {
        $mln = fn (int $sum): string => $sum >= 1_000_000
            ? str_replace('.', ',', (string) round($sum / 1_000_000, 1)) . ' mln'
            : number_format($sum, 0, '.', ' ');

        return $mln($flow['min']) . ' – ' . $mln($flow['max']);
    }

    public static function getRelations(): array
    {
        return [
            SettingChangesRelationManager::class,
        ];
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
