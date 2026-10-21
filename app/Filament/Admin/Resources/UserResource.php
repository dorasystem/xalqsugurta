<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;

/** Admin panel users (who may log in to /admin) */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Tizim';

    protected static ?string $navigationLabel = 'Foydalanuvchilar';

    protected static ?string $modelLabel = 'Foydalanuvchi';

    protected static ?string $pluralModelLabel = 'Foydalanuvchilar';

    protected static ?int $navigationSort = 2;

    // Nobody can delete their own account (and lock everyone out by accident)
    public static function isDeletable(User $record): bool
    {
        return $record->getKey() !== Filament::auth()->id();
    }

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ma\'lumotlar')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Ism')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Email (login)')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText(fn (): ?string => config('app.admin_emails', []) === []
                            ? null
                            : 'Panelga faqat .env dagi ADMIN_EMAILS ro\'yxatidagi emaillar kira oladi.'),
                ]),

            Section::make('Parol')
                ->description(fn (string $operation): ?string => $operation === 'edit'
                    ? 'O\'zgartirmaslik uchun bo\'sh qoldiring.'
                    : null)
                ->columns(2)
                ->schema([
                    TextInput::make('password')
                        ->label('Yangi parol')
                        ->password()
                        ->revealable()
                        ->rule(Password::min(10)->letters()->numbers())
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->confirmed()
                        ->autocomplete('new-password'),

                    TextInput::make('password_confirmation')
                        ->label('Parolni takrorlang')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(false)
                        ->autocomplete('new-password'),
                ]),
        ]);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ism')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                IconColumn::make('panel_access')
                    ->label('Panelga kira oladi')
                    ->state(fn (User $record): bool => $record->canAccessPanel(Filament::getCurrentOrDefaultPanel()))
                    ->boolean()
                    ->tooltip(fn (bool $state): ?string => $state ? null : '.env dagi ADMIN_EMAILS ro\'yxatida yo\'q'),

                TextColumn::make('created_at')
                    ->label('Qo\'shilgan')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('Tahrirlash'),
                DeleteAction::make()
                    ->label('O\'chirish')
                    ->visible(fn (User $record): bool => static::isDeletable($record)),
            ])
            ->defaultSort('created_at');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
