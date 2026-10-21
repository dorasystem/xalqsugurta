<?php

namespace App\Filament\Admin\Resources\InfoPageResource\Pages;

use App\Filament\Admin\Resources\InfoPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInfoPages extends ListRecords
{
    protected static string $resource = InfoPageResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
