<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApiLogResource\Pages;
use App\Models\ApiLog;
use App\Models\Product;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Read-only journal of requests to the insurer's API (written by App\Services\ApiLogger) */
class ApiLogResource extends Resource
{
    protected static ?string $model = ApiLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-signal';

    protected static string|\UnitEnum|null $navigationGroup = 'Nazorat';

    protected static ?string $navigationLabel = 'API jurnali';

    protected static ?string $modelLabel = 'API so\'rovi';

    protected static ?string $pluralModelLabel = 'API jurnali';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    // ─── Navigation badge: today's failures ───────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $count = ApiLog::failed()->whereDate('created_at', today())->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Bugungi xatolar';
    }

    // ─── Shared columns (list page + order card) ──────────────────────────────

    public static function columns(bool $withOrder = true): array
    {
        return array_values(array_filter([
            TextColumn::make('created_at')
                ->label('Vaqt')
                ->dateTime('d.m H:i:s')
                ->sortable(),

            TextColumn::make('endpoint')
                ->label('So\'rov')
                ->formatStateUsing(fn (ApiLog $record): string => $record->method . ' ' . $record->endpoint)
                ->fontFamily('mono')
                ->size('sm')
                ->searchable(),

            TextColumn::make('product')
                ->label('Mahsulot')
                ->badge()
                ->color('gray')
                ->placeholder('—'),

            $withOrder ? TextColumn::make('order_id')
                ->label('Buyurtma')
                ->formatStateUsing(fn (?int $state): string => '№' . $state)
                ->url(fn (ApiLog $record): ?string => $record->order_id ? OrderResource::getUrl('view', ['record' => $record->order_id]) : null)
                ->color('primary')
                ->placeholder('—')
                ->searchable() : null,

            TextColumn::make('outcome')
                ->label('Javob')
                ->state(fn (ApiLog $record): string => $record->outcome())
                ->badge()
                ->color(fn (ApiLog $record): string => $record->success ? 'success' : 'danger'),

            TextColumn::make('duration_ms')
                ->label('ms')
                ->numeric(thousandsSeparator: ' ')
                ->alignEnd()
                ->placeholder('—'),
        ]));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::columns())
            ->filters([
                TernaryFilter::make('success')
                    ->label('Natija')
                    ->trueLabel('Muvaffaqiyatli')
                    ->falseLabel('Xatolar'),

                SelectFilter::make('product')
                    ->label('Mahsulot')
                    ->options(collect(Product::CATEGORIES)->keys()->mapWithKeys(fn (string $k) => [$k => $k])->all()),

                SelectFilter::make('endpoint')
                    ->label('Endpoint')
                    ->options(fn (): array => ApiLog::query()
                        ->distinct()
                        ->orderBy('endpoint')
                        ->pluck('endpoint', 'endpoint')
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make()->label('Ochish'),
            ])
            ->recordClasses(fn (ApiLog $record): ?string => $record->success ? null : 'xs-row-failed')
            ->defaultSort('id', 'desc')
            ->searchPlaceholder('Endpoint yoki buyurtma №')
            ->emptyStateHeading('So\'rovlar yo\'q')
            ->emptyStateDescription('Sug\'urta API\'siga yuborilgan so\'rovlar shu yerda ' . ApiLog::KEEP_DAYS . ' kun saqlanadi.')
            ->emptyStateIcon('heroicon-o-signal')
            ->paginated([25, 50, 100]);
    }

    // ─── View page ────────────────────────────────────────────────────────────

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('So\'rov')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        TextEntry::make('outcome')
                            ->label('Natija')
                            ->state(fn (ApiLog $record): string => $record->outcome())
                            ->badge()
                            ->color(fn (ApiLog $record): string => $record->success ? 'success' : 'danger'),
                        TextEntry::make('url')
                            ->label('URL')
                            ->formatStateUsing(fn (ApiLog $record): string => $record->method . ' ' . $record->url)
                            ->fontFamily('mono')
                            ->copyable()
                            ->copyableState(fn (ApiLog $record): string => $record->url),
                        TextEntry::make('order_id')
                            ->label('Buyurtma')
                            ->formatStateUsing(fn (?int $state): string => '№' . $state)
                            ->url(fn (ApiLog $record): ?string => $record->order_id ? OrderResource::getUrl('view', ['record' => $record->order_id]) : null)
                            ->placeholder('Bog\'lanmagan'),
                        TextEntry::make('product')
                            ->label('Mahsulot')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label('Vaqt')
                            ->dateTime('d.m.Y H:i:s'),
                        TextEntry::make('duration_ms')
                            ->label('Davomiyligi')
                            ->formatStateUsing(fn (?int $state): string => number_format((int) $state, 0, '.', ' ') . ' ms')
                            ->placeholder('—'),
                        TextEntry::make('error')
                            ->label('Xato')
                            ->color('danger')
                            ->visible(fn (ApiLog $record): bool => filled($record->error)),
                    ]),

                Section::make('Body')
                    ->description('Postman\'da tekshirish uchun nusxa oling. Login va parol saqlanmaydi.')
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        TextEntry::make('request')
                            ->label('So\'rov (body)')
                            ->formatStateUsing(fn ($state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '—')
                            ->copyable()
                            ->copyableState(fn (ApiLog $record): string => json_encode($record->request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '')
                            ->placeholder('Bo\'sh')
                            ->extraAttributes(['class' => 'xs-json']),
                        TextEntry::make('response')
                            ->label('Javob')
                            ->state(fn (ApiLog $record): ?string => $record->prettyResponse())
                            ->copyable()
                            ->placeholder('Javob kelmadi')
                            ->extraAttributes(['class' => 'xs-json']),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiLogs::route('/'),
            'view'  => Pages\ViewApiLog::route('/{record}'),
        ];
    }
}
