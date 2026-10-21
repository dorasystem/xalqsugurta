<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => UserResource::isDeletable($this->record)),
        ];
    }

    // Changing your own password must not log you out (AuthenticateSession compares the hash)
    protected function afterSave(): void
    {
        if ($this->record->getKey() === Filament::auth()->id() && filled($this->data['password'] ?? null)) {
            session()->put('password_hash_' . Filament::getAuthGuard(), $this->record->getAuthPassword());
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
