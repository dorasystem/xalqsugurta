<?php

namespace App\Filament\Admin\Resources\NewsletterSubscriberResource\Pages;

use App\Filament\Admin\Resources\NewsletterSubscriberResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Excel (CSV) yuklab olish')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => NewsletterSubscriberResource::csv()),
        ];
    }
}
