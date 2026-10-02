<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Asosiy panel';

    /** 3 columns on wide screens: revenue chart (2) + product chart (1) share a row */
    public function getColumns(): int | array
    {
        return ['md' => 2, 'xl' => 3];
    }
}
