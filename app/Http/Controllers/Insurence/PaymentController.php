<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use App\Models\Order;
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

        $state = match (true) {
            $order->status === Order::STATUS_PAID && $order->awaitsPolicy() => 'policy_pending',
            $order->status === Order::STATUS_PAID                           => 'paid',
            in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_FAILED], true) => 'cancelled',
            default                                                         => 'pay',
        };

        return view('pages.insurence.payment', [
            'order'       => $order,
            'state'       => $state,
            'showDetails' => $this->orderService->canSeeDetails($order),
            'response'    => $order->insurances_response_data ?? [],
        ]);
    }
}
