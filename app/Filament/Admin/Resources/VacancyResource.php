<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VacancyResource\Pages;
use App\Models\Vacancy;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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
use Filament\Tables\Table;

/** Vacancies shown on /{locale}/career; CVs sent for them are in JobApplicationResource */
class VacancyResource extends Resource
{
    protected static ?string $model = Vacancy::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|\UnitEnum|null $navigationGroup = 'Karyera';

    protected static ?string $navigationLabel = 'Vakansiyalar';

    protected static ?string $modelLabel = 'Vakansiya';

    protected static ?string $pluralModelLabel = 'Vakansiyalar';

    protected static ?string $recordTitleAttribute = 'title_uz';

    private const LOCALES = ['uz' => 'O\'zbekcha', 'ru' => 'Русский', 'en' => 'English'];

    /** Vacancy::EMPLOYMENT labels */
    public const EMPLOYMENT = ['full' => 'To\'liq stavka', 'part' => 'Yarim stavka', 'intern' => 'Amaliyot'];

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
                            ->prefix('/career/')
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
                            ->label('Arizalar qabul qilinadigan oxirgi kun')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->afterOrEqual('published_at')
                            ->helperText('Shu kundan keyin saytdan yo\'qoladi. Bo\'sh — muddatsiz.'),
                        TextInput::make('location')
                            ->label('Hudud / filial')
                            ->maxLength(255),
                        Select::make('employment')
                            ->label('Bandlik turi')
                            ->options(self::EMPLOYMENT),
                        TextInput::make('sort_order')
                            ->label('Tartib')
                            ->numeric()
                            ->default(0)
                            ->helperText('Kichigi ro\'yxatda yuqorida.'),
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
                    ->description(fn (Vacancy $record): string => '/career/' . $record->slug)
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
                TextColumn::make('location')
                    ->label('Hudud')
                    ->placeholder('—'),
                TextColumn::make('applications_count')
                    ->label('Arizalar')
                    ->counts('applications')
                    ->badge()
                    ->color('gray'),
                ToggleColumn::make('is_published')
                    ->label('Saytda'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Saytda')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Vacancy $record): string => route('career.show', ['locale' => 'uz', 'slug' => $record->slug]))
                    ->openUrlInNewTab()
                    ->visible(fn (Vacancy $record): bool => $record->is_published && Vacancy::open()->whereKey($record->id)->exists()),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('published_at', 'desc')
            ->emptyStateHeading('Vakansiyalar yo\'q')
            ->paginated([25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVacancies::route('/'),
            'create' => Pages\CreateVacancy::route('/create'),
            'edit'   => Pages\EditVacancy::route('/{record}/edit'),
        ];
    }
}
