<?php

namespace App\Filament\Admin\Resources\SurveyResponseResource\Pages;

use App\Filament\Admin\Resources\SurveyResponseResource;
use App\Filament\Admin\Resources\SurveyResponseResource\Widgets\SurveyTotals;
use Filament\Resources\Pages\ListRecords;

class ListSurveyResponses extends ListRecords
{
    protected static string $resource = SurveyResponseResource::class;

    protected function getHeaderWidgets(): array
    {
        return [SurveyTotals::class];
    }
}
