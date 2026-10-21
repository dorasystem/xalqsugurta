<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CallbackRequestResource\Pages;
use App\Models\CallbackRequest;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** "Call me back" requests from the site (/{locale}/callback) */
class CallbackRequestResource extends Resource
{
    protected static ?string $model = CallbackRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-phone-arrow-down-left';

    protected static string|\UnitEnum|null $navigationGroup = 'Murojaatlar';

    protected static ?string $navigationLabel = 'Qayta qo\'ng\'iroqlar';

    protected static ?string $modelLabel = 'So\'rov';

    protected static ?string $pluralModelLabel = 'Qayta qo\'ng\'iroqlar';

    protected static ?int $navigationSort = 2;

    public const TOPICS = [
        'buy'    => 'Polis olish',
        'claim'  => 'Sug\'urta hodisasi',
        'policy' => 'Mavjud polis',
        'other'  => 'Boshqa',
    ];

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = CallbackRequest::where('status', CallbackRequest::STATUS_NEW)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Sana')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('name')->label('Ism')->searchable(),
                TextColumn::make('phone')->label('Telefon')->searchable()
                    ->formatStateUsing(fn (?string $state) => $state ? formatPhone($state) : null)
                    ->url(fn (CallbackRequest $record) => 'tel:+' . $record->phone),
                TextColumn::make('topic')->label('Mavzu')->formatStateUsing(fn (?string $state) => self::TOPICS[$state] ?? $state)->placeholder('—'),
                TextColumn::make('message')->label('Izoh')->limit(60)->wrap()->placeholder('—'),
                TextColumn::make('locale')->label('Til')->badge()->color('gray'),
                TextColumn::make('status')->label('Holat')->badge()
                    ->formatStateUsing(fn (string $state) => $state === CallbackRequest::STATUS_DONE ? 'Bog\'lanildi' : 'Yangi')
                    ->color(fn (string $state) => $state === CallbackRequest::STATUS_DONE ? 'success' : 'warning'),
                TextColumn::make('handler.name')->label('Xodim')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Holat')->options([
                    CallbackRequest::STATUS_NEW  => 'Yangi',
                    CallbackRequest::STATUS_DONE => 'Bog\'lanildi',
                ])->default(CallbackRequest::STATUS_NEW),
            ])
            ->recordActions([
                Action::make('done')
                    ->label('Bog\'lanildi')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (CallbackRequest $record) => $record->status === CallbackRequest::STATUS_NEW)
                    ->action(fn (CallbackRequest $record) => $record->update([
                        'status'     => CallbackRequest::STATUS_DONE,
                        'handled_by' => Filament::auth()->id(),
                        'handled_at' => now(),
                    ])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCallbackRequests::route('/'),
        ];
    }
}
