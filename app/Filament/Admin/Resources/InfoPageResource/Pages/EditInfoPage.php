<?php

namespace App\Filament\Admin\Resources\InfoPageResource\Pages;

use App\Filament\Admin\Resources\InfoPageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInfoPage extends EditRecord
{
    protected static string $resource = InfoPageResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
