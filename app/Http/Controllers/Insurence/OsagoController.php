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

            $requiredKeys = ['applicant', 'owner', 'details', 'cost', 'vehicle', 'drivers', 'govNumber', 'insurancePremium'];
            foreach ($requiredKeys as $key) {
                if (!isset($applicationData[$key])) {
                    return $this->handleValidationError(__('errors.insurance.invalid_session_data'));
                }
            }

            // Decode drivers JSON safely
            $drivers = [];
            if (!empty($applicationData['drivers']) && is_array($applicationData['drivers'])) {
                foreach ($applicationData['drivers'] as $driverJson) {
                    $decoded = is_string($driverJson) ? json_decode($driverJson, true) : $driverJson;
                    if ($decoded) $drivers[] = $decoded;
                }
            }
            $applicationData['drivers'] = $drivers;

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

            $requestData = $dataDTO->toApiFormat();
            $result = $this->apiService->sendApplication($requestData);

            if (!$result['success']) {
                return back()->withErrors(['error' => $result['error']])->withInput();
            }

            $apiResponse = $result['data'] ?? null;

            // SECURITY: Re-verify price
            $finalPriceCheck = $this->priceCalculator->verifyPrice(
                submittedAmount: $applicationData['insurancePremium'],
                govNumber: $applicationData['govNumber'],
                vehicleTypeId: $applicationData['vehicle']['typeId'],
                period: $applicationData['cost']['contractTermConclusionId'],
                driverLimit: $applicationData['details']['driverNumberRestriction'] ? 'limited' : 'unlimited',
                tolerance: 100
            );

            if (!$finalPriceCheck) {
                $correctPrice = $this->priceCalculator->calculate(
                    govNumber: $applicationData['govNumber'],
                    vehicleTypeId: $applicationData['vehicle']['typeId'],
                    period: $applicationData['cost']['contractTermConclusionId'],
                    driverLimit: $applicationData['details']['driverNumberRestriction'] ? 'limited' : 'unlimited'
                );
                $applicationData['insurancePremium'] = $correctPrice['amount'];
                $applicationData['cost']['insurancePremium'] = $correctPrice['amount'];
                $applicationData['cost']['insurancePremiumPaidToInsurer'] = $correctPrice['amount'];
            }

            // Create order
            $order = $this->orderService->createOrder([
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
            ]);

            session()->forget('osago_application_data');

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
