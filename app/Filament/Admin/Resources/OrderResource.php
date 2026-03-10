<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Buyurtmalar';

    protected static ?string $modelLabel = 'Buyurtma';

    protected static ?string $pluralModelLabel = 'Buyurtmalar';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('insuranceProductName')
                    ->label('Mahsulot')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('amount')
                    ->label('Summa')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, '.', ' ') . ' UZS')
                    ->sortable(),

                TextColumn::make('insurance_id')
                    ->label('Sug\'urta ID')
                    ->searchable()
                    ->copyable()
                    ->limit(20),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PAID      => 'success',
                        Order::STATUS_PENDING   => 'warning',
                        Order::STATUS_NEW       => 'info',
                        Order::STATUS_CANCELLED => 'danger',
                        Order::STATUS_FAILED    => 'danger',
                        default                 => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Order::STATUS_PAID      => 'To\'langan',
                        Order::STATUS_PENDING   => 'Kutilmoqda',
                        Order::STATUS_NEW       => 'Yangi',
                        Order::STATUS_CANCELLED => 'Bekor',
                        Order::STATUS_FAILED    => 'Xato',
                        default                 => $state,
                    }),

                TextColumn::make('contractStartDate')
                    ->label('Boshlanish')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('contractEndDate')
                    ->label('Tugash')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Sana')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Order::STATUS_NEW       => 'Yangi',
                        Order::STATUS_PENDING   => 'Kutilmoqda',
                        Order::STATUS_PAID      => 'To\'langan',
                        Order::STATUS_CANCELLED => 'Bekor',
                        Order::STATUS_FAILED    => 'Xato',
                    ]),
            ])
            ->defaultSort('id', 'desc')
            ->striped();
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asosiy ma\'lumotlar')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                Order::STATUS_PAID      => 'success',
                                Order::STATUS_PENDING   => 'warning',
                                Order::STATUS_NEW       => 'info',
                                Order::STATUS_CANCELLED => 'danger',
                                Order::STATUS_FAILED    => 'danger',
                                default                 => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                Order::STATUS_PAID      => 'To\'langan',
                                Order::STATUS_PENDING   => 'Kutilmoqda',
                                Order::STATUS_NEW       => 'Yangi',
                                Order::STATUS_CANCELLED => 'Bekor',
                                Order::STATUS_FAILED    => 'Xato',
                                default                 => $state,
                            }),
                        TextEntry::make('insuranceProductName')->label('Mahsulot'),
                        TextEntry::make('insurance_id')->label('Sug\'urta ID')->copyable(),
                        TextEntry::make('phone')->label('Telefon')->copyable(),
                        TextEntry::make('amount')
                            ->label('Summa')
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 0, '.', ' ') . ' UZS'),
                        TextEntry::make('contractStartDate')->label('Boshlanish sanasi')->date('d.m.Y'),
                        TextEntry::make('contractEndDate')->label('Tugash sanasi')->date('d.m.Y'),
                        TextEntry::make('payment_type')->label('To\'lov turi'),
                        TextEntry::make('created_at')->label('Yaratilgan')->dateTime('d.m.Y H:i'),
                    ]),

                Section::make('Polis')
                    ->columns(2)
                    ->visible(fn (Order $record): bool =>
                        !empty($record->insurances_response_data['download_url']) ||
                        !empty($record->insurances_response_data['polis_sery'])
                    )
                    ->schema([
                        TextEntry::make('insurances_response_data.polis_sery')
                            ->label('Polis seriya'),
                        TextEntry::make('insurances_response_data.polis_number')
                            ->label('Polis raqam'),
                        TextEntry::make('insurances_response_data.download_url')
                            ->label('PDF havola')
                            ->url(fn (Order $record): ?string => $record->insurances_response_data['download_url'] ?? null)
                            ->openUrlInNewTab()
                            ->columnSpanFull(),
                        TextEntry::make('insurances_response_data.polis_check')
                            ->label('Tekshirish havolasi')
                            ->url(fn (Order $record): ?string => $record->insurances_response_data['polis_check'] ?? null)
                            ->openUrlInNewTab()
                            ->columnSpanFull(),
                    ]),

                Section::make('API javobi')
                    ->collapsed()
                    ->schema([
                        TextEntry::make('insurances_response_data')
                            ->label('')
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                            ->html()
                            ->extraAttributes(['style' => 'white-space:pre;font-family:monospace;font-size:12px;']),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
