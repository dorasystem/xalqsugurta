<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $today      = CarbonImmutable::today();
        $weekStart  = $today->subDays(6);
        $monthStart = $today->startOfMonth();
        $from       = min($today->subDays(29), $monthStart);

        // One grouped query for every figure below (DATE() works on MySQL and SQLite)
        $rows = Order::query()
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, status, COUNT(*) as n, SUM(amount) as total')
            ->groupBy('day', 'status')
            ->get()
            ->map(fn ($r) => ['day' => (string) $r->day, 'status' => $r->status, 'n' => (int) $r->n, 'total' => (float) $r->total]);

        $sum = fn (string $field, string $since, ?array $statuses = null, ?string $until = null): float => $rows
            ->filter(fn (array $r) => $r['day'] >= $since && ($until === null || $r['day'] <= $until)
                && ($statuses === null || in_array($r['status'], $statuses, true)))
            ->sum($field);

        $paid = [Order::STATUS_PAID];
        $days = collect(range(0, 6))->map(fn (int $i) => $weekStart->addDays($i)->toDateString());

        $ordersPerDay  = $days->map(fn (string $d) => (int) $sum('n', $d, null, $d))->all();
        $revenuePerDay = $days->map(fn (string $d) => $sum('total', $d, $paid, $d))->all();

        $todayDate    = $today->toDateString();
        $todayOrders  = (int) $sum('n', $todayDate);
        $todayPaid    = (int) $sum('n', $todayDate, $paid);
        $monthRevenue = $sum('total', $monthStart->toDateString(), $paid);
        $last30Total  = (int) $sum('n', $today->subDays(29)->toDateString());
        $last30Paid   = (int) $sum('n', $today->subDays(29)->toDateString(), $paid);
        $conversion   = $last30Total > 0 ? round($last30Paid / $last30Total * 100) : 0;
        $unpaid       = (int) $sum('n', $today->subDay()->toDateString(), [Order::STATUS_NEW, Order::STATUS_PENDING]);

        return [
            Stat::make('Bugungi buyurtmalar', $todayOrders)
                ->description("{$todayPaid} tasi to'langan")
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->chart($ordersPerDay)
                ->color('primary'),

            Stat::make('Bu oy tushum', formatMoney($monthRevenue))
                ->description('Faqat to\'langan buyurtmalar')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($revenuePerDay)
                ->color('success'),

            Stat::make('To\'lov konversiyasi', $conversion . '%')
                ->description("30 kun: {$last30Paid} / {$last30Total} buyurtma")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($conversion >= 50 ? 'success' : 'warning'),

            Stat::make('To\'lov kutilmoqda', $unpaid)
                ->description('Oxirgi 24 soatda to\'lanmagan')
                ->descriptionIcon('heroicon-m-clock')
                ->color($unpaid > 0 ? 'warning' : 'gray'),
        ];
    }
}
