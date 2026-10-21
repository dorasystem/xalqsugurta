<?php

namespace App\Http\Controllers\Insurence;

use App\Services\OrderService;

/** Accident insurance (Baxtsiz hodisa): persons flow, product code 202 */
final class AccidentController extends PersonsInsuranceController
{
    private const SESSION_KEY = 'accident';

    /** Sum insured per person (UZS); premium comes from the provider calculator */
    public const FLOW = [
        'key'     => self::SESSION_KEY,
        'icon'    => 'bi-heart-pulse-fill',
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
        return '202';
    }
}
