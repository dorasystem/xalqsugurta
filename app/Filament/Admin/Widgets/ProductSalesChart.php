<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class ProductSalesChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Mahsulotlar bo\'yicha';

    protected ?string $description = 'To\'langan polislar, oxirgi 30 kun';

    protected ?string $maxHeight = '280px';

    /** Brand purple first, then muted companions */
    private const PALETTE = ['#393185', '#6b62c9', '#9d97dd', '#1d7a4a', '#d97706', '#0e7490', '#be185d', '#64748b'];

    protected function getData(): array
    {
        $rows = Order::query()
            ->where('status', Order::STATUS_PAID)
            ->where('created_at', '>=', CarbonImmutable::today()->subDays(29))
            ->selectRaw("COALESCE(NULLIF(insuranceProductName, ''), NULLIF(product_name, ''), '—') as name, COUNT(*) as n")
            ->groupBy('name')
            ->orderByDesc('n')
            ->pluck('n', 'name')
            ->map(fn ($n) => (int) $n);

        return [
            'datasets' => [[
                'data'            => $rows->values()->all(),
                'backgroundColor' => array_slice(self::PALETTE, 0, max(1, $rows->count())),
                'borderWidth'     => 0,
            ]],
            'labels'   => $rows->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout'  => '62%',
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales'  => ['x' => ['display' => false], 'y' => ['display' => false]],
        ];
    }
}
