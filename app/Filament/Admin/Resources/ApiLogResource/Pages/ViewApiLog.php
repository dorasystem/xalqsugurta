<?php

namespace App\Filament\Admin\Resources\ApiLogResource\Pages;

use App\Filament\Admin\Resources\ApiLogResource;
use App\Filament\Admin\Resources\OrderResource;
use App\Models\ApiLog;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewApiLog extends ViewRecord
{
    protected static string $resource = ApiLogResource::class;

    public function getTitle(): string
    {
        /** @var ApiLog $log */
        $log = $this->getRecord();

        return $log->method . ' ' . $log->endpoint;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('order')
                ->label('Buyurtmani ochish')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn (ApiLog $record): ?string => $record->order_id ? OrderResource::getUrl('view', ['record' => $record->order_id]) : null)
                ->visible(fn (ApiLog $record): bool => $record->order_id !== null),
        ];
    }
}
