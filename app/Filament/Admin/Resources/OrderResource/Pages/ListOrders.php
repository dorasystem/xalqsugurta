<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use App\Filament\Admin\Widgets\OrdersStatsOverview;
use App\Models\Order;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        $counts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $count = fn (array $statuses): int => (int) $counts->only($statuses)->sum();

        return [
            'all' => Tab::make('Hammasi')
                ->badge($counts->sum()),

            'paid' => Tab::make('To\'langan')
                ->icon('heroicon-m-check-circle')
                ->badge($count([Order::STATUS_PAID]))
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Order::STATUS_PAID)),

            'waiting' => Tab::make('To\'lov kutilmoqda')
                ->icon('heroicon-m-clock')
                ->badge($count([Order::STATUS_NEW, Order::STATUS_PENDING]))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [Order::STATUS_NEW, Order::STATUS_PENDING])),

            'no_policy' => Tab::make('Polis chiqmagan')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge(Order::awaitingPolicy()->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->awaitingPolicy()),

            'failed' => Tab::make('Bekor / xato')
                ->icon('heroicon-m-x-circle')
                ->badge($count([Order::STATUS_CANCELLED, Order::STATUS_FAILED]))
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [Order::STATUS_CANCELLED, Order::STATUS_FAILED])),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrdersStatsOverview::class,
        ];
    }
}
