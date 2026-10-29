<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NewsResource\Pages;
use App\Models\News;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/** Press centre news shown on /{locale}/news */
class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Yangiliklar';

    protected static ?string $modelLabel = 'Yangilik';

    protected static ?string $pluralModelLabel = 'Yangiliklar';

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
                                ->maxLength(500),
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
                            ->prefix('/news/')
                            ->required()
                            ->regex('/^[a-z0-9-]+$/')
                            ->unique(ignoreRecord: true)
                            ->maxLength(120)
                            ->helperText('Lotin kichik harflar, raqam va chiziqcha.'),
                        DateTimePicker::make('published_at')
                            ->label('E\'lon qilingan vaqt')
                            ->native(false)
                            ->displayFormat('d.m.Y H:i')
                            ->default(now())
                            ->required()
                            ->helperText('Shu vaqtdan saytda ko\'rinadi.'),
                        FileUpload::make('image')
                            ->label('Rasm')
                            ->image()
                            ->disk('public')
                            ->directory('news')
                            ->maxSize(5120)
                            ->helperText('JPG yoki PNG, 5 MB gacha.'),
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
                    ->description(fn (News $record): string => '/news/' . $record->slug)
                    ->wrap()
                    ->searchable(['title_uz', 'title_ru', 'title_en', 'slug']),
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->square(),
                TextColumn::make('published_at')
                    ->label('E\'lon qilingan')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label('Saytda'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Saytda')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (News $record): string => route('news.show', ['locale' => 'uz', 'slug' => $record->slug]))
                    ->openUrlInNewTab()
                    ->visible(fn (News $record): bool => $record->is_published && $record->published_at?->isPast()),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('published_at', 'desc')
            ->emptyStateHeading('Yangiliklar yo\'q')
            ->paginated([25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit'   => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
