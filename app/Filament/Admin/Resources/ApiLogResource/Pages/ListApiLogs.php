<?php

namespace App\Filament\Admin\Resources\ApiLogResource\Pages;

use App\Filament\Admin\Resources\ApiLogResource;
use App\Models\ApiLog;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListApiLogs extends ListRecords
{
    protected static string $resource = ApiLogResource::class;

    public function getSubheading(): ?string
    {
        return 'Sug\'urta API\'siga yuborilgan har bir so\'rov va javob · ' . ApiLog::KEEP_DAYS . ' kun saqlanadi';
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Hammasi'),

            'failed' => Tab::make('Xatolar')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge(ApiLog::failed()->where('created_at', '>=', today()->subDays(6))->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('success', false)),
        ];
    }
}
