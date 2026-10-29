<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BranchResource\Pages;
use App\Models\Branch;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/** Branches shown on /{locale}/branches */
class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Filiallar';

    protected static ?string $modelLabel = 'Filial';

    protected static ?string $pluralModelLabel = 'Filiallar';

    protected static ?string $recordTitleAttribute = 'name_uz';

    private const LOCALES = ['uz' => 'O\'zbekcha', 'ru' => 'Русский', 'en' => 'English'];

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Tabs::make('Tillar')
                    ->columnSpan(['lg' => 2])
                    ->tabs(collect(self::LOCALES)->map(fn (string $label, string $locale) => Tab::make($label)
                        ->schema([
                            TextInput::make('name_' . $locale)
                                ->label('Nomi')
                                ->required($locale === 'ru')
                                ->maxLength(255)
                                ->helperText($locale === 'ru' ? null : 'Bo\'sh qolsa, ruscha nomi ko\'rsatiladi.'),
                            TextInput::make('address_' . $locale)
                                ->label('Manzil')
                                ->maxLength(500)
                                ->helperText($locale === 'ru' ? null : 'Bo\'sh qolsa, ruscha manzil ko\'rsatiladi.'),
                        ]))->values()->all()),

                Section::make('Aloqa')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        TextInput::make('manager')
                            ->label('Rahbar')
                            ->maxLength(255),
                        TagsInput::make('phones')
                            ->label('Telefonlar')
                            ->placeholder('(+998 71) 202-19-66')
                            ->helperText('Har bir raqamdan keyin Enter.'),
                        Grid::make(2)->schema([
                            TextInput::make('lat')->label('Kenglik')->numeric()->minValue(37)->maxValue(46),
                            TextInput::make('lng')->label('Uzunlik')->numeric()->minValue(55)->maxValue(74),
                        ]),
                        TextInput::make('sort_order')
                            ->label('Tartib')
                            ->numeric()
                            ->default(0)
                            ->helperText('Kichigi ro\'yxatda yuqorida.'),
                        Toggle::make('is_active')
                            ->label('Saytda ko\'rsatilsin')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_uz')
                    ->label('Nomi')
                    ->state(fn (Branch $record): string => $record->name('uz'))
                    ->description(fn (Branch $record): ?string => $record->address('ru'))
                    ->wrap()
                    ->searchable(['name_uz', 'name_ru', 'name_en', 'address_ru']),
                TextColumn::make('manager')->label('Rahbar')->placeholder('—'),
                TextColumn::make('phones')->label('Telefonlar')->badge()->color('gray'),
                TextColumn::make('sort_order')->label('Tartib')->sortable(),
                ToggleColumn::make('is_active')->label('Saytda'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated([50]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit'   => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
