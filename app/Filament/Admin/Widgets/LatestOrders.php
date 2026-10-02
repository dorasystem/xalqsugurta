<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrders extends TableWidget
{
    protected static ?int $sort = 5;

    protected static ?string $heading = 'So\'nggi buyurtmalar';

    protected int | string | array $columnSpan = ['md' => 2, 'xl' => 2];

    public function table(Table $table): Table
    {
        $columns = collect(OrderResource::columns())
            ->reject(fn ($column) => in_array($column->getName(), ['payment_type', 'contractStartDate'], true))
            ->values()
            ->all();

        return $table
            ->query(Order::query()->latest('id')->limit(10))
            ->columns($columns)
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('all')
                    ->label('Barchasi')
                    ->icon('heroicon-m-arrow-right')
                    ->iconPosition('after')
                    ->link()
                    ->url(OrderResource::getUrl('index')),
            ])
            ->searchable(false)
            ->paginated(false)
            ->emptyStateHeading('Hali buyurtmalar yo\'q');
    }
}
