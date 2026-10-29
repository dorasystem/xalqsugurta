<?php

namespace App\Filament\Admin\Resources\OfferProductResource\Pages;

use App\Filament\Admin\Resources\OfferProductResource;
use Filament\Resources\Pages\EditRecord;

class EditOfferProduct extends EditRecord
{
    protected static string $resource = OfferProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
