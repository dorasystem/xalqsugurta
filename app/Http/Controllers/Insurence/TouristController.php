<?php

namespace App\Http\Controllers\Insurence;

use App\Services\OrderService;

/** Tourist accident insurance (Turist): persons flow, product code 203 */
final class TouristController extends PersonsInsuranceController
{
    private const SESSION_KEY = 'tourist';

    /** Sum insured per person (UZS); premium comes from the provider calculator */
    public const FLOW = [
        'key'     => self::SESSION_KEY,
        'icon'    => 'bi-luggage-fill',
        'min'     => 50_000,
        'max'     => 1_000_000,
        'step'    => 50_000,
        'default' => 500_000,
        'presets' => [100_000, 300_000, 500_000, 1_000_000],
    ];

    public function __construct(OrderService $orderService)
    {
        parent::__construct($orderService);
    }

    protected function getProductKey(): string
    {
        return self::SESSION_KEY;
    }

    protected function productCode(): string
    {
        return '203';
    }
}
