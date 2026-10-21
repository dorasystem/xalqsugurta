<?php

namespace App\Filament\Admin\Resources\InfoPageResource\Pages;

use App\Filament\Admin\Resources\InfoPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInfoPage extends CreateRecord
{
    protected static string $resource = InfoPageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
