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
        $today     = CarbonImmutable::today();
        $weekStart = $today->subDays(6);
        $monthStart = $today->startOfMonth();

        // Last 7 days, loaded once and grouped in PHP (works on MySQL and SQLite)
        $week = Order::query()
            ->where('created_at', '>=', $weekStart)
            ->get(['created_at', 'amount', 'status']);

        $days = collect(range(0, 6))->map(fn (int $i) => $weekStart->addDays($i)->toDateString());
        $byDay = $week->groupBy(fn (Order $o) => $o->created_at->toDateString());

        $ordersPerDay  = $days->map(fn (string $d) => $byDay->get($d)?->count() ?? 0)->all();
        $revenuePerDay = $days->map(fn (string $d) => (float) ($byDay->get($d)?->where('status', Order::STATUS_PAID)->sum('amount') ?? 0))->all();

        $todayOrders = $byDay->get($today->toDateString())?->count() ?? 0;
        $todayPaid   = $byDay->get($today->toDateString())?->where('status', Order::STATUS_PAID)->count() ?? 0;

        $monthRevenue = (float) Order::query()
            ->where('status', Order::STATUS_PAID)
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');

        $last30      = Order::query()->where('created_at', '>=', $today->subDays(29));
        $last30Total = (clone $last30)->count();
        $last30Paid  = (clone $last30)->where('status', Order::STATUS_PAID)->count();
        $conversion  = $last30Total > 0 ? round($last30Paid / $last30Total * 100) : 0;

        $unpaid = Order::query()
            ->whereIn('status', [Order::STATUS_NEW, Order::STATUS_PENDING])
            ->where('created_at', '>=', $today->subDay())
            ->count();

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
