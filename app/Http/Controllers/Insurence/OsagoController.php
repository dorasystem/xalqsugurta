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

            $dataDTO = new OsagoApplicationData(
                applicant: $applicationData['applicant'],
                owner: $applicationData['owner'],
                details: $applicationData['details'],
                cost: $applicationData['cost'],
                vehicle: $applicationData['vehicle'],
                drivers: $applicationData['drivers'],
            );

            $result = $this->apiService->sendApplication($dataDTO->toApiFormat());

            if (!$result['success']) {
                return back()
                    ->withErrors(['error' => $result['error']])
                    ->withInput();
            }

            $apiResponse = $result['data'];
            $uuid = $apiResponse['UUID'] ?? $result['uuid'] ?? null;
            $finalAmount = $apiResponse['amount'] ?? $applicationData['cost']['insurancePremium'] ?? 0;
            $paymeUrl = $apiResponse['payme_url'] ?? null;
            $clickUrl = $apiResponse['click_url'] ?? null;

            $order = $this->orderService->createOrder([
                'product_name' => __('insurance.osago.product_name'),
                'amount' => $finalAmount,
                'state' => 0,
                'insurance_id' => $uuid ?? uniqid('osago_'),
                'phone' => $applicationData['applicant']['person']['phoneNumber'] ?? null,
                'insurances_data' => $applicationData,
                'insurances_response_data' => $apiResponse,
                'payme_url' => $paymeUrl,
                'click_url' => $clickUrl,
                'status' => Order::STATUS_NEW,
                'contractStartDate' => data_get($applicationData, 'details.startDate'),
                'contractEndDate' => data_get($applicationData, 'details.endDate'),
                'insuranceProductName' => __('insurance.osago.product_name'),
            ]);

            session()->forget(['osago_application_data']);

            return $this->redirectWithSuccess(
                'osago.payment',
                ['locale' => getCurrentLocale(), 'order' => $order->id],
                __('success.insurance.order_created')
            );
        } catch (\Exception $e) {
            return $this->handleOrderCreationError('osago', $e);
        }
    }

    public function payment($lang, Order $order): View
    {
        $paymeUrl = $order->payme_url ?? $order->insurances_response_data['payme_url'] ?? null;
        $clickUrl = $order->click_url ?? $order->insurances_response_data['click_url'] ?? null;

        return view('pages.insurence.osago.payment', compact('order', 'paymeUrl', 'clickUrl'));
    }
}
