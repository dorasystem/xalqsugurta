<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TenderResource\Pages;
use App\Models\Tender;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
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

/** Tenders shown on /{locale}/tenders */
class TenderResource extends Resource
{
    protected static ?string $model = Tender::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Tenderlar';

    protected static ?string $modelLabel = 'Tender';

    protected static ?string $pluralModelLabel = 'Tenderlar';

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
                                ->required($locale === 'uz')
                                ->maxLength(255),
                            RichEditor::make('body_' . $locale)
                                ->label('Matn')
                                ->required($locale === 'uz')
                                ->helperText($locale === 'uz' ? null : 'Bo\'sh qolsa, o\'zbekcha matn ko\'rsatiladi.')
                                ->toolbarButtons(['bold', 'italic', 'underline', 'h2', 'h3', 'bulletList', 'orderedList', 'link', 'undo', 'redo']),
                        ]))->values()->all()),

                Section::make('Sozlamalar')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        TextInput::make('slug')
                            ->label('Manzil (URL)')
                            ->prefix('/tenders/')
                            ->required()
                            ->regex('/^[a-z0-9-]+$/')
                            ->unique(ignoreRecord: true)
                            ->helperText('Lotin kichik harflar, raqam va chiziqcha.'),
                        DatePicker::make('published_at')
                            ->label('E\'lon qilingan sana')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->default(today())
                            ->required()
                            ->helperText('Shu kundan saytda ko\'rinadi.'),
                        DatePicker::make('deadline_at')
                            ->label('Takliflar qabul qilinadigan oxirgi kun')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->afterOrEqual('published_at')
                            ->helperText('Shu kun o\'tgach "Qabul yakunlangan" deb ko\'rsatiladi.'),
                        TextInput::make('email')
                            ->label('Takliflar uchun e-pochta')
                            ->email()
                            ->default('info@xalqsugurta.uz'),
                        Toggle::make('is_published')
                            ->label('Saytda ko\'rsatilsin')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title_uz')
                    ->label('Sarlavha')
                    ->description(fn (Tender $record): string => '/tenders/' . $record->slug)
                    ->wrap()
                    ->searchable(['title_uz', 'title_ru', 'title_en', 'slug']),
                TextColumn::make('published_at')
                    ->label('E\'lon qilingan')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('deadline_at')
                    ->label('Muddat')
                    ->date('d.m.Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('state')
                    ->label('Holati')
                    ->state(fn (Tender $record): string => $record->isOpen() ? 'Qabul qilinmoqda' : 'Yakunlangan')
                    ->badge()
                    ->color(fn (Tender $record): string => $record->isOpen() ? 'success' : 'gray'),
                ToggleColumn::make('is_published')
                    ->label('Saytda'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Saytda')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Tender $record): string => route('tenders.show', ['locale' => 'uz', 'slug' => $record->slug]))
                    ->openUrlInNewTab()
                    ->visible(fn (Tender $record): bool => $record->is_published),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('published_at', 'desc')
            ->emptyStateHeading('Tenderlar yo\'q')
            ->paginated([25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTenders::route('/'),
            'create' => Pages\CreateTender::route('/create'),
            'edit'   => Pages\EditTender::route('/{record}/edit'),
        ];
    }
}
