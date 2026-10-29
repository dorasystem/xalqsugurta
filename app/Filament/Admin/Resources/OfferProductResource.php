<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OfferProductResource\Pages;
use App\Models\OfferProduct;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/** Products sold on application (/{locale}/insurance/{slug}); their requests are in Qayta qo'ng'iroqlar */
class OfferProductResource extends Resource
{
    protected static ?string $model = OfferProduct::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Arizali mahsulotlar';

    protected static ?string $modelLabel = 'Mahsulot';

    protected static ?string $pluralModelLabel = 'Arizali mahsulotlar';

    private const LOCALES = ['uz' => 'O\'zbekcha', 'ru' => 'Русский', 'en' => 'English'];

    public static function canCreate(): bool
    {
        return false;   // the form fields are defined per product in OfferProduct::FORMS
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Tabs::make('Tillar')
                    ->columnSpan(['lg' => 2])
                    ->tabs(collect(self::LOCALES)->map(fn (string $label, string $l) => Tab::make($label)->schema([
                        TextInput::make('title_' . $l)->label('Nomi')->required($l === 'uz')->maxLength(255),
                        TextInput::make('kicker_' . $l)->label('Ustki yozuv')->maxLength(255)->helperText('Nomi ustidagi kichik yozuv, masalan "Baxtsiz hodisalardan sug\'urta".'),
                        Textarea::make('lead_' . $l)->label('Qisqa izoh')->rows(2),
                        Textarea::make('highlights_' . $l)->label('Belgilar')->rows(3)->helperText('Har qatorda bittasi, 3 tagacha.'),
                        Textarea::make('covers_' . $l)->label('Nimani qoplaydi')->rows(5)->helperText('Har qatorda bittasi.'),
                        Textarea::make('terms_' . $l)->label('Asosiy shartlar')->rows(5)->helperText('Har qatorda bittasi: "belgi | matn", masalan "100% | Vafot etganda".'),
                        Textarea::make('note_' . $l)->label('Izoh (ixtiyoriy)')->rows(2),
                    ]))->values()->all()),

                Section::make('Sozlamalar')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        TextInput::make('slug')->label('Manzil')->prefix('/insurance/')->disabled(),
                        Select::make('audience')->label('Kimlar uchun')->options(['private' => 'Jismoniy shaxslar', 'business' => 'Yuridik shaxslar'])->required(),
                        TextInput::make('sort_order')->label('Tartib')->numeric()->default(0),
                        Toggle::make('is_active')->label('Saytda ko\'rsatilsin')->helperText('O\'chirilsa, menyu eski saytga olib boradi.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title_uz')->label('Nomi')->description(fn (OfferProduct $r) => '/insurance/' . $r->slug),
                TextColumn::make('audience')->label('Kimlar uchun')->badge()->color('gray')
                    ->formatStateUsing(fn (string $state) => $state === 'business' ? 'Yuridik' : 'Jismoniy'),
                ToggleColumn::make('is_active')->label('Saytda'),
            ])
            ->recordActions([
                Action::make('open')->label('Saytda')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (OfferProduct $r) => route('offers.show', ['locale' => 'uz', 'slug' => $r->slug]))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfferProducts::route('/'),
            'edit'  => Pages\EditOfferProduct::route('/{record}/edit'),
        ];
    }
}
