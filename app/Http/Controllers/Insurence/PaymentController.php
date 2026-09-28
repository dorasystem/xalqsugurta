<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentSettings;
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
            // The insurer's own Click link when it gave one, else our Click merchant when it is set up in the panel
            'clickUrl'    => $order->click_url
                ?: (PaymentSettings::clickReady() ? route('payment.click', ['id' => $order->id]) : null),
            // The insurer's own Payme link when it gave one, else ours unless switched off in the panel
            'paymeUrl'    => $order->payme_url
                ?: (PaymentSettings::paymeEnabled() ? route('payment.payme', ['id' => $order->id]) : null),
        ]);
    }
}
