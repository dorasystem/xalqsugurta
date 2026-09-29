<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Tushum';

    protected ?string $description = 'To\'langan buyurtmalar summasi, kunlar bo\'yicha';

    protected int | string | array $columnSpan = ['md' => 2, 'xl' => 2];

    protected ?string $maxHeight = '280px';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7'  => '7 kun',
            '30' => '30 kun',
            '90' => '90 kun',
        ];
    }

    protected function getData(): array
    {
        $days  = (int) ($this->filter ?? 30);
        $start = CarbonImmutable::today()->subDays($days - 1);

        $byDay = Order::query()
            ->where('status', Order::STATUS_PAID)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];

        for ($i = 0; $i < $days; $i++) {
            $date     = $start->addDays($i);
            $labels[] = $date->format('d.m');
            $values[] = (float) ($byDay->get($date->toDateString()) ?? 0);
        }

        return [
            'datasets' => [[
                'label'           => 'Tushum, so\'m',
                'data'            => $values,
                'borderColor'     => '#393185',
                'backgroundColor' => 'rgba(57, 49, 133, 0.12)',
                'fill'            => true,
                'tension'         => 0.35,
                'pointRadius'     => 0,
            ]],
            'labels'   => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales'  => [
                'y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(57, 49, 133, 0.08)']],
                'x' => ['grid' => ['display' => false]],
            ],
        ];
    }
}
