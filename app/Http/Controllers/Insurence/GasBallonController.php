<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\GazApplicationRequest;
use App\DTOs\GazApplicationData;
use App\Models\Order;
use App\Services\GazBallonService;
use App\Services\OrderService;
use App\Traits\GasInsurenceApi;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

final class GasBallonController extends Controller
{
    use GasInsurenceApi, HandlesInsuranceErrors;

    public function __construct(
        private readonly GazBallonService $service,
        private readonly OrderService $orderService
    ) {}

    public function main(): View
    {
        return view('pages.insurence.gas.main');
    }

    public function applicationView(): View
    {
        return view('pages.insurence.gas.main');
    }

    public function application(GazApplicationRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $owner = session('gazballon_owner_info');
            $property = session('gazballon_property_info');

            if (!$owner || !$property) {
                return back()
                    ->withErrors(['error' => __('errors.insurance.session_expired')])
                    ->withInput();
            }

            // Create DTO from request data
            $applicationData = GazApplicationData::fromRequest($data, $owner, $property);

            // Send to API
            $response = $this->sendGasRequest('InitiateTransactionRequest', $applicationData->toApiFormat());

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = is_array($errorData)
                    ? json_encode($errorData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                    : (string) ($errorData['message'] ?? $response->body());

                Log::error('Gas balloon application: API returned error', [
                    'status' => $response->status(),
                    'error' => $errorData,
                    'formatted_error' => $errorMessage,
                ]);

                return back()
                    ->withErrors(['error' => $errorMessage])
                    ->withInput();
            }

            $responseData = $response->json();

            Log::info('Gas balloon application: API response received', [
                'has_data' => !empty($responseData),
                'result' => $responseData['result'] ?? null,
            ]);

            // Check if result indicates error (but 302 means transaction exists, which is acceptable)
            $result = $responseData['result'] ?? null;
            if ($result !== null && $result !== 0 && $result !== 302) {
                $errorMessage = $responseData['result_message'] ?? 'API xatosi';
                Log::error('Gas balloon application: API returned error result', [
                    'result' => $result,
                    'message' => $errorMessage,
                ]);

                return back()
                    ->withErrors(['error' => $errorMessage])
                    ->withInput();
            }

            // Extract insurance ID from polis data (polis_sery + polis_number)
            $insuranceId = null;
            if (isset($responseData['polis_sery']) && isset($responseData['polis_number'])) {
                $insuranceId = $responseData['polis_sery'] . $responseData['polis_number'];
            } else {
                $insuranceId = $responseData['id']
                    ?? $responseData['UUID']
                    ?? $responseData['contract_id']
                    ?? uniqid('gaz_');
            }

            // Extract payment URLs - polis_check might be used as payment URL
            $paymeUrl = $responseData['payme_url'] ?? null;
            $clickUrl = $responseData['click_url'] ?? null;

            // If polis_check exists and no payment URLs, use it as fallback
            if (!$paymeUrl && !$clickUrl && isset($responseData['polis_check'])) {
                $clickUrl = $responseData['polis_check'];
            }

            // Extract amount from API response or use calculated premium
            $amount = $responseData['amount']
                ?? $responseData['insurancePremium']
                ?? $applicationData->insurancePremium;

            // Create order
            $orderData = [
                'product_name' => __('insurance.gas.product_name'),
                'amount' => (int) $amount,
                'state' => 0,
                'insurance_id' => $insuranceId,
                'phone' => $applicationData->customer['phone'] ?? null,
                'insurances_data' => $applicationData->toArray(),
                'insurances_response_data' => $responseData,
                'payme_url' => $paymeUrl,
                'click_url' => $clickUrl,
                'status' => Order::STATUS_NEW,
                'contractStartDate' => $applicationData->paymentStartDate,
                'contractEndDate' => $applicationData->paymentEndDate,
                'insuranceProductName' => __('insurance.gas.product_name'),
            ];

            $order = $this->orderService->createOrder($orderData);

            Log::info('Gas balloon storage: Order created successfully', ['order_id' => $order->id]);

            // Clear session data
            session()->forget(['gazballon_application_data', 'gazballon_owner_info', 'gazballon_property_info']);

            return $this->redirectWithSuccess(
                'payment.show',
                ['locale' => getCurrentLocale(), 'orderId' => $order->id],
                __('success.insurance.order_created')
            );
        } catch (\Exception $e) {
            Log::error('Gas balloon application error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['error' => __('errors.insurance.general_error')])
                ->withInput();
        }
    }
}
