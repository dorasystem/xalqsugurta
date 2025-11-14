<?php

namespace App\Services;

use App\DTOs\OsagoApplicationData;
use App\Models\Order;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Support\Facades\Log;

class OsagoStorageService
{
    use HandlesInsuranceErrors;

    public function __construct(private readonly OrderService $orderService,) {}

    public function handle()
    {
        try {
            if (!session()->has('osago_application_data')) {
                return $this->handleSessionNotFound('osago');
            }

            $applicationData = session('osago_application_data');

            Log::info('OSAGO storage: Processing application', [
                'has_application_data' => !empty($applicationData),
            ]);

            $requiredKeys = ['applicant', 'owner', 'details', 'cost', 'vehicle', 'drivers', 'govNumber', 'insurancePremium'];
            foreach ($requiredKeys as $key) {
                if (!isset($applicationData[$key])) {
                    Log::error("OSAGO storage: Missing required key: {$key}");
                    return $this->handleValidationError(__('errors.insurance.invalid_session_data'));
                }
            }

            // Create DTO from session data
            $dataDTO = new OsagoApplicationData(
                applicant: $applicationData['applicant'],
                owner: $applicationData['owner'],
                details: $applicationData['details'],
                cost: $applicationData['cost'],
                vehicle: $applicationData['vehicle'],
                drivers: $applicationData['drivers'],
                govNumber: $applicationData['govNumber'],
                insurancePremium: $applicationData['insurancePremium'],
            );

            // Send data to API using service
            $requestData = $dataDTO->toApiFormat();
            $result = $this->apiService->sendApplication($requestData);

            if (!$result['success']) {
                return back()
                    ->withErrors(['error' => $result['error']])
                    ->withInput();
            }

            $apiResponse = $result['data'] ?? null;

            Log::info('OSAGO storage: Creating order', [
                'has_api_response' => !empty($apiResponse),
            ]);

            // SECURITY: Re-verify price on final submission
            $finalPriceCheck = $this->priceCalculator->verifyPrice(
                submittedAmount: $applicationData['insurancePremium'],
                govNumber: $applicationData['govNumber'],
                vehicleTypeId: $applicationData['vehicle']['typeId'],
                period: $applicationData['cost']['contractTermConclusionId'],
                driverLimit: $applicationData['details']['driverNumberRestriction'] ? 'limited' : 'unlimited',
                tolerance: 100
            );

            if (!$finalPriceCheck) {
                Log::warning('OSAGO final price verification failed', [
                    'submitted' => $applicationData['insurancePremium'],
                    'session_data' => $applicationData,
                    'ip' => request()->ip(),
                ]);

                // Recalculate correct price
                $correctPrice = $this->priceCalculator->calculate(
                    govNumber: $applicationData['govNumber'],
                    vehicleTypeId: $applicationData['vehicle']['typeId'],
                    period: $applicationData['cost']['contractTermConclusionId'],
                    driverLimit: $applicationData['details']['driverNumberRestriction'] ? 'limited' : 'unlimited'
                );

                // Use server-calculated price
                $applicationData['insurancePremium'] = $correctPrice['amount'];
                $applicationData['cost']['insurancePremium'] = $correctPrice['amount'];
                $applicationData['cost']['insurancePremiumPaidToInsurer'] = $correctPrice['amount'];
            }

            // Create order
            $orderData = [
                'product_name' => __('insurance.osago.product_name'),
                'amount' => $applicationData['insurancePremium'] ?? 0,
                'state' => 0,
                'insurance_id' => $apiResponse['response']['result']['uuid'] ?? uniqid('osago_'),
                'phone' => $applicationData['applicant']['person']['phoneNumber'] ?? null,
                'insurances_data' => $applicationData,
                'insurances_response_data' => $apiResponse,
                'status' => Order::STATUS_NEW,
                'contractStartDate' => data_get($applicationData, 'details.startDate'),
                'contractEndDate' => data_get($applicationData, 'details.endDate'),
                'insuranceProductName' => __('insurance.osago.product_name'),
            ];

            $order = $this->orderService->createOrder($orderData);

            Log::info('OSAGO storage: Order created successfully', ['order_id' => $order->id]);

            // Clear session data after successful order creation
            session()->forget(['osago_application_data']);

            return $this->redirectWithSuccess(
                'payment.show',
                ['locale' => getCurrentLocale(), 'orderId' => $order->id],
                __('success.insurance.order_created')
            );
        } catch (\Exception $e) {
            return $this->handleOrderCreationError('osago', $e);
        }
    }
}
