<?php

namespace App\Http\Controllers\Insurence\Concerns;

use App\Services\ProductSettings;

/**
 * The controller's FLOW constant with the numbers set in the admin panel
 * (Mahsulotlar → product → "Narx va chegaralar") applied on top.
 */
trait ConfigurableFlow
{
    private ?array $flowSettings = null;

    protected function flow(): array
    {
        return $this->flowSettings ??= ProductSettings::merge(static::FLOW, $this->getProduct()?->settings);
    }
}
