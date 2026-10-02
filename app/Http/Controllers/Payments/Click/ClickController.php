<?php

namespace App\Http\Controllers\Payments\Click;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentSettings;
use App\Services\Payments\ClickShopApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** Click payment link + SHOP API callbacks (routes/api.php: /prepare, /complete) */
class ClickController extends Controller
{
    public function __construct(private readonly ClickShopApi $shopApi)
    {
    }

    /** Sends the customer to Click's payment page for the order */
    public function payment(Request $request): RedirectResponse
    {
        $order = Order::findOrFail($request->id);

        abort_unless(PaymentSettings::clickReady(), 503, 'Click is not configured');

        $serviceId  = config('services.click.service_id');
        $merchantId = config('services.click.merchant_id');

        $query = http_build_query(array_filter([
            'service_id'        => $serviceId,
            'merchant_id'       => $merchantId,
            'merchant_user_id'  => config('services.click.merchant_user_id'),
            'amount'            => number_format((float) $order->amount, 2, '.', ''),
            'transaction_param' => $order->id,
            'return_url'        => route('payment.show', ['locale' => getCurrentLocale(), 'orderId' => $order->id]),
        ]));

        return redirect()->away('https://my.click.uz/services/pay?' . $query);
    }

    public function prepare(Request $request): JsonResponse
    {
        $response = $this->shopApi->prepare($this->params($request));
        Log::info('Click Prepare', ['request' => $this->params($request), 'response' => $response]);

        return response()->json($response);
    }

    public function complete(Request $request): JsonResponse
    {
        $response = $this->shopApi->complete($this->params($request));
        Log::info('Click Complete', ['request' => $this->params($request), 'response' => $response]);

        return response()->json($response);
    }

    /** Click posts flat form fields; older integrations nested them under "Request" */
    private function params(Request $request): array
    {
        $nested = $request->input('Request');

        return is_array($nested) ? $nested : $request->all();
    }
}
