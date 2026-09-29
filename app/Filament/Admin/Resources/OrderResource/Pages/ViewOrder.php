<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use App\Models\Order;
use App\Services\EshopPaymentService;
use App\Services\XalqPolicyService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return 'Buyurtma №' . $this->getRecord()->getKey();
    }

    public function getSubheading(): ?string
    {
        /** @var Order $order */
        $order = $this->getRecord();

        return trim(($order->insuranceProductName ?: $order->product_name) . ' · ' . $order->created_at?->format('d.m.Y H:i'), ' ·');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retryPolicy')
                ->label('Polisni qayta so\'rash')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->visible(fn (Order $record): bool => $record->awaitsPolicy())
                ->requiresConfirmation()
                ->modalHeading('Polisni qayta so\'rash')
                ->modalDescription('Xalq Sug\'urta\'ga to\'lov tasdig\'i (PerformTransactionRequest) shu shartnoma raqami bilan qayta yuboriladi. Mijozdan pul qayta yechilmaydi.')
                ->modalSubmitActionLabel('Yuborish')
                ->action(function (Order $record, XalqPolicyService $policies): void {
                    if ($policies->retry($record)) {
                        Notification::make()
                            ->title('Polis chiqarildi')
                            ->body('Yuklab olish havolasi buyurtmaga saqlandi.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Polis chiqmadi')
                            ->body('Xalq Sug\'urta so\'rovni qabul qilmadi. Javobni pastdagi "API so\'rovlari" jadvalida ko\'ring.')
                            ->danger()
                            ->persistent()
                            ->send();
                    }

                    $this->refreshFormData(['insurances_response_data']);
                }),

            Action::make('confirmPayment')
                ->label('To\'lovni tasdiqlash')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->visible(fn (Order $record): bool => $record->awaitsPaymentConfirmation())
                ->requiresConfirmation()
                ->modalHeading('To\'lovni sug\'urtachiga tasdiqlash')
                ->modalDescription(fn (Order $record): string => 'Xalq Sug\'urta\'ga eshop/payment so\'rovi shartnoma ID '
                    . (app(EshopPaymentService::class)->body($record)['contract_id'] ?? '—') . ' bilan yuboriladi. Mijozdan pul qayta yechilmaydi.')
                ->modalSubmitActionLabel('Yuborish')
                ->action(function (Order $record, EshopPaymentService $eshop): void {
                    if ($eshop->confirm($record)) {
                        Notification::make()
                            ->title('To\'lov tasdiqlandi')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('To\'lov tasdiqlanmadi')
                            ->body('Xalq Sug\'urta so\'rovni qabul qilmadi yoki shartnoma ID yo\'q. Javobni pastdagi "API so\'rovlari" jadvalida ko\'ring.')
                            ->danger()
                            ->persistent()
                            ->send();
                    }

                    $this->refreshFormData(['insurances_response_data']);
                }),

            Action::make('policy')
                ->label('Polisni yuklab olish')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (Order $record): ?string => $record->insurances_response_data['download_url'] ?? null, shouldOpenInNewTab: true)
                ->visible(fn (Order $record): bool => !empty($record->insurances_response_data['download_url'])),

            Action::make('paymentPage')
                ->label('To\'lov sahifasi')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (Order $record): string => route('payment.show', ['locale' => 'uz', 'orderId' => $record->id]), shouldOpenInNewTab: true),
        ];
    }
}
