<?php

namespace App\Filament\Admin\Resources\ClaimResource\Pages;

use App\Filament\Admin\Resources\ClaimResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditClaim extends EditRecord
{
    protected static string $resource = ClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /** Remember who changed the claim last */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data + ['handled_by' => Filament::auth()->id()];
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }
}
