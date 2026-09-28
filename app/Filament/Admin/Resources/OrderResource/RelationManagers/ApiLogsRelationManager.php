<?php

namespace App\Filament\Admin\Resources\OrderResource\RelationManagers;

use App\Filament\Admin\Resources\ApiLogResource;
use App\Models\ApiLog;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

/** Requests to the insurer's API made for this order (contract, payment confirmation) */
class ApiLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'apiLogs';

    protected static ?string $title = 'API so\'rovlari';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns(ApiLogResource::columns(withOrder: false))
            ->recordActions([
                Action::make('open')
                    ->label('Ochish')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ApiLog $record): string => ApiLogResource::getUrl('view', ['record' => $record])),
            ])
            ->recordClasses(fn (ApiLog $record): ?string => $record->success ? null : 'xs-row-failed')
            ->defaultSort('id', 'desc')
            ->paginated([10, 25])
            ->emptyStateHeading('So\'rovlar yozilmagan')
            ->emptyStateDescription('API jurnali ishga tushgunga qadar yaratilgan buyurtmalarda bo\'sh bo\'ladi.');
    }
}
