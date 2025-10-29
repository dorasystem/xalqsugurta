<?php

namespace App\Http\Controllers\Insurence;

use App\DTOs\AccidentApplicationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\AccidentApplicationRequest;
use App\Models\Order;
use App\Services\InsuranceApiService;
use App\Services\OrderService;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class AccidentController extends Controller
{
    use HandlesInsuranceErrors;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly InsuranceApiService $apiService
    ) {
        // Configure API service for ACCIDENT product
        $baseUrl = (string) config('services.insurance.accident.endpoint', 'https://erspapi.e-osgo.uz/api/v3');
        $token = (string) config('services.insurance.accident.api_token');

        $this->apiService
            ->setEndpoint(rtrim($baseUrl))
            ->setProductType('ACCIDENT')
            ->setHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])
            ->setTimeout((int) config('services.insurance.accident.timeout', 10))
            ->setRetries((int) config('services.insurance.accident.retries', 3));
    }

    public function main(): View
    {
        return view('pages.insurence.accident.main');
    }

    public function application(AccidentApplicationRequest $request): View|RedirectResponse
    {
        try {
            // Create DTO from validated request data
            $applicationData = AccidentApplicationData::fromRequest($request->validated());

            // Send data to API using service
            $requestData = $applicationData->toApiFormat();
            $result = $this->apiService->sendApplication($requestData);

            if (!$result['success']) {
                return back()
                    ->withErrors(['error' => $result['error']])
                    ->withInput();
            }

            // Store data in session for GET requests (language switching and order creation)
            session([
                'accident_application_data' => $applicationData->toArray(),
                'accident_api_response' => $result['data'] ?? null,
            ]);

            // Pass the structured data to the view
            return view('pages.insurence.accident.application', [
                'applicationData' => $applicationData->toArray(),
                'apiResponse' => $result['data'] ?? null,
            ]);
        } catch (\Exception $e) {
            return $this->handleGeneralError('accident', $e, 'application');
        }
    }


    public function applicationView(): View|RedirectResponse
    {
        if (!session()->has('accident_application_data')) {
            return $this->handleSessionNotFound('accident');
        }

        $applicationData = session('accident_application_data');
        return view('pages.insurence.accident.application', [
            'applicationData' => $applicationData,
        ]);
    }

    public function storage(): RedirectResponse
    {
        try {
            if (!session()->has('accident_application_data')) {
                return $this->handleSessionNotFound('accident');
            }

            $applicationData = session('accident_application_data');
            $apiResponse = session('accident_api_response');

            Log::info('Accident storage: Creating order', [
                'has_application_data' => !empty($applicationData),
                'has_api_response' => !empty($apiResponse),
            ]);

            // Create order
            $orderData = [
                'product_name' => __('insurance.accident.product_name'),
                'amount' => $applicationData['insuranceAmount'] ?? 0,
                'state' => 0,
                'insurance_id' => $apiResponse['id'] ?? uniqid('acc_'),
                'phone' => $applicationData['phone'] ?? null,
                'insurances_data' => $applicationData,
                'insurances_response_data' => $apiResponse['response']['result'] ?? null,
                'status' => Order::STATUS_NEW,
                'contractStartDate' => $applicationData['paymentStartDate'] ?? null,
                'contractEndDate' => $applicationData['paymentEndDate'] ?? null,
                'insuranceProductName' => config('services.insurance.accident.product_name', 'Jismoniy shaxslarni baxtsiz hodisalardan ehtiyot shart sug‘urtalash'),
                'polic_id_number' => $apiResponse['response']['result']['contractUuid'],
            ];

            $order = $this->orderService->createOrder($orderData);

            Log::info('Accident storage: Order created successfully', ['order_id' => $order->id]);

            // Clear session data after successful order creation
            session()->forget(['accident_application_data', 'accident_api_response']);

            return $this->redirectWithSuccess(
                'payment.show',
                ['locale' => getCurrentLocale(), 'orderId' => $order->id],
                __('success.insurance.order_created')
            );
        } catch (\Exception $e) {
            return $this->handleOrderCreationError('accident', $e);
        }
    }

    public function calculation(): View
    {
        return view('pages.insurence.accident.calculation');
    }
}
