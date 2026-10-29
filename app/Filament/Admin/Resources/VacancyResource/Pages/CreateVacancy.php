<?php

namespace App\Filament\Admin\Resources\VacancyResource\Pages;

use App\Filament\Admin\Resources\VacancyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVacancy extends CreateRecord
{
    protected static string $resource = VacancyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
