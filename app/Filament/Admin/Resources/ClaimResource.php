<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ClaimResource\Pages;
use App\Models\Claim;
use App\Models\Product;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Insured-event reports from the site (/{locale}/claims) */
class ClaimResource extends Resource
{
    protected static ?string $model = Claim::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-lifebuoy';

    protected static string|\UnitEnum|null $navigationGroup = 'Murojaatlar';

    protected static ?string $navigationLabel = 'Sug\'urta hodisalari';

    protected static ?string $modelLabel = 'Ariza';

    protected static ?string $pluralModelLabel = 'Sug\'urta hodisalari';

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Claim::where('status', Claim::STATUS_NEW)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /** route => Uzbek product name */
    public static function productNames(): array
    {
        return Product::pluck('name_uz', 'route')->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Ariza')
                    ->columnSpan(['lg' => 2])
                    ->columns(2)
                    ->schema([
                        TextEntry::make('number')->label('Raqam')->copyable(),
                        TextEntry::make('created_at')->label('Yuborilgan')->dateTime('d.m.Y H:i'),
                        TextEntry::make('full_name')->label('F.I.Sh.'),
                        TextEntry::make('phone')->label('Telefon')->formatStateUsing(fn (?string $state) => $state ? formatPhone($state) : null)
                            ->url(fn (Claim $record) => 'tel:+' . $record->phone),
                        TextEntry::make('product')->label('Sug\'urta turi')
                            ->formatStateUsing(fn (?string $state) => self::productNames()[$state] ?? $state)->placeholder('Ko\'rsatilmagan'),
                        TextEntry::make('policy_number')->label('Polis raqami')->copyable(),
                        TextEntry::make('event_date')->label('Hodisa sanasi')->date('d.m.Y'),
                        TextEntry::make('order_id')->label('Saytdagi buyurtma')
                            ->formatStateUsing(fn ($state) => '№' . $state)
                            ->url(fn (Claim $record) => $record->order_id ? OrderResource::getUrl('view', ['record' => $record->order_id]) : null)
                            ->placeholder('Bog\'lanmagan'),
                        TextEntry::make('description')->label('Tavsif')->columnSpanFull()->extraAttributes(['style' => 'white-space: pre-line']),
                        View::make('filament.admin.claim-files')->columnSpanFull(),
                    ]),

                Section::make('Ko\'rib chiqish')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Select::make('status')
                            ->label('Holat')
                            ->options(Claim::STATUS_LABELS)
                            ->required()
                            ->native(false),
                        Textarea::make('public_note')
                            ->label('Mijozga izoh')
                            ->helperText('Mijoz ariza holatini tekshirganda ko\'radi (masalan, qaysi hujjat kerak).')
                            ->rows(4)
                            ->maxLength(1000),
                        Textarea::make('internal_note')
                            ->label('Ichki izoh')
                            ->helperText('Faqat xodimlar ko\'radi.')
                            ->rows(4)
                            ->maxLength(2000),
                        TextEntry::make('handler.name')->label('Oxirgi ko\'rgan')->placeholder('—'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Raqam')->searchable()->fontFamily('mono')->weight('bold'),
                TextColumn::make('created_at')->label('Sana')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('full_name')->label('F.I.Sh.')->searchable()->limit(30),
                TextColumn::make('phone')->label('Telefon')->searchable()->formatStateUsing(fn (?string $state) => $state ? formatPhone($state) : null),
                TextColumn::make('product')->label('Turi')->formatStateUsing(fn (?string $state) => self::productNames()[$state] ?? $state)->placeholder('—'),
                TextColumn::make('policy_number')->label('Polis')->searchable()->toggleable(),
                TextColumn::make('status')->label('Holat')->badge()
                    ->formatStateUsing(fn (string $state) => Claim::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state) => Claim::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('files')->label('Fayl')->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0)
                    ->state(fn (Claim $record) => count($record->files ?? []))->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Holat')->options(Claim::STATUS_LABELS),
            ])
            ->recordActions([
                EditAction::make()->label('Ko\'rish'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClaims::route('/'),
            'edit'  => Pages\EditClaim::route('/{record}/edit'),
        ];
    }
}
