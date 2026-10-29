<?php

namespace App\Filament\Admin\Resources\VacancyResource\Pages;

use App\Filament\Admin\Resources\VacancyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVacancies extends ListRecords
{
    protected static string $resource = VacancyResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
