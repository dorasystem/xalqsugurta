<?php

namespace App\Services;

use App\Models\Order;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class AccidentStorageService
{
    use HandlesInsuranceErrors;

    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function handle(): RedirectResponse
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
}
