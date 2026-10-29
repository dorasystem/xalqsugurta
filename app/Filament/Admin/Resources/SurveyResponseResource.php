<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SurveyResponseResource\Pages;
use App\Models\SurveyResponse;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Answers to the site questionnaire (/{locale}/questionnaire); the list page shows the totals */
class SurveyResponseResource extends Resource
{
    protected static ?string $model = SurveyResponse::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Murojaatlar';

    protected static ?string $navigationLabel = 'Anketa javoblari';

    protected static ?string $modelLabel = 'Javob';

    protected static ?string $pluralModelLabel = 'Anketa javoblari';

    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        return false;
    }

    /** Uzbek text of an option (admin panel is Uzbek-only) */
    public static function option(string $group, ?string $key): string
    {
        return $key === null ? '—' : (string) __('messages.survey.' . $group . '.' . $key, [], 'uz');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Sana')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('reason')->label('Nega tanlaydi')->wrap()
                    ->formatStateUsing(fn (string $state, SurveyResponse $record) => self::option('reasons', $state) . ($record->reason_other ? ': ' . $record->reason_other : '')),
                TextColumn::make('products')->label('Qiziqtirgan mahsulotlar')->badge()->color('gray')
                    ->formatStateUsing(fn (string $state) => self::option('products', $state)),
                TextColumn::make('product_other')->label('Boshqa mahsulot')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ranking')->label('Eng muhim mezonlar')->wrap()
                    ->state(fn (SurveyResponse $record) => collect($record->ranking ?? [])->take(3)->map(fn (string $k, int $i) => ($i + 1) . '. ' . self::option('criteria', $k))->join("\n") ?: '—')
                    ->extraAttributes(['style' => 'white-space: pre-line']),
                TextColumn::make('locale')->label('Til')->badge()->color('gray'),
            ])
            ->filters([
                SelectFilter::make('reason')->label('Nega tanlaydi')
                    ->options(collect(SurveyResponse::REASONS)->mapWithKeys(fn (string $k) => [$k => self::option('reasons', $k)])->all()),
            ])
            ->recordActions([DeleteAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveyResponses::route('/'),
        ];
    }
}
