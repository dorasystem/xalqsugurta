<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\ApiLogResource;
use App\Filament\Admin\Resources\OrderResource;
use App\Models\ApiLog;
use App\Models\Order;
use Filament\Widgets\Widget;

/** Dashboard "Diqqat talab qiladi": things an operator should act on now */
class AttentionWidget extends Widget
{
    protected static ?int $sort = 3;

    protected string $view = 'filament.admin.attention-widget';

    /** Rendered with the page, not lazily: it is the first thing an operator should see */
    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 1;

    /** Orders older than this still waiting for payment are listed */
    private const PAYMENT_WAIT_HOURS = 2;

    protected function getViewData(): array
    {
        $items = [];

        $noPolicy = Order::awaitingPolicy()->where('created_at', '>=', now()->subDays(30))->count();
        if ($noPolicy > 0) {
            $items[] = [
                'tone'  => 'danger',
                'icon'  => 'shield',
                'title' => 'To\'langan, polis chiqmagan',
                'text'  => 'Buyurtmani ochib "Polisni qayta so\'rash" tugmasini bosing',
                'count' => $noPolicy,
                'url'   => OrderResource::getUrl('index', ['tab' => 'no_policy']),
            ];
        }

        $unconfirmed = Order::awaitingPaymentConfirmation()->where('created_at', '>=', now()->subDays(30))->count();
        if ($unconfirmed > 0) {
            $items[] = [
                'tone'  => 'danger',
                'icon'  => 'shield',
                'title' => 'To\'lov sug\'urtachiga tasdiqlanmagan',
                'text'  => 'Buyurtmani ochib "To\'lovni tasdiqlash" tugmasini bosing',
                'count' => $unconfirmed,
                'url'   => OrderResource::getUrl('index', ['tab' => 'unconfirmed']),
            ];
        }

        $failed = ApiLog::failed()->where('created_at', '>=', today());
        $failedCount = (clone $failed)->count();
        if ($failedCount > 0) {
            $last = (clone $failed)->latest('id')->first();
            $items[] = [
                'tone'  => 'danger',
                'icon'  => 'alert',
                'title' => 'Bugun API xatolari',
                'text'  => 'Oxirgisi: ' . $last->endpoint . ' · ' . $last->outcome() . ' · ' . $last->created_at->format('H:i'),
                'count' => $failedCount,
                'url'   => ApiLogResource::getUrl('index', ['tab' => 'failed']),
            ];
        }

        $waiting = Order::query()
            ->whereIn('status', [Order::STATUS_NEW, Order::STATUS_PENDING])
            ->whereBetween('created_at', [now()->subDays(2), now()->subHours(self::PAYMENT_WAIT_HOURS)])
            ->count();
        if ($waiting > 0) {
            $items[] = [
                'tone'  => 'warning',
                'icon'  => 'clock',
                'title' => 'To\'lov ' . self::PAYMENT_WAIT_HOURS . ' soatdan beri kutilmoqda',
                'text'  => 'Oxirgi 2 kun ichidagi buyurtmalar. Mijozga qo\'ng\'iroq qilish mumkin',
                'count' => $waiting,
                'url'   => OrderResource::getUrl('index', ['tab' => 'waiting']),
            ];
        }

        return ['items' => $items];
    }
}
