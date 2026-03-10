<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class PaymentController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function show(string $lang, int $orderId): View|RedirectResponse
    {
        $order = $this->orderService->getOrderById($orderId);

        if (!$order) {
            return redirect()->route('home', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => __('messages.order_not_found') ?? 'Order not found.']);
        }

        return view('pages.insurence.payment', compact('order'));
    }
}
