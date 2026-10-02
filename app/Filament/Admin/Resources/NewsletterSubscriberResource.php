<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NewsletterSubscriberResource\Pages;
use App\Models\NewsletterSubscriber;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Emails from the footer's subscribe form (/{locale}/subscribe) */
class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|\UnitEnum|null $navigationGroup = 'Murojaatlar';

    protected static ?string $navigationLabel = 'Obunachilar';

    protected static ?string $modelLabel = 'Obunachi';

    protected static ?string $pluralModelLabel = 'Obunachilar';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')->label('Email')->searchable()->copyable(),
                TextColumn::make('locale')->label('Til')->badge()->color('gray'),
                TextColumn::make('created_at')->label('Obuna bo\'lgan')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->recordActions([DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()])
            ->defaultSort('id', 'desc')
            ->emptyStateHeading('Obunachilar yo\'q')
            ->emptyStateDescription('Saytning pastki qismidagi obuna formasi orqali qoldirilgan emaillar shu yerda.')
            ->emptyStateIcon('heroicon-o-envelope');
    }

    /** CSV (opens in Excel: UTF-8 BOM, ";" separator) */
    public static function csv(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Email', 'Til', 'Sana'], ';');

            NewsletterSubscriber::orderBy('id')->chunk(500, function ($rows) use ($out): void {
                foreach ($rows as $row) {
                    fputcsv($out, array_map(self::cell(...), [$row->email, $row->locale, $row->created_at?->format('d.m.Y H:i')]), ';');
                }
            });

            fclose($out);
        }, 'obunachilar-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Excel runs a cell starting with = + - @ as a formula: prefix it with ' */
    private static function cell(?string $value): ?string
    {
        return $value !== null && preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterSubscribers::route('/'),
        ];
    }
}
