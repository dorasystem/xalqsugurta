<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InfoPageResource\Pages;
use App\Models\InfoPage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Company / disclosure pages (/{locale}/info/{key}), imported from the old site by site:import-old-pages */
class InfoPageResource extends Resource
{
    protected static ?string $model = InfoPage::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Kompaniya sahifalari';

    protected static ?string $modelLabel = 'Sahifa';

    protected static ?string $pluralModelLabel = 'Kompaniya sahifalari';

    protected static ?string $recordTitleAttribute = 'title_uz';

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
                            TextInput::make('title_' . $locale)
                                ->label('Sarlavha')
                                ->required($locale === 'ru')
                                ->maxLength(255),
                            RichEditor::make('body_' . $locale)
                                ->label('Matn')
                                ->helperText($locale === 'ru' ? null : 'Bo\'sh qolsa, ruscha matn ko\'rsatiladi.')
                                ->toolbarButtons(['bold', 'italic', 'underline', 'h2', 'h3', 'bulletList', 'orderedList', 'link', 'blockquote', 'table', 'undo', 'redo']),
                        ]))->values()->all()),

                Section::make('Sozlamalar')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        TextInput::make('key')
                            ->label('Manzil (URL)')
                            ->prefix('/info/')
                            ->required()
                            ->regex('/^[a-z0-9-]+$/')
                            ->unique(ignoreRecord: true)
                            ->helperText('Lotin kichik harflar, raqam va chiziqcha.'),
                        Select::make('section')
                            ->label('Bo\'lim')
                            ->options(InfoPage::SECTIONS)
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('Tartib')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_published')
                            ->label('Saytda ko\'rsatilsin')
                            ->helperText('O\'chiq bo\'lsa, menyudagi havola eski saytga olib boradi.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Sarlavha')
                    ->state(fn (InfoPage $record): ?string => $record->title('uz'))
                    ->description(fn (InfoPage $record): string => '/info/' . $record->key)
                    ->searchable(['title_uz', 'title_ru', 'key']),
                TextColumn::make('section')
                    ->label('Bo\'lim')
                    ->formatStateUsing(fn (string $state): string => InfoPage::SECTIONS[$state] ?? $state)
                    ->badge()
                    ->color('gray'),
                TextColumn::make('languages')
                    ->label('Tillar')
                    ->state(fn (InfoPage $record): string => collect(array_keys(self::LOCALES))
                        ->filter(fn (string $l): bool => filled($record->{'body_' . $l}))
                        ->implode(' · ') ?: '—'),
                ToggleColumn::make('is_published')
                    ->label('Saytda'),
                TextColumn::make('imported_at')
                    ->label('Eski saytdan olingan')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('O\'zgartirilgan')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('section')->label('Bo\'lim')->options(InfoPage::SECTIONS),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Saytda')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (InfoPage $record): string => route('info.show', ['locale' => 'uz', 'key' => $record->key]))
                    ->openUrlInNewTab()
                    ->visible(fn (InfoPage $record): bool => $record->is_published),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort(fn ($query) => $query->orderBy('section')->orderBy('sort_order'))
            ->emptyStateHeading('Sahifalar yo\'q')
            ->emptyStateDescription('Eski saytdagi sahifalarni olish: php artisan site:import-old-pages')
            ->paginated([25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInfoPages::route('/'),
            'create' => Pages\CreateInfoPage::route('/create'),
            'edit'   => Pages\EditInfoPage::route('/{record}/edit'),
        ];
    }
}
