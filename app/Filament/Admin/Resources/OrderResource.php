<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Filament\Admin\Resources\OrderResource\RelationManagers\ApiLogsRelationManager;
use App\Models\ApiLog;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Savdo';

    protected static ?string $navigationLabel = 'Buyurtmalar';

    protected static ?string $modelLabel = 'Buyurtma';

    protected static ?string $pluralModelLabel = 'Buyurtmalar';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';

    // ─── Navigation badge: today's orders ─────────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $count = Order::query()->whereDate('created_at', today())->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Bugungi buyurtmalar';
    }

    // ─── Global search (top bar) ──────────────────────────────────────────────

    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'phone', 'insurance_id', 'insuranceProductName', 'insurances_data'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        /** @var Order $record */
        return '№' . $record->id . ' · ' . ($record->client_name ?? formatPhone($record->phone));
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        /** @var Order $record */
        return [
            'Mahsulot' => $record->insuranceProductName ?: $record->product_name,
            'Summa'    => formatMoney($record->amount),
            'Holat'    => Order::statusLabel($record->status),
        ];
    }

    // ─── Shared columns (list page + dashboard widget) ────────────────────────

    public static function columns(): array
    {
        return [
            TextColumn::make('id')
                ->label('№')
                ->sortable()
                ->searchable()
                ->weight('bold')
                ->color('primary'),

            TextColumn::make('insuranceProductName')
                ->label('Mahsulot')
                ->state(fn (Order $record): string => $record->insuranceProductName ?: $record->product_name ?: '—')
                ->description(fn (Order $record): ?string => $record->insurance_id ? 'ID: ' . $record->insurance_id : null)
                ->searchable(['insuranceProductName', 'product_name', 'insurance_id']),

            TextColumn::make('client_name')
                ->label('Mijoz')
                ->state(fn (Order $record): ?string => $record->client_name)
                ->description(fn (Order $record): ?string => $record->phone ? formatPhone($record->phone) : null)
                ->placeholder('—')
                ->searchable(query: function (Builder $query, string $search): Builder {
                    $digits = preg_replace('/\D/', '', $search);

                    return $query
                        ->where('insurances_data', 'like', '%' . $search . '%')
                        ->when($digits !== '', fn (Builder $q) => $q->orWhere('phone', 'like', '%' . $digits . '%'));
                }),

            TextColumn::make('amount')
                ->label('Summa')
                ->formatStateUsing(fn ($state): string => formatMoney($state))
                ->alignEnd()
                ->sortable(),

            TextColumn::make('status')
                ->label('Holat')
                ->badge()
                ->color(fn (?string $state): string => Order::statusColor($state))
                ->formatStateUsing(fn (?string $state): string => Order::statusLabel($state)),

            TextColumn::make('payment_type')
                ->label('To\'lov')
                ->badge()
                ->color('gray')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    Order::PAYMENT_PAYME => 'Payme',
                    Order::PAYMENT_CLICK => 'Click',
                    default              => (string) $state,
                })
                ->placeholder('—')
                ->toggleable(),

            TextColumn::make('contractStartDate')
                ->label('Polis muddati')
                ->formatStateUsing(fn (Order $record): string => $record->contractStartDate
                    ? $record->contractStartDate->format('d.m.Y') . ' – ' . ($record->contractEndDate?->format('d.m.Y') ?? '…')
                    : '—')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('created_at')
                ->label('Yaratilgan')
                ->dateTime('d.m.Y H:i')
                ->description(fn (Order $record): string => $record->created_at?->locale('uz_Latn')->diffForHumans() ?? '')
                ->sortable(),
        ];
    }

    // ─── List ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        $columns = static::columns();

        // Total of the filtered rows under the amount column
        foreach ($columns as $column) {
            if ($column->getName() === 'amount') {
                $column->summarize(Sum::make()->label('Jami')->formatStateUsing(fn ($state): string => formatMoney($state)));
            }
        }

        return $table
            ->columns($columns)
            ->filters([
                SelectFilter::make('status')
                    ->label('Holat')
                    ->options(Order::STATUS_LABELS)
                    ->multiple(),

                SelectFilter::make('insuranceProductName')
                    ->label('Mahsulot')
                    ->options(fn (): array => Order::query()
                        ->whereNotNull('insuranceProductName')
                        ->distinct()
                        ->orderBy('insuranceProductName')
                        ->pluck('insuranceProductName', 'insuranceProductName')
                        ->all()),

                SelectFilter::make('payment_type')
                    ->label('To\'lov turi')
                    ->options([
                        Order::PAYMENT_PAYME => 'Payme',
                        Order::PAYMENT_CLICK => 'Click',
                    ]),

                Filter::make('created_at')
                    ->label('Sana')
                    ->schema([
                        DatePicker::make('from')->label('Dan')->native(false)->displayFormat('d.m.Y'),
                        DatePicker::make('until')->label('Gacha')->native(false)->displayFormat('d.m.Y'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date)))
                    ->indicateUsing(function (array $data): ?string {
                        $from  = $data['from'] ?? null;
                        $until = $data['until'] ?? null;

                        if (!$from && !$until) {
                            return null;
                        }

                        return 'Sana: ' . ($from ? Carbon::parse($from)->format('d.m.Y') : '…')
                            . ' – ' . ($until ? Carbon::parse($until)->format('d.m.Y') : '…');
                    }),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                ViewAction::make()->label('Ochish'),
                Action::make('policy')
                    ->label('Polisni yuklab olish')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->iconButton()
                    ->tooltip('Polisni yuklab olish')
                    ->color('success')
                    ->url(fn (Order $record): ?string => $record->insurances_response_data['download_url'] ?? null, shouldOpenInNewTab: true)
                    ->visible(fn (Order $record): bool => !empty($record->insurances_response_data['download_url'])),
            ])
            ->defaultSort('id', 'desc')
            ->searchPlaceholder('№, mijoz, telefon yoki polis ID')
            ->emptyStateHeading('Buyurtmalar topilmadi')
            ->emptyStateDescription('Filtrlarni o\'zgartirib ko\'ring.')
            ->emptyStateIcon('heroicon-o-document-magnifying-glass')
            ->paginated([25, 50, 100])
            ->striped();
    }

    // ─── View page ────────────────────────────────────────────────────────────

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Grid::make(1)
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Section::make('Mijoz')
                            ->icon('heroicon-o-user')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('client_name')
                                    ->label('F.I.O. / Tashkilot')
                                    ->state(fn (Order $record): ?string => $record->client_name)
                                    ->placeholder('—')
                                    ->weight('bold')
                                    ->columnSpanFull(),
                                TextEntry::make('phone')
                                    ->label('Telefon')
                                    ->formatStateUsing(fn (?string $state): string => formatPhone($state))
                                    ->copyable()
                                    ->copyableState(fn (Order $record): ?string => $record->phone)
                                    ->placeholder('—'),
                                TextEntry::make('applicant_document')
                                    ->label('Pasport / STIR')
                                    ->state(fn (Order $record): ?string => static::applicantDocument($record))
                                    ->placeholder('—')
                                    ->copyable(),
                                TextEntry::make('applicant_pinfl')
                                    ->label('JSHSHIR')
                                    ->state(fn (Order $record): ?string => $record->applicant['pinfl'] ?? null)
                                    ->placeholder('—')
                                    ->copyable(),
                                TextEntry::make('applicant_birth')
                                    ->label('Tug\'ilgan sana')
                                    ->state(fn (Order $record): ?string => !empty($record->applicant['birth_date'])
                                        ? Carbon::parse($record->applicant['birth_date'])->format('d.m.Y')
                                        : null)
                                    ->placeholder('—'),
                                TextEntry::make('applicant_address')
                                    ->label('Manzil')
                                    ->state(fn (Order $record): ?string => $record->applicant['address'] ?? null)
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Polis')
                            ->icon('heroicon-o-shield-check')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('insuranceProductName')
                                    ->label('Mahsulot')
                                    ->state(fn (Order $record): string => $record->insuranceProductName ?: $record->product_name ?: '—'),
                                TextEntry::make('insurance_id')
                                    ->label('Sug\'urta ID')
                                    ->copyable()
                                    ->placeholder('—'),
                                TextEntry::make('contractStartDate')
                                    ->label('Boshlanish')
                                    ->date('d.m.Y')
                                    ->placeholder('—'),
                                TextEntry::make('contractEndDate')
                                    ->label('Tugash')
                                    ->date('d.m.Y')
                                    ->placeholder('—'),
                                TextEntry::make('polis_number')
                                    ->label('Polis seriya va raqami')
                                    ->state(fn (Order $record): ?string => isset($record->insurances_response_data['polis_sery'])
                                        ? $record->insurances_response_data['polis_sery'] . ' ' . ($record->insurances_response_data['polis_number'] ?? '')
                                        : null)
                                    ->placeholder('To\'lovdan keyin beriladi'),
                                TextEntry::make('polis_check')
                                    ->label('Tekshirish havolasi')
                                    ->state(fn (Order $record): ?string => $record->insurances_response_data['polis_check'] ?? null)
                                    ->url(fn (Order $record): ?string => $record->insurances_response_data['polis_check'] ?? null, shouldOpenInNewTab: true)
                                    ->limit(40)
                                    ->placeholder('—'),
                            ]),

                        Section::make('Texnik ma\'lumotlar')
                            ->icon('heroicon-o-code-bracket')
                            ->description('Ariza va sug\'urta API javobi (JSON)')
                            ->collapsed()
                            ->schema([
                                TextEntry::make('insurances_data')
                                    ->label('Ariza ma\'lumotlari')
                                    ->formatStateUsing(fn ($state): string => static::prettyJson($state))
                                    ->extraAttributes(['class' => 'xs-json']),
                                TextEntry::make('insurances_response_data')
                                    ->label('API javobi')
                                    ->formatStateUsing(fn ($state): string => static::prettyJson($state))
                                    ->extraAttributes(['class' => 'xs-json']),
                            ]),
                    ]),

                Grid::make(1)
                    ->columnSpan(['lg' => 1])
                    ->schema([
                Section::make('Jarayon')
                    ->icon('heroicon-o-queue-list')
                    ->schema([
                        ViewEntry::make('timeline')
                            ->hiddenLabel()
                            ->view('filament.admin.order-timeline'),
                    ]),

                Section::make('To\'lov')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextEntry::make('amount')
                            ->label('Summa')
                            ->formatStateUsing(fn ($state): string => formatMoney($state))
                            ->size('lg')
                            ->weight('bold')
                            ->color('primary'),
                        TextEntry::make('status')
                            ->label('Holat')
                            ->badge()
                            ->color(fn (?string $state): string => Order::statusColor($state))
                            ->formatStateUsing(fn (?string $state): string => Order::statusLabel($state)),
                        TextEntry::make('payment_type')
                            ->label('To\'lov turi')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                Order::PAYMENT_PAYME => 'Payme',
                                Order::PAYMENT_CLICK => 'Click',
                                default              => (string) $state,
                            })
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label('Yaratilgan')
                            ->dateTime('d.m.Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Oxirgi o\'zgarish')
                            ->dateTime('d.m.Y H:i'),
                    ]),
                    ]),
            ]);
    }

    /**
     * Steps of the order for the "Jarayon" block: application → contract → payment → policy.
     * Each step: [state (ok|fail|wait|todo), title, detail, time].
     */
    public static function timeline(Order $order): array
    {
        $logs      = $order->apiLogs()->get(['id', 'endpoint', 'status', 'result', 'success', 'created_at', 'duration_ms']);
        $perform   = $logs->filter(fn (ApiLog $l) => str_contains($l->endpoint, 'PerformTransaction'));
        $contract  = $logs->reject(fn (ApiLog $l) => str_contains($l->endpoint, 'PerformTransaction'))->first();
        $paid      = $order->status === Order::STATUS_PAID;
        $hasPolicy = !empty($order->insurances_response_data['download_url']);

        $steps = [
            ['ok', 'Ariza to\'ldirildi', $order->insuranceProductName ?: $order->product_name, $order->created_at],
            $contract
                ? [$contract->success ? 'ok' : 'fail', 'Shartnoma yaratildi', $contract->endpoint . ' · ' . $contract->outcome(), $contract->created_at]
                : [$order->insurance_id ? 'ok' : 'todo', 'Shartnoma yaratildi', $order->insurance_id ? 'ID ' . $order->insurance_id : null, null],
            match ($order->status) {
                Order::STATUS_PAID                        => ['ok', 'To\'lov qabul qilindi', static::paymentLabel($order->payment_type) . ' · ' . formatMoney($order->amount), null],
                Order::STATUS_CANCELLED, Order::STATUS_FAILED => ['fail', 'To\'lov', Order::statusLabel($order->status), null],
                default                                   => ['wait', 'To\'lov kutilmoqda', formatMoney($order->amount), null],
            },
        ];

        if (in_array($order->product_key, Order::POLICY_AFTER_PAYMENT, true)) {
            $last    = $perform->first();
            $steps[] = match (true) {
                $hasPolicy       => ['ok', 'Polis chiqarildi', trim(($order->insurances_response_data['polis_sery'] ?? '') . ' ' . ($order->insurances_response_data['polis_number'] ?? '')) ?: null, $last?->created_at],
                $last !== null   => ['fail', 'Polis chiqmadi', 'PerformTransaction · ' . $last->outcome() . ($perform->count() > 1 ? ' · ' . $perform->count() . ' urinish' : ''), $last->created_at],
                $paid            => ['fail', 'Polis chiqmadi', 'PerformTransaction yuborilmagan', null],
                default          => ['todo', 'Polis chiqariladi', 'To\'lovdan keyin', null],
            };
        } else {
            $steps[] = $paid
                ? ['ok', 'Polis', $order->insurance_id ? 'ID ' . $order->insurance_id : null, null]
                : ['todo', 'Polis', 'To\'lovdan keyin', null];
        }

        return $steps;
    }

    private static function paymentLabel(?string $type): string
    {
        return match ($type) {
            Order::PAYMENT_PAYME => 'Payme',
            Order::PAYMENT_CLICK => 'Click',
            default              => (string) ($type ?: 'To\'lov'),
        };
    }

    public static function getRelations(): array
    {
        return [
            ApiLogsRelationManager::class,
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private static function applicantDocument(Order $record): ?string
    {
        $a = $record->applicant;

        if (!empty($a['passport_seria'])) {
            return $a['passport_seria'] . ' ' . ($a['passport_number'] ?? '');
        }

        return $a['inn'] ?? null;
    }

    private static function prettyJson(mixed $state): string
    {
        return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '—';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
