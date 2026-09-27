<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
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
