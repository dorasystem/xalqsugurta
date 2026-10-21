<?php

namespace App\Filament\Admin\Resources\ProductResource\RelationManagers;

use App\Models\ProductSettingChange;
use App\Services\ProductSettings;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Read-only history of price/limit/on-sale changes, written by Product::recordSettingChanges() */
class SettingChangesRelationManager extends RelationManager
{
    protected static string $relationship = 'settingChanges';

    protected static ?string $title = 'O\'zgarishlar tarixi';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Sana')
                    ->dateTime('d.m.Y H:i'),

                TextColumn::make('user.name')
                    ->label('Kim')
                    ->placeholder('Tizim'),

                TextColumn::make('field')
                    ->label('Sozlama')
                    ->formatStateUsing(fn (string $state): string => ProductSettings::label($state))
                    ->weight('bold'),

                TextColumn::make('old_value')
                    ->label('Eski qiymat')
                    ->color('gray'),

                TextColumn::make('new_value')
                    ->label('Yangi qiymat')
                    ->color(fn (ProductSettingChange $record): string => $record->field === 'is_active' && $record->new_value === 'O\'chirilgan' ? 'danger' : 'primary'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25])
            ->emptyStateHeading('Hali o\'zgarish yo\'q')
            ->emptyStateDescription('Narx, chegara yoki "Sotuvda" o\'zgartirilganda shu yerda ko\'rinadi.');
    }
}
