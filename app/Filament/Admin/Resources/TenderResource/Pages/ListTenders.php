<?php

namespace App\Filament\Admin\Resources\TenderResource\Pages;

use App\Filament\Admin\Resources\TenderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTenders extends ListRecords
{
    protected static string $resource = TenderResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
