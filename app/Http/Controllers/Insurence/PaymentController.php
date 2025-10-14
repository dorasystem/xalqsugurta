<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class PaymentController extends Controller
{
    use HandlesInsuranceErrors;
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * Display payment page for any insurance order
     */
    public function show(string $lang, int $orderId): View|RedirectResponse
    {
        try {
            // Cast orderId to int
            $id = (int) $orderId;

            $order = $this->orderService->getOrderById($id);

            if (!$order) {
                return redirect()->back()
                    ->with('error', __('errors.insurance.order_not_found'));
            }

            // Determine which insurance type this is based on product_name or insurance_data
            $insuranceType = $this->determineInsuranceType($order);

            return view('pages.insurence.payment', [
                'order' => $order,
                'insuranceType' => $insuranceType,
            ]);
        } catch (\Exception $e) {
            Log::error('Payment page error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('home', ['locale' => getCurrentLocale()])
                ->with('error', __('errors.general_error') . ': ' . $e->getMessage());
        }
    }

    /**
     * Determine insurance type from order data
     */
    private function determineInsuranceType($order): string
    {
        $productName = strtolower($order->product_name ?? '');

        if (str_contains($productName, 'osago') || str_contains($productName, 'осаго')) {
            return 'osago';
        }

        if (str_contains($productName, 'accident') || str_contains($productName, 'бахтсиз')) {
            return 'accident';
        }

        if (str_contains($productName, 'property') || str_contains($productName, 'мол-мулк') || str_contains($productName, 'mol-mulk')) {
            return 'property';
        }

        // Default fallback
        return 'unknown';
    }

    /**
     * Process payment (placeholder for future payment integration)
     */
    public function process(string $orderId): RedirectResponse
    {
        try {
            $id = (int) $orderId;

            $order = $this->orderService->getOrderById($id);

            if (!$order) {
                return redirect()->route('home', ['locale' => getCurrentLocale()])
                    ->with('error', __('errors.insurance.order_not_found'));
            }

            // TODO: Implement payment gateway integration (Click, Payme, etc.)
            // For now, just redirect back with success message

            return $this->redirectWithSuccess(
                'payment.show',
                ['locale' => getCurrentLocale(), 'orderId' => $id],
                __('success.insurance.payment_successful')
            );
        } catch (\Exception $e) {
            Log::error('Payment process error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('errors.insurance.payment.processing_failed') . ': ' . $e->getMessage());
        }
    }
}
