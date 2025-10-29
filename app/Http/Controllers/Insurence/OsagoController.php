<?php

namespace App\Http\Controllers\Insurence;

use App\DTOs\OsagoApplicationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\Osago\OsagoApplicationRequest;
use App\Models\Order;
use App\Services\InsuranceApiService;
use App\Services\OrderService;
use App\Services\OsagoPriceCalculator;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

final class OsagoController extends Controller
{
    use HandlesInsuranceErrors;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly OsagoPriceCalculator $priceCalculator,
        private readonly InsuranceApiService $apiService
    ) {}

    public function main(): View
    {
        return view('pages.insurence.osago.main');
    }

    public function application(OsagoApplicationRequest $request): View|RedirectResponse
    {
        try {
            // Create DTO from validated request data
            $applicationData = OsagoApplicationData::fromRequest($request->validated());

            // Store data in session for GET requests (language switching and order creation)
            session([
                'osago_application_data' => $applicationData->toArray(),
            ]);

            // Redirect to application page
            return redirect()->route('osago.application.view', ['locale' => getCurrentLocale()]);
        } catch (\Exception $e) {
            return $this->handleGeneralError('osago', $e, 'application');
        }
    }

    public function applicationView(): View|RedirectResponse
    {
        if (!session()->has('osago_application_data')) {
            return $this->handleSessionNotFound('osago');
        }

        $applicationData = session('osago_application_data');
        return view('pages.insurence.osago.application', [
            'data' => $applicationData,
        ]);
    }

    public function storage(): RedirectResponse
    {
        try {
            if (!session()->has('osago_application_data')) {
                return $this->handleSessionNotFound('osago');
            }

            $applicationData = session('osago_application_data');

            Log::info('OSAGO storage: Processing application', [
                'has_application_data' => !empty($applicationData),
            ]);

            // Validate required keys exist
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

    public function payment($lang, Order $order): View
    {
        return view('pages.insurence.payment', compact('order'));
    }
}
